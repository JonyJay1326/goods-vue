<?php
/**
 * User: yuanshixiao
 * Date: 2017/8/15
 * Time: 14:32
 */

class TbPurPaymentModel extends Model
{
    protected $trueTableName    = 'tb_pur_payment';
    public static $status = [
        0 => '待确认',
        1 => '待支付',
        2 => '已支付',
    ];

    public $_validate = [
        ['relevance_id','require','订单关联id必须'],
        ['amount','require','商品金额必须'],
        ['amount_payable','require','应付金额必须'],
        ['payable_date','require','付款日期必须'],
        ['payment_period','require','付款账期必须'],
    ];

    public function createPayableByShip($ship_id) {
        if(!$ship_id) {
            $this->error = '参数错误';
            return false;
        }
        $ship_info = M('ship','tb_pur_')->where(['id'=>$ship_id])->find();
        $order = M('order_detail','tb_pur_')
            ->alias('t')
            ->join('left join tb_pur_relevance_order as  a on a.order_id=t.order_id')
            ->where(['relevance_id'=>$ship_info['relevance_id']])
            ->find();

        if($order['payment_type'] != 1) {
            return true;
        }
        $payment_info = json_decode($order['payment_info'],true);
        foreach($payment_info as $k => $v) {
            if($v['payment_node'] == 'N001390200') {
                $payment = $v;
                $period = $k;
                break;
            }
        }
        if(!$payment) return true;
        $amount = M('ship_goods','tb_pur_')
            ->alias('t')
            ->join('left join tb_pur_goods_information a on a.information_id=t.information_id')
            ->where(['ship_id'=>$ship_id])
            ->sum('t.ship_number*a.unit_price');

        $payable['relevance_id']   = $ship_info['relevance_id'];
        $payable['payable_date']   = date('Y-m-d',strtotime($ship_info['shipment_date'])+$payment['payment_days']*24*3600);
        $payable['amount']         = $amount;
        $payable['amount_payable'] = $amount*$payment['payment_percent']/100;
        $payable['payment_period'] = "第{$period}期-"
            .cdVal($payment['payment_node'])
            .$payment['payment_days']
            .TbPurOrderDetailModel::$payment_day_type[$payment['payment_day_type']]
            .$payment['payment_percent'].'%';
        $payable['payment_no']      = $this->createPaymentNO();
        $res = M('payment','tb_pur_')->add($payable);
        if(!$res) {
            ELog::add('发货生成应付数据失败：'.json_encode($payable).M()->getDbError(),ELog::ERR);
            return false;
        }
        return true;
    }

    public function createPayableByWarehouse($ship_id) {
        if(!$ship_id) {
            $this->error = '参数错误';
            return false;
        }
        $ship_info = M('ship','tb_pur_')->where(['id'=>$ship_id])->find();
        $order = M('order_detail','tb_pur_')
            ->alias('t')
            ->join('left join tb_pur_relevance_order as  a on a.order_id=t.order_id')
            ->where(['relevance_id'=>$ship_info['relevance_id']])
            ->find();

        if($order['payment_type'] != 1) {
            return true;
        }
        $payment_info = json_decode($order['payment_info'],true);
        foreach($payment_info as $k => $v) {
            if($v['payment_node'] == 'N001390400') {
                $payment = $v;
                $period = $k;
                break;
            }
        }
        if(!$payment) return true;
        $amount = M('ship_goods','tb_pur_')
            ->alias('t')
            ->join('left join tb_pur_goods_information a on a.information_id=t.information_id')
            ->where(['ship_id'=>$ship_id])
            ->sum('t.warehouse_number*a.unit_price');

        $payable['relevance_id']   = $ship_info['relevance_id'];
        $payable['payable_date']   = date('Y-m-d',strtotime($ship_info['arrival_date_actual'])+$payment['payment_days']*24*3600);
        $payable['amount']         = $amount;
        $payable['amount_payable'] = $amount*$payment['payment_percent']/100;
        $payable['payment_period'] = "第{$period}期-"
            .cdVal($payment['payment_node'])
            .$payment['payment_days']
            .TbPurOrderDetailModel::$payment_day_type[$payment['payment_day_type']]
            .$payment['payment_percent'].'%';
        $payable['payment_no']      = $this->createPaymentNO();
        $res = M('payment','tb_pur_')->add($payable);
        if(!$res) {
            ELog::add('入库生成应付数据失败：'.json_encode($payable).M()->getDbError(),ELog::ERR);
        }
    }

    public function createPaymentNO() {
        $pre_payment_no = $this->where(['payment_no'=>['like','YF'.date('Ymd').'%']])->order('id desc')->getField('payment_no');
        if($pre_payment_no) {
            $num = substr($pre_payment_no,-3)+1;
        }else {
            $num = 1;
        }
        $payment_no = 'YF'.date('Ymd').substr(1000+$num,1);
        return $payment_no;
    }


}