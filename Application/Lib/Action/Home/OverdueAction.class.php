<?php

/**
 * User: yangsu
 * Date: 17/8/7
 * Time: 16:03
 * Class OverdueAction
 * 更新逾期
 */
class OverdueAction extends Action
{
    public static $mail = [
        'join' => ['legal@gshopper.com','xinfan@gshopper.com'],
//        'join' => ['huali@gshopper.com','yangsu@gshopper.com'],
        'title30' => '订单逾期超过30天未付款提醒',
        'title' => '订单逾期未付款提醒',
    ];

    /**
     *  子单逾期计算
     */
    public function overdue_update()
    {
        echo '<pre>';
        $Receipt = M('receipt', 'tb_b2b_');
        $Order = M('order', 'tb_b2b_');
        $where['tb_b2b_receipt.overdue_statue'] = array(array('EQ', '0'), array('EXP', 'IS NULL'), 'OR');
        $where['tb_b2b_receipt.transaction_type'] = array('EXP', 'IS NULL');
        $where['tb_b2b_receipt.actual_receipt_date'] = array('EXP', 'IS NULL');
        $receipt = $Receipt
            ->field('tb_b2b_receipt.*,tb_b2b_info.PO_TIME,tb_b2b_info.PAYMENT_NODE,tb_b2b_receipt.receiving_code,tb_b2b_warehouse_list.WAREING_DATE,tb_b2b_ship_list.DELIVERY_TIME,tb_b2b_ship_list.Estimated_arrival_DATE')
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join (select max(WAREING_DATE) as WAREING_DATE,status,ORDER_ID from tb_b2b_warehouse_list group by ORDER_ID) as tb_b2b_warehouse_list on tb_b2b_warehouse_list.ORDER_ID = tb_b2b_receipt.ORDER_ID ')
            ->join('left join (select tb_b2b_ship_list.SUBMIT_TIME,tb_b2b_ship_list.order_id,max(tb_b2b_ship_list.Estimated_arrival_DATE) as Estimated_arrival_DATE,max(tb_b2b_ship_list.DELIVERY_TIME) as DELIVERY_TIME from tb_b2b_ship_list group by order_id order by ID desc ) as tb_b2b_ship_list on tb_b2b_ship_list.order_id =  tb_b2b_receipt.ORDER_ID ')
            ->where($where)->select();

        foreach ($receipt as $v) {
            if (!$v['receiving_code']) break;
            $node = $v['receiving_code'];
            $check_data['po_time'] = $v['PO_TIME'];
            $check_data['DELIVERY_TIME'] = $v['DELIVERY_TIME'];
            $check_data['Estimated_arrival_DATE'] = $v['Estimated_arrival_DATE'];
            $check_data['WAREING_DATE'] = $v['WAREING_DATE'];
            $t_date = $v['actual_receipt_date'];
            $node_date = B2bModel::get_code('node_date');
            echo $v['PO_ID'] . PHP_EOL;
            list($res_type, $t_date, $type) = $this->check_yq($node, $check_data, $node_date, $t_date);
            if ($res_type) {
                $save['overdue_statue'] = 1;
                $save['overdue_date'] = $t_date;
                $Receipt->where('ID = ' . $v['ID'])->save($save);
//              同步Order
                $save_order['order_overdue_statue'] = 1;
                $Order->where('ID = ' . $v['ORDER_ID'])->save($save_order);
            }
            echo PHP_EOL;
        }
    }


    /**
     * 逾期效验
     * @param $node 当前节点
     * @param $check_data 遍历节点时间
     * @param $node_date  当前时间
     * @param $t_date 实际收款时间
     * @param $transaction_type 收款类型
     * @return array true (逾期) or false（否）
     */
    public function check_yq($node, $check_data, $node_date, $t_date = null, $transaction_type = null)
    {

        if (1 == $transaction_type) {
//            return false;
        }
        $node = json_decode($node, true);
        $times = null; // 处理时间
        if (isset($node['nodeDate'])) {
            switch ((int)$node['nodeType']) {
                case 0:
//                        合同
                    $type = '合同';
                    $times = $check_data['po_time'];
                    break;
                case 1:
//                        发货
                    $type = '发货';
                    $times = $check_data['DELIVERY_TIME'];
                    break;
                case 2:
//                      入港
                    $type = '入港';
                    $times = $check_data['Estimated_arrival_DATE'];
                    break;
                case 3:
//                        入库
                    $type = '入库';
                    $times = $check_data['WAREING_DATE'];
                    break;

                default:
            }
            var_dump($times);
            echo $type;
            if (empty($times)) return [false, $times, $type];
            $times = strtotime($times) + $node_date[$node['nodeDate']]['CD_VAL'] * 24 * 60 * 60;
            $times = date('Y-m-d', $times);
            if (!$t_date) $t_date = date('Y-m-d');
            $now_time = date('Y-m-d', strtotime($t_date));
            echo '$times' . $times . ' $now_time' . $now_time;
            if ($times < $now_time) {
                echo '逾期' . PHP_EOL;
                return [true, $times, $type];  //逾期
            }
        }
        return [false, $times, $type];
    }

