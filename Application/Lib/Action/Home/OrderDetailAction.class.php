<?php
/**
 * Created by PhpStorm.
 * User: b5m
 * Date: 2017/4/19
 * Time: 13:31
 */


class OrderDetailAction extends BaseAction{

    //发票类型与oa对应关系
    public $invoice_relation = [
        "N001350100" => "26",
        "N001350200" => "27",
        "N001350300" => "29",
        "N001350400" => "521",
        "N001350500" => "722",
    ];

    //货币类型与oa对应关系
    public $currency_relation = [
        "N000590100" => "2",
        "N000590200" => "1",
        "N000590300" => "0",
        "N000590400" => "4",
        "N000590500" => "6",
        "N000590600" => "3",
        "N000590700" => "5",
    ];

    /**
     * 添加采购订单
     * @param bool $is_review
     */
    public function order_add($is_review = false){
        $order_detail = M('order_detail','tb_pur_'); //实例化采购信息表
        if($this->isPost()){
            $add_data = $_REQUEST;
            if($add_data['contract_number']) {
                $add_data['has_contract'] = 1;
            }else {
                $add_data['has_contract'] = 0;
            }
            if($add_data['procurement_number']) {
                if($order_detail->where(['procurement_number'=>$add_data['procurement_number']])->find()) {
                    $this->error('PO单号或采购单号' . $add_data['procurement_number'] . '已经创建过订单，请勿重复创建', U('orderDetail/order_list', 2));
                }
            }else {
                $this->error('请填写PO单号或采购单号');
            }
            if ($_FILES['attachment']['name'] || $_FILES['approve_credential']['name']) {
                // 图片上传
                $fd = new FileUploadModel();
                $ret = $fd->uploadFileArr();
                if($ret){
                    foreach($ret as $v) {
                        if($v['key'] == 'attachment') {
                            $add_data['attachment'][] = $v['savename'];
                        }else {
                            $add_data['approve_credential'] = $v['savename'];
                        }
                    }
                    $add_data['attachment'] = implode(',',$add_data['attachment']);
                }else {
                    $this->error("修改失败：上传文件失败,".$fd->error,U('OrderDetail/order_list'),2);
                }
            }
            //print_r($add_data);die;
            if($add_data['payment_info']) {
                $add_data['payment_info'] = json_encode($add_data['payment_info']);
            }else {
                $purchase_info['payment_info'] = '';
            }
            $add_data['create_time'] = date("Y-m-d H:i:s",time());
            $add_data['money_total_rmb'] = I('post.money_total_rmb');;
            $model = new Model();
            $model->startTrans();
            $order_id                   = M('order_detail','tb_pur_')->add($add_data); //采购信息
            $sell_id                    = M('sell_information','tb_pur_')->add($add_data); //销售信息
            $drawback_id                = M('drawback_information','tb_pur_')->add($add_data); //退税信息
            $predict_id                 = M('predict_profit','tb_pur_')->add($add_data);
            //下面将上面的每张表汇总到关联的订单表中
            $prepared_by                = I('post.prepared_by');
            $prepared_time              = date("Y-m-d H:i:s",time());
            $add_data['prepared_time']  = $prepared_time; //加入制单时间
            $creation_time              = date("Y-m-d H:i:s",time()); //该时间是向订单关联总表中插入使用的，是为了方便搜索使用
            $creation_times             = date("Y-m-d H:i:s",time()); //该时间是向采购应付表中插入使用的，是为了方便搜索使用
            $sou_time                   = date("Y-m-d",time()); //搜索的时间，时间与制单时间保持一致，格式为年月日
            $add_data['sou_time']       = $sou_time;
            $add_data['creation_times'] = $creation_times;
            $receipt_time               = substr("$prepared_time",0,10); //订单编号的时间
            $receipt_time               = explode('-',$receipt_time); //将时间格式进行转换
            $receipt_time               = implode('',$receipt_time); //将时间格式进行转换
            $new_order                  = M('payable','tb_pur_')->order("prepared_time desc")->find(); //查出最新的一条关联订单
            $receipt_number             = $new_order['receipt_number']; //查出的最新的订单编号
            $serial_number              = substr("$receipt_number",-4)+1; //流水号
            $serial_number              = sprintf("%04d", $serial_number); //不够四位数用0自动补全
            $receipt_number             = 'YF'.$receipt_time.$serial_number;
            $number_total               = I('post.number_total');//接收商品数量的合计
            $money_total                = I('post.money_total');//接收商品金额的合计
            $money_total_rmb            = I('post.money_total_rmb');//接收合计的外币金额换算成人民币的金额
            $show_total_rate            = I('post.show_total_rate'); //用于保存 1USD = 6.8933RMB格式的汇率到数据库，然后在修改的时候显示
            $real_total_rate            = I('post.real_total_rate'); //用于保存当天的真实汇率
            $curType                    = $add_data['curType'];  //接收商品的币种
            $add_data['receipt_number'] = $receipt_number;
            //$payable_id                 = M('payable','tb_pur_')->add($add_data);
            $collect_order              = compact("order_id","sell_id","curType","sou_time","drawback_id","predict_id","prepared_by","prepared_time","create_time","receipt_number","number_total","money_total","payable_id","creation_time","money_total_rmb","show_total_rate","real_total_rate"); //所有订单ID打包汇总插入关联的订单表中
            $collect_order['last_update_time']  = date('Y-m-d H:i:s');
            $collect_order['last_update_user']  = $_SESSION['m_loginname'];
            $collect_order['prepared_by']       = $_SESSION['m_loginname'];
            $relevance_id                       = M('relevance_order','tb_pur_')->add($collect_order); //将相关信息向订单关联总表中插入

            //将商品信息接收入库
            $search_information     = I("post.search_information");
            $sku_information        = I("post.sku_information");
            $goods_name             = I("post.goods_name");
            $goods_attribute        = I("post.goods_attribute");
            $unit_price             = I("post.unit_price");
            $goods_number           = I("post.goods_number");
            $goods_money            = I("post.goods_money");
            $num                    = count($search_information);
            for($i=0;$i<$num;$i++){
                $goods['search_information']    = $search_information[$i];
                $goods['sku_information']       = $sku_information[$i];
                $goods['goods_name']            = $goods_name[$i];
                $goods['goods_attribute']       = $goods_attribute[$i];
                $goods['unit_price']            = $unit_price[$i];
                $goods['goods_number']          = $goods_number[$i];
                $goods['goods_money']           = $goods_money[$i];
                $goods['create_time']           = date("Y-m-d H:i:s",time());  //形成一一对应关系所以进行循环
                $goods['relevance_id']          = $relevance_id;
                $goods_all[]                    = $goods;
            }
            $goods_add_res = M('goods_information','tb_pur_')->addAll($goods_all);

            if($order_id&&$sell_id&&$drawback_id&&$predict_id&&$relevance_id&&$goods_add_res){
                $model->commit();
                if($is_review) {
                    return $relevance_id;
                }else {
                    $this->ajaxReturn($relevance_id,'保存成功',1);
                }
            }else{
                $model->rollback();
                if($is_review) {
                    return false;
                }else {
                    $this->ajaxReturn(0,'保存失败',0);
                }
            }
        }else{
            $cmn_cd             = new TbMsCmnCdModel(); // 实例化数据字典表
            $cmn_cd_info        = $cmn_cd->where("CD_NM='采购团队'")->select();
            $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select();
            $business_type      = $cmn_cd->where("CD_NM='业务类型'")->order('SORT_NO ASC')->select();
            $delivery_type      = $cmn_cd->where("CD_NM='交货方式'")->select();
            $tax_rate           = $cmn_cd->where("CD_NM='采购税率'")->select();
            $invoice_type       = $cmn_cd->where("CD_NM='发票类型'")->select();
            $our_company        = $cmn_cd->where("CD_NM='我方公司'")->select();
            $currency           = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
            $sell_team          = $cmn_cd->getCd($cmn_cd::$sell_team_cd_pre); //币种查询
            $sell_mode          = $cmn_cd->getCd($cmn_cd::$sell_mode_cd_pre); //币种查询
            $admin              = M('admin', 'bbm_'); // 实例化用户表
            $user_id            = $_SESSION['user_id'];
            $admin_info         = $admin->where("M_ID='$user_id'")->select();
            $this->assign('invoice_relation',$this->invoice_relation);
            $this->assign('currency_relation',$this->currency_relation);
            $this->assign('invoice_type',$invoice_type);
            $this->assign('cmn_cd_info',$cmn_cd_info);
            $this->assign('cmn_cd_info',$cmn_cd_info);
            $this->assign('business_direction',$business_direction);
            $this->assign('business_type',$business_type);
            $this->assign('delivery_type',$delivery_type);
            $this->assign('tax_rate',$tax_rate);
            $this->assign('our_company',$our_company);
            $this->assign('currency',$currency);
            $this->assign('invoice_type',$invoice_type);
            $this->assign('admin_info',$admin_info);
            $this->assign('sell_team',$sell_team);
            $this->assign('sell_mode',$sell_mode);
            $this->payment_type     = TbPurOrderDetailModel::$payment_type;
            $this->payment_period   = TbPurOrderDetailModel::$payment_period;
            $this->payment_day_type = TbPurOrderDetailModel::$payment_day_type;
            $this->payment_node     = $cmn_cd->getCdY($cmn_cd::$payment_node_cd_pre);
            $this->payment_days     = $cmn_cd->getCdY($cmn_cd::$payment_days_cd_pre);
            $this->payment_percent  = $cmn_cd->getCdY($cmn_cd::$payment_percent_cd_pre);
            //$this->assign('supper_info',$supper_info);
            $this->display('order_update');
        }
    }


    /**
     * 调取汇率的接口
     */
    public function exchange_rate(){
        $src_currency = I('get.currency'); //获取原始币种
        $rate = exchangeRate($src_currency);
        if($rate) {
            $this->success(['rate'=>$rate]);
        }else {
            $this->error(['rate'=>$rate]);
        }

    }


