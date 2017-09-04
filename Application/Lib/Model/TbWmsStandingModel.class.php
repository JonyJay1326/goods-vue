<?php

class TbWmsStandingModel extends BaseModel
{
    protected $trueTableName = 'tb_wms_standing';

    protected $_link = [
        'guds_opt' => [
            'mapping_type' => HAS_ONE,
            'class_name'   => 'TbMsGudsOpt',
            'foreign_key'  => 'GUDS_ID',
        ],
        'guds' => [
            'mapping_type' => BELONGS_TO,
            'class_name'   => 'Guds',
            'foreign_key'  => 'GUDS_ID',
        ],
    ];

    private $_inData; // 写入的数据
    private $_outData; // 写出的数据
    private $_data;
    private $_errorInfo;
    private $_msgCode;
    private $_requestData;
    private $_responseData;

    const DATA_IN = 0;
    const DATA_OUT = 1;

    public function search($params)
    {
        $conditions = [];
        if ($params ['SKU']) {
            $where ['tb_wms_standing.SKU_ID'] = ['like', "%" . $params ['SKU'] . "%"];
            $where ['tb_ms_guds_opt.GUDS_OPT_CODE'] = ['like', "%" . $params ['SKU'] . "%"];
            $where ['tb_ms_guds_opt.GUDS_OPT_UPC_ID'] = ['like', "%" . $params ['SKU'] . "%"];
            $where ['tb_ms_guds.GUDS_CODE'] = ['like', "%" . $params ['SKU'] . "%"];
            $where['_logic'] = 'or';
            $conditions [] = $where;
        }
        empty($params ['GUDS_CNS_NM']) or $conditions ['tb_ms_guds.GUDS_NM'] = ['like', "%" . $params ['GUDS_CNS_NM'] . "%"];
        empty($params ['DELIVERY_WAREHOUSE']) or $conditions []['tb_ms_guds.DELIVERY_WAREHOUSE'] = ['eq', $params ['DELIVERY_WAREHOUSE']];
        // 库存可以为0，在途不为0
        //$conditions ['tb_wms_standing.total_inventory'] = ['neq', 0];
        //$conditions ['tb_wms_standing.on_way'] = ['neq', 0];
        !empty($params ['DELIVERY_WAREHOUSE']) or $conditions ['tb_ms_guds.DELIVERY_WAREHOUSE'] = ['neq', 'null'];
        $conditions['tb_wms_standing.channel'] = array('eq', 'N000830100');
        $cond_t [] = [
            'tb_wms_standing.total_inventory' => ['neq', 0],
        ];
        $cond_t ['_logic'] = 'or';
        $cond_t [] = [
            'tb_wms_standing.total_inventory' => ['eq', 0],
            'tb_wms_standing.on_way' => ['neq', 0],
        ];
        if ($params ['sku_none']) {
            //unset($conditions ['tb_wms_standing.total_inventory']);
            $cond_t = null;
        } else {
            $conditions [] = $cond_t;
        }

        return $conditions;
    }

    public $attributes = [
        ['SKU_ID', 'require' => 'both', 'message' => 'SKU_ID'],
        ['GUDS_ID', 'require' => 'none', 'message' => 'SPU_ID'],
        ['channel', 'require' => 'none', 'message' => '渠道'],
        ['CHANNEL_SKU_ID', 'require' => 'none', 'message' => '渠道SKU_ID'],
        ['TYPE', 'require' => 'both', 'message' => '新增，减少(0新增，1减少)'],
    ];

    /**
     * 错误码
     *
     */
    public function msgCode()
    {
        return [
            '10000001' => '无数据',
            '10000010' => '参数缺失',
            '10000101' => '新增在途、在途金失败',
            '10000110' => '减少在途、在途金失败',
            '10000111' => '操作成功',
            '10001011' => '操作失败',
        ];
    }

    /**
     * 在途与在途金写入
     *
     */
    public function onWayAndOnWayMoney($data)
    {
        if ($data) {
            //$this->_data = $data;
            $this->setRequestData($data);
            $this->main();
        } else {
            $this->_msgCode = 10000001;
        }
        $this->setResponseData($ret = $this->parseInfo());
        $this->_catchMe();
        return $ret;
    }

