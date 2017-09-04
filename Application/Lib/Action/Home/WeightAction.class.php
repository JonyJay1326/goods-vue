<?php

/**
 * User: yangsu
 * Date: 17/4/27
 * Time: 14:56
 */
class WeightAction extends BaseAction
{
    public function _initialize()
    {

    }

    private $bill_state = [
        'N000590100' => 'USD',
        'N000590200' => 'KRW',
        'N000590300' => 'CNY',
        'N000590400' => 'JPY',
    ];

    public function index()
    {
        $this->UpdateAll();
    }

    /**
     * 更新全部
     */
    public function UpdateAll()
    {
        ini_set('max_execution_time', 18000);
        $Power_log = M('power_log', 'tb_wms_');
        $power_log_bill = $Power_log->where('actiontype = \'end_bill_id\'')->order('id desc')->limit(1)->getField('bill_id');
        if (I('limit_val')) {
            $limit_val = I('limit_val');
        } else {
            $limit_val = 100;
        }
        $Bill = M('bill', 'tb_wms_');
        $where['is_show'] = 1;
        // search all list
        $bill_arr = $Bill->where($where)->field('id,warehouse_id,bill_type,bill_date')->order('bill_date asc')->select();
        // const new bill list
        $bill_id_arr = array_flip(array_column($bill_arr, 'id'));
        if (!empty($power_log_bill)) {
            $bill_top_id = $bill_id_arr[$power_log_bill];
        } else {
            $bill_top_id = 0;
        }
        $sum_bill = count($bill_arr);
        $end_bill_id = $bill_top_id + $limit_val;
        if ($end_bill_id > $sum_bill) {
            $end_bill_id = $sum_bill;
        }
        if (count($bill_arr) > 0 && ($bill_top_id + 1) < $sum_bill) {
            $Stream = M('stream', 'tb_wms_');
            for ($i = $bill_top_id; $i < $end_bill_id; $i++) {
                $max_bill_id = $bill_arr[$end_bill_id - 1]['id'];
                $val = $bill_arr[$i];
//            check type
                $type = null;
                if ($this->check_type($val['bill_type']) == 'out') {
                    $type = '-';
                }
                $stream = $Stream->where('bill_id = ' . $val['id'])->field('id,bill_id,GSKU,send_num,unit_price,currency_id')->select();
                foreach ($this->return_this($stream) as $k => $v) {
//                update currency
//                N000590300 RMB
                    if ($v['currency_id'] != 'N000590300') {
                        $update_currency = $this->update_currency($this->bill_state[$v['currency_id']], $val['bill_date']);
                        $price = $v['unit_price'] * $update_currency;
                    } else {
                        $price = $v['unit_price'];
                    }
                    $arr['num'] = $type . $v['send_num'];
                    $arr['price'] = $price;
                    $arr['bill_id'] = $v['bill_id'];
                    $arr['bill_date'] = $val['bill_date'];
                    $arr['currency'] = $update_currency;
                    $arrs_sku[$v['GSKU']][] = $arr;
                }
            }
            trace($arrs_sku, '$arrs_sku');
            $Power = M('power', 'tb_wms_');
            foreach ($arrs_sku as $k => $v) {
//               期初
                $power_old_data = $Power->where('SKU_ID = \'' . $k . '\'')->find();
                if (empty($power_old_data)) {
                    $sku_weight = $this->weight_list($v);
                } else {
                    $sku_weight = $this->weight_list($v, $power_old_data['weight'], $power_old_data['num'], $power_old_data['num'] * $power_old_data['weight']);
                }
                $data['weight'] = $sku_weight['price'];
                $data['num'] = $sku_weight['num'];
                $Power_log->bill_id = $data['bill_id'] = $v[count($v) - 1]['bill_id'];
                $data['update_time'] = date('Y-m-d H:i:s');
                $power_old = $Power->where('SKU_ID = \'' . $k . '\'')->count();
                if ($power_old == 1) {
                    $power_old = $Power->where('SKU_ID = \'' . $k . '\'')->getField('SKU_ID,update_time,bill_id,num,weight');
                    $power = $Power->where('SKU_ID = \'' . $k . '\'')->save($data);
                    $Power_log->balance = serialize($power_old);
                } else {
                    $data['SKU_ID'] = $k;
                    $power = $Power->add($data);
                }

                $Power_log->add_time = date('Y-m-d H:i:s');
                $Power_log->sku = $k;
                $Power_log->actiontype = serialize($data) . '---' . serialize($v);
                $power = $Power_log->add();

            }
            if (!empty($max_bill_id)) {
                $Power_log->bill_id = $max_bill_id;
                $Power_log->actiontype = 'end_bill_id';
                $Power_log->add_time = date('Y-m-d H:i:s');
                $power = $Power_log->add();
            }
            echo $power;
        } else {
            printf("end sql %s ,count bill %s ,top id %s, sum bill %s , %s", $Bill->getLastSql(), count($bill_arr), $bill_top_id, $sum_bill, PHP_EOL);
        }

    }

