<?php

/**
 * 订单入库模型
 * 
 */
class TbWmsBillModel extends BaseModel
{
    protected $trueTableName = 'tb_wms_bill';
    public $bill;//订单信息
    public $guds;//商品信息
    private $_msgCode;//消息编码
    private $_errorInfo;//错误信息
    private $_lastBillId;//最后一次插入的订单id
    private $_requestData;//请求接口的数据
    private $_depr = '+';//类型标识符号，+为入库，-为出库
    private $_lrSuccessCode = 2000;//接口返回成功的编码，固定不变
    private $_inCode;//入库类型的code值
    private $_outCode;//出库类型的code值
    private $_billId;//出入库单号bill_id系统生成
    private $_flag = false;//是否虚拟入库
    const IN_OUR_STORAGE = 1;//入库规则，真实入库
    const NO_IN_STORAGE  = 0;//虚拟入库，入库即出库，虚拟仓N000680800
    
    public function __construct()
    {
        parent::__construct();
        //初始化出入库code吗
        $this->outAndInCode();
    }
    
    public $bill_attributes = [
        ['bill_id', 'require' => 'in', 'message' => '出入库单号'],
        ['bill_type', 'require' => 'both', 'message' => '收发类别'],
        ['link_bill_id', 'require' => 'none', 'message' => 'B5C单号'],
        ['warehouse_rule', 'require' => 'in', 'message' => '入库规则'],// 数据库无对应字段
        ['batch', 'require' => 'in', 'message' => '入库批次号'],
        ['sale_no', 'require' => 'none', 'message' => '对应销售单号'],// 数据库无对应字段
        ['channel', 'require' => 'both', 'message' => '渠道'], // 目前数据库保存的是值，不是对应的编码，这里需要商定！！！
        ['supplier', 'require' => 'in', 'message' => '供应商名称'],
        ['purchase_logistics_cost', 'require' => 'none', 'message' => '采购端物流费用'],// 数据库无对应字段
        ['warehouse_id', 'require' => 'none', 'message' => '仓库ID'],
        ['total_cost', 'require' => 'in', 'message' => '入库总成本'],// 数据库无对应字段
        ['bill_state', 'require' => 'none', 'message' => '单据状态'],
        ['SALE_TEAM', 'require' => 'none', 'message' => '销售团队'],
        ['SP_TEAM_CD', 'require' => 'none', 'message' => '采购团队'],
        ['CON_COMPANY_CD', 'require' => 'in', 'message' => '我方公司（公司code）'],
    ];
    
    public $guds_attributes = [
        ['GSKU', 'require' => 'both', 'message' => 'GSKU(SKU)'],
        ['taxes', 'require' => 'both', 'message' => '税率'],
        ['should_num', 'require' => 'none', 'message' => '应发数量'],
        ['send_num', 'require' => 'both', 'message' => '实发数量'],
        ['production_date', 'require' => 'none', 'message' => '生产日期(物品校期有效型必填)'],
        ['price', 'require' => 'both', 'message' => '单价'],
        ['currency_id', 'require' => 'both', 'message' => '币种'],// 数据库无对应字段
        ['currency_time', 'require' => 'both', 'message' => '交易发生时间，对应币种的汇率时间'],// 数据库无对应字段
        ['batch', 'require' => 'none', 'nessage' => '批次'],
    ];
    
    /**
     * 错误码
     * 
     */
    public function msgCode()
    {
        return [
            '10000000' => '入库成功',
            '10000001' => '订单或商品数据不能为空',
            '10000010' => '参数缺失',
            '10000101' => '订单写入失败',
            '10000110' => '接口数据写入失败',
            '10000111' => '出库成功',
            '10001011' => '虚拟入库成功(同时出库)',
            '11111111' => '出入库类型参数错误，未知类型',
        ];
    }
    