    /**
     * sku信息查询
     */
    public function order_add_ajax(){
        $sku = I('post.sku');
        $now_number = I('post.now_number'); //序号
        $url = U('Stock/Searchguds','','',false,true);
        $res = json_decode(curl_request($url,['GSKU'=>$sku]),true);
        if($res['status'] == 0) {
            echo 1;
            exit;
        }
        /*
        $guds_opt = M('guds_opt','tb_ms_'); //实例化商品属性表
        $guds = M('guds','tb_ms_'); //实例化商品表
        $guds_img = M('guds_img','tb_ms_'); //实例化商品图片表
        $sku_info = $guds_opt->where("GUDS_OPT_ID='$sku'")->find();
        if($sku_info==''){
            echo 1;   //如果sku查询为空，就返回1，并且程序停止执行
            exit; }
        $goods_id = $sku_info['GUDS_ID'];
        $attr_code = explode(';',$sku_info['GUDS_OPT_VAL_MPNG']);//商品选择价格图示

        foreach($attr_code as $key=>$v){
            //$attr01[] = explode(':',$v);
            $val_str = '';
            $o = explode(':', $v);
            $model = M('ms_opt', 'tb_');
            $opt_val_str = $model->join('left join tb_ms_opt_val on tb_ms_opt_val.OPT_ID = tb_ms_opt.OPT_ID')->where('tb_ms_opt.OPT_ID = ' . $o[0] . ' and tb_ms_opt_val.OPT_VAL_ID = ' . $o[1])->field('tb_ms_opt.OPT_CNS_NM,tb_ms_opt_val.OPT_VAL_CNS_NM,tb_ms_opt.OPT_ID')->find();
            if (empty($opt_val_str)) {
                $val_str = L('标配');
                $attr[$key] = $val_str;
            } elseif ($opt_val_str['OPT_ID'] == '8000') {
                $val_str = L('标配');
                $attr[$key] = $val_str;
            } elseif ($opt_val_str['OPT_ID'] != '8000') {
                $val_str = $opt_val_str['OPT_CNS_NM'] . ':' . $opt_val_str['OPT_VAL_CNS_NM'] . ' ';
                $attr[$key] = $val_str;

            }

        }
        $attr = implode(' ',$attr);
        $goods_info = $guds->where("GUDS_ID='$goods_id'")->find();
        $img_info = $guds_img->where("GUDS_ID='$goods_id'")->find();
        $guds_img = $img_info['GUDS_IMG_CDN_ADDR'];
        $goods_name =  $goods_info['GUDS_CNS_NM'];
        */
        $guds['goods_name'] = $res['info'][0]['Guds']['GUDS_NM'];
        $guds['sku']        = $res['info'][0]['GUDS_OPT_ID'];
        $guds['search']     = $sku;
        $guds['guds_img']   = $res['info'][0]['Img'];
        $guds['val_str']    = $res['info']['opt_val'][0]['val'];
        $guds['now_number'] = $now_number;
        $standing           = M('standing','tb_wms_')
            ->field('sum(sale) as sale_num,sum(on_way) on_way_num')
            ->where(['SKU_ID'=>$sku])
            ->group('SKU_ID')
            ->find();
        $guds['sale_num']   = $standing['sale_num'];
        $guds['on_way_num'] = $standing['on_way_num'];
        $this->guds         = [$guds];
        $cmn_cd             = M('cmn_cd','tb_ms_');
        $this->currency     = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
        $this->display();


    }


    /*  //提交的时候对sku验证
      public function commitSku(){
          $sku = I('post.sku');
          $sku = explode(',',$sku);
          $sku_len = count($sku);
          $guds_opt = M('guds_opt','tb_ms_'); //实例化商品属性表


          for($i=0;$i<$sku_len;$i++){
              $sku_info = $guds_opt->where("GUDS_OPT_ID='$sku[$i]'")->find();
              if($sku_info==''){
                  echo 1;   //如果sku查询为空，就返回1，并且程序停止执行
                  exit;
              }

          }


      }*/


    /**
     * 交易编码的生成
     */
    public function dealNumber(){
        $order_detail = M('order_detail','tb_pur_'); //实例化采购信息表
        $procurement_date = I('post.procurement_date'); //日期
        $business_direction = I('post.business_direction'); //业务方向
        $supplie_id = I('post.supplie_id'); //销售的客户
        $supplies_id = I('post.supplies_id'); //供应商
        $procurement_date = explode('-',$procurement_date);
        $procurement_date = implode('',$procurement_date);
        $procurement_date = substr("$procurement_date",2); //最后截取出来的日期
        $new_order = $order_detail->order("create_time desc")->find(); //查出最新的一条单据详情
        $deal_number = $new_order['deal_number'];
        $serial_number = substr("$deal_number",-2)+1;
        $serial_number = sprintf("%02d", $serial_number); //不够二位数用0自动补全
        if($business_direction!="请选择业务方向") {
            $order_number = $procurement_date . '_' . $business_direction . '_' . $supplie_id . '_' . $supplies_id . '_' . $serial_number;

        }

        echo $order_number;

    }


    /**
     * 获取查询条件
     * @param $params
     * @return mixed
     */
    public function getConditions($params)
    {
        !empty($params['ship_status']) ? $conditions['ship_status'] = $params['ship_status'] : '';
        !empty($params['procurement_number']) ? $conditions['tb_pur_order_detail.procurement_number'] = array('like','%'.$params['procurement_number'].'%') : '';
        !empty($params['sell_number']) ? $conditions['tb_pur_sell_information.sell_number'] = array('like','%'.$params['sell_number'].'%') : '';
        !empty($params['supplier_id']) ? $conditions['supplier_id'] = ['like','%'.$params['supplier_id'].'%'] : '';
        !empty($params['supp_id']) ? $conditions['supp_id'] = ['like','%'.$params['supp_id'].'%'] : '';
        !empty($params['business_direction']) ? $conditions['tb_pur_order_detail.business_direction'] = $params['business_direction'] : '';
        !empty($params['business_type']) ? $conditions['tb_pur_order_detail.business_type'] = $params['business_type'] : '';
        !empty($params['prepared_by']) ? $conditions['tb_pur_relevance_order.prepared_by'] = array('like', '%' . $params['prepared_by'] . '%') : '';
        !empty($params['payment_company']) ? $conditions['payment_company'] = $params['payment_company'] : '';
        !empty($params['goods_name']) ? $conditions['tb_pur_goods_information.goods_name'] =array('like','%'. $params['goods_name'].'%') : '';
        switch ($params['time_type']) {
            case 0 :
                break;
            case 1:
                break;
            case 2 :
                break;
            case 3 :
                break;
        }
        !empty($params['start_time']) ? $conditions['sou_time'] = array('EGT',$params['start_time']) : '';
        !empty($params['end_time']) ? $conditions['tb_pur_relevance_order.sou_time'] = array('ELT',$params['end_time']) : '';
        return $conditions;
    }

    /**
     * 订单列表的信息展示
     */
    public function order_list(){
        import('ORG.Util.Page');
        $cmn_cd = M('cmn_cd','tb_ms_'); // 实例化数据字典表
        $goods_information = M('goods_information','tb_pur_'); //实例化商品信息表
        $relevance_order = M('relevance_order','tb_pur_'); //实例化订单关联总表
        if(I('get.search')){
            //$sou_info = I('post.'); //接收搜索信息
            $params['ship_status'] = $ship_status = I("get.ship_status"); //发货状态
            $params['procurement_number'] = $procurement_number = I("get.procurement_number"); //接收采购单号
            $params['sell_number'] =$sell_number = I("get.sell_number");
            $params['supplier_id'] = $supplier_id = I("get.supplier_id");
            $params['supp_id'] = $supp_id = I("get.supp_id");
            $params['business_direction'] =$business_direction = I("get.business_direction"); //接收业务方向
            $params['business_type'] =$business_type = I("get.business_type"); //接收业务类型
            $params['prepared_by'] =$prepared_by = I("get.prepared_by"); //接收采购员
            $params['payment_company'] =$payment_company = I("get.payment_company");
            $params['goods_name'] =$goods_name = I("get.goods_name"); //接收商品名称
            $params['start_time'] =$start_time = I('get.start_time'); //起始时间
            $params['end_time'] =$end_time = I('get.end_time'); //结束时间
            $where=$this->getConditions($params);
            $purchase_sql = $relevance_order
                ->field('tb_pur_order_detail.order_id')
                ->join("left join tb_pur_order_detail on tb_pur_relevance_order.order_id = tb_pur_order_detail.order_id ")
                ->join("left join tb_pur_sell_information on tb_pur_relevance_order.sell_id = tb_pur_sell_information.sell_id ")
                ->join("left join tb_pur_drawback_information on tb_pur_drawback_information.drawback_id = tb_pur_relevance_order.drawback_id ")
                ->join("left join tb_pur_predict_profit on tb_pur_predict_profit.predict_id = tb_pur_relevance_order.predict_id ")
                ->join("left join tb_pur_goods_information on tb_pur_goods_information.relevance_id = tb_pur_relevance_order.relevance_id")
                ->group('tb_pur_goods_information.relevance_id')
                ->where($where)
                ->order("prepared_time desc")
                ->buildSql();

            $counts = M()->table($purchase_sql.' a')->count();
            $Page = new Page($counts, 20); //每页显示3条数据
            $show = $Page->show(); //分页显示

            $purchase_info = $relevance_order
                ->join("left join tb_pur_order_detail on tb_pur_relevance_order.order_id = tb_pur_order_detail.order_id ")
                ->join("left join tb_pur_sell_information on tb_pur_relevance_order.sell_id = tb_pur_sell_information.sell_id ")
                ->join("left join tb_pur_drawback_information on tb_pur_drawback_information.drawback_id = tb_pur_relevance_order.drawback_id ")
                ->join("left join tb_pur_predict_profit on tb_pur_predict_profit.predict_id = tb_pur_relevance_order.predict_id ")
                ->join("left join tb_pur_goods_information on tb_pur_goods_information.relevance_id = tb_pur_relevance_order.relevance_id")
                ->group('tb_pur_goods_information.relevance_id')
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("prepared_time desc")
                ->select();

            foreach($purchase_info as $key=>$v){
                $relevance_id = $v['relevance_id'];
                $goods_info = $goods_information->where("relevance_id=$relevance_id")->select();
                $purchase_info[$key]['goods_information'] = $goods_info;
            }
            $business_directions = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_types = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
            $purchase_team = $cmn_cd->where("CD_NM='采购团队'")->select();    //查出业务类型信息
            $order_status_cd = (new TbMsCmnCdModel())->getPurchaseOrderStatuskey();
            $this->assign('params',$params);
            $this->assign('show', $show);
            $this->assign('firstRow',$Page->firstRow);
            $this->assign('ship_status',$ship_status);
            $this->assign('purchase_info',$purchase_info);
            $this->assign('business_directions',$business_directions);
            $this->assign('business_types',$business_types);
            $this->assign('procurement_number',$procurement_number);
            $this->assign('goods_name',$goods_name);
            $this->assign('sell_number',$sell_number);
            $this->assign('prepared_by',$prepared_by);
            $this->assign('start_time',$start_time);
            $this->assign('end_time',$end_time);
            $this->assign('business_direction',$business_direction);
            $this->assign('business_type',$business_type);
            $this->assign('purchase_team',$purchase_team);
            $this->assign('order_status_cd',$order_status_cd);
            $this->display();


        }else {
            $counts = $relevance_order->count();
            $Page = new Page($counts, 20); //每页显示10条数据
            $show = $Page->show(); //分页显示
            $purchase_info = $relevance_order
                ->join("ro join tb_pur_order_detail od on ro.order_id=od.order_id ")
                ->join("tb_pur_sell_information si on si.sell_id=ro.sell_id")
                ->join("tb_pur_drawback_information di on di.drawback_id=ro.drawback_id")
                ->join("tb_pur_predict_profit pp on pp.predict_id=ro.predict_id")
                ->limit($Page->firstRow . ',' . $Page->listRows)->order("prepared_time desc")->select();
            foreach ($purchase_info as $key => $v) {
                $relevance_id = $v['relevance_id'];
                $goods_info = $goods_information->where("relevance_id=$relevance_id")->select();
                $purchase_info[$key]['goods_information'] = $goods_info;
            }

            //print_r($purchase_info);die;
            $business_directions = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_types = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
            $purchase_team = $cmn_cd->where("CD_NM='采购团队'")->select();    //查出业务类型信息
            $order_status_cd = (new TbMsCmnCdModel())->getPurchaseOrderStatuskey();
            //分配变量到模板
            $this->assign('show', $show);
            $this->assign('firstRow',$Page->firstRow);
            $this->assign('purchase_info', $purchase_info);
            $this->assign('business_directions', $business_directions);
            $this->assign('business_types', $business_types);
            $this->assign('purchase_team', $purchase_team);
            $this->assign('order_status_cd',$order_status_cd);
            $this->display();
        }

    }



