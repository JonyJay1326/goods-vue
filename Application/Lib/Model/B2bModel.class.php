<?php

/**
 * User: yansu
 * Date: 17/5/27
 * Time: 11:24
 */
class B2bModel extends Model
{
    public static $number_th = ['1st', '2nd', '3rd'];
    public static $bill_state = [
        'N000590100' => 'USD',
        'N000590200' => 'KRW',
        'N000590300' => 'CNY',
        'N000590400' => 'JPY',
        'N000590500' => 'EUR',
        'N000590600' => 'HKD',
        'N000590700' => 'SGD',
        'N000590800' => 'AUD',
        'N000590900' => 'GBP'
    ];

    public static $search_type = [
        'SKU_ID' => 'SKUID',
        'bar_code' => 'BarCode',
        'goods_title' => '商品名称',
    ];

//    now state
    public static $code = [
        /*'now_state' =>
            [
                '无',
                '待发货',
                '部分发货',
                '发货完成',
                '部分到港',
                '全部到港',
                '部分入库',
                '已入库',
                '部分收款',
                '全部收款',
                '已收到退税'
            ],*/
        'overdue' => [
            '未逾期',
            '已逾期',
        ],
        'order_fh' => [
            '全部',
            '待发货',
            '已发货',
        ],
        'order_sk' => [
            '全部',
            '待收款',
            '已收款'
        ],
        'order_ts' => [
            '全部',
            '待退税',
            '已退税'
        ],
        'currency_bz' => [
            'CNY',
            'KRW',
            'USD',
            'HKD',
            'JPY',
            'SGD',
            'EUR',
            'GBP',
            'AUD'
        ],
        'now_state' =>
            [
                '全部',
                '待发货',
                '发货完成',
                '未收款',
                '已收款',
                '已收到退税'
            ],
        'warehouse_state' =>
            [
                '全部',
                '待确认',
                '已确认'
            ],
        'warehousing_state' =>
            [
                '全部',
                '未确认',
                '已确认',
            ],
        'period' => [
            '1次性付款',
            '二期',
            '三期',
            '月结'
        ],
        'gathering' => [
            '全部',
            '待收款',
            '已收款'
        ],
        'ship_state' =>
            [
                '全部',
                '待发货',
                '已发货'
            ],
        'is_no' =>
            [
                '是', '否'
            ],
        'transaction_type' =>
            [
                '贷款', '退税'
            ],
        'or_invoice_arr' => ['已开票', '未开票'],
        'deviation_cause' => ['发错货', '少发货', '多发货', '标签/外观/质量不符客户拒收', '保质期不符客户拒收'],
        'node_type' => ['合同后', '发货后', '到港后', '入库后', '收到发票后'],
        'node_date' => [1, 3, 5, 7, 10, 15, 30, 45, 60, 75, 90, 100, 0],   // 付款天数
//        'node_is_workday' => ["天", "工作日"],
        'node_is_workday' => ["天"],
        'node_prop' => [5, 10, 15, 20, 25, 30, 35, 40, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100],
        'shipping' => ["CIF", "FOB", "EXW", "DDP", "一般贸易","KR Domestic","其他/Others"],
        'invioce' => ["不开票", "增值税普通发票", "增值税专用发票"],
        'tax_point' => [0, 6, 8, 10, 13, 17],
        'tax_rebate_ratio' => [0, 8, 10, 13, 17],
        'order_overdue_statue' => ['未逾期', '部分逾期', '已逾期'],
        'overdue_statue' => ['当期未逾期', '当期逾期', '实际未逾期', '实际逾期']
    ];

