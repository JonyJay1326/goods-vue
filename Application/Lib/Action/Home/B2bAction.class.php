<?php

/**
 * User: yangsu
 * Date: 17/5/25
 * Time: 13:33
 */
class B2bAction extends BaseAction
{
    private $url = 'ng-public/index';
    private $returndata = [
        'data' => null,
        'info' => null,
        'status' => null,
    ];

    public $action = [
        'shipping_status' => 0,
        'CLIENT_NAME' => '',
        'PO_ID' => '',
        'delivery_warehouse_code' => '',
        'sales_team_code' => '',
        'warehouse' => ''
    ];
    public $action_warehousing = [
        'warehouse' => '',
        'status' => '',
        'PO_ID' => '',
        'CLIENT_NAME' => '',
        'SALES_TEAM' => ''
    ];

    public $action_warehouse = [
        'shipping_status' => 0,
        'CLIENT_NAME' => '',
        'PO_ID' => '',
        'delivery_warehouse_code' => '',
        'sales_team_code' => ''
    ];
    /**
     * @var array
     */
    public $action_gathering = [
        'gathering' => '',
        'CLIENT_NAME' => '',
        'PO_ID' => '',
        'sales_team_code' => ''
    ];

    public $number_th = ['1st', '2nd', '3rd'];

    public function _initialize()
    {
        date_default_timezone_set("Asia/Shanghai");
        parent::_initialize();
        header('Access-Control-Allow-Origin: *');
        $this->assign('scope', 'b2b');
    }

    /**
     * 订单列表
     */
    public function order_list()
    {
        $this->assign('title', 'B2B订单');
        $this->display($this->url);
    }

    /**
     * 订单新增
     */
    public function order_add()
    {
        $this->assign('title', 'B2B订单新增');
        $this->display($this->url);
    }

    /**
     * 初始化
     */
    public function init()
    {
        $data['currency'] = B2bModel::get_currency();
//        $data['tax_rebate_ratio'] = B2bModel::get_code('tax_rebate_ratio');
        $data['tax_rebate_ratio'] = $this->rm_sign(B2bModel::get_code('采购税率'));
        $data['all_warehouse'] = StockModel::get_all_warehouse();
        $data['Country'] = BaseModel::getCountry();
        $data['sales_team'] = B2bModel::get_sales_team();
        $data['shipping_method'] = B2bModel::get_shipping_method();
        $data['payment_node'] = [
            'node_type' => B2bModel::get_code('node_type'),
            'node_date' => B2bModel::get_code('node_date'),
            'node_is_workday' => B2bModel::get_code('node_is_workday'),
            'node_prop' => B2bModel::get_code('node_prop')
        ];
        $data['payment_cycle'] = B2bModel::get_code('period');
//        $data['shipping'] = B2bModel::get_code('shipping');
        $data['shipping'] = B2bModel::get_code_lang('shipping');
        $data['invioce'] = B2bModel::get_code('invioce');
        $data['tax_point'] = B2bModel::get_code('tax_point');
        $data['number_th'] = $this->number_th;
        $data['currency_bz'] = B2bModel::get_code('currency_bz');
        $data['allPurchasingArr'] = B2bModel::get_code('采购团队');
        $data['allIntroduceArr'] = B2bModel::get_code('介绍团队');
        $data['wfgs'] = B2bModel::get_code('我方公司');

        $data['user'] = B2bModel::get_user();

        $data['invoice_point'] = B2bModel::get_invoice_point();

        $this->ajaxReturn($data, '', 1);
    }

    /**
     * 获取PO信息
     */
    public function get_po_data()
    {

        $CON_NO = $this->getParams()['CON_NO'];
        if (!B2bModel::search_contracct_by_con_no($CON_NO)) {
//            $this->ajaxReturn('编号为：' . $CON_NO . ' 的合同不存在SMS2中，请修改', '', 0);
        }
        $oci = new MeBYModel();
        $sql = "SELECT a.*,b.*,doc.IMAGEFILENAME FROM ECOLOGY.FORMTABLE_MAIN_91 a 
                left join ECOLOGY.HRMRESOURCE b on a.SQR = b.ID
                LEFT JOIN ECOLOGY.DOCIMAGEFILE doc on a.FJSC = doc.DOCID 
                WHERE DJBH='" . $CON_NO . "'";
        $checkSql = "SELECT wr.STATUS FROM ECOLOGY.FORMTABLE_MAIN_91 fm 
                    LEFT JOIN ECOLOGY.WORKFLOW_REQUESTBASE wr on fm.REQUESTID = wr.REQUESTID 
                    WHERE DJBH = '" . $CON_NO . "'";
        $checkRet = $oci->testQuery($checkSql);
        if ($checkRet[0]['STATUS'] != '结束') $this->ajaxReturn('编号为：' . $CON_NO . ' 的合同不存在或尚未完成审核，请修改', '', 0);
        $ret = $oci->testQuery($sql);
        if ($ret) {
            $data = $ret [0];
            $companyCd = BaseModel::conCompanyCd();
            $data['GSMC'] = $companyCd[$data['GSMC']];
            $data = $this->join_po_data($data);
            $this->ajaxReturn($data, '', 1);
        } else {
            $this->ajaxReturn('未查询到编号为：' . $CON_NO . ' 的合同，请修改', '', 0);
        }
    }

    private function join_po_data($e)
    {
        $e['YF'] = isset($e['YF']) ? $e['YF'] : trim($e['ECHO_COMPANY']);
        $e['CGBUSINESSLICENSE2'] = isset($e['CGBUSINESSLICENSE2']) ? $e['CGBUSINESSLICENSE2'] : trim($e['BUSINESSLICENSEFILLEDINBYLEGAL']);
        return $e;
    }