    /**
     * 期初数据
     */
    private function weight_list($arr, $y_price = 0, $y_num = 0, $y_sum = 0)
    {
        foreach ($this->return_this($arr) as $key => $val) {
            if ($val['num'] > 0) {
                $y_sum += $val['num'] * $val['price'];
                $y_num += $val['num'];
                $y_price = $y_sum / abs($y_num);
            } else {
                $y_num += $val['num'];
                $y_sum = $y_num * $y_price;
            }
        }
        $res['num'] = $y_num;
        $res['price'] = $y_price;
        $res['sum'] = $y_sum;
        return $res;
    }

    private function check_type($b)
    {
        if (empty(session('get_outgoing')) || empty(session('get_out'))) {
            $this->get_outgoing();
            $this->get_out();
        }
        if (in_array($b, session('get_outgoing'))) {
            return 'out';
        } elseif (in_array($b, session('get_out'))) {
            return 'go';
        } else {
            return false;
        }
    }

    private function get_outgoing()
    {
        $Res = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '出库类型';
        $res = $Res->where($where)->getField('CD,CD_VAL');
        $res_keys = array_keys($res);
        session('get_outgoing', $res_keys);
        return $res_keys;
    }

    private function get_out()
    {
        $Res = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '入库类型';
        $res = $Res->where($where)->getField('CD,CD_VAL');
        $res_keys = array_keys($res);
        session('get_out', $res_keys);
        return $res_keys;
    }

    /**
     * @param $unit_price
     * @param $digit
     * @param $date
     */
    private function update_currency($currency, $date)
    {
        $url = INSIGHT . '/dashboard-backend/external/exchangeRate?date=' . $date . '&dst_currency=CNY&src_currency=' . $currency;
        $url_md5 = md5($url);
        if (empty(session($url_md5))) {
            $currency = json_decode(curl_request($url), 1);
            session($url_md5, $currency);
        } else {
            $currency = session($url_md5);
        }
        if (empty($currency['data'][0]['rate'])) {
            return 1;
        } else {
            return $currency['data'][0]['rate'];
        }


    }

    private function get_xchr($updated_time)
    {
        $Xchr = M('xchr', 'tb_ms_');
        $where['updated_time'] = $updated_time;
        $xchr = $Xchr->where($where)->order('updated_time desc')->find();
        return $xchr;
    }

    // 币种
    private function get_currency()
    {
        $Currency = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '기준환율종류코드';
        $currency = $Currency->where($where)->getField('CD,CD_VAL');
        $currency_keys = array_keys($currency);
        session('currency_keys', $currency_keys);
        return $currency_keys;
    }

    private function get_log()
    {


    }

    private function log()
    {

    }

    /**
     * 清理权值
     */
    public function clean_king()
    {
        if (I('key') == 'dfbensie1') {
            $Power = M('power', 'tb_wms_');
            $data['weight'] = 0;
            $data['num'] = 0;
            $power = $Power->where('1 = 1')->data($data)->save();
            $Power_log = M('power_log', 'tb_wms_');
            $power_log = $Power_log->where('1 = 1')->delete();
            echo 'ok';
        } else {
            echo 'false';
        }
    }

    /**
     * 测试
     */
    public function test()
    {
        $url = 'http://insight.gshopper.com/dashboard-backend/external/exchangeRate?date=2016-05-12&dst_currency=CNY&src_currency=USD1';

    }

    private function return_this($e)
    {
        foreach ($e as $k) {
            yield $k;
        }
    }

}