<?php

/**
 * User: yuanshixiao
 * Date: 2017/5/17
 * Time: 10:46
 */
class TbOpOrdModel extends Model
{
    protected $trueTableName = 'tb_op_order';  //发货涉及到的表

    //发货
    public function sendOut($order_id,$plat_id=null) {  
        $set_stock_res = $this->setStock($order_id);  //锁库存   可能是锁定一个库存占用???

        if($set_stock_res['status'] == 'y' || $set_stock_res['code']=='x01') {
            $order = $this
                ->field('ORDER_ID,SHIPPING_DELIVERY_COMPANY,SHIPPING_DELIVERY_COMPANY_CD,SHIPPING_TRACKING_CODE,ADDRESS_USER_COUNTRY_CODE,ORDER_STATUS1,B5C_ORDER_NO')
                ->where('ORDER_ID = "' . $order_id . '"')
                ->find();
                //print_r($order);die;
//            
            if ($order['ORDER_STATUS1'] === null) { 
                if($plat_id === 'N000830100' || $plat_id === 'N000830200' || $plat_id === 'N000831300' || $plat_id === 'N000831400')
                {
                    $result = $this->qooTenSendOut($order);   //q10java接口
                } else {
                    $result['ResultCode'] = 0;
                }
            } else {
                $result['ResultCode'] = 0;
            }
            if ($result['ResultCode'] == 0) { 
                $data['BWC_ORDER_STATUS'] = 'N000550500';   //发货成功修改订单状态
                $data['SHIPPING_TIME'] = date('Y-m-d H:i:s');  //修改发货时间
                
                $this->data($data)->where('ORDER_ID = "' . $order_id . '"')->save();   //修改tb_op_order表中的订单数据(状态和时间)
                if($order['B5C_ORDER_NO']){
                    M('ms_ord', 'tb_')->data(['ORD_STAT_CD' => 'N000550500'])->where(['ORD_ID' => $order['B5C_ORDER_NO']])->save();
                    (new TbMsOrdPackageModel())->savePackage($order['B5C_ORDER_NO'], $order['SHIPPING_DELIVERY_COMPANY_CD'], $order['SHIPPING_TRACKING_CODE']);//修改发货信息,暂时只用于自营订单
                    $content = '设置发货成功';
                    $log = A('Log');
                    $log->index($order_id, 'N000550500', $content);  //写入ms_ord_hist日志
                    return true;
                }else{
                    return false;
                }
            } else {
                $deliver_res = $this->deliverStock($order_id);  //减库存
                $this->error = $result['ResultMsg'] . '|' . $deliver_res['info'];
                return false;
            }
        }else {
            $this->error = $set_stock_res['info'];
            return false;
        }
    }

    //调qoo10发货接口
    public function qooTenSendOut($order) {
        $url = 'http://api.qoo10.com/GMKT.INC.Front.OpenApiService/APIList/ShippingBasicService.api/SetSendingInfo';
        if ($order['ADDRESS_USER_COUNTRY_CODE'] == 'KR') {
            $data['Key'] = 'S5bnbfynQvO1AD3ap2KazBpuBUscJQcKXZTCi_g_2_ovyEI0ZqrNa7MWaBW2xEhIeDEQXOVPaW55ajZWkwUx9GIoB4vxd65M_g_1_3i_g_2_9NT8x8k3l0pFhDK8LLgb6g_g_3__g_3_';
        } else {
            $data['Key'] = 'S5bnbfynQvO1AD3ap2KazBpuBUscJQcKuEIK_g_1_7G5ScsrXQUzo4euo_g_2_iQ6_g_2_QXTKmfDQDTU5qN6_g_2_K6xTD_g_2_A3DKGVEEEym9lWcrJTmMz8YvLHHB2FeZJFEmbw_g_3__g_3_';
        }
        $data['OrderNo']        = $order['ORDER_ID'];
        $data['ShippingCorp']   = $order['SHIPPING_DELIVERY_COMPANY'];
        $data['TrackingNo']     = $order['SHIPPING_TRACKING_CODE'];
        $result                 = curl_request($url, $data);
        $result                 = json_encode(simplexml_load_string($result));
        $result                 = json_decode($result, 1);
        return $result;
    }

    //锁库存
    public function setStock($order_id) {
        $urlstock               = SMS2_URL . 'index.php?m=stock&a=deliver';
        $data_stock             = [];
        $data_stock['userId']   = $_SESSION['userId'];
        $data_stock['ordId']    = $order_id;
        $res                    = curl_request($urlstock, $data_stock);
        $res                    = json_decode($res, 1);
        return $res;
    }

    //解除库存
    public function deliverStock($order_id) {
        $urlstockrollback       = SMS2_URL . 'index.php?m=stock&a=deliver_back';
        $data_stock             = [];
        $data_stock['userId']   = $_SESSION['userId'];
        $data_stock['ordId']    = $order_id;
        $resrollback            = curl_request($urlstockrollback, $data_stock);
        $resrollback            = json_decode($resrollback, 1);
        return $resrollback;
    }
}