    /**
     * 出入库操作
     * 出库与入库操作类似，参数也类似，针对出库有不同的操作，需要扩展函数来包装它
     * @param $data 用户提交的数据，包含订单信息，与商品信息，是一个多维数组
     * @return Array 根据上面函数返回的状态判断操作是否成功
     */
    public function outAndInStorage($data)
    {
        $this->checkOutOrInType($data ['bill']['bill_type']);
        if ($this->_depr == '+' and $data ['bill']['warehouse_rule'] == self::NO_IN_STORAGE) {
            $data ['bill']['warehouse_id'] = 'N000680800';
        }
        //判断是否为空数据
        if (!empty($data ['bill']) and !empty($data ['guds'])) {
            $this->bill = $data ['bill'];
            $this->guds = $data ['guds'];
            $ndata = $this->checkWarehouseRule($data);
            if ($this->_flag) {
                $isInOrOutSuccess = false;
                $this->out_and_in_operation();
                if ($this->_msgCode == 10000000) $isInOrOutSuccess = true;
                $this->bill = $ndata ['bill'];
                $this->guds = $ndata ['guds'];
                $this->bill ['warehouse_rule'] = 5;// 虚拟出库
                $this->out_and_in_operation();
                if ($this->_msgCode == 10000111) $isInOrOutSuccess = true;
                if ($isInOrOutSuccess) $this->_msgCode = 10001011;
            } else {
                $this->out_and_in_operation();    
            }
        } else {
            //传递的数据不全，直接返回错误
            $this->_msgCode = 10000001;
        }
        return $this->parseInfo();
    }
    
    /**
     * 出入库具体操作
     * 
     */
    public function out_and_in_operation()
    {
        // 生成出入库单号
        $stock = A('Home/Stock');
        $this->bill ['bill_id'] = $stock->get_bill_id($this->bill ['bill_type'], $this->bill ['bill_date']);
        $this->startTrans();
        $ret = null;
        try {
            if ($this->_validata()) { //数据验证
                if ($this->parseBill()) { //订单写入
                    if ($this->parseGuds()) { //商品写入
                        $this->setRequestData(['bill' => $this->bill, 'guds' => $this->guds]);
                        $ret = $this->multParse();//接口请求
                        if ($ret ['code'] === $this->_lrSuccessCode) {
                            //返回成功
                            $this->_depr == '+'?$this->_msgCode = 10000000:$this->_msgCode = 10000111;
                            $this->commit();
                        } else {
                            //接口返回失败状态码
                            $this->_msgCode = 10000110;
                            $this->_errorInfo = $ret['msg'];
                            $ret ['msg'] = '接口异常';
                        }
                    } else {
                        //商品写入失败
                        $this->_msgCode = 10000101;
                        $this->_errorInfo = $this->getError();
                    }
                } else {
                    //订单写入失败
                    $this->_msgCode = 10000101;
                    $this->_errorInfo = $this->getError();
                }
            } else {
                //数据验证失败
                $this->_msgCode = 10000010;
            }
        } catch (\Exception $e) {
            //数据回滚
            $this->rollback();
        }
        $this->setResponseData(['origin' => $ret, 'local' => $this->_errorInfo]);
        $this->_catchMe();
    }
    