    /**
     * 编辑采购订单
     * @param bool $is_review
     */
    public function order_update($is_review = false){
        $order_detail = M('order_detail','tb_pur_'); //实例化采购信息表
        $sell_information = M('sell_information','tb_pur_'); //实例化销售信息表
        $drawback_information = M('drawback_information','tb_pur_'); //实例化退税信息表
        $goods_information = M('goods_information','tb_pur_'); //实例化商品信息表
        $predict_profit = M('predict_profit','tb_pur_');//实例化预计利润表
        $relevance_order = M('relevance_order','tb_pur_'); //实例化订单关联总表
        if($this->isPost()){
            $purchase_info = $_REQUEST;
            //$add_data = $_REQUEST;
            if($purchase_info['contract_number']) {
                $purchase_info['has_contract'] = 1;
            }else {
                $purchase_info['has_contract'] = 0;
            }
            $relevance_id = I('post.relevance_id');
            $purchase_info['money_total_rmb'] = I('post.money_total_rmb');
            $purchase_info['create_time'] = date("Y-m-d H:i:s",time());
            $relevance_info = $relevance_order->where("relevance_id='$relevance_id'")->find();
            $order_id = $relevance_info['order_id'];
            if($purchase_info['procurement_number']) {
                $res = $order_detail->where(['procurement_number'=>$purchase_info['procurement_number']])->find();
                if($res && $res['order_id'] != $order_id) {
                    $this->error('PO单号或采购单号'.$purchase_info['procurement_number'].'已经创建过订单，请勿重复创建',U('orderDetail/order_list',2));
                }
            }else {
                $this->error('请填写PO单号或采购单号');
            }
            if ($_FILES['attachment']['name'] || $_FILES['approve_credential']['name']) {
                // 图片上传
                $fd = new FileUploadModel();
                $ret = $fd->uploadFileArr();
                if($ret){
                    foreach($ret as $v) {
                        if($v['key'] == 'attachment') {
                            $purchase_info['attachment'][] = $v['savename'];
                        }else {
                            $purchase_info['approve_credential'] = $v['savename'];
                        }
                    }
                    $purchase_info['attachment'] = implode(',',$purchase_info['attachment']);
                }else {
                    $this->error("修改失败：上传文件失败,".$fd->error,U('OrderDetail/order_list'),2);
                }
            }else {
                $purchase_info['attachment'] = implode(',',$purchase_info['attachment']);
            }
            if($purchase_info['payment_info']) {
                $purchase_info['payment_info'] = json_encode($purchase_info['payment_info']);
            }else {
                $purchase_info['payment_info'] = '';
            }
            $sell_id = $relevance_info['sell_id'];
            $drawback_id = $relevance_info['drawback_id'];
            $predict_id = $relevance_info['predict_id'];
            $relevance_id = $relevance_info['relevance_id'];
            $payable_id = $relevance_info['payable_id'];
            $prepared_by = I('post.prepared_by');
            $creation_time = date("Y-m-d H:i:s",time());
            $creation_times = date("Y-m-d H:i:s",time()); //该时间是向采购应付表中插入使用的，是为了方便搜索使用
            $purchase_info['creation_times'] =  $creation_times;
            $prepared_time =  I('post.prepared_time'); //订单时间
            $number_total = I('post.number_total');//接收商品数量的合计
            $money_total = I('post.money_total');//接收商品金额的合计
            $money_total_rmb = I('post.money_total_rmb');//接收合计的外币金额换算成人民币的金额
            $show_total_rate = I('post.show_total_rate'); //用于保存 1USD = 6.8933RMB格式的汇率到数据库，然后在修改的时候显示
            $real_total_rate = I('post.real_total_rate'); //用于保存当天的真实汇率
            $curType = I("post.curType");  //接收商品的币种
            $relevance_info = compact("prepared_by","prepared_time","number_total","money_total","money_total_rmb","show_total_rate","real_total_rate","curType","creation_time"); //制单人和制单时间需要修改的信息
            $relevance_info['last_update_time'] = date('Y-m-d H:i:s');
            $relevance_info['last_update_user'] = $_SESSION['m_loginname'];
            $order_info = $order_detail->where("order_id='$order_id'")->save($purchase_info); //采购订单信息
            $sell_info = $sell_information->where("sell_id='$sell_id'")->save($purchase_info); //销售信息
            $drawback_info = $drawback_information->where("drawback_id='$drawback_id'")->save($purchase_info);  //退税信息
            $predict_info = $predict_profit->where("predict_id='$predict_id'")->save($purchase_info); //预计利润信息
            $relevance_info = $relevance_order->where("relevance_id='$relevance_id'")->save($relevance_info); //制单人和制单时间的修改
            //商品信息的修改
            $info_id = I("post.information_id");
            $sku_information = I("post.sku_information");
            $search_information = I("post.search_information");
            $goods_name = I("post.goods_name");
            $goods_attribute = I("post.goods_attribute");
            //$image = I("post.image");
            $unit_price = I("post.unit_price");
            // $curType = I("post.curType");
            $goods_number = I("post.goods_number");
            $goods_money = I("post.goods_money");
            $relevance_info = $relevance_order->order("prepared_time desc")->find();///查出最新一条数据

            unset($relevance_id);
            unset($payable_id);
            for($i=0;$i<count($search_information);$i++){
                $relevance_id[] = I('post.relevance_id');//形成一一对应关系所以进行循环
            }

            $data =  compact("info_id","search_information","sku_information","goods_name","goods_attribute","unit_price","goods_number","goods_money","relevance_id"
            );

            foreach($data as $key=>$v){

                foreach($v as  $key1=>$v1 ){
                    $goods[$key1][$key]=$v1;
                }
            }


            foreach($goods as $key=>$v){
                $info_id = $v['info_id'];
                if($info_id==''){
                    unset($info_id);
                    $v['create_time'] = date("Y-m-d H:i:s",time());
                    $goods_info[] = $goods_information->add($v);
                }else{
                    $goods_count = count($v);
                    if($goods_count==1){
                        $goods_info[] = $goods_information->where("information_id='$info_id'")->delete();
                    }else{
                        $goods_info[] = $goods_information->where("information_id=$info_id")->save($v);
                    }

                }

            }



            if($order_info>=0&&$sell_info>=0&&$drawback_info>=0&&$predict_info>=0&&$relevance_info>=0&&$goods_info>=0){
                if($is_review) {
                    return true;
                }else {
                    $this->success("修改成功",U('OrderDetail/order_list'),1);
                }
            }else{
                if($is_review){
                    return false;
                }else {
                    $this->error("修改失败",U('OrderDetail/order_list'),1);
                }
            }

        }else{
            $relevance_id       = I('get.id'); //接收订单的ID
            $relevance_info     = $relevance_order->where("relevance_id='$relevance_id'")->find();
            $order_id           = $relevance_info['order_id'];
            $sell_id            = $relevance_info['sell_id'];
            $drawback_id        = $relevance_info['drawback_id'];
            $predict_id         = $relevance_info['predict_id'];
            $relevance_id       = $relevance_info['relevance_id'];
            $order_info         = $order_detail->where("order_id='$order_id'")->find(); //采购订单信息
            $order_info['payment_info'] = json_decode($order_info['payment_info'],true);
            $sell_info          = $sell_information->where("sell_id='$sell_id'")->find(); //销售信息
            $drawback_info      = $drawback_information->where("drawback_id='$drawback_id'")->find();  //退税信息
            $predict_info       = $predict_profit->where("predict_id='$predict_id'")->find(); //预计利润信息
            $profit_margin      = ($predict_info['profit_margin']*100)."%";
            $total_profit_margin = ($predict_info['total_profit_margin']*100)."%";
            $retained_profits   = ($predict_info['retained_profits']*100)."%";
            $goods_info         = $goods_information
                ->field('t.*,sum(a.sale) as sale_num,sum(a.on_way) on_way_num')
                ->alias('t')
                ->join('left join tb_wms_standing a on a.SKU_ID=t.sku_information')
                ->group('t.sku_information')
                ->where(['relevance_id'=>$relevance_id,'sku_information'=>['exp','is not null']])
                ->select();
            $contracts          = M('contract','tb_crm_')->field('SP_BANK_CD,BANK_ACCOUNT,SWIFT_CODE,CON_NO,CON_NAME,SP_CHARTER_NO')->where(['SP_CHARTER_NO'=>$order_info['sp_charter_no']])->order('create_time desc')->select();
            $cmn_cd             = new TbMsCmnCdModel();
            $cmn_cd_info        = $cmn_cd->where("CD_NM='采购团队'")->select();
            $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select();
            $business_type      = $cmn_cd->where("CD_NM='业务类型'")->order('SORT_NO ASC')->select();
            $delivery_type      = $cmn_cd->where("CD_NM='交货方式'")->select();
            $tax_rate           = $cmn_cd->where("CD_NM='采购税率'")->select();
            $invoice_type       = $cmn_cd->where("CD_NM='发票类型'")->select();
            $our_company        = $cmn_cd->where("CD_NM='我方公司'")->select();
            $currency           = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
            $sell_team          = $cmn_cd->getCd($cmn_cd::$sell_team_cd_pre); //币种查询
            $sell_mode          = $cmn_cd->getCd($cmn_cd::$sell_mode_cd_pre); //币种查询
            $this->approve      = M('approve','tb_pur_')->where(['relevance_id'=>$relevance_id,'approve_status'=>['neq','N001320200']])->order('approve_time desc')->find(); //币种查询
            if($order_info['sp_charter_no']) {
                $this->risk_rating  = M('sp_supplier','tb_crm_')->where(['SP_CHARTER_NO'=>$order_info['sp_charter_no'],'DATA_MARKING'=>0])->getField('RISK_RATING');
                $this->has_cooperate = (new TbPurOrderDetailModel())->supplierHasCooperate($order_info['sp_charter_no']);
            }
            $this->assign('currency_relation',$this->currency_relation);
            $this->assign('cmn_cd_info',$cmn_cd_info);
            $this->assign('business_direction',$business_direction);
            $this->assign('business_type',$business_type);
            $this->assign('delivery_type',$delivery_type);
            $this->assign('order_info',$order_info);
            $this->assign('sell_info',$sell_info);
            $this->assign('drawback_info',$drawback_info);
            $this->assign('predict_info',$predict_info);
            $this->assign('goods_info',$goods_info);
            $this->assign('relevance_info',$relevance_info);
            $this->assign('relevance_id',$relevance_id);
            $this->assign('currency',$currency);
            $this->assign('tax_rate',$tax_rate);
            $this->assign('our_company',$our_company);
            $this->assign('invoice_type',$invoice_type);
            $this->assign('profit_margin',$profit_margin);
            $this->assign('total_profit_margin',$total_profit_margin);
            $this->assign('retained_profits',$retained_profits);
            $this->assign('contracts',$contracts);
            $this->assign('sell_team',$sell_team);
            $this->assign('sell_mode',$sell_mode);
            $this->payment_type     = TbPurOrderDetailModel::$payment_type;
            $this->payment_period   = TbPurOrderDetailModel::$payment_period;
            $this->payment_day_type = TbPurOrderDetailModel::$payment_day_type;
            $this->payment_node     = $cmn_cd->getCdY($cmn_cd::$payment_node_cd_pre);
            $this->payment_days     = $cmn_cd->getCdY($cmn_cd::$payment_days_cd_pre);
            $this->payment_percent  = $cmn_cd->getCdY($cmn_cd::$payment_percent_cd_pre);
            if(I('request.is_edit')) {
                $this->assign('is_edit',1);
            }else {
                $this->assign('is_edit',0);
            }
            $this->display();

        }


    }

