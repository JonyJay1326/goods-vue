<?php

/**
 * User: yangsu
 * Date: 17/5/17
 * Time: 18:02
 */
class DynamicAction extends BaseAction
{

    public $action = [
        "inventory_status" => 0,
        "warehouse" => 0,
        "all_channel" => 0,
        "switch1" => false,
        "GUDS_CNS_NM" => '',
        "SKU" => '',
        "ks_ico" => 'arrow-down-b',
        "jx_ico" => 'arrow-down-b',
    ];
    public $warehouse_head = [
        "CD" => 0,
        "company_id" => 0,
        "warehouse" => '全部'
    ];
    public $channel_head = [
        "CD" => 0,
        "CD_VAL" => '全部',
        "ETc" => 0
    ];
    public $key_index = [1, 2, 4];
    public $ratio = [1, 2, 3, 4, 5, 6, 7, 8];

    public function _initialize()
    {

    }

    public function early()
    {
        ini_set('max_execution_time', 18000);
        ini_set('default_socket_timeout', 18000);
//        $this->upd_safes();
        $Stand = M('standing', 'tb_wms_');
        $where_stand['tb_wms_standing.total_inventory'] = array('neq', 0);
        $where_stand['tb_wms_standing.CHANNEL_SKU_ID'] = array('eq', 0);
        $show = null;
        if ('down' != I('post.down')) {
            if (!empty(I("SKU"))) {
                $where_stand['tb_wms_standing.SKU_ID'] = array('like', "%" . I("SKU") . "%");
                $this->action['SKU'] = I('SKU');
            }
            if (!empty(I("GUDS_CNS_NM"))) {
                $where_stand['tmg.GUDS_NM'] = array('like', "%" . I("GUDS_CNS_NM") . "%");
                $this->action['GUDS_CNS_NM'] = I('GUDS_CNS_NM');
            }
            if (!empty(I("DELIVERY_WAREHOUSE"))) {
                $where_stand['tmg.DELIVERY_WAREHOUSE'] = array('eq', I("DELIVERY_WAREHOUSE"));
                $this->action['warehouse'] = I('DELIVERY_WAREHOUSE');

            }
            /*   if (I("channel") == 'false') {
                   $where_stand['tb_wms_standing.channel'] = array('eq', 'N000830100');
               }
               if (I("channel") == 'true') {
                   if (I('all_channel')) {
                       $where_stand['tb_wms_standing.channel'] = array('eq', I('all_channel'));
                   }
                   $this->action['switch1'] = true;
                   $this->action['all_channel'] = I('all_channel');
               }*/
            if (I('all_channel')) {
                $where_stand['tb_wms_standing.channel'] = array('eq', I('all_channel'));
                $this->action['all_channel'] = I('all_channel');
            }
            $this->action['ks_ico'] = I('ks_ico');
            $this->action['jx_ico'] = I('jx_ico');
            if ($this->action['ks_ico'] == 'arrow-down-b') {
                $order = 'tb_wms_standing.sale desc';
            } else {
                $order = 'tb_wms_standing.sale asc';
            }
        }
        if (!empty(I("inventory_status"))) {
            $this->action['inventory_status'] = I('inventory_status');
//            滞销（待）
            $Early = M('early', 'tb_wms_');
            if (I("inventory_status") == 5) {
                if (!empty(I("GUDS_CNS_NM")) || !empty(I("DELIVERY_WAREHOUSE"))) {
                    $count_all = $Stand->cache(30)->where($where_stand)
                        ->join('left join tb_wms_early on tb_wms_early.SKU = tb_wms_standing.SKU_ID')
                        ->join('left join (select * from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                        ->field('tb_wms_standing.SKU_ID')
                        ->count();
                } else {
                    $count_all = $Stand->cache(30)->where($where_stand)
                        ->join('left join tb_wms_early on tb_wms_early.SKU = tb_wms_standing.SKU_ID')
                        ->field('tb_wms_standing.SKU_ID')
                        ->count();
                }
                $where_stand['tb_wms_early.inventory_status'] = array('IN', array('1', '2', '3', '4'));
                $sel_h = $Stand->where($where_stand)
                    ->join('left join tb_wms_early on tb_wms_early.SKU = tb_wms_standing.SKU_ID AND tb_wms_early.channel = tb_wms_standing.channel')
                    ->field('tb_wms_standing.SKU_ID,tb_wms_standing.channel')
                    ->select();
                unset($where_stand['tb_wms_early.inventory_status']);
                $count = $count_all - count($sel_h);
            } else {
                $Model = new Model();
                $early_sql = $Early->where('inventory_status = ' . I('inventory_status'))->select(false);
                if (!empty(I("GUDS_CNS_NM")) || !empty(I("DELIVERY_WAREHOUSE"))) {
                    $count = $Model->table($early_sql . ' es')->where($where_stand)
                        ->join('left join tb_wms_standing on es.SKU = tb_wms_standing.SKU_ID AND es.channel = tb_wms_standing.channel')
                        ->join('left join (select * from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                        ->field('tb_wms_standing.total_inventory')
                        ->count();
                } else {
                    $count = $Model->table($early_sql . ' es')->where($where_stand)
                        ->join('left join tb_wms_standing on es.SKU = tb_wms_standing.SKU_ID AND es.channel = tb_wms_standing.channel')
                        ->field('tb_wms_standing.total_inventory')
                        ->count();
                }
                $where_stand['tb_wms_early.inventory_status'] = I('inventory_status');
            }
        } else {
            if (empty(I("DELIVERY_WAREHOUSE")) && empty(I("GUDS_CNS_NM"))) {
                $top_data = $Stand->where($where_stand)
                    ->field('tb_wms_standing.total_inventory')
                    ->select();
            } else {
                $top_data = $Stand->where($where_stand)
                    ->join('left join (select * from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                    ->field('tb_wms_standing.total_inventory')
                    ->select();
            }
            $count = count($top_data);
        }
        if ('down' != I('post.down')) {
            import('ORG.Util.Page');
            $page_num = I('page_num') > 0 ? I('page_num') : 10;
            $Page = new Page($count, $page_num);
            $Page->page_num = $page_num;
            $show = $Page->show();
            $model_s = M();
            if (!empty(I("inventory_status"))) {
                $sql_s = $model_s->table('tb_wms_standing')->where($where_stand)
                    ->field('tb_wms_standing.*,tmg.DELIVERY_WAREHOUSE as warehouse_id')
                    ->join('left join tb_wms_early on tb_wms_early.SKU = tb_wms_standing.SKU_ID AND tb_wms_early.channel = tb_wms_standing.channel')
                    ->join('left join  (select tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                    ->order($order)
                    ->select(false);
                $stream_arr = $model_s->table($sql_s . ' s')
                    ->join('left join tb_wms_early on tb_wms_early.channel = s.channel AND tb_wms_early.SKU = s.SKU_ID')
                    ->order('s.SKU_ID,s.channel desc')
                    ->field('s.*,tb_wms_early.day7_sales,tb_wms_early.buy_date,tb_wms_early.ratio,tb_wms_early.inventory_status')
                    ->select();

                if (I("inventory_status") == 5) {
                    foreach ($sel_h as $k => $v) {
                        $case_hash[] = $this->Two_cases_berth($v);
                    }
                    foreach ($stream_arr as $k => $v) {
                        $stream_hash = $this->Two_cases_berth($v);
                        if (in_array($stream_hash, $case_hash)) {
                            unset($stream_arr[$k]);
                        }
                    }
                    $count = count($stream_arr);
                    $Page = new Page($count, $page_num);
                    $Page->page_num = $page_num;
                    $show = $Page->show();
                }
                $stream_arr = array_slice($stream_arr, $Page->firstRow, $Page->listRows);


            } else {
                $sql_s = $model_s->table('tb_wms_standing')->where($where_stand)
                    ->field('tb_wms_standing.*,tmg.DELIVERY_WAREHOUSE as warehouse_id')
                    ->join('left join  (select tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                    ->order($order)
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->select(false);
                $stream_arr = $model_s->table($sql_s . ' s')
                    ->join('left join tb_wms_early on tb_wms_early.channel = s.channel AND tb_wms_early.SKU = s.SKU_ID')
                    ->order('s.SKU_ID,s.channel desc')
                    ->field('s.*,tb_wms_early.day7_sales,tb_wms_early.buy_date,tb_wms_early.ratio,tb_wms_early.inventory_status')
                    ->select();

            }

        }

        if ($stream_arr) {
            $new_stream_arr = $this->install_gods_opt($stream_arr);
        }
        $_param = $this->_param();
        $_param = empty($_param) ? 0 : $_param;
        $this->assign('action', json_encode($this->action, JSON_UNESCAPED_UNICODE));
        $this->assign('ratio', json_encode($this->ratio, JSON_UNESCAPED_UNICODE));
        $this->assign('param', json_encode($_param, JSON_UNESCAPED_UNICODE));
        $this->assign('get_url', json_encode(I('get'), JSON_UNESCAPED_UNICODE));
        $this->assign('stream_arr', json_encode($new_stream_arr, JSON_UNESCAPED_UNICODE));
        $house_list = StockModel::get_show_warehouse();
        array_unshift($house_list, $this->warehouse_head);
        $this->assign('house_list', json_encode($house_list, JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode(StockModel::get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('inventory_status', json_encode(StockModel::$inventory_status, JSON_UNESCAPED_UNICODE));
        $channel = StockModel::get_all_channel();
        array_unshift($channel, $this->channel_head);
        $this->assign('all_channel', json_encode($channel, JSON_UNESCAPED_UNICODE));
        $this->assign('pages', $show);
        $this->assign('count', $count);
        $this->display();
    }

    private function Two_cases_berth($e)
    {
        return md5($e['SKU_ID'] . $e['channel']);
    }


    public function Pin()
    {
        $this->assign('title', '动态库存');
        $this->assign('scope', 'dynamicpin');
        $this->display('ng-public/index');
    }

    private function getpin($start_date, $end_date)
    {
        $Operation_history = M('operation_history', 'tb_wms_');
        if (!empty($start_date) && !empty($end_date)) {
            $where['create_time'] = array('between', array($start_date, $end_date));
        } elseif (empty($start_date) && !empty($end_date)) {
            $where['create_time'] = array('ELT', $end_date);
        } elseif (empty($end_date) && !empty($start_date)) {
            $where['create_time'] = array('EGT', $start_date);
        }
        $where['ope_type'] = array('EQ', 'N001010300');
        return $Operation_history->where($where)->field('sku_id,sum(change_num) as change_sum')->group('sku_id')->select();
    }

//  出入库计算方式
    private function getbillpin($start_date, $end_date, $link_bill_id = null)
    {
        $Bill = M('bill', 'tb_wms_');
        if (!empty($start_date) && !empty($end_date)) {
            $where['bill_date'] = array('between', array($start_date, $end_date));
        } elseif (empty($start_date) && !empty($end_date)) {
            $where['bill_date'] = array('ELT', $end_date);
        } elseif (empty($end_date) && !empty($start_date)) {
            $where['bill_date'] = array('EGT', $start_date);
        }
        $where['bill_type'] = array('EQ', 'N000950100');
        if ($link_bill_id == 1) {
            $where['link_bill_id'] = array(array('EQ', ''), array('EXP', 'IS NULL'), 'or');
            $bill_id_arr = $Bill->where($where)->field('id')->select();
        }
        $bill_id_arr = $Bill->where($where)->field('id')->select();
        if ($bill_id_arr) {
            $Stream = M('stream', 'tb_wms_');
            $where_stream['tb_wms_stream.bill_id'] = array('IN', array_column($bill_id_arr, 'id'));
            if ($link_bill_id == 1) {
                return $Stream->where($where_stream)
                    ->field('tb_wms_bill.channel as PLAT_FORM,tb_wms_stream.GSKU as sku_id,sum(tb_wms_stream.send_num) as change_sum')
                    ->join('left join tb_wms_bill on tb_wms_bill.id = tb_wms_stream.bill_id')
                    ->group('GSKU,PLAT_FORM')->select();
            } else {
                return $Stream->where($where_stream)->field('GSKU as sku_id,sum(send_num) as change_sum')->group('GSKU')->select();
            }
        }

    }

    private function compare_sale($all_arr, $del_arr)
    {


    }


    public function getdata()
    {
        $post_json = file_get_contents('php://input', 'r');
        $post = json_decode($post_json);
        $res_opt['state'] = $sale_state = $post->state;
        $start_date = date("Y-m-d", strtotime($post->start_date));
        $end_date = date("Y-m-d", strtotime($post->end_date));
        $date_diff = $this->daysbetweendates($start_date, $end_date);
        $res = $this->getbillpin($start_date, $end_date);
//        $res = $this->getpin($start_date, $end_date);
        $Standing = M('standing', 'tb_wms_');
        $where['tb_wms_standing.total_inventory'] = array('neq', 0);
        //            is no channel
        $where['tb_wms_standing.channel'] = array('EQ', 'N000830100');
        if ($sale_state == 1) {
            $where['tb_wms_standing.SKU_ID'] = array('IN', array_column($res, 'sku_id'));
            $data_arr = $standing = $Standing->where($where)
                ->join('left join  (select tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
                ->field('tb_wms_standing.*,tmg.DELIVERY_WAREHOUSE as warehouse_id,tb_wms_power.weight')
                ->select();
        } else {
            if (count($res)) {
                $where['tb_wms_standing.SKU_ID'] = array('NOT IN', array_column($res, 'sku_id'));
            }
            $data_arr = $standing = $Standing->where($where)
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
                ->join('left join  (select tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                ->field('tb_wms_standing.*,tb_wms_power.weight,tmg.DELIVERY_WAREHOUSE as warehouse_id')->select();

        }

        $top_nums = 0;
        $top_sums = 0;
        foreach ($data_arr as $v) {
            $top_nums += $v['total_inventory'];
            $top_sums += $v['total_inventory'] * $v['weight'];
        }

        $res_opt['all_existing'] = $top_nums;
        $res_opt['all_sum'] = $top_sums;
        $res_opt['list'] = $this->install_gods_opt($data_arr);
        $res_opt['res_key'] = array_column($res, 'change_sum', 'sku_id');
        $res_opt['count'] = count($res_opt['list']);
        $res_opt['date_diff'] = $date_diff;


        import('ORG.Util.Page');
        $page_num = I('page_num') > 0 ? I('page_num') : 10;
        $Page = new Page(count($res), $page_num);
        $Page->parameter = $Page->url = '/index.php?m=dynamic&a=pin#/dynamicpin';
        $res_opt['show'] = $Page->show();
        $res_opt['list'] = array_slice($res_opt['list'], $post->firstRow, $page_num);
        $res_opt['$Page'] = $Page;
        echo json_encode($res_opt);
    }


    public function upd_safes()
    {
        ini_set('max_execution_time', 18000);
        ini_set('default_socket_timeout', 18000);
        $this->clean_early();
        $Operation_history = M('operation_history', 'tb_wms_');
        $date = date('Y-m-d', strtotime('-7 days'));
        $where['create_time'] = array('gt', $date);

        $operation_history_zy = $Operation_history->where($where)->group('order_id')->having('count(*) = 1')->field('id')->select();
        $where['ope_type'] = 'N001010300';
        $operation_history_fh = $Operation_history->where($where)->field('id')->select();
        $operation_history_zy_key = array_column($operation_history_zy, 'id');
        $operation_history_fh_key = array_column($operation_history_fh, 'id');
        if ($operation_history_zy_key && $operation_history_fh_key) {
            $operation_history_key = array_merge($operation_history_zy_key, $operation_history_fh_key);
        } elseif (count($operation_history_zy_key) > 0) {
            $operation_history_key = $operation_history_zy_key;
        } elseif (count($operation_history_fh_key) > 0) {
            $operation_history_key = $operation_history_fh_key;
        }
        $operation_history_key = array_unique($operation_history_key);
        if (count($operation_history_key) > 0) {
            $where_id['tb_wms_operation_history.id'] = array('in', $operation_history_key);
            $where_id['tb_wms_operation_history.ope_type'] = 'N001010100';
            $operation_history = $Operation_history->where($where_id)
                ->group('tb_wms_operation_history.sku_id,tb_ms_ord.PLAT_FORM')
                ->join('left join tb_ms_ord on tb_ms_ord.ORD_ID = tb_wms_operation_history.order_id')
                ->field('sum(change_num) as sum,tb_wms_operation_history.order_id,tb_wms_operation_history.sku_id,tb_wms_operation_history.ope_type,tb_wms_operation_history.change_num,tb_ms_ord.PLAT_FORM')
                ->select();
//            upd to warehouse data
            $operation_history_col = array_column($operation_history, 'sum', 'sku_id');
            $operation_history_plat_form = array_column($operation_history, 'PLAT_FORM', 'sku_id');
            $getbillpin = $this->getbillpin($date, date('Y-m-d'), 1);
            $getbillpin_col = array_column($getbillpin, 'change_sum', 'sku_id');
            $getbillpin_form = array_column($getbillpin, 'PLAT_FORM', 'sku_id');
            $arr_inter = array_intersect(array_keys($operation_history_col), array_keys($getbillpin_col));
            foreach ($arr_inter as $key => $val) {
                $mixeds[$val] = $operation_history_col[$val] + $getbillpin_col[$val];
                unset($operation_history_col[$val]);
                unset($getbillpin_col[$val]);
            }
            $mixed = $operation_history_col + $getbillpin_col + (array)$mixeds;
//            $mixed = array_merge($operation_history_cols, $getbillpin_cols,(array)$mixeds);
            foreach ($mixed as $key => $val) {
                $arr['change_num'] = $arr['sum'] = $val;
                $arr['sku_id'] = $key;
                if ($operation_history_plat_form[$key]) {
                    $arr['PLAT_FORM'] = $operation_history_plat_form[$key];
                } elseif ($getbillpin_form[$key]) {
                    $all_channel = StockModel::get_all_channel();
                    $all_channel_kel = array_column($all_channel, 'CD_VAL');
                    $all_channel_val = array_column($all_channel, 'CD', 'CD_VAL');
                    if (in_array($getbillpin_form[$key], $all_channel_kel)) {
                        $arr['PLAT_FORM'] = $all_channel_val[$getbillpin_form[$key]];
                    } else {
                        $arr['PLAT_FORM'] = $getbillpin_form[$key];
                    }
                } else {
                    $arr['PLAT_FORM'] = 'N000830100';
                }
                $operation_history_test_arr[] = $arr;
            }
            $operation_history = $this->conversion_channel($operation_history_test_arr);
            if (count($operation_history) > 0) {
                $operation_history_sku = array_column($operation_history, 'sku_id');
                $operation_history_sku_index = array_flip($operation_history_sku);
                $Standing = M('standing', 'tb_wms_');
                $where_stand['total_inventory'] = array('neq', 0);
                $where_stand['SKU_ID'] = array('in', $operation_history_sku);
                $standing = $Standing->where($where_stand)->field('SKU_ID,channel,sale')->select();
                $stand_arr = $this->join_standing_data($standing);
                $oa_arr = $this->join_data($operation_history);
                $Early = M('early', 'tb_wms_');
                $where_early['SKU'] = array('in', $operation_history_sku);
                $early = $Early->where($where_early)->select();
                $early_sku_index = array_flip(array_column($early, 'SKU'));
                foreach ($oa_arr as $k => $v) {
//   sell
                    $today = (int)$stand_arr[$k]['sale'];
                    $sum = $operation_history[$operation_history_sku_index[$v['sku_id']]]['sum'];
                    $sell = $today / ($sum / 7);
                    $safe_date = $early[$early_sku_index[$v['sku_id']]]['buy_date'] * $early[$early_sku_index[$v['sku_id']]]['ratio'];
                    if ($sell < ($safe_date * $this->key_index[0])) {
                        $early_data['inventory_status'] = 1;
                    }
                    if (($safe_date * $this->key_index[0]) <= $sell && $sell < ($safe_date * $this->key_index[1])) {
                        $early_data['inventory_status'] = 2;
                    }
                    if (($safe_date * $this->key_index[1]) <= $sell && $sell < ($safe_date * $this->key_index[2])) {
                        $early_data['inventory_status'] = 3;
                    }
                    if ($sell >= ($safe_date * $this->key_index[2])) {
                        $early_data['inventory_status'] = 4;
                    }
                    $early_data['day7_sales'] = $sum;
                    $early_data['update_user_id'] = session('m_loginname') ? session('m_loginname') : 1;
                    static::upd_early($v['sku_id'], $v['PLAT_FORM'], $early_data);
                }
            }
        } else {
//            upd to warehouse data
            $getbillpin = $this->getbillpin($date, date('Y-m-d'), 1);
            foreach ($getbillpin as $key => $val) {
                $all_channel = StockModel::get_all_channel();
                $all_channel_kel = array_column($all_channel, 'CD_VAL');
                $all_channel_val = array_column($all_channel, 'CD', 'CD_VAL');
                if (in_array($val['PLAT_FORM'], $all_channel_kel)) {
                    $val['PLAT_FORM'] = $all_channel_val[$val['PLAT_FORM']];
                } else {
                    $val['PLAT_FORM'] = $val['PLAT_FORM'];
                }
                $val['sum'] = $val['change_num'] = $val['change_sum'];
                $operation_history_test_arr[] = $val;
            }
            $operation_history = $this->conversion_channel($operation_history_test_arr);
            if (count($operation_history) > 0) {
                $operation_history_sku = array_column($operation_history, 'sku_id');
                $operation_history_sku_index = array_flip($operation_history_sku);
                $Standing = M('standing', 'tb_wms_');
                $where_stand['total_inventory'] = array('neq', 0);
                $where_stand['SKU_ID'] = array('in', $operation_history_sku);
                $standing = $Standing->where($where_stand)->field('SKU_ID,channel,sale')->select();
                $stand_arr = $this->join_standing_data($standing);
                $oa_arr = $this->join_data($operation_history);
                $Early = M('early', 'tb_wms_');
                $where_early['SKU'] = array('in', $operation_history_sku);
                $early = $Early->where($where_early)->select();
                $early_sku_index = array_flip(array_column($early, 'SKU'));
                foreach ($oa_arr as $k => $v) {
//   sell
                    $today = (int)$stand_arr[$k]['sale'];
                    $sum = $operation_history[$operation_history_sku_index[$v['sku_id']]]['sum'];
                    $sell = $today / ($sum / 7);
                    $safe_date = $early[$early_sku_index[$v['sku_id']]]['buy_date'] * $early[$early_sku_index[$v['sku_id']]]['ratio'];
                    if ($sell < ($safe_date * $this->key_index[0])) {
                        $early_data['inventory_status'] = 1;
                    }
                    if (($safe_date * $this->key_index[0]) <= $sell && $sell < ($safe_date * $this->key_index[1])) {
                        $early_data['inventory_status'] = 2;
                    }
                    if (($safe_date * $this->key_index[1]) <= $sell && $sell < ($safe_date * $this->key_index[2])) {
                        $early_data['inventory_status'] = 3;
                    }
                    if ($sell >= ($safe_date * $this->key_index[2])) {
                        $early_data['inventory_status'] = 4;
                    }
                    $early_data['day7_sales'] = empty($sum) ? 0 : $sum;
                    $early_data['update_user_id'] = session('m_loginname') ? session('m_loginname') : 1;
                    static::upd_early($v['sku_id'], $v['PLAT_FORM'], $early_data);
                }
            }
        }
        die('end_this');
    }

    public function clean_earlydata()
    {
        $Early = M('early', 'tb_wms_');
        $Early->where('1 = 1')->delete();
    }

    public function clean_early()
    {
        $Early = M('early', 'tb_wms_');
        $data['day7_sales'] = 0;
        $data['inventory_status'] = 4;
        $Early->where('1 = 1')->save($data);
    }

    public static function upd_early($sku, $channel, $data)
    {
        $Early = M('early', 'tb_wms_');
        $where['SKU'] = $sku;
        $where['channel'] = $channel;
        if ($Early->where($where)->count() > 0) {
            return $Early->where($where)->save($data);
        } else {
            if (!empty($where['SKU']) && !empty($where['channel'])) {
                $data_w = array_merge($data, $where);
                $data_w['ratio'] = 2;
                return $Early->add($data_w);
            } else {
                return false;
            }
        }
    }

    public function get_early()
    {
        $post_json = file_get_contents('php://input', 'r');
        $post = json_decode($post_json);

        $data['buy_date'] = $post->buy_date;
        $data['ratio'] = $post->ratio;
        $data['update_user_id'] = session('m_loginname');
        $data['inventory_status'] = $post->inventory_status;
        $sku = $post->sku;
        $channel = $post->channel;
        if (empty(I('sku')) && empty($channel) && empty($data['buy_date']) && empty($data['ratio'])) {
            $res['code'] = 4000;
            $res['msg'] = '数据缺失';
            $data['sku'] = $sku;
            $data['channel'] = $channel;
            $res['data'] = $data;
        } else {
            $res['data'] = static::upd_early($sku, $channel, $data);
            $data['sku'] = $sku;
            $data['channel'] = $channel;
            $res['err_data'] = $data;
            $res['code'] = 2000;
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function join_standing_data($data)
    {
        foreach ($data as $k => $v) {
            $hash_key = md5($v['channel'] . $v['SKU_ID']);
            $arr[$hash_key]['sale'] = $v['sale'];
            $arr[$hash_key]['SKU_ID'] = $v['SKU_ID'];
            $arr[$hash_key]['channel'] = $v['channel'];
        }
        return $arr;
    }

    public function join_data($data)
    {
        foreach ($data as $k => $v) {
            $hash_key = md5($v['PLAT_FORM'] . $v['sku_id']);
            $arr[$hash_key]['change_num'] = $v['change_num'];
            $arr[$hash_key]['PLAT_FORM'] = $v['PLAT_FORM'];
            $arr[$hash_key]['sku_id'] = $v['sku_id'];
            $arr[$hash_key]['PLAT_FORM'] = $v['PLAT_FORM'];
        }
        return $arr;
    }

    private function install_gods_opt($stream_arr)
    {
        foreach ($stream_arr as $key => &$val) {
            $sku = $val['SKU_ID'];
            if (empty($val['warehouse_id'])) {

            } else {

                $model = D('Opt');
                $GUDS_ID = $val['SKU_ID'];
                $guds = $model->cache(600)->relation(true)->where('GUDS_OPT_ID = ' . $GUDS_ID)->select();
                $guds['Opt'] = $guds;
                foreach ($guds['Opt'] as $key => $opt) {
                    $opt_val = explode(';', $opt['GUDS_OPT_VAL_MPNG']);
                    foreach ($opt_val as $v) {
                        $val_str = '';
                        $o = explode(':', $v);
                        $model = M('ms_opt', 'tb_');
                        $opt_val_str = $model->cache(600)->join('left join tb_ms_opt_val on tb_ms_opt_val.OPT_ID = tb_ms_opt.OPT_ID')->where('tb_ms_opt.OPT_ID = ' . $o[0] . ' and tb_ms_opt_val.OPT_VAL_ID = ' . $o[1])->field('tb_ms_opt.OPT_CNS_NM,tb_ms_opt_val.OPT_VAL_CNS_NM,tb_ms_opt.OPT_ID')->find();
                        if (empty($opt_val_str)) {
                            $val_str = L('标配');
                        } elseif ($opt_val_str['OPT_ID'] == '8000') {
                            $val_str = L('标配');
                        } elseif ($opt_val_str['OPT_ID'] != '8000') {
                            $val_str = $opt_val_str['OPT_CNS_NM'] . ':' . $opt_val_str['OPT_VAL_CNS_NM'] . ' ';
                        }
                        $guds['opt_val'][$key]['val'] .= $val_str;
                    }
                }


                $val['guds'] = $guds;
                unset($guds);
                $new_stream_arr[] = $val;
            }
        }
        return $new_stream_arr;
    }

    public function daysbetweendates($date1, $date2)
    {
        $date1 = strtotime($date1);
        $date2 = strtotime($date2);
        $days = ceil(abs($date1 - $date2) / 86400);
        return $days;
    }

    public function conversion_channel($e)
    {
        foreach ($e as $v) {
            $arr[$v['PLAT_FORM'] . $v['sku_id']]['change_sum'] = $arr[$v['PLAT_FORM'] . $v['sku_id']]['change_num'] = $arr[$v['PLAT_FORM'] . $v['sku_id']]['sum'] += empty($v['sum']) ? 0 : $v['sum'];
            $arr[$v['PLAT_FORM'] . $v['sku_id']]['PLAT_FORM'] = $v['PLAT_FORM'];
            $arr[$v['PLAT_FORM'] . $v['sku_id']]['sku_id'] = $v['sku_id'];
        }
        foreach ($arr as $v) {
            $arrs[] = $v;
        }
        return $arrs;
    }

    public function test()
    {
        $email = new SMSEmail();
        $title = 'a';
        $message = 'a';
        $cc = ['yuanshixiao@gshopper.com', 'huali@gshopper.com'];
        $res = $email->sendEmail('yangsu@gshopper.com', $title, $message, $cc);
    }

    public function tostring($e, $k)
    {
        foreach ($e as &$val) {
            $val['sku_id'] = (string)$val[$k];
        }
        return $e;
    }

    public function keytostring($e)
    {
        foreach ($e as $k => $v) {
            $k = (string)$k;
            $arr[$k] = $v;
        }
        return $arr;
    }

}