    /**
     * 保存订单
     */
    public function save_order()
    {
        $data = array();
        if (IS_POST) {
            $post = B2bModel::get_data();
            $poData_get = $post->poData;

            $Models = new Model();
            $Models->startTrans();
            $receiptData['PO_ID'] = $poData['PO_ID'] = $poData_get->poNum;
            $poData['create_time'] = date('Y-m-d H:i:s');
            $poData['create_user'] = session('m_loginname');
            $res['order_id'] = $receiptData['ORDER_ID'] = $poData['ORDER_ID'] = $Models->table('tb_b2b_order')->data($poData)->add();

            $receiptData['client_id'] = $poData['CLIENT_NAME'] = $poData_get->clientName;
            $poData['Business_License_No'] = $poData_get->busLice;

            $poData['contract'] = $poData_get->contract;
            $poData['our_company'] = $poData_get->ourCompany;
            $receiptData['sales_team_id'] = $poData['SALES_TEAM'] = $poData_get->SALES_TEAM;
            $poData['PO_USER'] = $poData_get->lastname;
            if (empty($poData['PO_USER'])) {
                $res['code'] = 402;
                $res['error'] = $poData['PO_USER'];
                $res['info'] = '新增失败,PO信息未填写完全';
                $Models->rollback();
                $this->ajaxReturn($res, $res['info'], $res['code']);
            }
            if (!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $poData['PO_USER'])) {
                if (count(B2bModel::get_user($poData['PO_USER'])) == 0) {
                    $res['code'] = 401;
                    $res['error'] = $poData['PO_USER'];
                    $res['info'] = '新增失败,销售同事填写信息不存在ERP中';
                    $Models->rollback();
                    $this->ajaxReturn($res, $res['info'], $res['code']);
                }
            }
            $receiptData['estimated_amount'] = $poData['po_amount'] = $this->unking($poData_get->poAmount);
            $poData['PO_FILFE_PATH'] = $poData_get->IMAGEFILENAME;
            $poData['po_erp_path'] = $poData_get->po_erp_path;
            $poData['po_currency'] = B2bModel::currency_po_to_erp($poData_get->BZ);

            $poData['po_time'] = $poData_get->poTime;

            $receiptData['invoice_type'] = $poData['INVOICE_CODE'] = $poData_get->invioce;
            $receiptData['tax_point'] = $poData['TAX_POINT'] = $poData_get->tax_point;
            $poData['DELIVERY_METHOD'] = $poData_get->shipping;

            $receiptData['payment_account_type'] = $poData['BILLING_CYCLE_STATE'] = $poData_get->cycleNum;
            $poData['PAYMENT_NODE'] = json_encode($poData_get->poPaymentNode);

            $poData['TARGET_PORT']['targetCity'] = $poData_get->detailAdd;
            $poData['TARGET_PORT']['country'] = $poData_get->country;
            $poData['TARGET_PORT']['city'] = $poData_get->city;
            $poData['TARGET_PORT']['province'] = $poData_get->province;
            $poData['TARGET_PORT']['stareet'] = $poData_get->province;
            $poData['TARGET_PORT'] = json_encode($poData['TARGET_PORT']);

            $receiptData['sales_team_id'] = $poData['SALES_TEAM'] = $poData_get->saleTeam;

            $poData['remarks'] = $poData_get->remarks;
            $poData['tax_rebate_income'] = $this->unking($poData_get->tax_rebate_income);

            //           商品币种  预估商品成本 。物流币种。预估物流成本
//            $poData['backend_estimat'] = $this->get_backend_estimat($post->skuData, $poData_get->poTime);
            $poData['backend_currency'] = $poData_get->backend_currency;
            $poData['backend_estimat'] = $poData_get->backend_estimat;
            $poData['logistics_currency'] = $poData_get->logistics_currency;
            $poData['logistics_estimat'] = $poData_get->logistics_estimat;

            $info_id = $Models->table('tb_b2b_info')->data($poData)->add();
//            add receipt
            $payment_node = json_decode($poData['PAYMENT_NODE'], true);
//                组装节点和比例
            if ($poData['BILLING_CYCLE_STATE'] == 4) {
                $receiptData['receiving_code'] = json_encode($payment_node[0]);
                $receiptData['overdue_statue'] = 0;
//                计算帐期金额
                $receiptData['expect_receipt_amount'] = $payment_node[0]['nodeProp'] / 100 * $poData['po_amount'];
                $receipt_id = $Models->table('tb_b2b_receipt')->data($receiptData)->add();
            } else {
                for ($i = 0; $i < $poData['BILLING_CYCLE_STATE']; $i++) {

                    $receiptData['receiving_code'] = json_encode($payment_node[$i]);
                    $receiptData['overdue_statue'] = 0;
//                计算帐期金额
                    $receiptData['expect_receipt_amount'] = $payment_node[$i]['nodeProp'] / 100 * $poData['po_amount'];
                    $receipt_id = $Models->table('tb_b2b_receipt')->data($receiptData)->add();
                }
            }
//            增加退税
            $receipt_taxes = $receiptData;
            unset($receipt_taxes['receiving_code']);
            unset($receipt_taxes['estimated_amount']);
            $transaction_type = $this->get_code_key('transaction_type');
            $receipt_taxes['transaction_type'] = $transaction_type['退税'];
            $receipt_taxes['expect_receipt_amount'] = $receipt_taxes['estimated_amount'] = $poData['tax_rebate_income'];
            $receipt_id = $Models->table('tb_b2b_receipt')->data($receipt_taxes)->add();
//            add order
            if (!$info_id) {
                $res['code'] = 402;
                $res['error'] = $info_id;
                $res['info'] = '新增失败,PO数据异常-信息重复';
                $Models->rollback();
            }
            if ($poData['ORDER_ID']) {
                $sku_get = $post->skuData;
                foreach ($sku_get as $v) {
                    $skuData['ORDER_ID'] = $poData['ORDER_ID'];
                    $skuData['SKU_ID'] = $v->skuId;
                    $skuData['sku_show'] = $v->toskuid;
                    $skuData['price_goods'] = $this->unking($v->gudsPrice);
                    $order_num += $skuData['TOBE_DELIVERED_NUM'] = $skuData['required_quantity'] = $this->unking($v->demand);
                    $skuData['tax_rebate_ratio'] = $v->drawback;
                    $skuData['goods_title'] = $v->gudsName;
                    $skuData['goods_info'] = $v->skuInfo;
                    $skuData['purchasing_team'] = $v->purchasing_team;
                    $skuData['introduce_team'] = $v->introduce_team;
                    $skuData['currency'] = $poData['po_currency'];
                    $skuData_arr[] = $skuData;
                }
                $sku_add = $Models->table('tb_b2b_goods')->addAll($skuData_arr);
                $do_status = $this->do_list_sync($poData, $skuData_arr, $order_num);
                if ($sku_add && $do_status) {
                    $res['code'] = 200;
                    $res['info'] = '新增成功';
                    $Models->commit();
                } else {
                    $res['code'] = 401;
                    $res['info'] = '新增失败，商品或PO数据异常';
                    $res['sku_add'] = $sku_add;
                    $res['do_status'] = $do_status;
                    $Models->rollback();
                }
            } else {
                $res['code'] = 400;
                $res['error'] = $info_id;
                $res['info'] = '新增失败,PO数据异常-重复';
                $Models->rollback();
            }
            if ($res['code'] != 400) B2bModel::add_log($poData['ORDER_ID'], $res['code'], $res['info']);
        }
        $this->ajaxReturn($res, $res['info'], $res['code']);
    }


    /**
     * 展示订单列表
     */
    public function show_list()
    {
        $post = $this->_param();
        if (!empty($post['goods_title_info'])) {
            switch ($post['search_type']) {
                case 'SKU_ID':
                    $post['SKU_ID'] = trim($post['goods_title_info']);
                    break;
                case 'bar_code':
                    $SKU_ID = B2bModel::bar_to_sku(trim($post['goods_title_info']));
                    $post['SKU_ID'] = $SKU_ID;
                    break;
                case 'goods_title':
                    $post['goods_title'] = trim($post['goods_title_info']);
                    break;
            }
        }
        if (!empty($post['yq'])) {
            $post['order_overdue_statue'] = trim($post['yq']);
        }
        $where = B2bModel::joinwhere($post, 'b2blist');
        $Order = M('order', 'tb_b2b_');
        import('ORG.Util.Page');
//        where is why ?
        if ($post['yq'] == 0 && isset($post['yq'])) {
            $where['tb_b2b_order.order_overdue_statue'] = trim($post['yq']);
        } else {
            if (empty($post['yq'])) unset($where['tb_b2b_order.order_overdue_statue']);
        }

        if ($where['tb_b2b_warehouse_list.warehouse_state']) {
            switch ($where['tb_b2b_warehouse_list.warehouse_state'][1]) {
                case 2:
                    $where['tb_b2b_warehouse_list.status'] = array('EQ', 1);
                    break;
                case 1:
                    $where['tb_b2b_warehouse_list.status'] = array(array('NEQ', 1), array('EXP', 'IS NULL'), 'or');
                    break;
            }
            unset($where['tb_b2b_warehouse_list.warehouse_state']);
        }
        if ($where['tb_b2b_ship_list.order_fh']) {
            switch ($where['tb_b2b_ship_list.order_fh'][1]) {
                case 2:
                    $where['tb_b2b_doship.todo_sent_num'] = array('EQ', 0);
                    break;
                case 1:
                    $where['tb_b2b_doship.todo_sent_num'] = array('NEQ', 0);
                    break;
            }
            unset($where['tb_b2b_ship_list.order_fh']);
        }
        if ($where['tb_b2b_receipt.order_sk']) {
            $where['tb_b2b_receipt.transaction_type'] = array('NEQ', 1);
            switch ($where['tb_b2b_receipt.order_sk'][1]) {
                case 2:
                    $where['tb_b2b_receipt.receipt_operation_status'] = array('EQ', 1);
                    break;
                case 1:
                    $where['tb_b2b_receipt.receipt_operation_status'] = array('NEQ', 1);
                    break;
            }
            unset($where['tb_b2b_receipt.order_sk']);
        }
        if ($where['tb_b2b_receipt.order_ts']) {
            $where['tb_b2b_receipt.transaction_type'] = array('EQ', 1);
            switch ($where['tb_b2b_receipt.order_ts'][1]) {
                case 2:
                    $where['tb_b2b_receipt.receipt_operation_status'] = array('EQ', 1);
                    break;
                case 1:
                    $where['tb_b2b_receipt.receipt_operation_status'] = array('NEQ', 1);
                    break;
            }
            unset($where['tb_b2b_receipt.order_ts']);
        }
        $count = $Order->where($where)
            ->field('tb_b2b_order.order_overdue_statue,tb_b2b_info.*,tb_b2b_doship.order_num,tb_b2b_doship.shipping_status,g.goods_title,tb_b2b_warehouse_list.status as warehouse_status')
            ->join('left join tb_b2b_doship on tb_b2b_doship.ORDER_ID = tb_b2b_order.ID')
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_order.ID')
            ->join('left join (select status,ORDER_ID from tb_b2b_warehouse_list GROUP BY ORDER_ID ORDER BY ID DESC) as tb_b2b_warehouse_list on tb_b2b_warehouse_list.ORDER_ID = tb_b2b_order.ID')
            ->join('left join (select ORDER_ID,goods_title,SKU_ID from tb_b2b_goods group by tb_b2b_goods.ORDER_ID ) as g on g.ORDER_ID = tb_b2b_order.ID ')
            ->count();

        $Page = new Page($count, 10);
        $show = $Page->show();
        $data['order'] = $Order->where($where)
            ->field('tb_b2b_order.order_overdue_statue,tb_b2b_info.*,tb_b2b_doship.order_num,tb_b2b_doship.shipping_status,g.goods_title,tb_b2b_warehouse_list.status as warehouse_status')
            ->join('left join tb_b2b_doship on tb_b2b_doship.ORDER_ID = tb_b2b_order.ID')
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_order.ID')
            ->join('left join (select status,ORDER_ID from tb_b2b_warehouse_list GROUP BY ORDER_ID ORDER BY ID DESC) as tb_b2b_warehouse_list on tb_b2b_warehouse_list.ORDER_ID = tb_b2b_order.ID')
            ->join('left join (select ORDER_ID,goods_title,SKU_ID from tb_b2b_goods group by tb_b2b_goods.ORDER_ID ) as g on g.ORDER_ID = tb_b2b_order.ID ')
            ->order('tb_b2b_order.ID desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $node_date = B2bModel::get_code('node_date');
        foreach ($data['order'] as &$v) {
            //        毛利
            $ml = $this->get_ygml($v['ORDER_ID'], $node_date);
            $v['ygml'] = $ml['yg'];
            $v['sjml'] = $ml['sj'];
            //        逾期
//            $v['if_yq'] = $ml['if_yq'];
        }

        $data['count_where'] = count($where);
        if (!count($where)) {
            $data['period'] = B2bModel::get_code('period'); //帐期
            $data['now_state'] = B2bModel::get_code('now_state');
            $data['search_type_arr'] = B2bModel::$search_type;

            $data['sales_team'] = B2bModel::get_sales_team();
            $data['currency'] = B2bModel::get_code('기준환율종류코드');

            $data['order_fh'] = B2bModel::get_code_lang('order_fh'); //发货
            $data['warehouse_state'] = B2bModel::get_code_lang('warehouse_state'); //入库
            $data['order_sk'] = B2bModel::get_code_lang('order_sk'); //收款
            $data['order_ts'] = B2bModel::get_code_lang('order_ts'); //税率

            $data['yq_arr'] = B2bModel::get_code('overdue');
        }

        $data['count'] = $count;
        $this->ajaxReturn($data, '', 1);
    }

    /**
     * 预估毛利/逾期
     */
    private function get_ygml($order_id, $node_date, $type = null)
    {
        $order_id = trim($order_id);

        $data = $this->exchange_rete_calculation($order_id);

        $ml['yg'] = $data['profit']['H'];
        $ml['sj'] = $data['profit']['W'];

        /*$node = $data['info'][0]['PAYMENT_NODE'];
        $check_data['po_time'] = $data['receipt'][0]['po_time'];
        $check_data['SUBMIT_TIME'] = $data['receipt'][0]['SUBMIT_TIME'];
        $check_data['Estimated_arrival_DATE'] = $data['receipt'][0]['Estimated_arrival_DATE'];
        $check_data['WAREING_DATE'] = $data['receipt'][0]['WAREING_DATE'];
        $t_date = $data['receipt'][0]['actual_receipt_date']; // 实际收款时间
        $transaction_type = $data['receipt'][0]['transaction_type'];
        $ml['if_yq'] = $this->check_yq($order_id, $node, $check_data, $node_date, $t_date, $transaction_type);*/

        return $ml;
    }

    /**
     *  逾期效验
     */
    public function check_yq($order_id, $node, $check_data, $node_date, $t_date, $transaction_type)
    {
        if ($transaction_type) {
            trace($transaction_type, '$transaction_type');
        }
        $node = json_decode($node, true);
        $times = null; // 处理时间

        foreach ($node as $v) {
            switch ($v['nodeType']) {
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
                case 4:
//                        月结
                    $type = '月结';
                    $times = $check_data['WAREING_DATE'];
                    break;

                default:
            }
            if (empty($t_date)) break;
            if (empty($times)) break;
            $times = date('Y-m-d', strtotime($times));
            if ($t_date) $now_time = date('Y-m-d', strtotime($t_date, "-" . $node_date[$v['nodeDate']]['CD_VAL'] . "day"));

            if ($times > $now_time) {
                trace((bool)($times > $now_time), '$times > $now_time');
                return true;  //逾期
                break;
            }
        }
        return false;
    }


    /**
     * 获取订单详情
     */
    public function order_content()
    {
        $get_data = $this->_param();
        $order_id = trim($get_data['order_id']);

        $data = $this->exchange_rete_calculation($order_id);

        $data['sales_team'] = B2bModel::get_sales_team();
        $data['area'] = B2bModel::get_area();
        $data['number_th'] = $this->number_th;
        $data['node_is_workday'] = B2bModel::get_code('node_is_workday');
        $data['node_type'] = B2bModel::get_code('node_type');
        $data['node_date'] = B2bModel::get_code('node_date');
        $data['invioce'] = B2bModel::get_code('invioce');
        $data['tax_point'] = B2bModel::get_code('tax_point');
        $data['period'] = B2bModel::get_code('period');
        $data['or_invoice_arr'] = B2bModel::get_code('or_invoice_arr');
        $data['warehousing_state'] = B2bModel::get_code('warehousing_state');
        $data['currency_bz'] = B2bModel::get_code('기준환율종류코드');


        if ($data) {
            $res['code'] = 200;
        } else {
            $res['code'] = 400;
            $res['info'] = '数据查询失败';
        }

        $this->ajaxReturn($data, $res['info'], $res['code']);
    }



//    发货

    /**
     * add todoing ship list
     * @param $o    order_id
     * @param $g    goods_arr
     */
    private function do_list_sync($o, $g, $sum)
    {
        $data['ORDER_ID'] = $o['ORDER_ID'];
        $data['PO_ID'] = $o['PO_ID'];
        $data['CLIENT_NAME'] = $o['CLIENT_NAME'];
        $data['delivery_warehouse_code'] = $this->join_ware_arr_code($o['ORDER_ID']);
        $data['target_port'] = $o['TARGET_PORT'];

        $data['todo_sent_num'] = $data['order_num'] = $sum;
        $data['sent_num'] = 0;

        $data['order_date'] = $o['po_time']; // po time to order date
        $data['sales_team_code'] = $o['SALES_TEAM'];
        // 发货状态
        $ship_state_arr = $this->get_code_key('ship_state');
        $data['shipping_status'] = $ship_state_arr['待发货'];
        $Models = new Model();
        $Models->startTrans();
        $doship_id = $Models->table('tb_b2b_doship')->data($data)->add();
        /* if ($doship_id) {
             foreach ($g as $v) {
                 $slist['SHIP_ID'] = $doship_id;
                 $slist['SHIPPING_SKU'] = $v['SKU_ID'];
                 $slist['DELIVERED_NUM'] = $slist['TOBE_DELIVERED_NUM'] = $v['required_quantity'];
                 $slist['REMARKS'] = $slist['ACTUAL_DELIVERY_WAREHOUSE'] = '';
                 $slist_arr[] = $slist;
             }
             $goods_id = $Models->table('tb_b2b_ship_goods')->addALL($slist_arr);
             if($goods_id){
                 $Models->commit();
             }else{
                 $Models->rollback();
             }
         }*/
        return $doship_id;
    }

    /**
     * 增加自身订单仓库CODE
     */
    public function join_ware_arr_code($order_id = null)
    {
        $arr[] = ['ORDER_ID' => $order_id];
        $arr = $this->get_warehouse($arr);
        foreach ($arr[0]['warehouse'] as $v) {
            $wareshouse_arr .= empty($wareshouse_arr) ? $v->scalar : ',' . $v->scalar;
        }
        return $wareshouse_arr;
    }


    /**
     * 待发货列表
     */
    public function do_ship_list()
    {
        $Doship = M('doship', 'tb_b2b_');
        $getdata = $this->_param();
        $this->action['shipping_status'] = empty($getdata['shipping_status']) ? 0 : $getdata['shipping_status'];
        $this->action['CLIENT_NAME'] = empty($getdata['CLIENT_NAME']) ? '' : $getdata['CLIENT_NAME'];
        $this->action['PO_ID'] = empty($getdata['PO_ID']) ? '' : $getdata['PO_ID'];
        $this->action['delivery_warehouse_code'] = empty($getdata['delivery_warehouse_code']) ? '' : $getdata['delivery_warehouse_code'];
        $this->action['sales_team_code'] = empty($getdata['sales_team_code']) ? '' : $getdata['sales_team_code'];
        $where = B2bModel::joinwhere($getdata, 'do_ship_list');
        $page_num = I('page_num') > 0 ? I('page_num') : 10;
        $count = $Doship->where($where)->order('ID DESC')->count();
        import('ORG.Util.Page');
        $Page = new Page($count, $page_num);
        $Page->page_num = $page_num;
        $show = $Page->show();
        $doship_list = $Doship->where($where)
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_doship.ORDER_ID ')
            ->order('tb_b2b_doship.ID DESC')
            ->field('tb_b2b_doship.*,tb_b2b_info.po_time,tb_b2b_info.PO_USER,tb_b2b_info.SALES_TEAM,tb_b2b_info.TARGET_PORT')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $doship_list = $this->get_warehouse($doship_list);
        $this->assign('doship_list', B2bModel::set_json($doship_list));

        $initdata['sales_team'] = B2bModel::get_sales_team();
        $initdata['show_warehouse'] = StockModel::get_show_warehouse();
        $initdata['area'] = B2bModel::get_area();
        $this->assign('initdata', B2bModel::set_json($initdata));

        $this->assign('all_warehouse', B2bModel::set_json(StockModel::get_all_warehouse()));
        $this->assign('ship_state', B2bModel::set_json(B2bModel::get_code_lang('ship_state')));
        $this->assign('action', B2bModel::set_json($this->action));
        $this->assign('count', B2bModel::set_json($count));
        $this->assign('pages', $show);
        $this->display();
    }

    /**
     * get this warehouse
     * @param  $warehouse_arr
     * @return mixed
     */
    private function get_warehouse($e)
    {
        $Goods = M('goods', 'tb_b2b_');
        $Guds = M('guds', 'tb_ms_');
        foreach ($e as &$v) {
            $doship_goods = $Goods->where('tb_b2b_goods.ORDER_ID = \'' . $v['ORDER_ID'] . '\'')
                ->field('tb_b2b_goods.SKU_ID')
                ->select();
            if (count($doship_goods)) {
                foreach ($doship_goods as $d) {
                    $gudsId[] = substr($d['SKU_ID'], 0, -2);
                }
                $where_guds['GUDS_ID'] = array('in', $gudsId);
                $guds = $Guds->where($where_guds)->field('GUDS_ID,DELIVERY_WAREHOUSE')->select();
                if (count($guds)) {
                    $warehouse = array_unique(array_column($guds, 'DELIVERY_WAREHOUSE'));
                    if (is_string($v['warehouse'])) {
                        unset($v['warehouse']);
                    }
                    foreach ($warehouse as $vw) {
                        $v['warehouse'][] = (object)$vw;
                    }
                }
                unset($gudsId);
            } else {
                $v['warehouse'] = '';
            }
        }
        return $e;
    }

    /**
     * 待发货子列表
     */
    public function do_ship()
    {
        $order_id = I('order_id');
        $Doship = M('doship', 'tb_b2b_');
        $where['tb_b2b_doship.ORDER_ID'] = $order_id;
        $doship = $Doship->where($where)
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_doship.ORDER_ID')
            ->field('tb_b2b_doship.*,tb_b2b_info.REMARKS,tb_b2b_info.DELIVERY_METHOD,tb_b2b_info.PO_USER,tb_b2b_info.po_time')
            ->find();
        $Goods = M('goods', 'tb_b2b_');
        /*
         *
         *  N000830100 is b5c
        */
        $doship_goods = $Goods->where('tb_b2b_goods.ORDER_ID = \'' . $order_id . '\'')
            ->join('left join (select * from tb_wms_standing where tb_wms_standing.channel = \'N000830100\' ) sc on sc.SKU_ID = tb_b2b_goods.SKU_ID')
            ->field('tb_b2b_goods.*,sc.sale')
            ->select();
        foreach ($doship_goods as $d) {
            $gudsId[] = substr($d['SKU_ID'], 0, -2);
        }
        $Guds = M('guds', 'tb_ms_');
        $where_guds['GUDS_ID'] = array('in', $gudsId);
        $guds = $Guds->where($where_guds)->field('GUDS_ID,DELIVERY_WAREHOUSE')->select();
        $guds_col = array_column($guds, 'DELIVERY_WAREHOUSE', 'GUDS_ID');
        $hwarehouse = array_column($guds, 'DELIVERY_WAREHOUSE', 'DELIVERY_WAREHOUSE');
        foreach ($doship_goods as &$d) {
            $d['DELIVERY_WAREHOUSE'] = $guds_col[substr($d['SKU_ID'], 0, -2)];
        }
        $initdata['area'] = B2bModel::get_area();
        $this->assign('doship', B2bModel::set_json($doship));
        $this->assign('hwarehouse', B2bModel::set_json($this->arr2obj(array_unique($hwarehouse), 'warehouse')));
        $this->assign('currency', B2bModel::set_json(B2bModel::get_currency()));
        $this->assign('initdata', B2bModel::set_json($initdata));
        $this->assign('all_warehouse', B2bModel::set_json(StockModel::get_all_warehouse()));
        $this->assign('doship_goods', B2bModel::set_json($doship_goods));
        $this->display();
    }

    public function search_bar_code()
    {
        $data = B2bModel::get_data('params', true);
        $Guds_opt = M('guds_opt', 'tb_ms_');
        $guds_opt = $Guds_opt->where('GUDS_OPT_ID = \'' . $data['goods_info'] . '\'')->getField('GUDS_OPT_UPC_ID');
        return $guds_opt;
    }


    /**
     * 待发货详情
     */
    public function do_ship_show()
    {
//        多操作人？
        $order_id = I('order_id');
        $Doship = M('doship', 'tb_b2b_');
        $where['tb_b2b_doship.ORDER_ID'] = $order_id;
        $doship = $Doship->where($where)
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_doship.ORDER_ID')
            ->join('left join (select sum(power_all) as power_all_sum,sum(LOGISTICS_COSTS) as logistics_costs_sum,LOGISTICS_CURRENCY ,SUBMIT_TIME,AUTHOR,order_id from tb_b2b_ship_list group by order_id order by ID desc) tb_b2b_ship_list on tb_b2b_ship_list.order_id = tb_b2b_doship.ORDER_ID')
            ->field('tb_b2b_doship.*,tb_b2b_info.REMARKS,tb_b2b_info.DELIVERY_METHOD,tb_b2b_info.PO_USER,tb_b2b_info.po_time,tb_b2b_ship_list.*')
            ->find();

        $Ship_list = M('ship_list', 'tb_b2b_');
        $where_ship['DOSHIP_ID'] = $doship['ID'];
        $ship_list = $Ship_list->where($where_ship)->select();
        $Ship_goods = M('ship_goods', 'tb_b2b_');
        $where_goods['SHIP_ID'] = array('IN', array_column($ship_list, 'ID'));
        $ship_goods = $Ship_goods->where($where_goods)
            ->join('left join (select SKU_ID,goods_title,goods_info from tb_b2b_goods group by SKU_ID)  tb_b2b_goods on tb_b2b_goods.SKU_ID = tb_b2b_ship_goods.SHIPPING_SKU')
            ->select();
        $arr = array();
        foreach ($ship_goods as $v) {
            $arr[$v['SHIP_ID']][] = $v;
        }
        foreach ($ship_list as &$v) {
            if ($arr[$v['ID']]) $v['goods'] = $arr[$v['ID']];
        }

        $initdata['area'] = B2bModel::get_area();
        $initdata['currency'] = B2bModel::get_currency();

        $this->assign('doship', B2bModel::set_json($doship));
        $this->assign('ship_list', B2bModel::set_json($ship_list));
        $this->assign('initdata', B2bModel::set_json($initdata));
        $this->assign('currency', B2bModel::set_json(B2bModel::get_currency()));
        $this->assign('all_warehouse', B2bModel::set_json(StockModel::get_all_warehouse()));
        $this->display();
    }

    /**
     * 发货保存
     */
    public function save_ship()
    {
        $post = B2bModel::get_data('params');
        $Model = new Model();
//        add ship list in goods
        $Model->startTrans();
        $doship_id = $post->doship_id;
        $order_id = $post->goods_info[0]->ORDER_ID;
        foreach ((array)$post->ships as $v) {
            if ($v->BILL_LADING_CODE) {
                $v->DOSHIP_ID = (int)$doship_id;
                $v->AUTHOR = session('m_loginname');
                $v->order_id = $order_id;
                $v->SUBMIT_TIME = date('Y-m-d h:i:s');
                unset($v->GUDS_ID);
                $ship_id['ship_id'] = $Model->table('tb_b2b_ship_list')->add((array)$v);
                $vw = $v;
                unset($vw->BILL_LADING_CODE);
                unset($vw->DELIVERY_WAREHOUSE);
                unset($vw->DELIVERY_TIME);
                unset($vw->Estimated_arrival_DATE);
                unset($vw->LOGISTICS_CURRENCY);
                unset($vw->LOGISTICS_COSTS);
                unset($vw->REMARKS);
                unset($vw->SUBMIT_TIME);
                $vw->SHIP_LIST_ID = $ship_id['ship_id'];
                $ship_id['w_id'] = $Model->table('tb_b2b_warehouse_list')->add((array)$vw);
                if (!$ship_id['w_id']) {
                    $Model->rollback();
                    $msg = '生成入库单异常';
                    B2bModel::add_log($order_id, 0, $msg);
                    goto ajaxretrun;
                }
                $ship_id['DELIVERY_WAREHOUSE'] = $v->warehouse;
                $ship_id_arr[] = $ship_id;
                B2bModel::add_log($order_id, 1, '新增订单');
            }
        }
        $msg = '发货信息异常';
        $status = 0;
        if ($ship_id_arr) {
            $ship_ids = array_column($ship_id_arr, 'ship_id', 'DELIVERY_WAREHOUSE');
            $w_ids = array_column($ship_id_arr, 'w_id', 'DELIVERY_WAREHOUSE');
            $upd_sum = 0;
            foreach ((array)$post->goods_info as $v) {
                if ($v->DELIVERED_NUM) {
                    /*$upd_goods['TOBE_DELIVERED_NUM'] = $v->required_quantity - $v->DELIVERED_NUM;
                    $upd_goods['SHIPPED_NUM'] = $v->required_quantity - $v->DELIVERED_NUM;
                    $Model->table('tb_b2b_goods')->where('id = ' . $v->ID)->save($upd_goods);*/
                    $data['SHIP_ID'] = $ship_ids[$v->DELIVERY_WAREHOUSE];
                    $dataw['ship_id'] = $ship_ids[$v->DELIVERY_WAREHOUSE];
                    $dataw['warehousing_id'] = $w_ids[$v->DELIVERY_WAREHOUSE];
                    $dataw['warehouse_sku'] = $data['SHIPPING_SKU'] = $v->SKU_ID;
                    $dataw['sku_show'] = $data['sku_show'] = $v->sku_show;
                    $data['SHIPPED_NUM'] = $v->SHIPPED_NUM;
                    $data['TOBE_DELIVERED_NUM'] = $v->TOBE_DELIVERED_NUM;
                    $data['DELIVERED_NUM'] = $v->DELIVERED_NUM;
                    $data['power'] = $this->get_power($v->SKU_ID) * $data['DELIVERED_NUM']; // get sku this power
//                    $dataw['TOBE_WAREHOUSING_NUM'] = $data['DELIVERED_NUM'] + $data['SHIPPED_NUM'];
                    $dataw['TOBE_WAREHOUSING_NUM'] = $data['DELIVERED_NUM'];

                    $dataw['DELIVERY_WAREHOUSE'] = $data['DELIVERY_WAREHOUSE'] = $v->DELIVERY_WAREHOUSE;
                    $dataw['REMARKS'] = $data['REMARKS'] = $v->REMARKS;
                    $upd_sum += $data['DELIVERED_NUM'];
                    $goods_data[] = $data;
                    $goods_dataw[] = $dataw;

                    $goods_num = $Model->table('tb_b2b_goods')->where('id = ' . $v->ID)->field('SHIPPED_NUM,TOBE_DELIVERED_NUM')->select();
                    $goods_num = $goods_num[0];
                    $num_sum_sku['SHIPPED_NUM'] = $goods_num['SHIPPED_NUM'] + $data['DELIVERED_NUM'];
                    $num_sum_sku['TOBE_DELIVERED_NUM'] = $goods_num['TOBE_DELIVERED_NUM'] - $data['DELIVERED_NUM'];

                    $sku_save = $Model->table('tb_b2b_goods')->where('id = ' . $v->ID)->save($num_sum_sku);

                    if (!$sku_save) {
                        $Model->rollback();
                        $msg = 'SKU error';
                        goto ajaxretrun;
                    }
                    unset($num_sum_sku);
                }
            }
            if ($goods_data) {
                if ($upd_sum) {
                    $doship_data = $Model->table('tb_b2b_doship')->where('ORDER_ID = ' . $order_id)->field('delivery_warehouse_code,sent_num,todo_sent_num')->select();
                    $doship_data = $doship_data[0];
                    $num_sum['sent_num'] = $doship_data['sent_num'] + $upd_sum;
                    $num_sum['todo_sent_num'] = $doship_data['todo_sent_num'] - $upd_sum;
                    $ship_state_arr = $this->get_code_key('ship_state');
                    if ($num_sum['todo_sent_num'] == 0) {
                        $num_sum['shipping_status'] = $ship_state_arr['已发货'];
                        $this->upd_order_node($order_id, 2); // 发货完成
                    }
                    if ($num_sum['todo_sent_num'] < 0) {
                        $Model->rollback();
                        $msg = '待发数据不足';
                        goto ajaxretrun;
                    }
                    $Model->table('tb_b2b_doship')->where('ORDER_ID = ' . $order_id)->save($num_sum);
                    $res = $Model->table('tb_b2b_ship_goods')->addAll($goods_data);
                    $res_w = $Model->table('tb_b2b_warehousing_goods')->addAll($goods_dataw);
                    if ($res && $res_w) {
//                       out warehouse
                        $warehouse_status = B2bModel::out_warehouse($goods_data);
                        if ($warehouse_status['code'] != 10000111) {
                            $Model->rollback();
                            $msg = $warehouse_status['code'] . $warehouse_status['msg'] . json_encode($warehouse_status['info']);
                            trace($warehouse_status, '$warehouse_status');
                        } else {
                            $Model->commit();
                            // upd ship list  power all
                            $this->sync_ship_power_all($goods_data);
                            $msg = 'success';
                            $status = 1;
                        }
                    } else {
                        $Model->rollback();
                        $msg = 'error';
                    }
                } else {
                    $Model->rollback();
                    $msg = 'error num is null';
                }

            } else {

                $Model->rollback();
                $msg = 'goods data is null';
            }
        }
        ajaxretrun:
        $this->ajaxReturn($res, $msg, $status);
    }

    /**
     * 入库列表
     */
    public function warehousing_list()
    {

        $initdata['all_warehouse'] = StockModel::get_all_warehouse();
        $initdata['sales_team'] = B2bModel::get_sales_team();
        $initdata['area'] = B2bModel::get_area();
        $initdata['warehousing_state'] = B2bModel::get_code_lang('warehousing_state');
        $this->assign('initdata', B2bModel::set_json($initdata));
//
        $getdata = $this->_param();

        $this->action['warehouse'] = empty($getdata['warehouse']) ? '' : $getdata['warehouse'];
        $this->action['PO_ID'] = empty($getdata['PO_ID']) ? '' : $getdata['PO_ID'];
        $this->action['CLIENT_NAME'] = empty($getdata['CLIENT_NAME']) ? '' : $getdata['CLIENT_NAME'];
        $this->action['SALES_TEAM'] = empty($getdata['SALES_TEAM']) ? '' : $getdata['SALES_TEAM'];
        $where = B2bModel::joinwhere($getdata, 'b2bwarehousing');
        if ($getdata['status'] === '0' || !empty($getdata['status'])) {
            $this->action['status'] = $getdata['status'];
            if ($getdata['status'] != 0) $where['tb_b2b_warehouse_list.status'] = $getdata['status'];
            if ($getdata['status'] == 1) $where['tb_b2b_warehouse_list.status'] = $getdata['status'] - 1;
        } else {
            $this->action['status'] = 0;
        }
        $Warehouse_list = M('warehouse_list', 'tb_b2b_');
        import('ORG.Util.Page');
        $count = $Warehouse_list
            ->where($where)
            ->join('tb_b2b_info on tb_b2b_info.ORDER_ID =  tb_b2b_warehouse_list.ORDER_ID')
            ->join('tb_b2b_doship on tb_b2b_doship.ID =  tb_b2b_warehouse_list.DOSHIP_ID')
            ->join('tb_b2b_ship_list on tb_b2b_ship_list.ID =  tb_b2b_warehouse_list.SHIP_LIST_ID')
            ->order('tb_b2b_warehouse_list.ID desc')
            ->field('tb_b2b_warehouse_list.*,tb_b2b_info.CLIENT_NAME,tb_b2b_info.PO_ID,tb_b2b_info.TARGET_PORT,tb_b2b_info.po_time,tb_b2b_doship.sent_num,tb_b2b_ship_list.SHIPMENTS_NUMBER,tb_b2b_ship_list.SUBMIT_TIME,tb_b2b_ship_list.BILL_LADING_CODE')
            ->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $data = $Warehouse_list
            ->where($where)
            ->join('tb_b2b_info on tb_b2b_info.ORDER_ID =  tb_b2b_warehouse_list.ORDER_ID')
            ->join('tb_b2b_doship on tb_b2b_doship.ID =  tb_b2b_warehouse_list.DOSHIP_ID')
            ->join('tb_b2b_ship_list on tb_b2b_ship_list.ID =  tb_b2b_warehouse_list.SHIP_LIST_ID')
            ->order('tb_b2b_warehouse_list.ID desc')
            ->field('tb_b2b_warehouse_list.*,tb_b2b_info.CLIENT_NAME,tb_b2b_info.PO_ID,tb_b2b_info.TARGET_PORT,tb_b2b_info.po_time,tb_b2b_doship.sent_num,tb_b2b_ship_list.SHIPMENTS_NUMBER,tb_b2b_ship_list.SUBMIT_TIME,tb_b2b_ship_list.BILL_LADING_CODE')
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();
//        $datas = $this->get_warehouse($data);
        $datas = $data;
        $this->assign('data', B2bModel::set_json($datas));
        $this->assign('action', B2bModel::set_json($this->action));
        $this->assign('count', $count);
        $this->assign('page', $show);


        $this->display();
    }

    /**
     * 获取入库数据
     */
    public function get_warehousing()
    {
        $order_id = I('ORDER_ID');
        $ID = I('ID');
        $Model = new Model();
        $where['tb_b2b_warehouse_list.ORDER_ID'] = $order_id;
        if ($ID) $where['tb_b2b_warehouse_list.ID'] = $ID;
        $warehousing_info = $Model->table('tb_b2b_warehouse_list')
            ->where($where)
            ->join('tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_warehouse_list.ORDER_ID')
            ->join('left join tb_b2b_ship_list on tb_b2b_ship_list.ID = tb_b2b_warehouse_list.SHIP_LIST_ID')
            ->group('tb_b2b_warehouse_list.ORDER_ID')
            ->field('tb_b2b_info.*,sum(tb_b2b_warehouse_list.SHIPMENTS_NUMBER) as SHIPMENTS_NUMBER_all,tb_b2b_warehouse_list.*,sum(WAREHOUSEING_NUM) as WAREHOUSEING_NUMS,sum(DEVIATION_NUM) as DEVIATION_NUMS,sum(AGAIN_WAREING_NUM) as AGAIN_WAREING_NUMS,sum(RECOVE_MONEY) as RECOVE_MONEYS,tb_b2b_ship_list.power_all as power_all_sum,tb_b2b_ship_list.LOGISTICS_COSTS as logistics_costs_sum,tb_b2b_ship_list.LOGISTICS_CURRENCY,tb_b2b_ship_list.order_id,tb_b2b_ship_list.REMARKS,tb_b2b_ship_list.SUBMIT_TIME')
            ->find();
//        get ship list
        $ship_list = $this->get_warehousing_goods($order_id, $ID);
        $initdata['all_warehouse'] = StockModel::get_all_warehouse();
        $initdata['sales_team'] = B2bModel::get_sales_team();
        $initdata['deviation_cause'] = B2bModel::get_code('deviation_cause');
        $initdata['is_no_arr'] = array_column(B2bModel::get_code('is_no'), 'CD_VAL');
        $initdata['area'] = B2bModel::get_area();
        $initdata['currency'] = B2bModel::get_currency();
        $this->assign('initdata', B2bModel::set_json($initdata));
        $this->assign('url', '&ORDER_ID=' . $order_id . '&ID=' . $ID);
        $this->assign('warehousing_info', B2bModel::set_json($warehousing_info));
        $this->assign('ship_list', B2bModel::set_json($ship_list));
    }

    /**
     * 理货确认
     */
    public function warehousing_confirm()
    {
        $this->get_warehousing();
        $this->display();
    }

    /**
     * 理货详情
     */
    public function warehousing_detail()
    {
        $this->get_warehousing();
        $this->display();

    }


    /**
     *保存理货确认
     */
    public function warehouseing_save()
    {
        $url = 'ORDER_ID=' . $_GET['ORDER_ID'] . '&ID=' . $_GET['ID'];
        if ($_FILES['file']['name']) {
            // 图片上传
            $fd = new FileUploadModel();
            if ($_SERVER['SERVER_NAME'] == '172.16.13.80') $fd->filePath = __DIR__;
            $ret = $fd->uploadFile();
            if ($ret) {
                $save_list['VOUCHER_ADDRESS'] = $fd->save_name;
                $save_list['file_name'] = $_FILES['file']['name'];
            } else {
                $info = '保存失败：上传文件失败';
                $this->error($info, U('warehousing_detail', $url), 2);
            }
        }
        $get_data = $this->_param();
        $wareshousing_goods = json_decode($get_data['wareshousing_goods']);
        $model = new Model();
        $model->startTrans();
        $data = null;
        $info = '保存成功';
        $status = 1;
        $list_res_state = 0;
//        更新物流成本
        $wareshousing_goods = json_decode($get_data['wareshousing_goods']);
        $where_wl['ID'] = $get_data['ship_list_id'];
        $save_wl['LOGISTICS_CURRENCY'] = $get_data['LOGISTICS_CURRENCY'];
        $save_wl['LOGISTICS_COSTS'] = $this->unking($get_data['logistics_costs_sum']);
        $save_wl['power_all'] = $this->unking($get_data['power_all_sum']);
        $wl_upd = $model->table('tb_b2b_ship_list')->where($where_wl)->save($save_wl);
        /*if (!$wl_upd) {
            trace($wl_upd, '$wl_upd');
            $model->rollback();
            $info = '物流修改失败';
            $status = 0;
            goto ajaxreturn;
        }*/
        foreach ($wareshousing_goods->goods as $k => $v) {
            $model->table('tb_b2b_warehousing_goods')->where('ship_id = ' . $v->ID)->getField('warehousing_id');
            $where['ID'] = $v->ID;
            $save['DELIVERED_NUM'] = $v->DELIVERED_NUM;
            $save['DEVIATION_NUM'] = $v->DEVIATION_NUM;
            $save['DEVIATION_REASON'] = $v->DEVIATION_REASON;
            $save['OR_AGAIN_WAREING'] = $v->OR_AGAIN_WAREING;
            $save['AGAIN_WAREING_NUM'] = $v->AGAIN_WAREING_NUM;
            $save['RECOVE_MONEY'] = $v->RECOVE_MONEY;
            $save['recove_curreny'] = $v->recove_curreny;


            $save['SUBMIT_TIME'] = date('Y-m-d H:m:s');
            $save_list['WAREING_DATE'] = $save['WAREING_DATE'] = $get_data['WAREING_DATE'];
            if ($save['DELIVERED_NUM']) {
                $save_list['submit_user'] = $save['SUBMIT_USER_ID'] = empty(session('m_loginname')) ? 'admin_null' : session('m_loginname');
            }

            $goods_res = $model->table('tb_b2b_warehousing_goods')->where($where)->save($save);


            $where_order['ID'] = $v->warehousing_id;
            $model->table('tb_b2b_warehouse_list')->where($where_order)->setInc('WAREHOUSEING_NUM', $save['DELIVERED_NUM']);
            $model->table('tb_b2b_warehouse_list')->where($where_order)->setInc('DEVIATION_NUM', $save['DEVIATION_NUM']);
            $model->table('tb_b2b_warehouse_list')->where($where_order)->setInc('AGAIN_WAREING_NUM', $save['AGAIN_WAREING_NUM']);
            $recove_money = $model->table('tb_b2b_warehouse_list')->where($where_order)->getField('RECOVE_MONEY');
            $save_list['RECOVE_MONEY'] = round(($recove_money + $save['RECOVE_MONEY']), 6);
            $warehousing_state = $this->get_code_key('warehousing_state');
            $save_list['status'] = $warehousing_state['已确认'];
            $save_list['recove_curreny'] = $save['recove_curreny'];
            $where_goods['ORDER_ID'] = $_GET['ORDER_ID'];
            $where_goods['SKU_ID'] = $v->warehouse_sku;
            $model->table('tb_b2b_goods')->where($where_goods)->setInc('is_inwarehouse_num', $save['DELIVERED_NUM']);
            $list_res = $model->table('tb_b2b_warehouse_list')->where($where_order)->save($save_list);
            if ($list_res && $list_res_state == 0) {
                $list_res_state = 1;
            }
//            增加待收款单据
            if (!$goods_res || !$list_res_state) {
                $model->rollback();
                $info = '保存失败';
                $status = 0;
                break;
                goto ajaxreturn;
            }
        }
        $model->commit();
        ajaxreturn:
//        $this->ajaxReturn($data, $info, $status);
        B2bModel::add_log($_GET['ORDER_ID'], $status, $info);
        if ($status == 1) {
            $this->success($info, U('warehousing_detail', $url), 2);
        } else {
            $this->error($info, U('warehousing_detail', $url), 2);
        }
    }

    /**
     * 收款列表
     */
    public function gathering_list()
    {
        $receipt = M('receipt', 'tb_b2b_');
        $getdata = $this->_param();
        $this->action_gathering['sales_team_code'] = empty($getdata['sales_team_id']) ? '' : $getdata['sales_team_id'];
        $this->action_gathering['CLIENT_NAME'] = empty($getdata['client_id']) ? '' : $getdata['client_id'];
        $this->action_gathering['PO_ID'] = empty($getdata['PO_ID']) ? '' : $getdata['PO_ID'];
        $where = B2bModel::joinwhere($getdata, 'b2bgathering');

        if ($getdata['receipt_operation_status'] === '0' || !empty($getdata['receipt_operation_status'])) {
            $this->action_gathering['gathering'] = $getdata['receipt_operation_status'];
            if ($getdata['receipt_operation_status'] != 0) $where['tb_b2b_receipt.receipt_operation_status'] = $getdata['receipt_operation_status'] - 1;
        } else {
            $this->action_gathering['gathering'] = 0;
        }

        $count = $receipt
            ->where($where)
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join tb_b2b_doship on tb_b2b_doship.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join (select max(WAREING_DATE) as WAREING_DATE,status,ORDER_ID from tb_b2b_warehouse_list group by ORDER_ID) as tb_b2b_warehouse_list on tb_b2b_warehouse_list.ORDER_ID = tb_b2b_receipt.ORDER_ID ')
            ->join('left join (select tb_b2b_ship_list.SUBMIT_TIME,tb_b2b_ship_list.order_id,max(tb_b2b_ship_list.Estimated_arrival_DATE) as Estimated_arrival_DATE,max(tb_b2b_ship_list.DELIVERY_TIME) as DELIVERY_TIME from tb_b2b_ship_list group by order_id order by ID desc ) as tb_b2b_ship_list on tb_b2b_ship_list.order_id =  tb_b2b_receipt.ORDER_ID ')
            ->field('tb_b2b_receipt.*,tb_b2b_info.PO_USER,tb_b2b_info.po_time,tb_b2b_doship.shipping_status,tb_b2b_doship.update_time,tb_b2b_warehouse_list.status,tb_b2b_warehouse_list.WAREING_DATE,tb_b2b_ship_list.Estimated_arrival_DATE,tb_b2b_ship_list.DELIVERY_TIME,tb_b2b_ship_list.SUBMIT_TIME')
            ->count();
        import('ORG.Util.Page');
        $Page = new Page($count, 10);
        $show = $Page->show();
        $gathering_list = $receipt
            ->where($where)
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join tb_b2b_doship on tb_b2b_doship.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join (select max(WAREING_DATE) as WAREING_DATE,status,ORDER_ID from tb_b2b_warehouse_list group by ORDER_ID) as tb_b2b_warehouse_list on tb_b2b_warehouse_list.ORDER_ID = tb_b2b_receipt.ORDER_ID ')
            ->join('left join (select tb_b2b_ship_list.SUBMIT_TIME,tb_b2b_ship_list.order_id,max(tb_b2b_ship_list.Estimated_arrival_DATE) as Estimated_arrival_DATE,max(tb_b2b_ship_list.DELIVERY_TIME) as DELIVERY_TIME from tb_b2b_ship_list group by order_id order by ID desc ) as tb_b2b_ship_list on tb_b2b_ship_list.order_id =  tb_b2b_receipt.ORDER_ID ')
            ->order('tb_b2b_receipt.ID desc')
            ->field('tb_b2b_receipt.*,tb_b2b_info.PO_USER,tb_b2b_info.po_time,tb_b2b_doship.shipping_status,tb_b2b_doship.update_time,tb_b2b_warehouse_list.status,tb_b2b_warehouse_list.WAREING_DATE,tb_b2b_ship_list.Estimated_arrival_DATE,tb_b2b_ship_list.DELIVERY_TIME,tb_b2b_ship_list.SUBMIT_TIME')
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //        合同 oa_TIME , 发货后update_time -> DELIVERY_TIME, 到港后 Estimated_arrival_DATE >.<,入库后 WAREING_DATE
        $initdata['sales_team'] = B2bModel::get_sales_team();
        $initdata['number_th'] = $this->number_th;
        $initdata['node_is_workday'] = B2bModel::get_code('node_is_workday');
        $initdata['node_type'] = B2bModel::get_code('node_type');
        $initdata['node_date'] = B2bModel::get_code('node_date');
        $initdata['gathering'] = B2bModel::get_code_lang('gathering');
        $this->assign('action', B2bModel::set_json($this->action_gathering));
        $this->assign('initdata', B2bModel::set_json($initdata));
        $this->assign('gathering_list', B2bModel::set_json($gathering_list));
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display();
    }

    /**
     * 收款下载
     */
    public function gathering_down()
    {
        $receipt = M('receipt', 'tb_b2b_');
        $receipt_arr = $receipt->select();
        $this->down_existing($receipt_arr);
    }


    /**
     * 收款确认
     */
    public function gathering_detail()
    {
        $id = I('id');
        $receipt = M('receipt', 'tb_b2b_');
        $gathering = $receipt->where('tb_b2b_receipt.id = ' . $id)
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join tb_b2b_doship on tb_b2b_doship.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join (select max(WAREING_DATE) as WAREING_DATE ,status,ORDER_ID from tb_b2b_warehouse_list group by ORDER_ID) as tb_b2b_warehouse_list on tb_b2b_warehouse_list.ORDER_ID = tb_b2b_receipt.ORDER_ID ')
            ->join('left join (select tb_b2b_ship_list.SUBMIT_TIME,tb_b2b_ship_list.order_id,max(tb_b2b_ship_list.Estimated_arrival_DATE) as Estimated_arrival_DATE,max(tb_b2b_ship_list.DELIVERY_TIME) as DELIVERY_TIME from tb_b2b_ship_list group by tb_b2b_ship_list.order_id order by tb_b2b_ship_list.ID desc ) as tb_b2b_ship_list on tb_b2b_ship_list.order_id =  tb_b2b_receipt.ORDER_ID ')
            ->field('tb_b2b_receipt.*,tb_b2b_receipt.actual_payment_amount as actual_payment_amount_z,tb_b2b_receipt.expect_receipt_amount as actual_payment_amount,tb_b2b_info.PO_USER,tb_b2b_info.po_time,tb_b2b_info.po_currency,tb_b2b_info.our_company as our_company_info,tb_b2b_info.our_company as company_our,tb_b2b_doship.update_time,tb_b2b_ship_list.Estimated_arrival_DATE,tb_b2b_ship_list.DELIVERY_TIME,tb_b2b_warehouse_list.WAREING_DATE,tb_b2b_ship_list.SUBMIT_TIME')
            ->select();
        $gathering = $gathering[0];
        $gathering['all_node'] = $receipt->where('ORDER_ID = ' . $gathering['ORDER_ID'])->order('ID')->field('receiving_code')->select();
        $initdata['number_th'] = $this->number_th;
        $initdata['node_is_workday'] = B2bModel::get_code('node_is_workday');
        $initdata['node_type'] = B2bModel::get_code('node_type');
        $initdata['node_date'] = B2bModel::get_code('node_date');
        $initdata['period'] = B2bModel::get_code('period');
        $initdata['invioce'] = B2bModel::get_code('invioce');
        $initdata['tax_point'] = B2bModel::get_code('tax_point');
        $initdata['wfgs'] = B2bModel::get_code('我方公司');
        $initdata['currency'] = B2bModel::get_code('기준환율종류코드');

        $this->assign('initdata', B2bModel::set_json($initdata));
        $this->assign('gathering', B2bModel::set_json($gathering));
        $this->assign('deviation_cause', B2bModel::set_json(B2bModel::get_code('deviation_cause')));
        $this->assign('or_invoice_arr', B2bModel::set_json(array_column(B2bModel::get_code('or_invoice_arr'), 'CD_VAL')));
        $this->assign('url', '&id=' . $id);
        $this->display();
    }

    /**
     * 收货保存
     */
    public function gathering_save()
    {
        $url = '&id=' . $_GET['id'];
        if ($_FILES['file']['name']) {
            // 图片上传
            $fd = new FileUploadModel();
            if ($_SERVER['SERVER_NAME'] == '172.16.13.80') $fd->filePath = __DIR__;
            $ret = $fd->uploadFile();
            if ($ret) {
                $save['file_path'] = $fd->save_name;
                $save['file_name'] = $_FILES['file']['name'];
            } else {
                $this->error("保存失败：上传文件失败," . $fd->error, U('warehousing_confirm', $url), 2);
            }
        }
        $get_data = $this->_param();
        $post = json_decode($get_data['gathering'], true);
        $data = null;
        $info = '保存失败';
        $status = 0;
        $Receipt = M('receipt', 'tb_b2b_');
        if ($post) {
            $save['actual_payment_amount'] = $post['actual_payment_amount'];
            $actual_receipt_date = $post['actual_receipt_date'];
            if ($post['actual_receipt_date'] == '-' || empty($post['actual_receipt_date'])) {
                $actual_receipt_date = date('Y-m-d H:i:s');
            }
            $save['actual_receipt_date'] = $actual_receipt_date;
            $save['DEVIATION_REASON'] = $post['DEVIATION_REASON'];
            $save['invoice_status'] = $post['invoice_status'];
            $save['company_our'] = $post['company_our'];
            $save['receipt_serial_number'] = $post['receipt_serial_number'];
            $save['company_our'] = $post['company_our'];
            $save['receipt_serial_number'] = $post['receipt_serial_number'];
            $save['remarks'] = $post['remarks'];
            $save['create_time'] = date('Y-m-d H:i:s');
            $gathering_arr = $this->get_code_key('gathering');
            $save['receipt_operation_status'] = $gathering_arr['待收款'];   // 实际为收款code映射为 +1
            $save['operator_id'] = empty(session('m_loginname')) ? 'admin_null' : session('m_loginname');;
            if ($post['actual_receipt_date'] != '-') {
//           更新逾期状态
                OverdueAction::actual_overdue_upd($post['ID'], $actual_receipt_date, $Receipt);
            }
            $receipt = $Receipt->where(' ID = ' . $post['ID'])->save($save);

            if ($receipt) {
                $data = $receipt;
                $info = '保存成功';
                $status = 1;
            }
        }
        B2bModel::add_log($_GET['ORDER_ID'], $status, $info);
        if ($status == 1) {
            $this->success($info, U('gathering_detail', $url), 2);
        } else {
            $this->error($info, U('gathering_detail', $url), 2);
        }
    }


    /**
     * 日志列表
     */
    public function log_list()
    {
        $order_id = I('order_id');
        $order_url = U('b2b/order_list') . '&order_id=' . $order_id . '#/b2bsend';
        $Log = M('log', 'tb_b2b_');
        $logs = $Log->where('ORDER_ID = ' . $order_id)->select();

        $this->assign('logs', $logs);
        $this->assign('order_url', $order_url);
        $this->display();
    }

    /**
     * 版本
     */
    public function v()
    {
        $v = 1.1;
        die($v);
    }

    /**
     * 清理
     */
    public function clean()
    {
        $Model = new Model();
        $Model->table('tb_b2b_doship')->where('1 = 1')->delete();
        $Model->table('tb_b2b_goods')->where('1 = 1')->delete();
        $Model->table('tb_b2b_info')->where('1 = 1')->delete();
        $Model->table('tb_b2b_order')->where('1 = 1')->delete();
        $Model->table('tb_b2b_ship_goods')->where('1 = 1')->delete();
        $Model->table('tb_b2b_ship_list')->where('1 = 1')->delete();
        $Model->table('tb_b2b_warehouse_list')->where('1 = 1')->delete();
        $Model->table('tb_b2b_warehousing_goods')->where('1 = 1')->delete();
        $Model->table('tb_b2b_receipt')->where('1 = 1')->delete();

    }

// private function list

    /**
     * 返回订单所有数据
     */
    public function goods_all_powers($order_id)
    {
        $all_power = 0;
        $Ship_list = M('ship_list', 'tb_b2b_');
        $all_power = $Ship_list->field('sum(power_all) as power_all')
            ->where('order_id = ' . $order_id)
            ->select();
        return $all_power[0]['power_all'];
    }


    /**
     * 更新权值求和
     * @param $goods
     * @return bool
     */
    private function sync_ship_power_all($goods)
    {
        $Ship_list = M('ship_list', 'tb_b2b_');
        foreach ($goods as $v) {
            $ship_list = $Ship_list->where('ID = ' . $v['SHIP_ID'])->setInc('power_all', $v['power']);
            if (!$ship_list) {
                goto return_data;
            }
        }
        return_data:
        return true;
    }


    /**
     * get sku in power value
     * @param $sku
     * @param $date
     * @return int
     */
    private function get_power($sku, $date = null)
    {
        $power = 0;
        $Power = M('power', 'tb_wms_');
        $power_now = $Power->where('SKU_ID = ' . $sku)->getField('weight');
        if ($power_now) $power = $power_now;
        return $power;
    }

    private function get_currency($currency, $date)
    {
        $url = INSIGHT . '/dashboard-backend/external/exchangeRate?date=' . $date . '&dst_currency=CNY&src_currency=' . $currency;
        $currency = json_decode(curl_request($url), 1);
        return $currency;
    }

    /**
     *  获取总价
     * @param $e
     * @return int
     */
    private function get_sale_income($e)
    {
        return $this->join_income($e, 'actual_payment_amount');
    }

    /**
     * 获取残次品
     * @param $e
     * @return int
     */
    private function get_imperfections_income($e)
    {
        $Warehouse_list = M('warehouse_list', 'tb_b2b_');
        $warehouse_list = $Warehouse_list->where('ORDER_ID = ' . $e)->field('RECOVE_MONEY,recove_curreny,WAREING_DATE')->select();
//        return array_sum(array_column($warehouse_list, 'RECOVE_MONEY'));

        return $this->join_income($warehouse_list, 'RECOVE_MONEY', 'recove_curreny', 'WAREING_DATE');
    }

    /**
     * 物流成本
     * @param $e
     * @return int
     */
    private function get_logistics_income($e)
    {
        return $this->join_income($e, 'LOGISTICS_COSTS', 'LOGISTICS_CURRENCY');
    }

    /**
     * 最后核算时间
     */
    private function get_end_time($id)
    {
        $Model = M('receipt', 'tb_b2b_');
        $where['ORDER_ID'] = $id;
        $where['actual_payment_amount'] = array('exp', 'is not null');
        $tb_b2b_receipt = $Model->where($where)->order('ID desc')->getField('create_time');
        return $tb_b2b_receipt;
    }


    /**
     * 获取backend商品采购价
     * @param $e
     * @return int
     */
    private function get_backend_estimat($e, $time)
    {
        $Model = new Model();
        $allsum = 0;
        foreach ($e as $v) {
            $guds_opt_org_prc = $Model->table('tb_ms_guds_opt')->where('GUDS_OPT_ID = ' . $v->skuId)->getField('GUDS_OPT_ORG_PRC');
            $std_xchr_kind_cd = $Model->table('tb_ms_guds')->where('GUDS_ID = ' . substr($v->skuId, 0, -2))->getField('STD_XCHR_KIND_CD');
            $sum = $guds_opt_org_prc * B2bModel::update_currency($std_xchr_kind_cd, $time) * $v->demand;
            $allsum += empty($sum) ? 0 : $sum;
        }
        return $allsum;
    }

    /**
     * 清理缓存
     */
    public function clean_cache()
    {
        $get_path = empty(I('get_path')) ? '/Temp' : I('get_path');
        $path = APP_PATH . '/Runtime' . $get_path;
        $dirarr = scandir($path);
        foreach ($dirarr as $v) {
            if ($v != '.' && $v != '..') {
                unlink($path . '/' . $v);
            }
        }
    }

    /**
     * 测试
     */
    public function test()
    {
        var_dump(B2bModel::get_code_lang('order_fh'));

    }


//    array to object
    private function arr2obj($e, $k = '')
    {
        foreach ($e as $v) {
            $arr[] = [$k => $v];
        }
        return $arr;
    }


    private function get_doship($e)
    {

    }

    private function unking($e)
    {
        return str_replace(',', '', $e);
    }

//    ship list and goods
    private function get_list_goods($order_id)
    {
        $Model = new Model();
        $ship_list = $Model->table('tb_b2b_ship_list')
            ->where('order_id = ' . $order_id)
            ->select();
        foreach ($ship_list as &$v) {
            $v['goods'] = $Model->table('tb_b2b_ship_goods')
                ->where('SHIP_ID = ' . $v['ID'])
                ->join('left join tb_b2b_goods on tb_b2b_goods.ORDER_ID = ' . $order_id . ' AND tb_b2b_goods.SKU_ID = tb_b2b_ship_goods.SHIPPING_SKU ')
                ->field('tb_b2b_ship_goods.*,tb_b2b_goods.goods_title,tb_b2b_goods.goods_info')
                ->select();
        }
        return $ship_list;
    }

    //    ship list and goods
    private function get_warehousing_goods($order_id, $ID = null)
    {
        $Model = new Model();
        $where['tb_b2b_warehouse_list.order_id'] = $order_id;
        if ($ID) $where['tb_b2b_warehouse_list.ID'] = $ID;
        $ship_list = $Model->table('tb_b2b_warehouse_list')
            ->where($where)
            ->join('left join tb_b2b_ship_list on  tb_b2b_ship_list.ID = tb_b2b_warehouse_list.SHIP_LIST_ID  ')
            ->field('tb_b2b_warehouse_list.*,tb_b2b_ship_list.BILL_LADING_CODE,tb_b2b_ship_list.warehouse as ship_warehouse,tb_b2b_ship_list.DELIVERY_TIME as ship_delivery_time,tb_b2b_ship_list.Estimated_arrival_DATE,tb_b2b_ship_list.REMARKS,tb_b2b_ship_list.power_all,tb_b2b_ship_list.DELIVERY_TIME')
            ->select();
        foreach ($ship_list as $v) {
            $v_arr[] = $v['ID'];
        }
        $where_goods['warehousing_id'] = array('in', $v_arr);
        $v_goods = $Model->table('tb_b2b_warehousing_goods')
            ->where($where_goods)
            ->join('left join tb_b2b_goods on tb_b2b_goods.ORDER_ID = ' . $order_id . ' AND tb_b2b_goods.SKU_ID = tb_b2b_warehousing_goods.warehouse_sku ')
            ->field('tb_b2b_warehousing_goods.*,tb_b2b_warehousing_goods.DELIVERED_NUM as DELIVERED_NUM_z,tb_b2b_warehousing_goods.TOBE_WAREHOUSING_NUM as DELIVERED_NUM,tb_b2b_goods.goods_title,tb_b2b_goods.goods_info,tb_b2b_goods.price_goods')
            ->select();
        $v_col = array_flip(array_column($v_goods, 'warehousing_id'));
        foreach ($ship_list as $k => &$v) {
            $v['goods'][] = $v_goods[$v_col[$v['ID']]];
        }
        return $ship_list;
    }

    /**
     * @param $e
     * @param $key
     * @param null $bz
     * @param string $time
     * @return int
     */
    private function join_income($e, $key, $bz = null, $time = 'DELIVERY_TIME')
    {
        $sum = 0;
        foreach ($e as $v) {
            if (empty($bz)) {
                $sum += $v[$key];
            } else {
                $sum += $v[$key] * B2bModel::update_currency($v[$bz], date('Y-m-d', strtotime($v[$time])));
            }
        }
        return $sum;
    }

    /**
     * @return array
     */
    private function get_code_key($nm_val)
    {
        $ship_state = B2bModel::get_code($nm_val);
        $ship_state_arr = array_column($ship_state, 'CD', 'CD_VAL');
        return $ship_state_arr;
    }

    private function down_existing($e)
    {
        $expTitle = "应收款单";
        $expCellName = array(
            array('ID', '子收款单号'),
            array('PO_ID', 'PO编号'),
            array('client_id', '客户'),
            array('kxlx', '款项类型'),
            array('receiving_code', '收款节点与比例'),
            array('expect_receipt_amount', '预期金额'),
            array('expect_receipt_date', '预期收款时间'),
            array('receipt_operation_status', '收款状态'),
            array('yqqk', '逾期情况'),
            array('sales_team_id', '销售'),
        );

//        join exp excel
        foreach ($e as $key => $val) {
            $join_data['ID'] = $val['ID'];
            $join_data['PO_ID'] = $val['PO_ID'];
            $join_data['client_id'] = $val['client_id'];
            $join_data['kxlx'] = empty($val['receiving_code']) ? '退税' : '货款';
            $join_data['receiving_code'] = $val['receiving_code'];
            $join_data['expect_receipt_amount'] = $val['expect_receipt_amount'];
            $join_data['expect_receipt_date'] = $val['expect_receipt_date'];
            $join_data['receipt_operation_status'] = $val['receipt_operation_status'];
            $join_data['yqqk'] = $val['yqqk'];
            $join_data['sales_team_id'] = $val['sales_team_id'];
            $expTableData[] = $join_data;
        }
        $excel = new StockAction();
        $excel->exportExcel($expTitle, $expCellName, $expTableData);
    }

    /**
     * 清理关闭订单
     */
    public function click_err_close()
    {
        $arr = [];
        foreach ($arr as $v) {
            $result = json_decode(curl_request($v), 1);
            print_r($result);
        }
    }

    /**
     * 利润计算
     * @param $order_id
     * @param $data
     * @return mixed
     */
    private function exchange_rete_calculation($order_id)
    {
        $Model = new Model();
        $data['info'] = $Model->table('tb_b2b_order')->where('tb_b2b_order.ID = ' . $order_id)
            ->field('tb_b2b_order.*,tb_b2b_info.*')
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_order.ID')
            ->select();
        $data['goods'] = $Model->table('tb_b2b_goods')->where('tb_b2b_goods.ORDER_ID = ' . $order_id)
            ->select();
//        物流
        $data['ship'] = $Model->table('tb_b2b_ship_list')
            ->field('tb_b2b_ship_list.*,tb_b2b_warehouse_list.status')
            ->join('tb_b2b_warehouse_list on tb_b2b_warehouse_list.SHIP_LIST_ID = tb_b2b_ship_list.ID')
            ->where('tb_b2b_ship_list.order_id = ' . $order_id)
            ->select();
//        收款
        $data['receipt'] = $Model->table('tb_b2b_receipt')->where('tb_b2b_receipt.ORDER_ID = ' . $order_id)
            ->join('left join tb_b2b_info on tb_b2b_info.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join tb_b2b_doship on tb_b2b_doship.ORDER_ID = tb_b2b_receipt.ORDER_ID')
            ->join('left join (select max(WAREING_DATE) as WAREING_DATE,status,ORDER_ID from tb_b2b_warehouse_list group by ORDER_ID) as tb_b2b_warehouse_list on tb_b2b_warehouse_list.ORDER_ID = tb_b2b_receipt.ORDER_ID ')
            ->join('left join (select tb_b2b_ship_list.SUBMIT_TIME,tb_b2b_ship_list.order_id,max(tb_b2b_ship_list.Estimated_arrival_DATE) as Estimated_arrival_DATE,max(tb_b2b_ship_list.DELIVERY_TIME) as DELIVERY_TIME from tb_b2b_ship_list group by order_id order by Estimated_arrival_DATE desc ) as tb_b2b_ship_list on tb_b2b_ship_list.order_id =  tb_b2b_receipt.ORDER_ID ')
            ->field('tb_b2b_receipt.*,tb_b2b_info.PO_USER,tb_b2b_info.po_time,tb_b2b_doship.shipping_status,tb_b2b_doship.update_time,tb_b2b_warehouse_list.status,tb_b2b_warehouse_list.WAREING_DATE,tb_b2b_ship_list.Estimated_arrival_DATE,tb_b2b_ship_list.DELIVERY_TIME,tb_b2b_ship_list.SUBMIT_TIME')
            ->select();
//        利润
        $data['profit']['A'] = number_format($data['info'][0]['po_amount'] * (B2bModel::update_currency($data['info'][0]['po_currency'], $data['info'][0]['po_time'])), 2); //PO金额
        $data['profit']['B'] = number_format($data['info'][0]['tax_rebate_income'] * (B2bModel::update_currency($data['info'][0]['po_currency'], $data['info'][0]['po_time'])), 2);//退税收入
        $data['profit']['C'] = number_format($this->unking($data['profit']['A']) + $this->unking($data['profit']['B']), 2);//总收入
        $data['profit']['D'] = number_format($data['info'][0]['backend_estimat'] * (B2bModel::update_currency($data['info'][0]['backend_currency'], $data['info'][0]['po_time'])), 2);
        $data['profit']['E'] = number_format($data['info'][0]['logistics_estimat'] * (B2bModel::update_currency($data['info'][0]['logistics_currency'], $data['info'][0]['po_time'])), 2); // 预估物流成本
        $data['profit']['F'] = number_format($this->unking($data['profit']['D']) + $this->unking($data['profit']['E']), 2); //预估总成本
        $data['profit']['G'] = number_format($this->unking($data['profit']['C']) - $this->unking($data['profit']['F']), 2); //预估利润
        $data['profit']['H'] = round($this->unking($data['profit']['G']) / $this->unking($data['profit']['C']), 4) * 100;  // 预估利润
        $data['profit']['order_time'] = $data['info'][0]['create_time'];   // 生成时间

        $data['profit']['I'] = number_format(($this->get_sale_income($data['receipt']) * (B2bModel::update_currency($data['info'][0]['po_currency'], $data['info'][0]['po_time'])) - $this->unking($data['profit']['B'])), 2); //实际销售收入

        $data['profit']['J'] = $data['profit']['B'];
        $data['profit']['K'] = number_format($this->get_imperfections_income($order_id), 2); // 残次品
        $data['profit']['L'] = number_format($this->unking($data['profit']['I']) + $this->unking($data['profit']['J']) + $this->unking($data['profit']['K']), 2);


        $data['profit']['M'] = number_format($this->goods_all_powers($order_id) * (B2bModel::update_currency('N000590300', date('Y-m-d', strtotime($data['ship'][0]['DELIVERY_TIME'])))), 2); // 实际商品成本
        $data['profit']['N'] = number_format($this->get_logistics_income($data['ship']), 2); //物流成本
        $data['profit']['U'] = number_format($this->unking($data['profit']['M']) + $this->unking($data['profit']['N']), 2); // 实际总成本
        $data['profit']['V'] = number_format($this->unking($data['profit']['L']) - $this->unking($data['profit']['U']), 2);
        $data['profit']['W'] = round($this->unking($data['profit']['V']) / $this->unking($data['profit']['L']), 4) * 100;
        $data['profit']['create_time'] = $this->get_end_time($order_id);
        return $data; // 最后核算时间
    }

    /**
     * @param $order_id
     * @param $node
     * @return bool
     */
    private function upd_order_node($order_id, $node)
    {
        $Order = M('order', 'tb_b2b');
        $save['order_state'] = $node;
        $order = $Order->where('ID = ' . $order_id)->save($save);
        return $order;
    }

    private function rm_sign($e)
    {
        foreach ($e as &$v) {
            $v['CD_VAL'] = rtrim($v['CD_VAL'], '%');
        }
        return $e;
    }

    /**
     * 合同查询
     */
    public function get_ht()
    {
        trace(time(), '$date_act');
        $sp_charter_no = trim(I('sp_charter_no'));
        $Model = M();
        $where_s['tb_crm_sp_supplier.SP_NAME'] = array('eq', $sp_charter_no);
//        $where_s['tb_crm_sp_supplier.SP_NAME'] = array('like', '%' . $sp_charter_no . '%');
        $where_s['tb_crm_sp_supplier.DATA_MARKING'] = array('eq', '1');
        $contract = $Model->table('tb_crm_sp_supplier')->field('tb_crm_contract.CON_NO,tb_crm_contract.CON_COMPANY_CD')
            ->join('left join tb_crm_contract on tb_crm_contract.SP_CHARTER_NO = tb_crm_sp_supplier.SP_CHARTER_NO')
            ->where($where_s)->select();
        $data['contract'] = $contract;
        $data['c2c_data'] = array_column($contract, 'CON_COMPANY_CD', 'CON_NO');
        $data['cd_company'] = BaseModel::conCompanyCd();
        trace(time(), '$date_end');
        $this->ajaxReturn($data, '', 1);
    }

    /**
     * 获取币种
     */
    public function get_currency_backend()
    {
        echo B2bModel::update_currency(I('currency'), I('date'), I('dst_currency'));
    }

    /**
     * 导入商品
     */
    public function importGoods()
    {
        header("content-type:text/html;charset=utf-8");
        $filePath = $_FILES['file']['tmp_name'];
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        //默认用excel2007读取excel，若格式不对，则用之前的版本进行读取
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                $this->error('请上传EXCEL文件', '', true);
            }
        }
        //读取Excel文件
        $PHPExcel = $PHPReader->load($filePath);
        //读取excel文件中的第一个工作表
        $sheet = $PHPExcel->getSheet(0);
        //取得最大的列号
        $allColumn = $sheet->getHighestColumn();
        //取得最大的行号
        $allRow = $sheet->getHighestRow();
        $expe = [];
        $goods_info_url = U('Stock/Searchguds', '', '', false, true);
        $msg = '';
        $skus = [];
        $all_warehouse = StockModel::get_all_warehouse();
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $temp = [];
            $search = trim((string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue());
            $price = trim((string)$PHPExcel->getActiveSheet()->getCell("B" . $currentRow)->getValue());
            $number = trim((string)$PHPExcel->getActiveSheet()->getCell("C" . $currentRow)->getValue());
            $res = json_decode(curl_request($goods_info_url, ['GSKU' => $search]), true);
            if (!$search || $res['status'] == 0) {
                $error = true;
                $msg .= "第{$currentRow}行商品不存在<br />";
            } else {
                $sku = $res['info'][0]['GUDS_OPT_ID'];
                if ($key = array_search($sku, $skus)) {
                    $error = true;
                    $msg .= "第{$currentRow}行与第{$key}行商品重复<br />";
                }
                $skus[$currentRow] = $res['info'][0]['GUDS_OPT_ID'];
            }
            if (!is_numeric($price) || $price <= 0) {
                $error = true;
                $msg .= "第{$currentRow}行商品价格有误（比如数量中含0、或者为负数）<br />";
            }
            if (!is_numeric($number) || strstr($number, '.') || $number <= 0) {
                $error = true;
                $msg .= "第{$currentRow}行商品数量有误（比如数量中含小数、或者小于1）<br />";
            }
            if ($sku && $price && $number) {
                $temp['search'] = $search;
                $temp['sku'] = $sku;
                $temp['now_number'] = $currentRow - 1;
                $temp['price'] = $price;
                $temp['number'] = $number;
                $temp['goods_money'] = $price * $number;
                $temp['goods_name'] = $res['info'][0]['Guds']['GUDS_NM'];
                $temp['warehouse'] = $all_warehouse[$res['info'][0]['Guds']['DELIVERY_WAREHOUSE']]['warehouse'];
                $temp['guds_img'] = $res['info'][0]['Img'];
                $temp['val_str'] = $res['info']['opt_val'][0]['val'];
                $temp['drawback'] = B2bModel::TO_CD_VAL($res['info'][0]['Guds']['RETURN_RATE']);
                $temp['STD_XCHR_KIND_CD'] = $res['info'][0]['Guds']['STD_XCHR_KIND_CD'];
                $temp['GUDS_OPT_ORG_PRC'] = $res['info'][0]['GUDS_OPT_ORG_PRC'];
                $expe[] = $temp;
            }
        }
        trace($res, '$res');
        if ($error) {
            $this->error($msg, '', true);
        } else {
            $this->success($expe, '', true);
        }
    }

    public function importPo()
    {
        header("content-type:text/html;charset=utf-8");
        trace($_FILES, '$_FILES');

        if ($_FILES['size'] > 20971520) {
            $info = '保存失败：上传文件大于20m';
            $this->error($info, 0, true);
        }
        $filePath = $_FILES['file']['tmp_name'];
        if ($_FILES['file']['name']) {
            // 图片上传
            $fd = new FileUploadModel();
            if ($_SERVER['SERVER_NAME'] == '172.16.13.57' || $_SERVER['SERVER_NAME'] == 'sms2.b5c.com') $fd->filePath = __DIR__;
            $ret = $fd->uploadFile();
            if ($ret) {
                $save_list['VOUCHER_ADDRESS'] = $fd->save_name;
                $save_list['file_name'] = $_FILES['file']['name'];
                $this->success($save_list, 1, true);
            } else {
                $info = '保存失败：上传文件失败';
                $this->error($info, 0, true);
            }
        }

    }

    /**
     * @param $in
     * @param bool $to_num
     * @param bool $pad_up
     * @param null $pass_key
     * @return number|string
     */
    private function alphaID($in, $to_num = false, $pad_up = false, $pass_key = null)
    {
        $out = '';
        $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($index);
        if ($pass_key !== null) {
            for ($n = 0; $n < strlen($index); $n++) {
                $i[] = substr($index, $n, 1);
            }
            $pass_hash = hash('sha256', $pass_key);
            $pass_hash = (strlen($pass_hash) < strlen($index) ? hash('sha512', $pass_key) : $pass_hash);
            for ($n = 0; $n < strlen($index); $n++) {
                $p[] = substr($pass_hash, $n, 1);
            }
            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }
        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $len = strlen($in) - 1;
            for ($t = $len; $t >= 0; $t--) {
                $bcp = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
            }
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
        } else {
            // Digital number  -->>  alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }
            for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }
        }
        $this->bubble_sort($out);
        return $out;
    }

    /**
     * @param $arr
     */
    private function bubble_sort(&$arr)
    {
        $n = count($arr);
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if ($arr[$j] > $arr[$j + 1]) {
                    $buf = $arr[$j + 1];
                    $arr[$j + 1] = $arr[$j];
                    $arr[$j] = $buf;
                }
            }
        }
    }

    private function auth_in_mac($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $MerchantName, $AuthResURL, $OrderDetail, $AutoCap, $Customize, $debug)
    {
        $CombineStr = "|" . $MerchantID . "|" . $TerminalID . "|" . $lidm . "|" . $purchAmt . "|" . $txType . "|" . $Option . "|";
        $ParameterArray = array($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $MerchantName, $AuthResURL, $OrderDetail, $AutoCap, $Customize);
        if ($debug == 1) {
            echo "debug=$$debug \n";
            echo "CombineStr is : $CombineStr \n";
            while (list($var, $val) = each($ParameterArray)) {
                echo "$var is $val\n";
            }
        }
        $CMP = checkAuthInMacParameter($ParameterArray);

        if ($CMP == "000") {
            $MACString = DESMAC($CombineStr, $Key, $debug);
            $MACString = substr($MACString, -48, 48);
            return $MACString;
        } else {
            return "0x" . dechex($CMP);
        }
    }

    private function get_auth_urlenc($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $MerchantName, $AuthResURL, $OrderDetail, $AutoCap, $Customize, $InMac, $debug)
    {
        if ($txType == "2") {
            $ProdCode = $Option;
            $NumberOfPay = "";
        } else {
            $ProdCode = "";
            $NumberOfPay = $Option;
        }
        $encStr =
            "MerchantID=" . $MerchantID . "&" . "TerminalID=" . $TerminalID . "&" .
            "lidm=" . $lidm . "&" . "purchAmt=" . $purchAmt . "&" .
            "txType=" . $txType . "&" . "MerchantName=" . $MerchantName . "&" .
            "AuthResURL=" . $AuthResURL . "&" . "OrderDetail=" . $OrderDetail . "&" .
            "ProdCode=" . $ProdCode . "&" . "AutoCap=" . $AutoCap . "&" .
            "customize=" . $Customize . "&" . "NumberOfPay=" . $NumberOfPay . "&" .
            "InMac=" . $InMac;
        $URLEnc = DESMAC($encStr, $Key, $debug);
        return $URLEnc;
    }

    private function checkAuthInMacParameter($ParameterArray)
    {
        if ($ParameterArray[0] == NULL || !is_numeric($ParameterArray[0]) || strlen($ParameterArray[0]) != 13)
            return '285212673';
        if ($ParameterArray[1] == NULL || !is_numeric($ParameterArray[1]) || strlen($ParameterArray[1]) != 8)
            return '285212674';
        if ($ParameterArray[2] == NULL || strlen($ParameterArray[2]) < 1 || strlen($ParameterArray[2]) > 19 ||
            (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[2]))
        )
            return '285212675';
        if ($ParameterArray[3] == NULL || !is_numeric($ParameterArray[3]) || strlen($ParameterArray[3]) < 1)
            return '285212676';
        if ($ParameterArray[4] == NULL || !is_numeric($ParameterArray[4]) || strlen($ParameterArray[4]) != 1)
            return '285212677';
        if ($ParameterArray[5] == NULL && ($ParameterArray[4] == '0' || $ParameterArray[4] == '1' || $ParameterArray[4] == '2' || $ParameterArray[4] == '9'))
            ;
        elseif ($ParameterArray[5] == NULL || !is_numeric($ParameterArray[5]))
            return '285212679';
        if ($ParameterArray[4] == '4') {
            if (strlen($ParameterArray[5]) < 3 || strlen($ParameterArray[5]) > 4) {
                return '285212679';
            }
        } else {
            if (strlen($ParameterArray[5]) != 0 && strlen($ParameterArray[5]) > 2)
                return '285212679';
        }
        if ($ParameterArray[6] == NULL || strlen($ParameterArray[6]) != 24)
            return '285212697';
        else
            return "000";
    }

    private function auth_out_mac($status, $errCode, $authCode, $authAmt, $lidm, $OffsetAmt, $OriginalAmt, $UtilizedPoint, $Option, $Last4digitPAN, $Key, $debug)
    {
        $CombineStr = "|" . $status . "|" . $errCode . "|" . $authCode . "|" . $authAmt . "|" . $lidm . "|" . $OffsetAmt . "|" . $OriginalAmt . "|" . $UtilizedPoint . "|" . $Option . "|" . $Last4digitPAN . "|";
        $ParameterArray = array($status, $errCode, $authCode, $authAmt, $lidm, $OffsetAmt, $OriginalAmt, $UtilizedPoint, $Option, $Last4digitPAN, $Key);
        if ($debug == 1) {
            echo "debug=$$debug \n";
            echo "CombineStr is : $CombineStr \n";
            while (list($var, $val) = each($ParameterArray)) {
                echo "$var is $val\n";
            }
        }
        $CMP = checkAuthOutMacParameter($ParameterArray);
        if ($CMP == "000") {
            $MACString = DESMAC($CombineStr, $Key, $debug);
            $MACString = substr($MACString, -48, 48);
            return $MACString;
        } else {
            return "0x" . dechex($CMP);
        }
    }

    private function checkAuthOutMacParameter($ParameterArray)
    {
        if ($ParameterArray[0] == NULL || !is_numeric($ParameterArray[0]) || strlen($ParameterArray[0]) < 0 || strlen($ParameterArray[0]) > 2)
            return '285212680';
        if ($ParameterArray[1] == NULL || strlen($ParameterArray[1]) < 2 || strlen($ParameterArray[1]) > 4)
            return '285212681';
        if ($ParameterArray[2] == NULL || strlen($ParameterArray[2]) < 1 || strlen($ParameterArray[2]) > 7)
            return '285212682';
        if ($ParameterArray[3] == NULL || !is_numeric($ParameterArray[3]) || strlen($ParameterArray[3]) < 1 || strlen($ParameterArray[3]) > 7)
            return '285212683';
        if ($ParameterArray[4] == NULL || strlen($ParameterArray[4]) < 1 || strlen($ParameterArray[4]) > 19 ||
            (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[4]))
        )
            return '285212675';
        if ($ParameterArray[5] != NULL && (!is_numeric($ParameterArray[5]) || strlen($ParameterArray[5]) > 7))
            return '285212684';
        if ($ParameterArray[6] != NULL && (!is_numeric($ParameterArray[6]) || strlen($ParameterArray[6]) > 7))
            return '285212685';
        if ($ParameterArray[7] != NULL && (!is_numeric($ParameterArray[7]) || strlen($ParameterArray[7]) > 7))
            return '285212686';
        if (strlen($ParameterArray[8]) != 0 && strlen($ParameterArray[8]) > 4)
            return '285212679';
        if (!is_numeric($ParameterArray[8]) && $ParameterArray[8] != NULL)
            return '285212679';
        if (strlen($ParameterArray[9]) != 0 && strlen($ParameterArray[9]) != 4)
            return '285212687';
        if (!is_numeric($ParameterArray[9]) && $ParameterArray[9] != NULL)
            return '285212687';
        if ($ParameterArray[10] == NULL || strlen($ParameterArray[10]) != 24)
            return '285212697';
        else
            return "000";
    }

    private function checkDecryptParameter($ParameterArray)
    {
        if ($ParameterArray[0] == NULL || $ParameterArray[0] % 8 != 0)
            return '285212701';
        if ($ParameterArray[1] == NULL || strlen($ParameterArray[1]) != 24)
            return '285212697';
        else
            return "000";
    }

    private function mpiauth_in_mac($MerchantID, $TerminalID, $AcquireBIN, $CardNo, $ExpYear, $ExpMonth, $authAmt, $lidm, $Key, $RetURL, $debug)
    {
        $CombineStr = "|" . $MerchantID . "|" . $AcquireBIN . "|" . $CardNo . "|" . $ExpYear . "|" . $ExpMonth . "|" . $authAmt . "|" . $lidm . "|";
        $ParameterArray = array($MerchantID, $TerminalID, $AcquireBIN, $CardNo, $ExpYear, $ExpMonth, $authAmt, $lidm, $Key, $RetURL);
        if ($debug == 1) {
            echo "debug=$$debug \n";
            echo "CombineStr is : $CombineStr \n";
            while (list($var, $val) = each($ParameterArray)) {
                echo "$var is $val\n";
            }
        }
        $CMP = checkMPIinMacParameter($ParameterArray);
        if ($CMP == "000") {
            $MACString = DESMAC($CombineStr, $Key, $debug);
            $MACString = substr($MACString, -48, 48);
            return $MACString;
        } else {
            return "0x" . dechex($CMP);
        }
    }

    private function get_mpi_urlenc($MerchantID, $TerminalID, $AcquireBIN, $CardNo, $ExpYear, $ExpMonth, $authAmt, $lidm, $Key, $RetURL, $InMac, $debug)
    {
        $encStr =
            "merchantID=" . $MerchantID . "&" . "terminalID=" . $TerminalID . "&" .
            "acquirerBIN=" . $AcquireBIN . "&" . "cardNumber=" . $CardNo . "&" .
            "expYear=" . $ExpYear . "&" . "expMonth=" . $ExpMonth . "&" .
            "totalAmount=" . $authAmt . "&" . "XID=" . $lidm . "&" .
            "RetUrl=" . $RetURL . "&" . "InMac=" . $InMac;
        $URLEnc = DESMAC($encStr, $Key, $debug);
        return $URLEnc;
    }

    private function checkMPIinMacParameter($ParameterArray)
    {
        if ($ParameterArray[0] == NULL || !is_numeric($ParameterArray[0]) || strlen($ParameterArray[0]) < 4 || strlen($ParameterArray[0]) > 15)
            return '285212673';
        if ($ParameterArray[1] == NULL || !is_numeric($ParameterArray[1]) || strlen($ParameterArray[1]) != 8)
            return '285212674';
        if (strlen($ParameterArray[2]) != 0 && strlen($ParameterArray[2]) != 6)
            return '285212688';
        if ($ParameterArray[2] != NULL && !is_numeric($ParameterArray[2]))
            return '285212688';
        if (strlen($ParameterArray[3]) != 16)
            return '285212689';
        if (strlen($ParameterArray[4]) != 4)
            return '285212690';
        if (strlen($ParameterArray[5]) != 2)
            return '285212691';
        if ($ParameterArray[6] == NULL || !is_numeric($ParameterArray[6]) || $ParameterArray[6] > 9999999999)
            return '285212683';
        if ($ParameterArray[7] == NULL || strlen($ParameterArray[7]) < 1 || strlen($ParameterArray[7]) > 20 ||
            (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[7]))
        )
            return '285212675';
        if ($ParameterArray[8] == NULL || strlen($ParameterArray[8]) != 24)
            return '285212697';
        else
            return "000";
    }

    private function mpiauth_out_mac($CardNo, $ExpDate, $lidm, $ECI, $CAVV, $errCode, $Key, $debug)
    {
        $CombineStr = "|" . $CardNo . "|" . $ExpDate . "|" . $lidm . "|" . $ECI . "|" . $CAVV . "|" . $errCode . "|";
        $ParameterArray = array($CardNo, $ExpDate, $lidm, $ECI, $CAVV, $errCode, $Key);
        if ($debug == 1) {
            echo "debug=$$debug \n";
            echo "CombineStr is : $CombineStr \n";
            while (list($var, $val) = each($ParameterArray)) {
                echo "$var is $val\n";
            }
        }
        $CMP = checkMPIoutMacParameter($ParameterArray);
        if ($CMP == "000") {
            $MACString = DESMAC($CombineStr, $Key, $debug);
            $MACString = substr($MACString, -48, 48);
            return $MACString;
        } else {
            return "0x" . dechex($CMP);
        }
    }

    private function checkMPIoutMacParameter($ParameterArray)
    {
        if (strlen($ParameterArray[0]) != 16) return '285212689';
        if (strlen($ParameterArray[1]) != 6) return '285212694';
        if ($ParameterArray[2] == NULL || strlen($ParameterArray[2]) < 1 || strlen($ParameterArray[2]) > 20 ||
            (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[2]))
        )
            return '285212675';
        if (strlen($ParameterArray[3]) != 1 || !is_numeric($ParameterArray[3]))
            return '285212695';
        if ($ParameterArray[5] == NULL || strlen($ParameterArray[5]) < 1 || strlen($ParameterArray[5]) > 4)
            return '285212681';
        if ($ParameterArray[6] == NULL || strlen($ParameterArray[6]) != 24)
            return '285212697';
        else
            return "000";
    }

    private function checkMPIDecryptMacParameter($ParameterArray)
    {
        if ($ParameterArray[0] == NULL || $ParameterArray[0] % 8 != 0)
            return '285212701';
        if ($ParameterArray[1] == NULL || strlen($ParameterArray[1]) != 24)
            return '285212697';
        else
            return "000";
    }

    private function DESMAC($msg, $key, $debug)
    {
        $block_size = mcrypt_get_block_size('tripledes', 'cbc');
        $padlen = $block_size - (strlen($msg) % $block_size);
        for ($i = 0; $i < $padlen; $i++)
            $msg .= chr($padlen);
        if ($debug == 1) {
            echo "DESMAC:key=$key\n";
            echo "DESMAC:msg=$msg\n";
        }
        $iv = "hywebpg5";
        if ($key == null)
            return '285212698';
        $cipherText = mcrypt_cbc(MCRYPT_3DES, $key, $msg, MCRYPT_ENCRYPT, $iv);
        return strtoupper(bin2hex($cipherText));
    }

    private function utf8_2_big5($utf8_str)
    {
        $i = 0;
        $len = strlen($utf8_str);
        $big5_str = "";
        for ($i = 0; $i < $len; $i++) {
            $sbit = ord(substr($utf8_str, $i, 1));
            if ($sbit < 128) {
                $big5_str .= substr($utf8_str, $i, 1);
            } else if ($sbit > 191 && $sbit < 224) {
                $new_word = iconv("UTF-8", "Big5", substr($utf8_str, $i, 2));
                $big5_str .= ($new_word == "") ? "¡½" : $new_word;
                $i++;
            } else if ($sbit > 223 && $sbit < 240) {
                $new_word = iconv("UTF-8", "Big5", substr($utf8_str, $i, 3));
                $big5_str .= ($new_word == "") ? "¡½" : $new_word;
                $i += 2;
            } else if ($sbit > 239 && $sbit < 248) {
                $new_word = iconv("UTF-8", "Big5", substr($utf8_str, $i, 4));
                $big5_str .= ($new_word == "") ? "¡½" : $new_word;
                $i += 3;
            }
        }
        return $big5_str;
    }

    private function pairstr2Arr($str, $separator, $delim)
    {
        $elems = explode($delim, $str);
        foreach ($elems as $elem => $val) {
            $val = trim($val);
            $nameVal[] = explode($separator, $val);
            $arr[trim(strtolower($nameVal[$elem][0]))] = trim($nameVal[$elem][1]);
        }
        return $arr;
    }

    private function genencrypt($encStr, $Key, $debug)
    {
        $URLEnc = DESMAC($encStr, $Key, $debug);
        return $URLEnc;
    }

    private function gendecrypt($EncRes, $Key, $debug)
    {
        $ParameterArray = array($EncRes, $Key);
        $CombineStr = "|" . $EncRes . "|" . $Key . "|";
        if ($debug == 1) {
            echo "debug=$$debug \n";
            echo "CombineStr is : $CombineStr \n";
            while (list($var, $val) = each($ParameterArray)) {
                echo "$var is $val\n";
            }
        }
        $CMP = "000";
        if ($CMP == "000") {
            $iv = "hywebpg5";
            $DesText = mcrypt_cbc(MCRYPT_3DES, $Key, hex2bin($EncRes), MCRYPT_DECRYPT, $iv);
            $DesText = trim($DesText, "\x00..\x08");
            $ParseArray = pairstr2Arr($DesText, "=", "&");
            return $ParseArray;
        } else {
            return "0x" . dechex($CMP);
        }
    }

    private function genmpidecrypt($EncRes, $Key, $debug)
    {
        $ParameterArray = array($EncRes, $Key);
        $CombineStr = "|" . $EncRes . "|" . $Key . "|";
        if ($debug == 1) {
            echo "debug=$$debug \n";
            echo "CombineStr is : $CombineStr \n";
            while (list($var, $val) = each($ParameterArray)) {
                echo "$var is $val\n";
            }
        }
        $CMP = "000";
        if ($CMP == "000") {
            $iv = "hywebpg5";
            $DesText = mcrypt_cbc(MCRYPT_3DES, $Key, hex2bin($EncRes), MCRYPT_DECRYPT, $iv);
            $DesText = trim($DesText, "\x00..\x08");
            $ParseArray = pairstr2Arr($DesText, "=", "&");
            return $ParseArray;
        } else {
            return "0x" . dechex($CMP);
        }
    }

    private function get_auth_atmurlenc($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $storeName, $AuthResURL, $billShortDesc, $WebATMAcct, $note, $InMac, $debug)
    {
        $encStr = "MerchantID=" . $MerchantID . "&" . "TerminalID=" . $TerminalID . "&" .
            "lidm=" . $lidm . "&" . "purchAmt=" . $purchAmt . "&" .
            "txType=" . $txType . "&" . "InMac=" . $InMac . "&" .
            "AuthResURL=" . $AuthResURL . "&" . "WebATMAcct=" . $WebATMAcct . "&" .
            "billShortDesc=" . $billShortDesc . "&" . "note=" . $note . "&" .
            "storeName=" . $storeName;
        $URLEnc = DESMAC($encStr, $Key, $debug);
        return $URLEnc;
    }

    private function get_auth_dbcurlenc($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $storeName, $AuthResURL, $billShortDesc, $note, $InMac, $debug)
    {
        $encStr = "MerchantID=" . $MerchantID . "&" . "TerminalID=" . $TerminalID . "&" .
            "lidm=" . $lidm . "&" . "purchAmt=" . $purchAmt . "&" .
            "txType=" . $txType . "&" . "InMac=" . $InMac . "&" .
            "AuthResURL=" . $AuthResURL . "&" . "billShortDesc=" . $billShortDesc . "&" .
            "note=" . $note . "&" . "storeName=" . $storeName;
        $URLEnc = DESMAC($encStr, $Key, $debug);
        return $URLEnc;
    }


}