    /**
     * 订单详情
     */
    public function order_detail() {
        $order_detail = M('order_detail','tb_pur_'); //实例化采购信息表
        $sell_information = M('sell_information','tb_pur_'); //实例化销售信息表
        $drawback_information = M('drawback_information','tb_pur_'); //实例化退税信息表
        $goods_information = M('goods_information','tb_pur_'); //实例化商品信息表
        $predict_profit = M('predict_profit','tb_pur_');//实例化预计利润表
        $relevance_order = M('relevance_order','tb_pur_'); //实例化订单关联总表
        $payable = M('payable','tb_pur_'); //实例化采购应付表
        $relevance_id       = I('get.id'); //接收订单的ID
        $relevance_info     = $relevance_order->where("relevance_id='$relevance_id'")->find();
        $order_id           = $relevance_info['order_id'];
        $sell_id            = $relevance_info['sell_id'];
        $drawback_id        = $relevance_info['drawback_id'];
        $predict_id         = $relevance_info['predict_id'];
        $relevance_id       = $relevance_info['relevance_id'];
        $order_info         = $order_detail->where("order_id='$order_id'")->find(); //采购订单信息
        $order_info['payment_info'] = json_decode($order_info['payment_info'],true);
        $sell_info          = $sell_information->where("sell_id='$sell_id'")->find(); //销售信息
        $drawback_info      = $drawback_information->where("drawback_id='$drawback_id'")->find();  //退税信息
        $predict_info       = $predict_profit->where("predict_id='$predict_id'")->find(); //预计利润信息
        $profit_margin      = ($predict_info['profit_margin']*100)."%";
        $total_profit_margin = ($predict_info['total_profit_margin']*100)."%";
        $retained_profits   = ($predict_info['retained_profits']*100)."%";
        $goods_info         = $goods_information
            ->field('tb_pur_goods_information.*,tb_ms_guds.DELIVERY_WAREHOUSE as warehouse_cd,tb_ms_cmn_cd.CD_VAL as warehouse')
            ->where(["relevance_id"=>$relevance_id])
            ->join('left join tb_ms_guds on tb_ms_guds.MAIN_GUDS_ID = left(tb_pur_goods_information.sku_information,8)')
            ->join('left join tb_ms_cmn_cd on tb_ms_cmn_cd.CD = tb_ms_guds.DELIVERY_WAREHOUSE')
            ->group('tb_pur_goods_information.information_id')
            ->select(); //商品信息
        $contracts          = M('contract','tb_crm_')->field('SP_BANK_CD,BANK_ACCOUNT,SWIFT_CODE,CON_NO,CON_NAME,SP_CHARTER_NO')->where(['SP_CHARTER_NO'=>$order_info['sp_charter_no']])->order('create_time desc')->select();
        $cmn_cd             = M('cmn_cd','tb_ms_');
        $cmn_cd_info        = $cmn_cd->where("CD_NM='采购团队'")->select();
        $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select();
        $business_type      = $cmn_cd->where("CD_NM='业务类型'")->order('SORT_NO ASC')->select();
        $delivery_type      = $cmn_cd->where("CD_NM='交货方式'")->select();
        $tax_rate           = $cmn_cd->where("CD_NM='采购税率'")->select();
        $invoice_type       = $cmn_cd->where("CD_NM='发票类型'")->select();
        $our_company        = $cmn_cd->where("CD_NM='我方公司'")->select();
        $currency           = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
        $this->approve      = M('approve','tb_pur_')->where(['relevance_id'=>$relevance_id,'approve_status'=>['neq','N001320200']])->order('approve_time desc')->find(); //币种查询
        if($order_info['sp_charter_no']) {
            $this->risk_rating  = M('sp_supplier','tb_crm_')->where(['SP_CHARTER_NO'=>$order_info['sp_charter_no'],'DATA_MARKING'=>0])->getField('RISK_RATING');
            $this->has_cooperate = (new TbPurOrderDetailModel())->supplierHasCooperate($order_info['sp_charter_no']);
        }
        $this->assign('currency_relation',$this->currency_relation);
        $this->assign('cmn_cd_info',$cmn_cd_info);
        $this->assign('business_direction',$business_direction);
        $this->assign('business_type',$business_type);
        $this->assign('delivery_type',$delivery_type);
        $this->assign('order_info',$order_info);
        $this->assign('sell_info',$sell_info);
        $this->assign('drawback_info',$drawback_info);
        $this->assign('predict_info',$predict_info);
        $this->assign('goods_info',$goods_info);
        $this->assign('relevance_info',$relevance_info);
        $this->assign('relevance_id',$relevance_id);
        $this->assign('currency',$currency);
        $this->assign('tax_rate',$tax_rate);
        $this->assign('our_company',$our_company);
        $this->assign('invoice_type',$invoice_type);
        $this->assign('profit_margin',$profit_margin);
        $this->assign('total_profit_margin',$total_profit_margin);
        $this->assign('retained_profits',$retained_profits);
        $this->assign('contracts',$contracts);
        if(I('request.is_edit')) {
            $this->assign('is_edit',1);
        }else {
            $this->assign('is_edit',0);
        }
        $this->display();
    }

    /**
     * 发货
     */
    public function ship() {
        if(IS_POST) {
            $add_data = $post = $_POST;
            unset($add_data['shipped_goods']);
            $shipped_goods = $post['shipped_goods'];
            $model = new Model();
            $model->startTrans();
            $relevance = $model->table('tb_pur_relevance_order')->where(['relevance_id'=>$post['relevance_id']])->find();
            if(!$relevance || $relevance['order_status'] != 'N001320300' || $relevance['ship_status'] == 2) {
                $model->rollback();
                $this->ajaxReturn(0,'需要发货的订单不存在或不符合发货条件',0);
            }
            $shipped_number = $relevance['shipped_number']+$post['shipping_number'];
            $relevance_update['shipped_number'] = $shipped_number;
            if($relevance['number_total'] == $shipped_number) {
                $relevance_update['ship_status'] = 2;
            }else {
                $relevance_update['ship_status'] = 1;
            }
            //更新订单发货数
            $res_rel = $model
                ->table('tb_pur_relevance_order')
                ->where(['relevance_id'=>$post['relevance_id']])
                ->save($relevance_update);
            if($res_rel === false) {
                $model->rollback();
                $this->ajaxReturn(0,'更新订单发货数失败',0);
            }
            $ship_m = new TbPurShipModel();

            //发货信息数据校验
            if($add_data['has_ship_info']) {
                if(!$add_data['bill_of_landing']) {
                    $model->rollback();
                    $this->ajaxReturn(0,'提单号(或其他有效单据号)必须',0);
                }
            }else {
                //没有提单号/物流单号时自动生成单号
                $pre_bill = $model->table('tb_pur_ship')->lock(true)->where(['bill_of_landing'=>['like','BL'.date('Ymd').'%']])->order('bill_of_landing desc')->getField('bill_of_landing');
                if($pre_bill) {
                    $num = substr($pre_bill,-3)+1;
                }else {
                    $num = 1;
                }
                $add_data['bill_of_landing'] = 'BL'.date('Ymd').substr(1000+$num,1);
            }
            if($add_data['has_ship_info'] && !$add_data['shipment_date']) {
                $model->rollback();
                $this->ajaxReturn(0,'发货日期必须',0);
            }
            if($add_data['shipment_date'] > date('Y-m-d')) {
                $model->rollback();
                $this->ajaxReturn(0,'发货日期不能晚于今天',0);
            }
            if($add_data['shipment_date'] > $add_data['arrival_date']) {
                $model->rollback();
                $this->ajaxReturn(0,'到货日期不得早于发货日期',0);
            }
            if(!$add_data['shipment_date']) $add_data['shipment_date'] = date('Y-m-d');
            if($add_data['extra_cost'] && !$add_data['extra_cost_currency']) {
                $model->rollback();
                $this->ajaxReturn(0,'请选择币种',0);
            }
            if(!$add_data['need_warehousing']) {
                $add_data['warehouse_status'] = 1;
            }
            //上传文件处理
            //上传文件必须选择类型
            $files_name = $_FILES['credential']['name'];
            $files_type = $post['credential_type'];
            $n          = 0;
            $file       = [];
            foreach ($files_name as $k => $v) {
                if($v) $n++;
                if($v && !$post['credential_type'][$k]) {
                    $this->error('请选择上传的文件类型');
                }
            }
            //有上传文件时才处理
            if($n > 0) {
                $fd     = new FileUploadModel();
                $res    = $fd->uploadFileArr();
                if(!$res){
                    $model->rollback();
                    $this->error("保存失败：上传文件失败,".$fd->error,U('OrderDetail/order_list'),2);
                }
                foreach ($files_name as $k => $v) {
                    if($v) {
                        $file_info['type'] = $files_type[$k];
                        $file_info['name'] = array_shift($res)['savename'];
                        $file[] = $file_info;
                    }
                }
            }
            $add_data['credential'] = json_encode($file);
            //保存发货信息
            if($model->table('tb_pur_ship')->validate($ship_m->_validate)->auto($ship_m->_auto)->create($add_data)) {
                $ship_id = $model->add();
                if(!$ship_id) {
                    $model->rollback();
                    $this->ajaxReturn(0,'保存发货信息失败',0);
                }
            }else {
                $model->rollback();
                $this->ajaxReturn(0,$model->getError(),0);
            }

            //保存入库编号
            $res = $model->table('tb_pur_ship')->where(['create_time'=>['lt',date('Y-m-d')]])->order('id desc')->find();
            $d_value = str_pad($ship_id-$res['id'],3,'0',STR_PAD_LEFT );
            $warehouse_id = date('Ymd').$d_value;
            $res = $model->table('tb_pur_ship')->where(['id'=>$ship_id])->save(['warehouse_id'=>$warehouse_id]);
            if(!$res) {
                $model->rollback();
                $this->ajaxReturn(0,'保存失败：生成入库编号失败',0);
            }


            //单个商品更新发货数,计算商品总金额
            foreach ($shipped_goods as $v) {
                $arr = json_decode($v,true);
                $goods = $model
                    ->table('tb_pur_goods_information')
                    ->where(['information_id'=>$arr['information_id']])
                    ->find();
                if(!$goods) {
                    $model->rollback();
                    $this->ajaxReturn(0,'采购订单中不存在该商品',0);
                }
                if($goods['goods_number']<$goods['shipped_number']+$arr['ship_number']) {
                    $model->rollback();
                    $this->ajaxReturn(0,'发货数超过了商品总数,请检查是否重复提交',0);
                }
                $goods_add['ship_id']               = $ship_id;
                $goods_add['information_id']        = $arr['information_id'];
                $goods_add['ship_number']           = $arr['ship_number'];
                $goods_add['number_info_ship']      = json_encode($arr['number_info']);
                $res_ship_goods = $model->table('tb_pur_ship_goods')->add($goods_add);
                if(!$res_ship_goods) {
                    $model->rollback();
                    $this->ajaxReturn(0,'创建发货商品失败',0);
                }
                $res_goods = $model->table('tb_pur_goods_information')->where(['information_id'=>$arr['information_id']])->setInc('shipped_number',$arr['ship_number']);
                if($res_goods === false) {
                    $model->rollback();
                    $this->ajaxReturn(0,'更新商品发货数失败',0);
                }
            }
            $model->commit();
            (new TbPurPaymentModel())->createPayableByShip($ship_id);
            if(!$add_data['need_warehousing']) {
                (new TbPurShipModel())->warehouseVirtual($ship_id);
            }
            $this->ajaxReturn(0,'发货成功',1);
        }else {
            $order_detail = M('order_detail', 'tb_pur_'); //实例化采购信息表
            $goods_information = M('goods_information', 'tb_pur_'); //实例化商品信息表
            $relevance_order = M('relevance_order', 'tb_pur_'); //实例化订单关联总表
            $relevance_id = I('get.id'); //接收订单的ID
            $relevance_info = $relevance_order->where("relevance_id='$relevance_id'")->find();
            $order_id = $relevance_info['order_id'];
            $relevance_id = $relevance_info['relevance_id'];
            $order_info = $order_detail->where("order_id='$order_id'")->find(); //采购订单信息
            $goods_info = $goods_information
                ->field('tb_pur_goods_information.*,tb_ms_guds.DELIVERY_WAREHOUSE as warehouse_cd,tb_ms_cmn_cd.CD_VAL as warehouse')
                ->where(["relevance_id" => $relevance_id])
                ->join('left join tb_ms_guds on tb_ms_guds.MAIN_GUDS_ID = left(tb_pur_goods_information.sku_information,8)')
                ->join('left join tb_ms_cmn_cd on tb_ms_cmn_cd.CD = tb_ms_guds.DELIVERY_WAREHOUSE')
                ->group('tb_pur_goods_information.information_id')
                ->select(); //商品信息
            $cmn_cd             = TbMsCmnCdModel::getInstance();
            $cmn_cd_info        = $cmn_cd->getCd($cmn_cd::$purchase_team_cd_pre);
            $currency           = $cmn_cd->getCd($cmn_cd::$currency_cd_pre);
            $credential_types   = $cmn_cd->getCd($cmn_cd::$ship_credential_type_cd_pre); //币种查询
            $this->approve      = M('approve', 'tb_pur_')->where(['relevance_id' => $relevance_id, 'approve_status' => 'N001320300'])->find(); //币种查询
            $this->assign('currency_relation', $this->currency_relation);
            $this->assign('cmn_cd_info', $cmn_cd_info);
            $this->assign('order_info', $order_info);
            $this->assign('goods_info', $goods_info);
            $this->assign('relevance_info', $relevance_info);
            $this->assign('relevance_id', $relevance_id);
            $this->assign('currency', $currency);
            $this->assign('credential_types', $credential_types);
            if (I('request.is_edit')) {
                $this->assign('is_edit', 1);
            } else {
                $this->assign('is_edit', 0);
            }
            $this->display();
        }
    }