    /**
     * 入库和出库的判断依据
     * 将出库与入库存为两个数组
     */
    public function outAndInCode()
    {
        $model = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] =  '入库类型';
        $this->_inCode = array_column($model->field('CD, CD_VAL')->where($where)->select(), 'CD_VAL', 'CD');
        $where['CD_NM'] = '出库类型';
        $this->_outCode = array_column($model->field('CD, CD_VAL')->where($where)->select(), 'CD_VAL', 'CD');
    }
    
    /**
     * 订单处理
     * @return 成功返回插入数据id，失败返回false
     */
    public function parseBill()
    {
        if ($this->add($this->bill)) {
            return $this->_lastBillId = $this->getLastInsID();
        }
        return false;
    }
    
    /**
     * 商品处理
     * 商品处理，计算商品的基本数据，写入商品表
     * @return boolean
     */
    public function parseGuds()
    {
        $requestData = [];
        foreach ($this->guds as $key => &$value) {
            $value ['bill_id'] = $this->_lastBillId;
            $value ['unit_price'] = $value ['price'];//含税单价
            $value ['no_unit_price'] = bcsub($value ['price'], bcmul($value ['price'], $value ['taxes'], 2), 2);//不含税单价
            $value ['unit_money'] = bcmul($value ['unit_price'], $value ['send_num'], 2);//含税金额
            $value ['no_unit_money'] = bcmul($value ['no_unit_price'],  $value ['send_num'], 2);//去税金额
            $value ['duty'] = bcsub($value ['unit_money'], $value ['no_unit_money'], 2);//税额
            $value ['add_time'] = Date('Y-m-d H:i:s', time());//添加时间
            $data = null;
            $data ['gudsId']   = substr($value ['GSKU'], 0, -2);
            $data ['skuId']    = trim($value ['GSKU']); 
            $data ['changeNm'] = (int)($this->_depr . $value ['send_num']);
            $data ['billId'] = $this->bill['bill_id'];
            $data ['productionDate'] = $value ['production_date'];
            $data ['createUserId'] = $_SESSION['userId'];
            $data ['saleTeamCode'] = $this->bill ['SALE_TEAM'];
            $data ['spTeamCd'] = $this->bill ['SP_TEAM_CD'];
            $data ['createTime'] = $this->bill ['zd_date'];
            if ($this->_depr == '-') $data ['batchCode'] = $value ['batch'];
            $requestData [$key] = $data;
        }
        $stream = M('_wms_stream', 'tb_');
        if ($stream->addAll($this->guds)) {
            $this->_setRequestData($requestData);
            return true;    
        }
        return false;
    }
    
    /**
     * 入库规则检测
     * 入库+入库规则为不入我方实体库，则断定为虚拟入库，其他方案均为真实出入库
     * 当为虚拟入库时，才走该函数 
     */
    public function checkWarehouseRule($data)
    {
        if ($data ['bill']['warehouse_rule'] == self::NO_IN_STORAGE and $this->bill ['warehouse_id'] == 'N000680800' and $this->_depr = '+') {
            $this->_flag = true;
            $data ['bill']['bill_type'] = $this->matchStorageType($data ['bill']['bill_type']);
        } else {
            $this->_flag = false;
        }
        return $data;
    }
    
    /**
     * 数据出入库类型判断
     * 根据用户传递的bill_type参数，调整$this->_depr为正还是负，如果用户传递的参数在已知参数中不存在则抛出异常
     */
    public function checkOutOrInType($bill_type)
    {
        if (isset($this->_inCode[$bill_type])) {
            $this->_depr = '+';
        } elseif (isset($this->_outCode[$bill_type])) {
            $this->_depr = '-';
        } else {
            $this->_msgCode = 11111111;
            return false;
        }
        return true;
    }
    
    /**
     * 检查用户传递的仓库是否真实存在，并且启用
     * 
     */
    public function checkWarehouse($warehouse_id)
    {
        $ret = BaseModel::getCd('N000680');
        if ($ret [$warehouse_id]) return true;
        return false;
    }
    
    /**
     * 虚拟入库时，同时出库需要匹配出相对应的出入类型
     * @param $bill_type 入库类型
     * @param $bill_type 出库类型
     */
    public function matchStorageType($bill_type)
    {
        $cd_val = $this->_inCode [$bill_type];
        $ncd_val = mb_substr($cd_val, 0, -2, 'utf-8') . '出库';
        $code = 'N000950100'; //默认为其他出库
        return $code;
    }
    
    /**
     * $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state . $changeNm;
     * 并发请求,商品入库存，访问java接口
     * 
     */
    public function multParse()
    {
        $url = HOST_URL_API . '/guds_stock/update_total.json?';
        foreach ($this->_requestData as $key => $value) {
            $data [] = $value;
        }
        $ret = curl_get_json($url, json_encode($data));
        //$ret = ZWebHttp::multiRequest($data);// 并发操作curl_get_json
        return json_decode($ret, true);
    }
    
    /**
     * 消息回送
     * @return code code值，预先定义。msg 信息类型文本提示。info 具体提示
     */
    public function parseInfo()
    {
        return ['code' => $this->_msgCode, 'msg' => $this->msgCode()[$this->_msgCode], 'info' => $this->_errorInfo];    
    }
    
    /**
     * 设置请求数据
     * @param $requstData 请求接口的数据
     */
    private function _setRequestData($requestData)
    {
        $this->_requestData = $requestData;
    }
    
    /**
     * 数据验证
     * 对订单与商品必填的数据进行校验，若有缺失则返回相对应的错误信息
     * @return boolean
     */
    private function _validata()
    {
        if ($this->_depr == '+') $flag = 'in';
        else $flag = 'out';
        foreach ($this->bill_attributes as $k => $value) {
            if (!isset($this->bill[$value [0]]) and ($value ['require'] == $flag or $value ['require'] == 'both')) $this->_errorInfo ['bill'][$value [0]] = $value ['message'];
        }
        foreach ($this->guds as $s => $j) {
            foreach ($this->guds_attributes as $k => $value) {
                if (!isset($this->guds [$s][$value [0]]) and ($value ['require'] == $flag or $value ['require'] == 'both')) $this->_errorInfo ['guds'][$s][$value [0]] = $value ['message']; 
            }
        }
        if (!$this->checkWarehouse($this->bill['warehouse_id'])) $this->_errorInfo ['warehouse_id'] = '仓库无效';
        if (!$this->checkOutOrInType($this->bill['bill_type'])) $this->_errorInfo ['warehouse_id'] = '出入库类型无效';
        if ($this->_errorInfo) return false;
        return true;
    }
}