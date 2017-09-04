<?php

class LogAction extends Action
{
    public $errorMsg;
    
    public function Index($ORD_ID = '',$ORD_STAT_CD = '',$content = ''){
        if($ORD_STAT_CD === ''){
            $ORD_STAT_CD = M('ms_ord','tb_')->where('ORD_ID = "'.$ORD_ID.'"')->field('ORD_STAT_CD')->find();
            $ORD_STAT_CD = $ORD_STAT_CD['ORD_STAT_CD'];
        }
        //订单操作日志
        $his = M('ms_ord_hist','sms_');
        $data['ORD_NO'] = $ORD_ID;
        //$ORD_HIST_SEQ = $his->where('ORD_NO = "'.$ORD_ID.'"')->field('ORD_HIST_SEQ')->order('ORD_HIST_SEQ desc')->find();
        $ORD_HIST_SEQ = time();
        //if(isset($ORD_HIST_SEQ['ORD_HIST_SEQ']) ){
//            $ORD_HIST_SEQ = intval($ORD_HIST_SEQ['ORD_HIST_SEQ']) + 1;
//        }else{
//            $ORD_HIST_SEQ = 1;
//        }
        $data['ORD_HIST_SEQ'] = $ORD_HIST_SEQ;
        $data['ORD_STAT_CD'] = $ORD_STAT_CD;
        $data['ORD_HIST_WRTR_EML'] = $_SESSION['m_loginname'];
        $data['ORD_HIST_REG_DTTM'] = date('Y-m-d H:i:s', time());
        $data['ORD_HIST_HIST_CONT'] = $content;
        $data['updated_time'] = date('Y-m-d H:i:s', time());
        if($his->data($data)->add()){   //写入订单操作日志表ms_ord_hist
            return true;
        }else{
            $this->errorMsg = $his->getError();
            return false;
        }
    }
    
    /**
     * 更新日志
     * 用于供应商与客户管理模块
     * 当营业执照号发生变化时，需要同时更新日志信息
     * @param $historySpCharterNo 历史营业执照号
     * @param $type               数据类型，0供应商，1客户管理
     * @param $newSpCharterNo     新的营业执照号
     * 
     */
    public function updateLog($historySpCharterNo, $type, $newSpCharterNo)
    {
        $model = M('ms_ord_hist','sms_');
        $data ['ORD_NO'] = $newSpCharterNo;
        $where ['ORD_STAT_CD'] = $type;
        $where ['ORD_NO'] = $historySpCharterNo;
        if ($model->where($where)->save($data)) {
            return true;
        }
        return $model->getError();
    }

    public function recordSyslog(){

    }
    
    /**
     * 用于批量写入
     * 
     */
    public function IndexExtend($ORD_ID, $ORD_STAT_CD, $content = ''){
        $temp = null;
        // 如果不是数组，改装为数组
        if (!is_array($ORD_ID)) {
            $temp [] = $ORD_ID;
        } else {
            $temp = $ORD_ID;
        }
        //订单操作日志
        $his = M('ms_ord_hist','sms_');
        // 拼接 id
//        foreach ($temp as $key => $value) {
//            $ids .= "'" . $value . "'" . ',';
//        }
//        $ids = rtrim($ids, ',');
        // 每增加一条相同数据ORD_HIST_SEQ增1
        //var_dump($ids);exit;
        //$ORD_HIST_SEQ = $his->where('ORD_NO in ('.$ids.')')->field('ORD_HIST_SEQ, ORD_NO')->order('ORD_HIST_SEQ ASC')->select();
        //$data = null;
        //$ORD_HIST_SEQ = array_column($ORD_HIST_SEQ, 'ORD_HIST_SEQ', 'ORD_NO');
        foreach ($temp as $key => $value) {
            $data = null;
            $data['ORD_NO'] = $value;
            $data['ORD_STAT_CD'] = $ORD_STAT_CD;
            $data['ORD_HIST_WRTR_EML'] = $_SESSION['m_loginname'];
            $data['ORD_HIST_REG_DTTM'] = date('Y-m-d H:i:s', time());
            $data['ORD_HIST_HIST_CONT'] = $content;
            $data['updated_time'] = date('Y-m-d H:i:s', time());
            $data['ORD_HIST_SEQ'] = time();
            //if ($value) {
//                $data['ORD_HIST_SEQ'] = intval($value) + 1;
//            } else {
//                $data['ORD_HIST_SEQ'] = 1;
//            }
            $ret [] = $data;
        }
        if($his->addAll($ret)){
            return true;
        }else{
            return false;
        }
    }
}

//class LogExtendAction extends LogAction
//{
//    copy example:
//    // 批量添加数据，使用二维数组存储数据，需要调用者自己组织好数据
//    // 数据结构，参数需按此结构进行传递
//    $dataList [] = [
//        'ord_id_01' => [
//            'ord_stat_cd' => '',
//            'content' => 
//        ],
//        'ord_id_02' => [
//            'ord_stat_cd' => '',
//            'content' => 
//        ],
//    ];
//    
//    $dataList[] = [
//        'ORD_HIST_SEQ'      => '',
//        'ORD_STAT_CD'       => '',
//        'ORD_HIST_WRTR_EML' => '',
//        'ORD_HIST_REG_DTTM' => '',
//        'ORD_HIST_HIST_CONT'=> '',
//        'updated_time'      => '',  // 请注意，时间并没有使用时间戳，依照UTC中国时区格式
//    ];
//    $User->addAll($dataList);
//    /**
//     * 增加批量操作
//     * 1、如果未传递当前单的状态code，则在数据库中查询出来
//     * 2、获取排序，如果在历史表中未查到该单号，则排序从1开始，否就在原排序基础上+1
//     * 3、写入数据库
//     * @param String $ORD_ID 订单号支持数组
//     * @param String $ORD_STAT_CD 订单状态码
//     * @param String $content 备注  
//     * 
//     */
//    public function Index($ORD_ID = '',$ORD_STAT_CD = '',$content = ''){
//        if($ORD_STAT_CD == ''){
//            $ORD_STAT_CD = M('ms_ord','tb_')->where('ORD_ID = "'.$ORD_ID.'"')->field('ORD_STAT_CD')->find();
//            $ORD_STAT_CD = $ORD_STAT_CD['ORD_STAT_CD'];
//        }
//        //订单操作日志
//        $his = M('ms_ord_hist','sms_');
//        $data['ORD_NO'] = $ORD_ID;
//        $ORD_HIST_SEQ = $his->where('ORD_NO = "'.$ORD_ID.'"')->field('ORD_HIST_SEQ')->order('ORD_HIST_SEQ desc')->find();
//        if(isset($ORD_HIST_SEQ['ORD_HIST_SEQ']) ){
//            $ORD_HIST_SEQ = intval($ORD_HIST_SEQ['ORD_HIST_SEQ']) + 1;
//        }else{
//            $ORD_HIST_SEQ = 1;
//        }
//        $data['ORD_HIST_SEQ'] = $ORD_HIST_SEQ;
//        $data['ORD_STAT_CD'] = $ORD_STAT_CD;
//        $data['ORD_HIST_WRTR_EML'] = $_SESSION['m_loginname'];
//        $data['ORD_HIST_REG_DTTM'] = date('Y-m-d H:i:s', time());
//        $data['ORD_HIST_HIST_CONT'] = $content;
//        $data['updated_time'] = date('Y-m-d H:i:s', time());
//        if($his->data($data)->add()){
//            return true;
//        }else{
//            return false;
//        }
//    }
//}
