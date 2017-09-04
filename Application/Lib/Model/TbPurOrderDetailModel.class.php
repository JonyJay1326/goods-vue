<?php

/**
 * User: yuanshixiao
 * Date: 2017/6/20
 * Time: 14:01
 */
class TbPurOrderDetailModel extends Model
{
    protected $trueTableName    = 'tb_pur_order_detail';

    public static $payment_period = [
        '0' => '请选择期数',
        '1' => '一次性付清',
        '2' => '分两期付清',
        '3' => '分三期付清',
    ];

    public static $payment_type = [
        '0' => '按指定时间付款',
        '1' => '按实际情况付款',
    ];

    public static $payment_node = [
        '0' => '第##期节点',
        '1' => '合同后',
        '2' => '发货后',
        '3' => '入库后'
    ];

    public static $payment_day_type = [
        0 => '天',
//        1 => '工作日'
    ];

    public static $payment_percent = [5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100];

    public static $payment_days = [7,15,30,45,60,0];

    public function supplierHasCooperate($sp_charter_no = '') {
        if($sp_charter_no == '') {
            $this->error = '营业执照号不能为空';
            return false;
        }else {
            $res = $this->alias('t')
                ->join('left join tb_pur_relevance_order a on a.order_id=t.order_id')
                ->where(['sp_charter_no'=>$sp_charter_no,'order_status'=>'N001320300'])
                ->find();
            if($res) {
                return true;
            }else {
                return false;
            }
        }
    }
}