    /**
     * 发货详情
     */
    public function ship_detail() {
        $order_detail       = M('order_detail','tb_pur_'); //实例化采购信息表
        $goods_information  = M('goods_information','tb_pur_'); //实例化商品信息表
        $relevance_order    = M('relevance_order','tb_pur_'); //实例化订单关联总表
        $relevance_id       = I('get.id'); //接收订单的ID
        $relevance_info     = $relevance_order->where("relevance_id='$relevance_id'")->find();
        $order_id           = $relevance_info['order_id'];
        $relevance_id       = $relevance_info['relevance_id'];
        $order_info         = $order_detail->where("order_id='$order_id'")->find(); //采购订单信息
        $goods_info         = $goods_information
            ->field('tb_pur_goods_information.*,tb_ms_guds.DELIVERY_WAREHOUSE as warehouse_cd,tb_ms_cmn_cd.CD_VAL as warehouse')
            ->where(["relevance_id"=>$relevance_id])
            ->join('left join tb_ms_guds on tb_ms_guds.MAIN_GUDS_ID = left(tb_pur_goods_information.sku_information,8)')
            ->join('left join tb_ms_cmn_cd on tb_ms_cmn_cd.CD = tb_ms_guds.DELIVERY_WAREHOUSE')
            ->group('tb_pur_goods_information.sku_information')
            ->select(); //商品信息
        $ships              = M('ship','tb_pur_')->where(['relevance_id'=>$relevance_id])->select();
        foreach ($ships as $k => $v) {
            $ships[$k]['credential'] = json_decode($v['credential'],true);
        }
        $cmn_cd             = M('cmn_cd','tb_ms_');
        $currency           = $cmn_cd->where("CD_NM='기준환율종류코드'")->getField('CD,CD_VAL',true); //币种查询
        $warehouses         = $cmn_cd->where("CD_NM='DELIVERY_WAREHOUSE'")->getField('CD,CD_VAL',true); //币种查询
        $this->assign('currency_relation',$this->currency_relation);
        $this->assign('order_info',$order_info);
        $this->assign('goods_info',$goods_info);
        $this->assign('ships',$ships);
        $this->assign('relevance_info',$relevance_info);
        $this->assign('relevance_id',$relevance_id);
        $this->assign('currency',$currency);
        $this->assign('warehouses',$warehouses);
        $this->display();
    }



    /**
     * 删除订单
     */
    public function order_del(){
        $order_detail = M('order_detail','tb_pur_'); //实例化采购信息表
        $sell_information = M('sell_information','tb_pur_'); //实例化销售信息表
        $drawback_information = M('drawback_information','tb_pur_'); //实例化退税信息表
        $goods_information = M('goods_information','tb_pur_'); //实例化商品信息表
        $predict_profit = M('predict_profit','tb_pur_');//实例化预计利润表
        $relevance_order = M('relevance_order','tb_pur_'); //实例化订单关联总表
        $payable = M('payable', 'tb_pur_'); //实例化采购应付表
        $relevance_id = I('get.relevance_id'); //接收需要删除的订单ID
        $relevance_id = explode(',',$relevance_id);
        for($i=0;$i<count($relevance_id);$i++){
            $relevance_info[] = $relevance_order->where("relevance_id='$relevance_id[$i]'")->find();
        }

        foreach($relevance_info as $key=>$v){
            $order_id = $v['order_id']; //订单ID
            $sell_id = $v['sell_id'];  //销售ID
            $drawback_id = $v['drawback_id']; //退税信息ID
            $predict_id = $v['predict_id']; //预计利润表信息ID
            $relevance_id = $v['relevance_id']; //关联总表的信息ID
            $payable_id = $v['payable_id']; //采购应付表的信息ID
            $order_del = $order_detail->where("order_id='$order_id'")->delete(); //删除采购订单信息
            $sell_del = $sell_information->where("sell_id='$sell_id'")->delete(); //删除销售订单信息
            $drawback_del = $drawback_information->where("drawback_id='$drawback_id'")->delete(); //删除退税订单信息
            $predict_del = $predict_profit->where("predict_id='$predict_id'")->delete(); //删除退税订单信息
            $goods_del = $goods_information->where(["relevance_id"=>$relevance_id])->delete(); //删除退税订单信息
            $goods_info = $goods_information->where("relevance_id='$relevance_id'")->select(); //查出需要删除的商品信息
            foreach($goods_info as $key1=>$v1){
                $information_id = $v1['information_id'];

            }

            $relevance_info =  $relevance_order->where("relevance_id='$relevance_id'")->delete(); //查出需要删除的商品信息

        }
        if($order_del&&$sell_del&&$drawback_del&&$predict_del&&$goods_del&&$relevance_info){
            echo 1;
        }else{
            echo 0;
        }



    }


    /**
     * 获取供应商查询条件
     * @param $params
     * @return mixed
     */
    public function getSupper($params){

        if(!empty($params['supplier_id'])) {
            $conditions['SP_NAME']      = array('like','%'. $params ['supplier_id'].'%');
            $conditions['SP_NAME_EN']   = array('like','%'. $params ['supplier_id'].'%');
            $conditions['_logic']       = 'or';
        }
        $where['_complex'] = $conditions;
        return  $where;
    }



    /**
     * 供应商查询
     */
    public function supplier_sou(){
        //供应商查询
        $params['supplier_id'] = $supplier_id = I('get.supplies_id');
        $sp_supplier = M('sp_supplier','tb_crm_');
        $where=$this->getSupper($params);
        $supper_info = $sp_supplier->where($where)->select();
        $purchase_order_m = new TbPurOrderDetailModel();
        foreach ($supper_info as $k=>$v) {
            $has_cooperate = $purchase_order_m->supplierHasCooperate($v['SP_CHARTER_NO']);
            if($has_cooperate) {
                $supper_info[$k]['has_cooperate'] = 1;
            }else {
                $supper_info[$k]['has_cooperate'] = 0;
            }
        }
        if($supper_info==''){
            echo 1;
            exit;
        }
        $this->assign('supper_info',$supper_info);
        $this->display("supplier_sou_ajax");
        //echo json_encode($supper_info);

    }

