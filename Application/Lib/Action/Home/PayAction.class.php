<?php

/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/2
 * Time: 13:13
 */
class PayAction extends BaseAction
{

    public function pay_list()
    {

        $call = D("Pay");

        $where = array();

        if ($this->isPost()) {
            if (I("post.status")) {
                $where['bbm_pay_order.order_status'] = I("post.status");
            }

            if (I("post.channel")) {
                $where['back.channel'] = I("post.channel");
            }
            if (I('post.starttime') && I('post.endtime')) {
                $where['bbm_pay_order.created_time'] = array(array('gt', I('post.starttime')), array('lt', I('post.endtime')));
            } elseif (I('post.starttime')) {
                $where['bbm_pay_order.created_time'] = array('gt', I('post.starttime'));
            } elseif (I('post.endtime')) {
                $where['bbm_pay_order.created_time'] = array('lt', I('post.endtime'));
            }

            if (I('post.payid')) {
                $where['bbm_pay_order.pay_id'] = I("post.payid");
            }

            if (I('post.serialnumber')) {
                $where['back.serial_number'] = I("post.serialnumber");
            }

            if (I('post.orderid')) {
                $where['bbm_pay_order.order_id'] = array('like', '%' . I("post.orderid") . '%');
            }
        }

        import('ORG.Util.Page');// 导入分页类
        $count = $call->where($where)->count();
        $page = new Page($count, 15);
        $show = $page->show();

        $result = $call->Distinct(true)->field('bbm_pay_order.*, back.serial_number, back.channel')->join('bbm_pay_callback as back on back.pay_id = bbm_pay_order.pay_id')->limit($page->firstRow . ',' . $page->listRows)->where($where)->order('id desc')->select();
        foreach ($result as $k => $v) {
            if ($v['channel'] == 'YHJ' && $v['order_status'] == 'SUCCESS') {
                $ord = M('ms_ord', 'tb')->where('ORD_ID = "' . $v['order_id'] . '" and DELIVERY_WAREHOUSE = "N000680300" and ORD_TYPE_CD = "N000620400"')->find();
                if (empty($ord)) {
                    continue;
                }
                $num = D("Pay")->where('back.pay_id = "' . $v['pay_id'] . '" and back.status ="success"')->join('bbm_pay_callback as back on back.pay_id = bbm_pay_order.pay_id')->count();
                if ($num < 2) {
                    $result[$k]['need_cust'] = 1;
                }
            }
        }
        $this->assign('result', $result);
        $this->assign('pages', $show);

        $this->assign('total', $count);
        $this->display();
    }