    /**
     * 邮件发送
     */
    public function overdue_mail_send()
    {
        echo '<pre>';
        $Receipt = M('receipt', 'tb_b2b_');
        $where['tb_b2b_receipt.overdue_statue'] = array('EQ', '1');
        $where['tb_b2b_receipt.transaction_type'] = array('EXP', 'IS NULL');
        $where['tb_b2b_receipt.actual_receipt_date'] = array('EXP', 'IS NULL');
        $receipt = $Receipt->field('tb_b2b_receipt.*,tb_b2b_info.PO_USER,tb_b2b_info.CLIENT_NAME,tb_b2b_info.po_time,tb_b2b_order.create_user')
            ->join('left join tb_b2b_info on tb_b2b_info.PO_ID = tb_b2b_receipt.PO_ID')
            ->join('left join tb_b2b_order on tb_b2b_order.PO_ID = tb_b2b_receipt.PO_ID')
            ->where($where)->select();
        $cc = $this->get_mail_group($e);
        foreach ($receipt as &$v) {
            $v['overdue_day'] = $overdue_day = round((strtotime("now") - strtotime($v['overdue_date'])) / 60 / 60 / 24) - 1;
//        单邮件（1，10，20）
            if ($v['mail_num'] == 0 && $overdue_day < 30) {
                $mail_arr['self'][$v['create_user']][] = $v;
                static::upd_mail_info($Receipt, $v['ID']);
            }
            if ($v['mail_num'] != 0 && ($overdue_day % 10) == 0 && $overdue_day < 30) {
                $mail_arr['self'][$v['create_user']][] = $v;
                static::upd_mail_info($Receipt, $v['ID']);
            }
//        聚合
            if ($overdue_day >= 30) {
                $mail_arr['self'][$v['create_user']][] = $v;
                $mail_arr['group'][$v['sales_team_id']][] = $v;
                $mail_arr['join'][] = $v;
                static::upd_mail_info($Receipt, $v['ID']);
            }
        }
        $this->mail_arr_send($mail_arr);

    }

    /**
     * 更新实际逾期状态
     * @param $id
     * @param $date 实际收款时间
     * @return int
     */
    public static function actual_overdue_upd($id, $date, $Receipt)
    {
//        $Receipt = M('receipt', 'tb_b2b_');
        $receipt_s = $Receipt->field('overdue_date,ORDER_ID')->where('ID = ' . $id)->find();
        trace($receipt_s, '$receipt_s');
        if ($receipt_s['overdue_date'] > $date) {
            $save['overdue_statue'] = $status = 2;
            $Receipt->where('ID = ' . $id)->save($save);
        } else {
            $save['overdue_statue'] = $status = 3;
            $Receipt->where('ID = ' . $id)->save($save);
        }
        static::actual_overdue_order_upd($receipt_s['ORDER_ID'], $Receipt);
        return $status;
    }

    /**
     * 更新订单逾期状态
     * @param $order_id
     * @param $Receipt
     * @return int
     */
    public static function actual_overdue_order_upd($order_id, $Receipt)
    {
        $receipt = $Receipt->where('ORDER_ID = ' . $order_id)->select();
        $count = 0;
        foreach ($receipt as $v) {
            if ($v['overdue_statue'] == 1 || $v['overdue_statue'] == 3) {
                $count++;
            }
        }
        if ($count > 0) {
            $Order = M('order', 'tb_b2b_');
            $order_save['order_overdue_statue'] = 1;
            $order = $Order->where('ID = ' . $order_id)->save($order_save);
            trace($order, '$order');
        }
        return $count;
    }

    /**
     * 更新邮件信息
     * @param $Receipt
     * @param $ID
     * @param null $mail_save
     */
    private static function upd_mail_info($Receipt, $ID, $mail_save = null)
    {
        $Receipt->where('ID = ' . $ID)->setInc('mail_num', 1);
        $mail_save['mail_send_date'] = date("Y-m-d H:i:s");
        $Receipt->where('ID = ' . $ID)->save($mail_save);
    }