    public static $checkdata = [
        'b2bgathering' => [
            'PO_ID' => ['tb' => 'receipt', 'type' => 'LIKE', 'require' => '',],
            'client_id' => ['tb' => 'receipt', 'type' => 'LIKE', 'require' => ''],  // 客户名称
            'sales_team_code' => ['tb' => 'receipt', 'type' => '', 'require' => ''],  // 销售团队ID
        ],
        'b2bgathering_def' => [
            'pre' => 'tb_b2b_',
            'type' => 'EQ'
        ],
        'b2bwarehousing' => [
            'warehouse' => ['tb' => 'ship_list', 'type' => 'LIKE', 'require' => ''],
            'PO_ID' => ['tb' => 'info', 'type' => 'LIKE', 'require' => '',],
            'CLIENT_NAME' => ['tb' => 'info', 'type' => 'LIKE', 'require' => ''],  // 客户名称
            'SALES_TEAM' => ['tb' => 'info', 'type' => '', 'require' => ''],  // 销售团队ID
        ],
        'b2bwarehousing_def' => [
            'pre' => 'tb_b2b_',
            'type' => 'EQ'
        ],
        'b2blist' => [
            'warehouse_state' => ['tb' => 'warehouse_list', 'type' => '', 'require' => ''],
            'order_overdue_statue' => ['tb' => 'order', 'type' => '', 'require' => ''],
            'order_fh' => ['tb' => 'ship_list', 'type' => '', 'require' => ''],
            'order_sk' => ['tb' => 'receipt', 'type' => '', 'require' => ''],
            'order_ts' => ['tb' => 'receipt', 'type' => '', 'require' => ''],
            'PO_ID' => ['tb' => 'order', 'type' => 'LIKE', 'require' => '',],
            'CLIENT_NAME' => ['tb' => 'info', 'type' => 'LIKE', 'require' => ''],
            'PO_USER' => ['tb' => 'info', 'type' => 'LIKE', 'require' => ''],
            'SALES_TEAM' => ['tb' => 'info', 'type' => '', 'require' => ''],
            'BILLING_CYCLE_STATE' => ['tb' => 'info', 'type' => '', 'require' => ''],
            'goods_title' => ['tb' => '', 'type' => '', 'require' => '', 'pre' => 'g'],
            'SKU_ID' => ['tb' => '', 'type' => '', 'require' => '', 'pre' => 'g'],
            'po_time_action' => ['tb' => 'order', 'type' => 'BETWEEN', 'require' => ''],
            'po_time_end' => ['tb' => 'order', 'type' => 'BETWEEN', 'require' => '']
        ],
        'b2blist_def' => [
            'pre' => 'tb_b2b_',
            'type' => 'EQ'
        ],
        'do_ship_list' => [
            'shipping_status' => ['tb' => 'doship', 'type' => '', 'require' => ''],
            'CLIENT_NAME' => ['tb' => 'doship', 'type' => 'LIKE', 'require' => ''],
            'PO_ID' => ['tb' => 'doship', 'type' => 'LIKE', 'require' => ''],
            'delivery_warehouse_code' => ['tb' => 'doship', 'type' => 'LIKE', 'require' => ''],
            'sales_team_code' => ['tb' => 'doship', 'type' => '', 'require' => ''],
        ],
        'do_ship_list_def' => [
            'pre' => 'tb_b2b_',
            'type' => 'EQ'
        ],
        'b2b_warehouse_list' => [
            'shipping_status' => ['tb' => 'warehouse_list', 'type' => '', 'require' => ''],
            'CLIENT_NAME' => ['tb' => 'warehouse_list', 'type' => 'LIKE', 'require' => ''],
            'PO_ID' => ['tb' => 'warehouse_list', 'type' => 'LIKE', 'require' => ''],
            'delivery_warehouse_code' => ['tb' => 'warehouse_list', 'type' => '', 'require' => ''],
            'sales_team_code' => ['tb' => 'warehouse_list', 'type' => '', 'require' => ''],
        ],
        'b2b_warehouse_list_def' => [
            'pre' => 'tb_b2b_',
            'type' => 'EQ'
        ],
        '_def' => [
            'pre' => 'tb_b2b_',
            'type' => 'EQ'
        ],

    ];

    /**
     * public get code
     * @param $nm_val
     * @param $cache
     * @param int $cache_time
     * @param string $nm
     * @return mixed
     */
    public static function get_code($nm_val, $cache, $cache_time = 60, $nm = 'CD_NM')
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        $where[$nm] = $nm_val;
        if (1 == $cache) return $Cd->cache($cache_time)->where($where)->getField('CD,CD_VAL,ETc');
        $cd = $Cd->where($where)->getField('CD,CD_VAL,SORT_NO,ETc');
        if ($cd) return $cd;
        return static::to_cd(static::$code[$nm_val]);
    }

    /**
     * @param $nm_val
     * @param $cache
     * @param int $cache_time
     * @param string $nm
     * @return mixed
     */
    public static function get_code_lang($nm_val, $cache, $cache_time = 60, $nm = 'CD_NM')
    {
        $cd_arr = B2bModel::get_code($nm_val, $cache, $cache_time = 60, $nm = 'CD_NM');
        foreach ($cd_arr as &$v) {
            $v['CD_VAL'] = L($v['CD_VAL']);
        }
        return $cd_arr;
    }

//  币种
    public static function get_currency()
    {
        $Currency = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '기준환율종류코드';
        return $Currency->where($where)->getField('CD,CD_VAL,ETc');
    }

//  退税比例
    public static function get_tax_rebate_ratio()
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '退税比例';
        return $Cd->where($where)->getField('CD,CD_VAL,ETc');
    }

//  销售团队
    public static function get_sales_team()
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '销售团队';
        return $Cd->where($where)->getField('CD,CD_VAL,ETc');
    }

//    付款节点
    public static function get_payment_node()
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '付款节点';
        return $Cd->where($where)->getField('CD,CD_VAL,ETc');
    }