    /**
     * 发送审批邮件
     */
    public function sendForReview() {
        $relevance_id       = I('request.relevance_id');
        if(IS_POST) {
            if($relevance_id) {
                $res = $this->order_update(true);
            }else {
                $relevance_id = $res = $this->order_add(true);
            }
            if(!$res) {
                $this->ajaxReturn(0,'保存信息失败,请重试',0);
            }
        }
        $relevance          = M('relevance_order','tb_pur_')->where(['relevance_id'=>$relevance_id])->find();
        $predict_id         = $relevance['predict_id'];
        $order_id           = $relevance['predict_id'];
        $this->relevance    = $relevance;
        $order        = M('order_detail','tb_pur_')->where(['order_id'=>$order_id])->find();
        $info         = M('predict_profit','tb_pur_')->where(['predict_id'=>$predict_id])->find();
        $drawback     = M('drawback_information','tb_pur_')->where(['drawback_id'=>$relevance['drawback_id']])->find();
        $sell         = M('sell_information','tb_pur_')->where(['sell_id'=>$relevance['sell_id']])->find();
        $inventory    = M('goods_information','tb_pur_')->alias('t')->join('left join tb_wms_power as a on a.SKU_ID=t.sku_information')->where(['relevance_id'=>$relevance_id])->getField('sum(weight)');
        $goods        = M('goods_information','tb_pur_')
            ->field('t.*,sum(a.sale) as sale_num,sum(a.on_way) on_way_num')
            ->alias('t')
            ->join('left join tb_wms_standing a on a.SKU_ID=t.sku_information')
            ->group('t.information_id')
            ->where(['relevance_id'=>$relevance_id,'sku_information'=>['exp','is not null']])
            ->select();
        if(!$order['procurement_number']) {
            $this->ajaxReturn(0,'请填写采购单号',0);
        }
        if(!$order['payment_company']) {
            $this->ajaxReturn(0,'请选采购团队',0);
        }
        if(!$order['supplier_id']) {
            $this->ajaxReturn(0,'请填写供应商',0);
        }
        if(!$order['contract_number']) {
            $this->ajaxReturn(0,'请选择采购合同',0);
        }
        if(!$order['amount']) {
            $this->ajaxReturn(0,'请填写采购金额',0);
        }
        if(!$order['amount_currency']) {
            $this->ajaxReturn(0,'请选择币种',0);
        }
        if(!$order['business_type']) {
            $this->ajaxReturn(0,'请选择业务类型',0);
        }
        if(!$order['business_direction']) {
            $this->ajaxReturn(0,'请选择业务方向',0);
        }
        if(!$order['delivery_type']) {
            $this->ajaxReturn(0,'请选择交货方式',0);
        }
        if($order['procurement_date'] == '0000-00-00') {
            $this->ajaxReturn(0,'请填写预计采购日期',0);
        }
        if($order['arrival_date'] == '0000-00-00') {
            $this->ajaxReturn(0,'请填写预计到货日期',0);
        }
        if(!$order['payment_info']) $this->ajaxReturn(0,'请完善付款信息',0);
        $payment_info = json_decode($order['payment_info'],true);
        $payment_percent_total = 0;
        foreach ($payment_info as $k => $v) {
            $payment_percent_total += $v['payment_percent'];
            if($order['payment_type'] == 0) {
                if(!$v['payment_date'] || !$v['payment_percent']) $this->error('请完善付款信息');
                if($payment_info[$k-1] && $v['payment_date']<$payment_info[$k-1]['payment_date']) $this->error('付款时间中，账期时间排序有误');
            }else {
                if(!$v['payment_node'] || !$v['payment_days'] || !$v['payment_percent']) $this->error('请完善付款信息');
                if($payment_info[$k-1] && $v['payment_date_estimate']<$payment_info[$k-1]['payment_date_estimate']) $this->error('付款时间中，账期时间排序有误');
            }
        }
        if($payment_percent_total != 100) $this->error('付款比例必须为100%');
        if(!$order['supplier_opening_bank']) {
            $this->ajaxReturn(0,'请填写供应商开户行',0);
        }
        if(!$order['supplier_card_number']) {
            $this->ajaxReturn(0,'请填写供应商银行账号',0);
        }
        if(!$order['invoice_type']) {
            $this->ajaxReturn(0,'请选择发票类型',0);
        }
        if($order['has_tax'] === null) {
            $this->ajaxReturn(0,'请选择是否含税',0);
        }
        if(($order['has_tax'] == 0 && !$order['tax_rate'])) {
            $this->ajaxReturn(0,'请选择税点',0);
        }
        if(!$order['our_company']) {
            $this->ajaxReturn(0,'请选择我方公司',0);
        }

        if($sell['has_sell_number'] && !$sell['sell_number']) {
            $this->ajaxReturn(0,'请填写销售PO',0);
        }
        $b2b_arr = ['B2B Online Team1（商鞅）','B2B Offline','B2B Online Team2（卫青）','B2B Online West'];
        if(in_array($order['business_type'],$b2b_arr)) {
            if(!$sell['has_sell_contract'] && !$sell['approve_credential']) {
                $this->ajaxReturn(0,'B2B业务如果没有销售合同必须上传CEO特批凭证',0);
            }
        }
        if($sell['has_sell_contract'] && !$sell['sell_contract']) {
            $this->ajaxReturn(0,'请填写销售合同编号',0);
        }
        if($sell['has_sell_contract'] && !$sell['supp_id']) {
            $this->ajaxReturn(0,'请填写对应销售客户',0);
        }
        if(!$sell['sell_team']) {
            $this->ajaxReturn(0,'请选择销售团队',0);
        }
        if(!$sell['sell_mode']) {
            $this->ajaxReturn(0,'请选择销售方式',0);
        }
        if($sell['gathering_date'] == '0000-00-00') {
            $this->ajaxReturn(0,'请填写预计收款日期',0);
        }
        if(!$sell['curr']) {
            $this->ajaxReturn(0,'请选择销售币种',0);
        }
        if(!$sell['sell_money']) {
            $this->ajaxReturn(0,'请天填写销售金额',0);
        }
        $receivable = 0;
        if($sell['pre_receivable_percent']) {
            if($sell['pre_receivable_date'] == null || $sell['pre_receivable_date'] == '0000-00-00') $this->error('有收款比例的收款日期必填');
            $receivable += $sell['pre_receivable_percent'];
            $receivable_date[] = $sell['pre_receivable_date'];
        }
        if($sell['mid_receivable_percent']) {
            if($sell['mid_receivable_date'] == null || $sell['mid_receivable_date'] == '0000-00-00') $this->error('有收款比例的收款日期必填');
            $receivable += $sell['mid_receivable_percent'];
            $receivable_date[] = $sell['mid_receivable_date'];
        }
        if($sell['end_receivable_percent']) {
            if($sell['end_receivable_date'] == null || $sell['end_receivable_date'] == '0000-00-00') $this->error('有收款比例的收款日期必填');
            $receivable += $sell['end_receivable_percent'];
            $receivable_date[] = $sell['end_receivable_date'];
        }
        if($receivable != 100) {
            $this->error('收款比例必须为100%');
        }
        foreach ($receivable_date as $k => $v) {
            if($k>0 && $v<$receivable_date[$k-1]) {
                $this->error('付款时间中，账期时间排序有误');
            }
        }
        if(number_format($order['amount'],2) != number_format($relevance['money_total']+$order['expense'],2)) {
            $this->ajaxReturn(0,'采购金额必须为商品金额和采购端物流费用总和',0);
        }
        foreach ($goods as $v) {
            if(!$v['search_information']) {
                $this->ajaxReturn(0,'sku/条形码不存在',0);
            }
            if(!$v['unit_price']) {
                $this->ajaxReturn(0,$v['search_information'].'商品单价必填',0);
            }
            if(!$v['goods_number']){
                $this->ajaxReturn(0,$v['search_information'].'商品数量必填',0);
            }

        }
        $attachment = explode(',',$order['attachment']);
        $attachment_number = 0;
        foreach ($attachment as $v) {
            if($v) {
                $attachment_number++;
            }
        }
        if(!$attachment_number) $this->ajaxReturn(0,'至少上传一个附件',0);

        $this->order        = $order;
        $this->info         = $info;
        $this->drawback     = $drawback;
        $this->sell         = $sell;
        $this->inventory    = $inventory;
        $this->goods        = $goods;
        $address            = C('screening_email_address');
        $save               = [];
        $email_info         = [];
        $approve_user       = '';
        foreach ($address as $k =>$v) {
            $secret                     = md5(uniqid().mt_rand(10000,99999)); //生成唯一秘钥
            $save[$k]['relevance_id']   = $relevance_id;
            $save[$k]['secret_key']     = $secret; //生成唯一秘钥
            $save[$k]['approve_status'] = 'N001320200'; //审核中
            $save[$k]['approve_user']   = explode('@',$v)[0]; //截取邮箱账号作为审核人
            $save[$k]['create_time']    = date('Y-m-d H:i:s');
            $save[$k]['create_user']    = $_SESSION['m_loginname'];
            $email_info[$k]['secret']   = $secret;
            $email_info[$k]['address']  = $v;
            $approve_user               .= explode('@',$v)[0].',';
        }

        $res = M('approve','tb_pur_')->addall($save);
        if($res === false) {
            $this->ajaxReturn(0,'发送审核邮件失败',0);
        }
        M('relevance_order','tb_pur_')->where(['relevance_id'=>$relevance_id])->save(['order_status'=>'N001320200']);



        $email_log['from']          = C('email_address');
        $email_log['email_type']    = 'N001310100';
        $email_log['create_user']   = $_SESSION['m_loginname'];
        $email_log['send_time']     = date('Y-m-d H:i:s');
        $email                      = new SMSEmail();
        $cc                         = C('screening_cc_email_address');
        $purchase_team_email        = M('cmn_cd','tb_ms_')->where(['CD_VAL'=>$order['payment_company'],'CD'=>['like','N00129%']])->getField('ETC');
        if($purchase_team_email) {
            $cc[] = $purchase_team_email;
        }
        foreach ($email_info as $v) {
            $email_log['to']        = $v['address'];
            $this->secret           = $v['secret'];
            $message                = $this->fetch();
            $email_log['content']   = $message;
            $title                  = "审批提醒：采购订单需要审批 - （ID：{$order['procurement_number']}） - {$relevance['prepared_by']} - ".str_replace('-','/',substr($relevance['prepared_time'],0,10));
            M('email','tb_')->add($email_log);
            $res                    = $email->sendEmail($v['address'],$title,$message,$cc);
        }

        if($res) {
            $this->ajaxReturn(['jump_url'=>U('order_detail',['id'=>$relevance_id])],'申请邮件已发送，等待'.rtrim($approve_user,',').'审核中。',1);
        }else {
            $this->ajaxReturn(0,'申请邮件发送失败，请重试。',0);
        }

    }

    /**
     * 订单审批
     */
    public function review() {
        $secret     = I('request.secret');
        $status     = I('request.status');
        if(!in_array($status,[0,1])) {
            $this->error('参数错误',U('index/index'));
        }
        $approve_m  = M('approve','tb_pur_');
        $approve    = $approve_m->where(['secret_key'=>$secret])->find();
        $goods = M('goods_information','tb_pur_')->where(['relevance_id'=>$approve['relevance_id']])->select();
        $order = M('order_detail','tb_pur_')->alias('t')->join('left join tb_pur_relevance_order a on a.order_id=t.order_id')->where(['relevance_id'=>$approve['relevance_id']])->find();

        //审批记录为审批通过或审批失败
        if($approve['approve_status'] == 'N001320300' || $approve['approve_status'] == 'N001320400') {
            $this->error('此次请求已经审批过',U('index/index'));
        }
        if(!$approve) {
            $this->error('审批请求不存在',U('index/index'));
        }
        $order_status = $order['order_status'];
        //采购订单状态为审批通过或审批失败
        if($order_status == 'N001320300'){
            $this->error('该采购订单已经审核通过',U('index/index'));
        }
        $model = new Model();
        $model->startTrans();
        $save['approve_time']           = date('Y-m-d H:i:s');
        $save['actual_approve_user']    = $_SESSION['m_loginname'];
        if($status == 1) {
            $msg = '审批通过';
            $save['approve_status']     = 'N001320300';
            $approve_res                = $model->table('tb_pur_approve')->where(['secret_key'=>$secret])->save($save);
            $relevance_res              = $model->table('tb_pur_relevance_order')->where(['relevance_id'=>$approve['relevance_id']])->save(['order_status'=>'N001320300']);
            $email_content  = "Dear {$order['prepared_by']}:<br />Your purchase order ID: {$order['procurement_number']} has been approved.<br />Please arrange the next steps. Thank you.";
            $email_title    = "Purchase orders from {$order['procurement_number']} is approved";
        }else {
            $msg                        = '审批不通过';
            $save['approve_status']     = 'N001320400';
            $approve_res                = $model->table('tb_pur_approve')->where(['secret_key'=>$secret])->save($save);
            $relevance_res              = $model->table('tb_pur_relevance_order')->where(['relevance_id'=>$approve['relevance_id']])->save(['order_status'=>'N001320400']);
            $email_content  = "Dear {$order['prepared_by']}:<br />Your purchase order ID: {$order['procurement_number']} has been failed.<br />Please arrange the next steps. Thank you.";
            $email_title    = "Purchase orders from {$order['procurement_number']} is failed";
        }
        if($approve_res && $relevance_res) {
            $model->commit();

            if($status == 1) {
                //生成应付
                $payment_m = new TbPurPaymentModel();
                if($order['payment_type'] == 0) {
                    if($order['payment_period']) {
                        $payment_info = json_decode($order['payment_info'],true);
                        foreach($payment_info as $k => $v) {
                            $payable['relevance_id']    = $order['relevance_id'];
                            $payable['payable_date']    = $v['payment_date'];
                            $payable['amount']          = ($order['amount']-$order['expense']);
                            $payable['amount_payable']  = $payable['amount']*$v['payment_percent']/100;
                            $payable['payment_period']  = "第{$k}期-{$v['payment_percent']}%";
                            $payable['payment_no']      = $payment_m->createPaymentNO();
                            $res                        = $payment_m->add($payable);
                            if(!$res) {
                                ELog::add('生成应付数据失败：'.json_encode($payable).M()->getDbError(),ELog::ERR);
                            }
                        }
                    }
                }else {
                    $payment_info = json_decode($order['payment_info'],true);
                    foreach ($payment_info as $v) {
                        if($v['payment_node'] == 'N001390100') {
                            $payable['relevance_id']   = $order['relevance_id'];
                            $payable['payable_date']   = date('Y-m-d',strtotime($order['procurement_date'])+$v['payment_days']*24*3600);
                            $payable['amount']         = ($order['amount']-$order['expense']);
                            $payable['amount_payable'] = $payable['amount']*$v['payment_percent']/100;
                            $payable['payment_period'] = "第1期-"
                                .cdVal($v['payment_node'])
                                .$v['payment_days']
                                .TbPurOrderDetailModel::$payment_day_type[$v['payment_day_type']]
                                .$v['payment_percent'].'%';
                            $payable['payment_no']     = $payment_m->createPaymentNO();
                            $res = $payment_m->add($payable);
                            if(!$res) {
                                ELog::add('生成应付数据失败：'.json_encode($payable).M()->getDbError(),ELog::ERR);
                            }
                            break;
                        }
                    }
                }
                //增加商品在途
                foreach ($goods as $v) {
                    $on_way['SKU_ID']       = $v['sku_information'];
                    $on_way['TYPE']         = 0;
                    $on_way['on_way']       = $v['goods_number'];
                    $on_way['on_way_money'] = round($v['goods_number']*$v['unit_price']*$order['amount_currency_rate'],2);
                    $on_way_all[]           = $on_way;
                }
                $url        = U('bill/on_way_and_on_way_money','','',false,true);
                $res        = curl_request($url,$on_way_all);
                $res_arr    = json_decode($res,true);
                if($res_arr['code'] != 10000111) {
                    ELog::add('增加在途数据失败：'.json_encode($on_way_all).$res,ELog::ERR);
                }
            }
            $email          = new SMSEmail();
            $email_address  = $order['prepared_by'].'@gshopper.com';
            $res = $email->sendEmail($email_address,$email_title,$email_content);
            $this->success($msg.',操作成功',U('index/index'));
        }else {
            $model->rollback();
            $this->error($msg.'操作失败',U('index/index'));
        }
    }