    public function main()
    {
        $this->startTrans();
        try {
            if ($this->_validata()) {
                $this->classification(); //数据分类
                if ($this->writeData()) {
                    $this->commit();
                    $this->_msgCode = 10000111;
                } else {
                    $this->rollback();
                }
            } else {
                $this->_msgCode = 10000010;
            }
        } catch (\Exception $e) {
            $this->rollback();
        }
    }

    public function writeData()
    {
        $inOk = true;
        $outOk = true;
        if (empty($this->_inData) and empty($this->_outData)) {
            $isok = false;
            $this->_msgCode = 10000001;
        } else {
            if ($this->_inData) {
                foreach ($this->_inData as $k => $v) {
                    $where ['SKU_ID'] = ['eq', $v ['SKU_ID']];
                    $where ['channel'] = ['eq', $v ['channel']];
                    $where ['CHANNEL_SKU_ID'] = ['eq', $v ['CHANNEL_SKU_ID']];
                    if (!$this->where($where)->setInc('on_way', $v ['on_way']) or !$this->where($where)->setInc('on_way_money', $v ['on_way_money'])) {
                        $inOk = false;
                        $info = [];
                        $info ['SKU_ID'] = $v['SKU_ID'];
                        $info ['MSG'] = $this->msgCode()[10000101] . '，未查询到数据';
                        $this->_errorInfo [] = $info;
                    }
                }
            }
            if ($this->_outData) {
                foreach ($this->_outData as $k => $v) {
                    $where ['SKU_ID'] = ['eq', $v ['SKU_ID']];
                    $where ['channel'] = ['eq', $v ['channel']];
                    $where ['CHANNEL_SKU_ID'] = ['eq', $v ['CHANNEL_SKU_ID']];
                    if (!$this->where($where)->setDec('on_way', $v ['on_way']) or !$this->where($where)->setDec('on_way_money', $v ['on_way_money'])) {
                        $outOk = false;
                        $info = [];
                        $info ['SKU_ID'] = $v['SKU_ID'];
                        $info ['MSG'] = $this->msgCode()[10000110] . '，未查询到数据';
                        $this->_errorInfo [] = $info;
                    }
                }
            }
        }
        if ($inOk and $outOk) {
            $this->_msgCode = 10000111;
            return true;
        }
        $this->_msgCode = 10001011;
        return false;
    }

    /**
     * 数据分类、填充
     *
     */
    public function classification()
    {
        foreach ($this->getRequestData() as $key => $info) {
            if ($info['TYPE'] == self::DATA_IN) {
                // 数据优先填充
                if (!isset($info ['channel'])) $info ['channel'] = 'N000830100';
                if (!isset($info ['CHANNEL_SKU_ID'])) $info ['CHANNEL_SKU_ID'] = '0';
                unset($info ['TYPE']);
                $this->_inData [] = $info;
            } else {
                if (!isset($info ['channel'])) $info ['channel'] = 'N000830100';
                if (!isset($info ['CHANNEL_SKU_ID'])) $info ['CHANNEL_SKU_ID'] = '0';
                unset($info ['TYPE']);
                $this->_outData [] = $info;
            }
        }
    }

    /**
     * 数据验证
     * 对订单与商品必填的数据进行校验，若有缺失则返回相对应的错误信息
     * @return boolean
     */
    private function _validata()
    {
        foreach ($this->getRequestData() as $key => $info) {
            foreach ($this->attributes as $k => $value) {
                if (!isset($info [$value [0]]) and $value ['require'] == 'both') $this->_errorInfo [][$info['SKU_ID']][$value [0]] = $value ['message'];
            }
        }
        if ($this->_errorInfo) return false;
        return true;
    }

    /**
     * 消息回送
     * @return code code值，预先定义。msg 信息类型文本提示。info 具体提示
     */
    public function parseInfo()
    {
        if (is_array($this->_msgCode)) {
            foreach ($this->_msgCode as $key => $v) {
                $msg .= $this->msgCode()[$v] . ':';
            }
        } else {
            $msg = $this->msgCode()[$this->_msgCode];
        }
        $ret = ['code' => $this->_msgCode, 'msg' => $msg, 'info' => $this->_errorInfo];
        return $ret;
    }
}