//    付款周期
    public static function get_payment_cycle()
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '付款周期';
        return $Cd->where($where)->getField('CD,CD_VAL,ETc');
    }

//    发票and税点
    public static function get_invoice_point()
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        $where_invoice['CD_NM'] = '发票类型';
        $res['invoice'] = $Cd->where($where_invoice)->getField('CD,CD_VAL,ETc');
        $where_point['CD_NM'] = '发票税点';
        $res['point'] = $Cd->where($where_point)->getField('CD,CD_VAL,ETc');
        return $res;
    }


//  发货方式
    public static function get_shipping_method()
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '发货方式';
        return $Cd->where($where)->getField('CD,CD_VAL,ETc');
    }

    /**
     * 获取商品信息
     *
     */
    public static function get_goods_info($sku)
    {
        $Guds_opt = M('guds_opt', 'tb_ms_');
        $guds = $Guds_opt
            ->field('tb_ms_guds_opt.GUDS_OPT_ORG_PRC,tb_ms_guds.STD_XCHR_KIND_CD,tb_ms_guds_opt.GUDS_ID')
            ->join('left join tb_ms_guds on tb_ms_guds.GUDS_ID = tb_ms_guds_opt.GUDS_ID')
            ->where('tb_ms_guds_opt.GUDS_OPT_ID = ' . $sku)
            ->select();
        return $guds[0];
    }

    /**
     * @return array
     */
    private static function join_guds($goods)
    {
        $date = date('Y-m-d h:i:s');
        foreach ($goods as $v) {
            $gud['GSKU'] = $v['SHIPPING_SKU'];
            $info = self::get_goods_info($gud['GSKU']);
            $gud['taxes'] = 0;
            $gud['should_num'] = $v['DELIVERED_NUM'];
            $gud['send_num'] = $v['DELIVERED_NUM'];
            $gud['price'] = $info['GUDS_OPT_ORG_PRC'];// 单价
            $gud['currency_id'] = $info['STD_XCHR_KIND_CD'];// 币种
            $gud['currency_time'] = $date;
            $guds[] = $gud;
        }
        return $guds;
    }

    /**
     * 根据合同编号查询是否有相应的合同
     * @param $conNo
     * @return bool|mixed
     */
    public function search_contracct_by_con_no($conNo)
    {
        $model = D('TbCrmContract');
        $ret = $model->where('CON_NO ="' . $conNo . '"')->find();
        if ($ret) return $ret;
        return false;
    }

    /**
     * get json data to file flow
     * @param null $p
     * @param null $assoc
     * @return mixed
     */
    public static function get_data($p = null, $assoc = null)
    {
        $data = file_get_contents("php://input", 'r');
        if ($assoc) return json_decode($data, $assoc)[$p];
        if ($p) return json_decode($data)->$p;
        return json_decode($data);
    }

    /**
     * @param $res
     * @return string
     */
    public static function set_json($res)
    {
        return json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    /**
     * cd code
     * @param $e
     * @return array
     */
    private function to_cd($e)
    {
        foreach ($e as $k => $v) {
            $arr['CD'] = $k;
            $arr['CD_VAL'] = $v;
            $arr['ETc'] = $v;
            $arrs[] = $arr;
        }
        return $arrs;
    }

    /**
     * @param $k
     * @param $check
     * @param $e
     * @param $checks
     * @return mixed
     */
    public static function getpio($k, $check, $e, $checks)
    {
        return empty($checks[$k][$e]) ? static::$checkdata[$check . '_def'][$e] : $checks[$k][$e];
    }

    /**
     * @param $v
     * @param $type
     * @return string
     */
    public static function check_v($v, $type)
    {
        switch ($type) {
            case 'LIKE':
                $check_v = '%' . $v . '%';
                break;
            default:
                $check_v = $v;
                break;

        }
        return $check_v;
    }

    /**
     * @param $data
     * @param $check
     * @return null
     */
    public static function joinwhere($data, $check)
    {
        $checks = static::$checkdata[$check];
        $where = null;
        $start_date = $data['po_time_action'];
        $end_date = $data['po_time_end'];
        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($checks)) && !empty($v)) {
                $pre = static::getpio($k, $check, 'pre', $checks);
                $type = static::getpio($k, $check, 'type', $checks);
                if (!empty($start_date) && !empty($end_date)) {
                    $where['po_time'] = array('between', array($start_date, $end_date));
                } elseif (empty($start_date) && !empty($end_date)) {
                    $where['po_time'] = array('ELT', $end_date);
                } elseif (empty($end_date) && !empty($start_date)) {
                    $where['po_time'] = array('EGT', $start_date);
                } else {
                    $where[$pre . $checks[$k]['tb'] . '.' . $k] = array($type, static::check_v($v, $type));
                }
            }
        }
        return $where;
    }

    public static function get_area()
    {
        $model = M('_crm_site', 'tb_');
        return $model->getField('ID,NAME');
    }

    /**
     * 处理出库
     */
    public static function out_warehouse($goods, $bill = null)
    {
//        拆分SKU
        $goods_warehouse = array_column($goods, 'DELIVERY_WAREHOUSE');
        $goods_warehouse = array_unique($goods_warehouse);
        foreach ($goods_warehouse as $gw) {
            foreach ($goods as $g) {
                if ($g['DELIVERY_WAREHOUSE'] == $gw) $goods_sku_arr[$gw][] = $g;
            }
        }
        foreach ($goods_sku_arr as $key => $goods_sku) {
            $data = [
                'bill' => [
                    'bill_type' => 'N000950100',
                    'channel' => 'N000830100',
                    'warehouse_id' => $key
                ],
                'guds' => self::join_guds($goods_sku)
            ];
            $url = U('bill/out_and_in_storage', '', true, false, true);
            $res = json_decode(curl_request($url, $data, 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; think_language=zh-CN'), true);
            if ($res['code'] != 10000111) trace($data, '$data');
        }
        return $res;
    }

    /**
     * add log
     * @param $order_id
     * @param $type
     * @param $info
     * @param null $user
     */
    public static function add_log($order_id, $state, $info, $user = null)
    {
        $Log = M('log', 'tb_b2b_');
        $data['ORDER_ID'] = $order_id;
        $data['STATE'] = $state;
        $data['COUNT'] = $info;
        $data['USER_ID'] = session('m_loginname');
        $data['create_time'] = date('Y-m-d H:i:s');
        return $Log->add($data);
    }

    /**
     * @param $currency
     * @param $date
     * @param string $dst_currency
     * @return int
     */
    public static function update_currency($currency, $date, $dst_currency = 'USD')
    {
        $insight = INSIGHT;
        if (INSIGHT == 'INSIGHT') $insight = 'http://insight.gshopper.com';
        $url = $insight . '/dashboard-backend/external/exchangeRate?date=' . $date . '&dst_currency=' . $dst_currency . '&src_currency=' . static::$bill_state[$currency];
        trace($url, '$url');
        $url_md5 = md5($url);
        if (empty(session($url_md5))) {
            $currency = json_decode(curl_request($url), 1);
            session($url_md5, $currency);
        } else {
            $currency = session($url_md5);
        }
        if (empty($currency['data'][0]['rate'])) {
            trace($url, '$url');
            return 1;
        } else {
            return $currency['data'][0]['rate'];
        }
    }

    public static function currency_po_to_erp($currency_code)
    {
        $code_arr = static::get_code('기준환율종류코드');
        $code_arr_sort = array_column($code_arr, 'CD', 'SORT_NO');
        trace($code_arr_sort, '$code_arr_sort');
        return $code_arr_sort[$currency_code];
    }

    /**
     * barcode to sku
     * @param $bar
     * @return null|string
     */
    public static function bar_to_sku($bar)
    {
        $sku = null;
        if (strlen($bar) > 10) {
            $qr_code = $bar;
            $Guds_opt = M('guds_opt', 'tb_ms_');
            if (!empty($qr_code)) {
                $where_qr['GUDS_OPT_UPC_ID'] = $bar;
                $res = $Guds_opt->where($where_qr)->field('GUDS_OPT_ID')->find();
                $sku = empty($res) ? '' : $res['GUDS_OPT_ID'];
            }
        }
        return $sku;
    }

    public static function get_user($u = null)
    {
        $Admin = M('admin', 'bbm_');
        $where = null;
        if ($u) $where['M_NAME'] = $u;
        $user_arr = $Admin->field('M_NAME')->where($where)->select();
        return $user_arr;
    }

    public static function toNode($e)
    {
        $d = json_decode($e, true);
        if (!$d['nodeProp']) return null;
        $init_data['number_th'] = static::$number_th;
        $init_data['node_type'] = static::$code['node_type'];
        $init_data['node_date'] = static::$code['node_date'];
        $init_data['node_is_workday'] = static::$code['node_is_workday'];
        $run_e = $init_data['number_th'][$d['nodei']] . ':' . $init_data['node_type'][$d['nodeType']] . $init_data['node_date'][$d['nodeDate']] . $init_data['node_is_workday'][$d['nodeWorkday']] . '-' . $d['nodeProp'] . '%';
        return $run_e;
    }

    public static function TO_CD_VAL($e)
    {
        $Cd = M('cmn_cd', 'tb_ms_');
        return $Cd->where('CD = \'' . $e . '\'')->getField('CD_VAL');
    }

}