    /**
     * 下载文件
     */
    public function download() {
        $download = new FileDownloadModel();
        $download->fname = I('get.file');
        $download->downloadFile();
    }

    /**
     * 获取OA上的PO数据
     */
    public function get_po_data()
    {

        $CON_NO = $this->getParams()['CON_NO'];
        if(M('order_detail','tb_pur_')->where(['procurement_number'=>$CON_NO])->find()) {
            $this->ajaxReturn('该PO单号已创建过采购订单，请勿重复创建!','',0);
        }
        $oci = new MeBYModel();
        $sql = "SELECT * FROM ECOLOGY.FORMTABLE_MAIN_91 a left join ECOLOGY.HRMRESOURCE b on a.SQR = b.ID  WHERE DJBH='" . $CON_NO . "'";
        $checkSql = "SELECT wr.STATUS FROM ECOLOGY.FORMTABLE_MAIN_91 fm LEFT JOIN ECOLOGY.WORKFLOW_REQUESTBASE wr on fm.REQUESTID = wr.REQUESTID WHERE DJBH = '" . $CON_NO . "'";
        $checkRet = $oci->testQuery($checkSql);
        //if ($checkRet[0]['STATUS'] != '结束') $this->ajaxReturn('编号为：' . $CON_NO . ' 的合同不存在或尚未完成审核，请修改', '', 0);
        $ret = $oci->testQuery($sql);
        if ($ret) {
            $data = $ret[0];
            if($data['CGBUSINESSLICENSE']) {
                $data['supplier'] = M('sp_supplier','tb_crm_')->field('SP_NAME,SP_TEAM_CD,RISK_RATING,SP_CHARTER_NO')->where(['SP_CHARTER_NO'=>$data['CGBUSINESSLICENSE'],'DATA_MARKING'=>0])->find();
                $has_cooperate = (new TbPurOrderDetailModel())->supplierHasCooperate($data['CGBUSINESSLICENSE']);
                if($has_cooperate) {
                    $data['supplier']['has_cooperate'] = 1;
                }else {
                    $data['supplier']['has_cooperate'] = 0;
                }
            }
            $this->ajaxReturn($data, '', 1);
        } else {
            $this->ajaxReturn('未查询到编号为：' . $CON_NO . ' 的合同，请修改', '', 0);
        }
    }

    /**
     * 查询合同
     */
    public function getContract() {
        $sp_charter_no = I('request.sp_charter_no');
        $contract = M('contract','tb_crm_')->field('SP_BANK_CD,BANK_ACCOUNT,SWIFT_CODE,CON_NO,CON_NAME,SP_CHARTER_NO')->where(['SP_CHARTER_NO'=>$sp_charter_no])->order('create_time desc')->select();
        if($contract !== false) {
            $this->ajaxReturn($contract,'',1);
        }else {
            $this->ajaxReturn('获取失败','',0);
        }
    }

    /**
     * 导入商品
     */
    public function importGoods() {
        header("content-type:text/html;charset=utf-8");
        $filePath = $_FILES['file']['tmp_name'];
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        //默认用excel2007读取excel，若格式不对，则用之前的版本进行读取
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                $this->error('请上传EXCEL文件','',true);
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
        $goods_info_url = U('Stock/Searchguds','','',false,true);
        $msg = '';
        $skus = [];
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $temp   = [];
            $search    = trim((string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue());
            $price  = trim((string)$PHPExcel->getActiveSheet()->getCell("B" . $currentRow)->getValue());
            $number     = trim((string)$PHPExcel->getActiveSheet()->getCell("C" . $currentRow)->getValue());
            $res = json_decode(curl_request($goods_info_url,['GSKU'=>$search]),true);
            if(!$search || $res['status'] == 0) {
                $error = true;
                $msg .= "第{$currentRow}行商品不存在<br />";
            }else {
                $sku = $res['info'][0]['GUDS_OPT_ID'];
                $skus[$currentRow] = $res['info'][0]['GUDS_OPT_ID'];
            }
            if(!is_numeric($price) || $price<=0) {
                $error = true;
                $msg .= "第{$currentRow}行商品价格有误（比如书籍中含0、或者为负数）<br />";
            }
            if(!is_numeric($number) || strstr($number,'.') || $number<=0) {
                $error = true;
                $msg .= "第{$currentRow}行商品数量有误（比如数量中含小数、或者小于1）<br />";
            }
            if ($sku && $price && $number) {
                $temp['search']     = $search;
                $temp['sku']        = $sku;
                $temp['now_number'] = $currentRow-1;
                $temp['price']      = $price;
                $temp['number']     = $number;
                $temp['goods_money']= $price*$number;
                $temp['goods_name'] = $res['info'][0]['Guds']['GUDS_NM'];
                $temp['guds_img']   = $res['info'][0]['Img'];
                $temp['val_str']    = $res['info']['opt_val'][0]['val'];
                $standing           = M('standing','tb_wms_')
                    ->field('sum(sale) as sale_num,sum(on_way) on_way_num')
                    ->where(['SKU_ID'=>$sku])
                    ->group('SKU_ID')
                    ->find();
                $temp['sale_num']   = $standing['sale_num'];
                $temp['on_way_num'] = $standing['on_way_num'];
                $expe[]             = $temp;
            }
        }
        if($error) {
            $this->error($msg,'',true);
        }else {
            $currency_s = I('request.currency');
            $this->guds = $expe;
            $cmn_cd           = M('cmn_cd','tb_ms_');
            $this->currency   = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
            $this->currency_s   = $currency_s;
            $this->is_import = 1;
            $html = $this->fetch('order_add_ajax');
            $this->success($html,'',true);
        }
    }

    /**
     * 入库
     */
    public function warehouse() {
        if(IS_POST) {
            $warehouse_save = $_POST;
            foreach ($warehouse_save['goods'] as $k => $v) {
                $number_info = [];
                foreach ($v['number_info']['production_date'] as $key => $value) {
                    $info['number'] = $v['number_info']['number'][$key];
                    $info['production_date'] = $value;
                    $number_info[] = $info;
                }
                $warehouse_save['goods'][$k]['number_info_warehouse'] = json_encode($number_info);
            }
            $model = (new TbPurShipModel());
            $res = $model->warehouse($warehouse_save);
            if($res) {
                $this->success('入库成功');
            }else {
                $this->error('入库失败：'.$model->getError());
            }
        }
        $model                      = new TbPurShipModel();
        $id                         = I('request.id');
        $detail                     = $model->relation(true)->where(['id'=>$id])->find();
        $detail['credential']       = json_decode($detail['credential'],true);
        $this->detail               = $detail;
        $cd_m                       = new TbMsCmnCdModel();
        $this->warehouse            = $cd_m->warehouse();
        $this->currency             = $cd_m->currency();
        $this->tax_rate             = $cd_m->taxRate();
        $this->warehouse_difference = $cd_m->warehouseDifference();
        $this->display();
    }

    /**
     * 入库列表
     */
    public function warehouse_list() {
        import('ORG.Util.Page');
        $model  = M('ship','tb_pur_');
        $params = I('request.');
        if(is_numeric($params['warehouse_status'])) {
            $params['warehouse_status'] == 2         ? $where['t.need_warehousing']  = 0 : $where['t.warehouse_status'] = $params['warehouse_status'];
        }
        $params['warehouse']                            ? $where['t.warehouse']         = $params['warehouse']:'';
        $params['supplier_id']                          ? $where['b.supplier_id']       = ['like','%'.$params['supplier_id'].'%']:'';
        $params['procurement_number']                   ? $where['procurement_number']  = $params['procurement_number']:'';
        $params['prepared_by']                          ? $where['prepared_by']         = ['like','%'.$params['prepared_by'].'%']:'';
        $params['payment_company']                      ? $where['payment_company']     = $params['payment_company']:'';
        $params['payment_company']                      ? $where['payment_company']     = $params['payment_company']:'';
        $params['start_time']                           ? $where['shipment_date']       = ['egt',$params['start_time']]:'';
        $params['end_time']                             ? $where['shipment_date']       = ['elt',$params['end_time']]:'';
        $params['start_time'] && $params['end_time']    ? $where['shipment_date']       = ['between',[$params['start_time'],$params['end_time']]]:'';
        $count  = $model->alias('t')
            ->join('left join tb_pur_relevance_order a on a.relevance_id=t.relevance_id')
            ->join('left join tb_pur_order_detail b on b.order_id=a.order_id')
            ->where($where)
            ->count();
        $page = new Page($count,20);
        $list = $model->alias('t')
            ->field('t.*,a.prepared_by,b.procurement_number,b.supplier_id,c.CD_VAL warehouse,e.goods_name')
            ->join('left join tb_pur_relevance_order a on a.relevance_id=t.relevance_id')
            ->join('left join tb_pur_order_detail b on b.order_id=a.order_id')
            ->join('left join tb_ms_cmn_cd c on c.CD=t.warehouse')
            ->join('left join tb_pur_ship_goods d on d.ship_id=t.id')
            ->join('left join tb_pur_goods_information e on e.information_id=d.information_id')
            ->where($where)
            ->group('t.id')
            ->limit($page->firstRow.','.$page->listRows)
            ->order('t.id desc')
            ->select();
        $cd_m                       = new TbMsCmnCdModel();
        $this->warehouses           = $cd_m->warehouseKey();
        $this->purchase_teams       = $cd_m->purchaseTeamsKey();
        $this->show                 = $page->show();
        $this->param                = $params;
        $this->list                 = $list;
        $this->display();
    }