    public function bulk_pay()
    {
        ini_set("max_execution_time", "1800");
        $where = [];
        if (I('order_id')) {
            $where['bbm_pay_money_flow.order_id'] = array('like', '%' . I("order_id") . '%');
        }
        if (I('pay_id')) {
            $where['bbm_pay_money_flow.pay_id'] = array('like', '%' . I("pay_id") . '%');
        }
        if (I('request_id')) {
            $where['bbm_pay_money_flow.request_id'] = array('like', '%' . I("request_id") . '%');
        }
        if (I('starttime') && I('endtime')) {
            $where['bbm_pay_money_flow.create_time'] = array(array('gt', I('starttime')), array('lt', I('endtime')));
        } elseif (I('starttime')) {
            $where['bbm_pay_money_flow.create_time'] = array('gt', I('starttime'));
        } elseif (I('endtime')) {
            $where['bbm_pay_money_flow.create_time'] = array('lt', I('endtime'));
        }
        if (I('ope_type')) {
            $where['bbm_pay_money_flow.ope_type'] = I("ope_type");
        }
        if (I('status')) {
            $where['bbm_pay_money_flow.status'] = I("status");
        }
        import('ORG.Util.Page');// 导入分页类
        $count = M('pay_money_flow')->where($where)->count();
        $page = new Page($count, 10);
        $show = $page->show();
        $model = M('pay_money_flow');
        $pay_limit_sql = $model->where($where)
            ->group('bbm_pay_money_flow.request_id')
            ->order('bbm_pay_money_flow.updated_time desc,bbm_pay_money_flow.ope_type asc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select(false);
        $result = $model->table($pay_limit_sql . ' pay')
            ->where($where)
            ->join('left join bbm_pay_exception on bbm_pay_exception.request_id = pay.order_id')
            ->join('left join bbm_pay_union_account on bbm_pay_union_account.member_id = pay.member_id')
            ->field('
			pay.request_id,pay.order_id,pay.pay_id,pay.order_amount,pay.create_time,pay.updated_time,
			case ope_type when "MF_001" then "充值" when "MF_002" then "转账" when "MF_003" then "支付" end as ope_type,
			pay.need_ope,pay.be_operated,pay.from_member,pay.to_member,pay.status,
			bbm_pay_union_account.customer_id,
			bbm_pay_exception.rsp_data
		')
            ->select();


        foreach ($result as $k => $v) {
            if ($v['status'] == 'SUCCESS' && $v['ope_type'] == '支付') {
                $pay_num = M('pay_callback')->where('pay_id = "' . $v['request_id'] . '"')->count();
                if ($pay_num < 2) {
                    $result[$k]['need_cust'] = 1;
                }
            }
        }

        $this->assign('pages', $show);
        $this->assign('total', $count);
        $this->assign('pay_money_flow', $result);
        $this->display();
    }

    public function bulk_pay_new()
    {
        ini_set("max_execution_time", "1800");
        $where = [];
        if (I('order_id')) {
            $where['bbm_pay_order.order_id'] = array('like', '%' . I("order_id") . '%');
        }
        if (I('pay_id')) {
            $where['bbm_pay_order.pay_id'] = array('like', '%' . I("pay_id") . '%');
        }
        if (I('request_id')) {
            $where['bbm_pay_order.request_id'] = array('like', '%' . I("request_id") . '%');
        }
        if (I('starttime') && I('endtime')) {
            $where['bbm_pay_order.created_time'] = array(array('gt', I('starttime')), array('lt', I('endtime')));
        } elseif (I('starttime')) {
            $where['bbm_pay_order.created_time'] = array('gt', I('starttime'));
        } elseif (I('endtime')) {
            $where['bbm_pay_order.created_time'] = array('lt', I('endtime'));
        }
        if (I('ope_type')) {
            $where['bbm_pay_order.ope_type'] = I("ope_type");
        }
        if (I('status')) {
            $where['bbm_pay_order.status'] = I("status");
        }

        $where['order_status'] = 'SUCCESS';
        $where['remark'] = 'recharge';
        $all_count = M('pay_order')->where($where)->order('updated_time desc')->field('pay_id,order_id')->select();

        foreach ($all_count as $key => $val) {
            $exp_val = explode(',', $val['order_id']);
            foreach ($exp_val as $k) {
                if (I('order_id') && I('pay_id')) {
                    if (I('order_id') == $k && $val['pay_id'] == I('pay_id')) {
                        $all_val['order_id'] = $k;
                        $all_val['pay_id'] = $val['pay_id'];
                        $all_val_arr[] = $all_val;
                    }
                } elseif (I('order_id')) {
                    if (I('order_id') == $k) {
                        $all_val['order_id'] = $k;
                        $all_val['pay_id'] = $val['pay_id'];
                        $all_val_arr[] = $all_val;
                    }
                } elseif (I('pay_id')) {
                    if ($val['pay_id'] == I('pay_id')) {
                        $all_val['order_id'] = $k;
                        $all_val['pay_id'] = $val['pay_id'];
                        $all_val_arr[] = $all_val;
                    }
                } else {
                    $all_val['order_id'] = $k;
                    $all_val['pay_id'] = $val['pay_id'];
                    $all_val_arr[] = $all_val;
                }
            }

        }

        import('ORG.Util.Page');// 导入分页类
        $count = count($all_val_arr);
        $page = new Page($count, 10);
        $show = $page->show();
        $region = array_slice($all_val_arr, $page->firstRow, $page->listRows);
        $val_test = array_column($region, 'pay_id');
        $val_test_un = array_unique($val_test);


        $where_regioin['pay_id'] = array('in', $val_test_un);
        $where_regioin['ope_type'] = 'MF_001';
        $Pay_money_flow = M('pay_money_flow');
        $pay_limit_sql = $Pay_money_flow->where($where_regioin)
            ->group('bbm_pay_money_flow.request_id')
            ->order('bbm_pay_money_flow.updated_time desc')
            ->select(false);
        $result = $Pay_money_flow->table($pay_limit_sql . ' pay')
            ->join('left join bbm_pay_union_account on bbm_pay_union_account.member_id = pay.member_id')
            ->field('pay.request_id,pay.order_id,pay.pay_id,pay.order_amount,pay.create_time,pay.updated_time,
            case ope_type when "MF_001" then "充值" when "MF_002" then "转账" when "MF_003" then "支付" end as ope_type,pay.need_ope,pay.be_operated,pay.from_member,pay.to_member,pay.status,bbm_pay_union_account.customer_id,pay.member_id')
            ->select();
        $key_pay_id = array_column($result, 'pay_id');
        foreach ($region as $key => $val) {
//                join
            $test_val = $result[array_keys($key_pay_id, $val['pay_id'])[0]];
            $test_val['order_id'] = $val['order_id'];
            $result_new['mf1'] = $test_val;
//            get 2 and 3
            $result_new['mf2'] = $this->region_mf(2, $val['order_id']);
            $result_new['mf3'] = $this->region_mf(3, $val['order_id']);
            $result_new_arr[] = $result_new;
        }
        foreach ($result_new_arr as $k => $v) {
            if ($v['mf3']['status'] == 'SUCCESS' && $v['mf3']['ope_type'] == '支付') {
                $pay_num = M('pay_callback')->where('pay_id = "' . $v['mf3']['request_id'] . '"')->count();
                if ($pay_num < 2) {
                    $result_new_arr[$k]['need_cust'] = 1;
                }
            }
        }
        $this->assign('pages', $show);
        $this->assign('total', $count);
        $this->assign('pay_money_flow', $result_new_arr);
        $this->display();
    }

    public function show_money()
    {
        $memberId = I('post.memberId');
        $url = PAY_URL_API . '/union_account/account_query.json?memberId=' . $memberId;
        $result = json_decode(curl_request($url), 1);
        if ($result['msg'] == 'success') {
            $this->ajaxReturn(0, $result['data']['balance'], 200);
        } else {
            $this->ajaxReturn(0, $result['msg'] . '请求地址：' . $url, $result['code']);
        }
    }

    private function region_mf($mf, $order_id)
    {
        $Pay_money_flow = M('pay_money_flow');
        $pay_limit_sql = $Pay_money_flow->where("ope_type = 'MF_00" . $mf . "' AND order_id = '" . $order_id . "'")
            ->group('bbm_pay_money_flow.request_id')
            ->order('bbm_pay_money_flow.updated_time desc')
            ->select(false);
        $Pay_money_flow_data = $Pay_money_flow->table($pay_limit_sql . ' pay')
//            ->join('left join bbm_pay_exception on bbm_pay_exception.request_id = pay.order_id')
            ->join('left join bbm_pay_union_account on bbm_pay_union_account.member_id = pay.member_id')
            ->field('pay.request_id,pay.order_id,pay.pay_id,pay.order_amount,pay.create_time,pay.updated_time,case ope_type when "MF_001" then "充值" when "MF_002" then "转账" when "MF_003" then "支付" end as ope_type,pay.need_ope,pay.be_operated,pay.from_member,pay.to_member,pay.status,bbm_pay_union_account.customer_id,pay.member_id,pay.to_member')
            ->select();
        return $Pay_money_flow_data[0];
    }

    public function push()
    {
        $type = I('type');
        $request_id = I('request_id');
        if ($type == 1) {
            $url = HOST_URL . 'union_account/manu_account_transfer.json?requestId=' . $request_id;
        } elseif ($type == 2) {
            $url = HOST_URL . 'union_account/manu_order_pay.json?requestId=' . $request_id;
        } elseif ($type == 3) {
            $url = HOST_URL . 'order/trans_pay.json?ordId=' . I('order_id') . '&payId=' . I('pay_id');
        } else {
            $amount = sprintf('%.2f', I('order_amount') / 100);
            $url = HOST_URL . 'order/order_baoguan_ope.json?ordId=' . I('order_id') . '&ppayId=' . I('ppay_id') . '&payId=' . I('pay_id') . '&payNm=' . $amount;
            trace($url,'$url');
        }
        $result = json_decode(curl_request($url), 1);
        if ($result['code'] == 2000 || $result['code'] == 200) {
            $this->ajaxReturn(0, '操作成功,请求地址：' . $url, 1);
        } else {
            $this->ajaxReturn(0, '操作失败,请求地址：' . $url, 0);
        }
    }
}
