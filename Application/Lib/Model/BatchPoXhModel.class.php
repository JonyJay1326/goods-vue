<?php
/**
 * 批量po
 * 
 */
class BatchPoXhModel extends ExcelOperationModel
{
    const IN_CONFIRMATION = 'N000550200'; //确认中
    const PENDING_PAYMENT = 'N000550300'; //代付款

    // 成功po的数据
    public $success_data = null;
    
    // 失败的po数据
    public $error_data = null;
    
    // 能po
    public $can_po = null;
    
    // 不能po
    public $can_not_po = null;
    
    public function __construct($excel_path)
    {
        parent::__construct($excel_path);
    }
    
    public static function state()
    {
        return [
            'N000550100' => '待确认',
            'N000550200' => '确认中',
            'N000550300' => '待付款',
            'N000550400' => '待发货',
            'N000550500' => '待收货',
            'N000550600' => '已收货',
            'N000550700' => '已付尾款',
            'N000550800' => '交易成功',
            'N000550900' => '交易关闭',
            'N000551000' => '交易取消',
        ];
    }
    
    /**
     * 批量po主函数,现货订单
     * 
     */
    public function main()
    {
        // 分块读取，不用一次性将整个Excel表读入到内存中
        $startRow = $this->start_row;
        // 性能优化
        $this->setOptions();
        // 成功更新的条数
        $success_update = 0;
        // 失败的条数
        $fail_update = 0;
        // r
        for ($startRow; $startRow <= (int)$this->max_row; $startRow ++) {
            $id = (String)$this->obj_excel->getActiveSheet($this->sheet)->getCell("A" . $startRow)->getValue();
            if ($id) $ord_ids ['A'.$startRow] = $id;
        }
        // Todo  
        if ($ord_ids) {
            // id拼接
            foreach ($ord_ids as $key => $value) {
                $ids .= "'" . $value . "'" . ',';
            }
            $ids = rtrim($ids, ',');
            $data['ORD_STAT_CD'] = static::PENDING_PAYMENT;
            $model = M('ms_ord','tb_');
            $ORD_STAT_CD = $data['ORD_STAT_CD'];
            // r 获取到ids集合的数据
            $ret = $model->where('ORD_ID in ('.$ids.')')->select();
            // 取得每个ord_id的状态值
            $ret = array_column($ret, 'ORD_STAT_CD', 'ORD_ID');
            // key-value互换,构成以ord_id为key，Excel表坐标为value
            $ord_ids = array_flip($ord_ids);
            // 将不存在的订单id筛选出来
            foreach ($ord_ids as $o_id => $o_index) {
                if (!$ret [$o_id]) {
                    $n_search_data [$o_index] = $o_id; 
                }
            }
            $ord_ids = array_flip($ord_ids);
            // 数据筛选
            if (!$this->filterData($ret, $ord_ids)) {
                return [
                    'state' => 0, 'message' => '空文件', 'check_data' => $this->can_not_po, 'n_search_data' => $n_search_data
                ];
            }
            // start transaction
            $model->startTrans();
            // 能po的，重新组装ids
            if ($this->can_po) {
                foreach ($this->can_po as $key => $value) {
                    foreach ($value as $key_index => $info) {
                        $tmp .= "'" . $info['ord_id'] . "'" . ',';    
                    }
                }
                $tmp = rtrim($tmp, ',');
            }

            if($model->data($data)->where('ORD_ID in ('.$tmp.')')->save() === count($this->can_po)){
                $info['message'] = L('批量PO确认成功');
                $info['status'] = '待付款';
                $log = A('Log');
                $log->IndexExtend($ord_ids, static::PENDING_PAYMENT, 'PO确认成功');
                $model->commit();
                $msg = ['state' => 1, 'message' => count($this->can_po), 'success_data' => $this->can_po, 'check_data' => $this->can_not_po, 'n_search_data' => $n_search_data];
            } else {
                $model->rollback();
                $info['message'] = L('批量PO确认失败');
                $info['status'] = '代付款';
                $msg = ['state' => 0, 'message' => '批量po确认失败，有B5CID无法确认', 'check_data' => $this->can_not_po, 'n_search_data' => $n_search_data];
                $log = A('Log');
            }
            $log = A('Log');
            $log->index($ord_ids, $ORD_STAT_CD, $info['message']);
        } else {
            $msg = [
                'state' => 0, 'message' => '空文件'
            ];
        }
        return $msg;
    }
    
    /**
     * 数据筛选
     * 静态操作，从内存中筛选出不满足的数据
     * @param $ret 批量上传po的ord_ids所获取到的集合
     */
    public function filterData($ret, $ord_ids)
    {
        $ord_ids = array_flip($ord_ids);
        
        foreach ($ret as $key => $value) {
            if ($value == static::IN_CONFIRMATION) {
                $cp = null;
                $cp[$ord_ids[$key]]['ord_id'] = $key;
                $cp[$ord_ids[$key]]['message'] = 'po成功';
                $this->can_po [] = $cp;
            } else {
                $ar = null;
                $ar[$ord_ids[$key]]['ord_id'] = $key;
                $ar[$ord_ids[$key]]['message'] = 'po失败，状态错误，当前单号状态为：【' . static::state()[$value] . '】 当前Code码为：【' . $value . '】';
                $this->can_not_po [] = $ar;
            }
        }
        
        if (!$this->can_po) return false;
        return true;
    }
}