    /**
     * 入库详情
     */
    public function warehouse_detail() {
        $model                  = D('TbPurShip');
        $id                     = I('request.id');
        $detail                 = $model->relation(true)->where(['id'=>$id])->find();
        $detail['credential']   = json_decode($detail['credential'],true);
        $service_cost           = 0;
        foreach ($detail['goods'] as $v) {
            $price_rmb          = $v['information']['unit_price']*$detail['relevance']['real_total_rate'];
            $per_service_cost   = ($v['warehouse_cost']-$price_rmb)*$v['warehouse_number'];
            $service_cost       += $per_service_cost;
        }
        $detail['service_cost'] = number_format($service_cost,2);
        $this->detail           = $detail;
        $this->display();
    }

    /**
     * 查询商品
     */
    public function search_goods() {
        $search_id  = I('request.search_id');
        $now_number = I('post.now_number'); //序号
        $url        = U('Stock/Searchguds','','',false,true);
        $res        = json_decode(curl_request($url,['GSKU'=>$search_id]),true);
        if($res['status'] == 0) {
            $this->error('SKU编码/条形码错误');
        }
        $guds['goods_name'] = $res['info'][0]['Guds']['GUDS_NM'];
        $guds['sku_id']     = $res['info'][0]['GUDS_OPT_ID'];
        $guds['search_id']  = $search_id;
        $guds['guds_img']   = $res['info'][0]['Img'];
        $guds['val_str']    = $res['info']['opt_val'][0]['val'];
        $guds['now_number'] = $now_number;
        $warehouse          = M('guds','tb_ms_')
            ->alias('t')
            ->field('a.CD warehouse,a.CD_VAL warehouse_name')
            ->join('left join tb_ms_cmn_cd a on a.CD = t.DELIVERY_WAREHOUSE')
            ->where(['MAIN_GUDS_ID'=>substr($guds['sku_id'],0,8)])
            ->find();
        $guds['warehouse']      = $warehouse['warehouse'];
        $guds['warehouse_name'] = $warehouse['warehouse_name'];
        $this->success($guds);
    }

    public function payable_list() {
        import('ORG.Util.Page');
        $params = I('get.');
        if($params['payment_no']) $where['t.payment_no'] = ['like','%'.$params['payment_no'].'%'];
        if($params['procurement_number']) $where['b.procurement_number'] = ['like','%'.$params['procurement_number'].'%'];
        if($params['supplier_id']) $where['b.supplier_id'] = ['like','%'.$params['supplier_id'].'%'];
        if($params['our_company']) $where['b.our_company'] = $params['our_company'];
        if($params['payment_company']) $where['b.payment_company'] = $params['payment_company'];
        if($params['prepared_by']) $where['a.prepared_by'] = ['like','%'.$params['prepared_by'].'%'];
        if($params['start_time'] && $params['end_time']) {
            $where['t.paid_date'] = ['between',[$params['start_time'],$params['end_time']]];
        }elseif($params['start_time']) {
            $where['t.paid_date'] = ['egt',$params['start_time']];
        }elseif($params['end_time']) {
            $where['t.paid_date'] = ['elt',$params['end_time']];
        }
        if($params['status'] !== '' && isset($params['status']) ) $where['t.status'] = $params['status'];
        $payment_m  = M('payment','tb_pur_');
        $count      = $payment_m
            ->alias('t')
            ->join('left join tb_pur_relevance_order a on t.relevance_id=a.relevance_id')
            ->join('left join tb_pur_order_detail b on b.order_id=a.order_id')
            ->join('left join tb_pur_sell_information c on c.sell_id=a.sell_id')
            ->where($where)
            ->count();
        $page       = new Page($count, 20); //每页显示3条数据
        $show       = $page->show(); //分页显示
        $list       = $payment_m
            ->alias('t')
            ->field('t.*,a.prepared_by,b.procurement_number,b.supplier_id,b.our_company,b.amount_currency,b.payment_company')
            ->join('left join tb_pur_relevance_order a on t.relevance_id=a.relevance_id')
            ->join('left join tb_pur_order_detail b on b.order_id=a.order_id')
            ->join('left join tb_pur_sell_information c on c.sell_id=a.sell_id')
            ->where($where)
            ->order('id desc')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $cmn_m = new TbMsCmnCdModel();
        $this->list             = $list;
        $this->page             = $show;
        $this->count            = $count;
        $this->param            = $params;
        $this->our_company      = $cmn_m->getCd($cmn_m::$our_company_cd_pre);
        $this->purchase_team    = $cmn_m->getCd($cmn_m::$purchase_team_cd_pre);
        $this->display();
    }

    public function payable_detail() {
        $id = I('request.id');
        $info = M('payment','tb_pur_')
            ->where(['id'=>$id])
            ->find();
        if(!$info) $this->error('采购应付不存在');
        $order = M('relevance_order','tb_pur_')
            ->alias('t')
            ->join('left join tb_pur_order_detail a on a.order_id=t.order_id')
            ->join('left join tb_pur_sell_information b on b.sell_id=t.sell_id')
            ->where(['t.relevance_id'=>$info['relevance_id']])
            ->find();
        $risk_rating  = M('sp_supplier','tb_crm_')->where(['SP_CHARTER_NO'=>$order['sp_charter_no'],'DATA_MARKING'=>0])->getField('RISK_RATING');
        $old_sup = M('order_detail','tb_pur_')->alias('t')
            ->join('left join tb_pur_relevance_order a on a.order_id=t.order_id')
            ->where(['sp_charter_no'=>$order['sp_charter_no'],'order_status'=>'N001320300','relevance_id'=>['lt',$order['relevance_id']]])
            ->find();
        $this->has_cooperate    = $old_sup ? 1 : 0;
        $this->risk_rating      = $risk_rating;
        $this->info     = $info;
        $this->order    = $order;
        $this->display();
    }

    public function payable_confirm() {
        if(IS_POST) {
            $data                   = I('post.');
            $data['confirm_time']   = date('Y-m-d H:i:s');
            $data['confirm_user']   = $_SESSION['m_loginname'];
            $data['status']         = 1;
            if(!$data['amount_confirm']) $this->error('请填写确认金额','',true);
            if($data['amount_difference'] != 0 && !$data['difference_reason']) $this->error('请选择差异原因','',true);
            $res = M('payment','tb_pur_')->save($data);
            if($res) {
                $payable            = M('payment','tb_pur_')->where(['id'=>$data['id']])->find();
                $this->payable      = $payable;
                $relevance          = M('relevance_order','tb_pur_')->where(['relevance_id'=>$payable['relevance_id']])->find();
                $this->relevance    = $relevance;
                $this->order        = M('order_detail','tb_pur_')->alias('t')->field('t.*,a.CD_VAL')->join('left join tb_ms_cmn_cd a on a.CD=t.our_company')->where(['order_id'=>$relevance['order_id']])->find();
                $this->predict      = M('predict_profit','tb_pur_')->where(['predict_id'=>$relevance['predict_id']])->find();
                $message_payable    = $this->fetch('payable_email');
                $email                  = new SMSEmail();
                $email_res = $email->sendEmail(C('purchase_payable_notice_email'),'应付提醒',$message_payable);
                if(!$email_res) {
                    ELog::add(['message'=>'应付提醒邮件发送失败','info'=>$data],ELog::ERR);
                }
                $this->success('保存成功','',true);
            }else {
                $this->error('保存失败','',true);
            }
        }
        $id = I('request.id');
        $info = M('payment','tb_pur_')
            ->where(['id'=>$id])
            ->find();
        if(!$info) $this->error('采购应付不存在');
        $order = M('relevance_order','tb_pur_')
            ->alias('t')
            ->join('left join tb_pur_order_detail a on a.order_id=t.order_id')
            ->join('left join tb_pur_sell_information b on b.sell_id=t.sell_id')
            ->where(['t.relevance_id'=>$info['relevance_id']])
            ->find();
        $this->info                 = $info;
        $this->order                = $order;
        $this->payment_dif_reason   = (new TbMsCmnCdModel())->getCdY(TbMsCmnCdModel::$payment_dif_reason_cd_pre);
        $this->display();
    }

    public function payable_write_off() {
        if(IS_POST) {
            $data = I('post.');
            if(!$data['amount_paid']) $this->error("请填写付款金额", '', true);
            if(!$data['paid_date']) $this->error("请填写付款时间", '', true);
            if(!$data['our_company']) $this->error("请选择我方公司", '', true);
            if ($_FILES['voucher']['name']) {
                // 图片上传
                $fd = new FileUploadModel();
                $ret = $fd->uploadFile();
                if ($ret) {
                    $data['voucher'] = $fd->save_name;
                } else {
                    $this->error("修改失败：上传文件失败," . $fd->error, '', true);
                }
            }else {
                $this->error("请上传支付凭证", '', true);
            }
            $data['payment_submit_time']    = date('Y-m-d H:i:s');
            $data['payment_submit_user']    = $_SESSION['m_loginname'];
            $data['status']                 = 2;
            $res = M('payment','tb_pur_')->save($data);
            if($res) {
                $this->success('保存成功','',true);
            }else {
                $this->error('保存失败','',true);
            }
        }
        $id = I('request.id');
        $info = M('payment','tb_pur_')
            ->where(['id'=>$id])
            ->find();
        if(!$info) $this->error('采购应付不存在');
        $order = M('relevance_order','tb_pur_')
            ->alias('t')
            ->join('left join tb_pur_order_detail a on a.order_id=t.order_id')
            ->join('left join tb_pur_sell_information b on b.sell_id=t.sell_id')
            ->where(['t.relevance_id'=>$info['relevance_id']])
            ->find();
        $risk_rating  = M('sp_supplier','tb_crm_')->where(['SP_CHARTER_NO'=>$order['sp_charter_no'],'DATA_MARKING'=>0])->getField('RISK_RATING');
        $old_sup = M('order_detail','tb_pur_')->alias('t')
            ->join('left join tb_pur_relevance_order a on a.order_id=t.order_id')
            ->where(['sp_charter_no'=>$order['sp_charter_no'],'order_status'=>'N001320300','relevance_id'=>['lt',$order['relevance_id']]])
            ->find();
        $this->has_cooperate    = $old_sup ? 1 : 0;
        $this->risk_rating      = $risk_rating;
        $this->info             = $info;
        $this->order            = $order;
        $this->our_company      = (new TbMsCmnCdModel())->getCdY(TbMsCmnCdModel::$our_company_cd_pre);
        $this->display();
    }
}