    /**
     * self/ten/thr
     * group
     * join
     * @param $mail_all
     */
    private static function mail_arr_send($mail_all)
    {
        $cd = B2bModel::get_sales_team();
        foreach ($mail_all['self'] as $k => $v) {
            $user = $k . '@gshopper.com';
            static::mail_send(static::$mail['title'], static::check_mail_message($v, $k), null, $user);
//            static::mail_send(static::$mail['title'], static::check_mail_message($v, $k), null);
        }
        foreach ($mail_all['group'] as $k => $v) {
            $group = $cd[$k]['ETc'] . '@gshopper.com';
            static::mail_send(static::$mail['title'], static::check_mail_message($v, $cd[$k]['ETc']), null, $group);
//            static::mail_send(static::$mail['title'], static::check_mail_message($v, $cd[$k]['ETc']), null);
        }
      if(count($mail_all['join']) > 0)static::mail_send(static::$mail['title30'], static::check_mail_message($mail_all['join']), static::$mail['join'],'finance@gshopper.com');
//        if(count($mail_all['join']) > 0)static::mail_send(static::$mail['title30'], static::check_mail_message($mail_all['join']), NULL,'yangsu@gshopper.com');
    }

    /**
     * @param $e
     * @param null $user
     * @return string
     */
    private static function check_mail_message($e, $user = null)
    {
        if (empty($user)) {
            $users = 'DearAll';
            $title = '今日逾期超过30天未付款的订单有以下' . count($e) . '个，请及时跟进收款，谢谢。';
            $get_sales_team = B2bModel::get_sales_team();
        } else {
            $users = $user;
            $title = '你今天有以下' . count($e) . '个收款客户已经逾期未付款，请跟进并及时收款';
        }


        $msg = '<div><span></span>' . $users . '</div><div>' . $title . '</div>';
        $msg .= '<div><table width="932" height="57" style="border-collapse:collapse;width:699.00pt;"><colgroup><col width="143"><col width="206"><col width="109"><col width="71"><col width="141"><col width="90"><col width="109"><col width="63"></colgroup><tbody><tr height="19"><td class="et4" x:str="" height="19" width="143" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 107.25pt;">订单号</td><td class="et4" x:str="" height="19" width="206" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 154.5pt;">客户</td><td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">总金额</td><td class="et4" x:str="" height="19" width="71" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 53.25pt;">PO时间</td><td class="et4" x:str="" height="19" width="141" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 105.75pt;">期数</td><td class="et4" x:str="" height="19" width="90" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 67.5pt;">预计付款时间</td><td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">本期付款金额</td><td class="et4" x:str="" height="19" width="63" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 47.25pt;">逾期天数</td>';
        if (empty($user)) {
            $msg .= '<td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">所属销售</td><td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">订单发起人</td><td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">销售团队</td>';
        }
        $msg .= '</tr>';
        foreach ($e as $v) {
            $msg .= '<tr height="19" style="font-size: 13px;"><td class="et4" x:str="" height="19" width="143" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 107.25pt;">' . $v['PO_ID'] . '</td><td class="et6" x:str="" height="19" width="206" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 154.5pt;">' . $v['CLIENT_NAME'] . '</td><td class="et6" x:str="" height="19" width="109" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">' . $v['expect_receipt_amount'] . '</td><td class="et7" x:num="42948" height="19" width="71" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 53.25pt;">' . date('Y-m-d', strtotime($v['po_time'])) . '</td><td class="et4" x:str="" height="19" width="141" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 105.75pt;">' . B2bModel::toNode($v['receiving_code']) . '</td><td class="et8" x:num="43048" height="19" width="90" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 67.5pt;">' . date('Y-m-d', strtotime($v['overdue_date'])) . '</td><td class="et6" x:str="" height="19" width="109" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">' . $v['expect_receipt_amount'] . '</td><td class="et4" x:num="10" height="19" width="63" style="text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 47.25pt;">' . $v['overdue_day'] . '</td>';
            if (empty($user)) {
                $msg .= '<td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">' . $v['PO_USER'] . '</td><td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">' . $v['create_user'] . '</td><td class="et4" x:str="" height="19" width="109" style="font-size: 13px; text-align: center; vertical-align: middle; border: 0.5pt solid rgb(0, 0, 0); height: 14.25pt; width: 81.75pt;">' . $get_sales_team[$v['sales_team_id']]['CD_VAL'] . '</td>';
            }
            $msg .= '</tr>';
        }


        $msg .= '</tbody ></table ><br ></div > ';
        return $msg;

    }


    /**
     * @param $title
     * @param $message
     * @param string $user
     * @param null $cc
     * @return bool
     */
    public static function mail_send($title, $message, $cc = null, $user = 'yangsu@gshopper.com')
    {
        $email = new SMSEmail();
        $res = $email->sendEmail($user, $title, $message, $cc);
        return $res;
    }

    public function get_mail_group($e)
    {
        $res = ['yuanfeng@gshopper.com'];
        return $res;
    }


}