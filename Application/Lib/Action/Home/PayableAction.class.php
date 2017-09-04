<?php
/**
 * Created by PhpStorm.
 * User: b5m
 * Date: 2017/5/2
 * Time: 13:23
 */


class PayableAction extends BaseAction
{


    public function getConditions($params)
    {
        !empty($params ['procurement_number']) ? $conditions ['tb_pur_payable.receipt_number'] = array('like', '%' . $params ['procurement_number'] . '%') : '';
        !empty($params ['goods_name']) ? $conditions ['tb_pur_goods_information.goods_name'] = array('like', '%' . $params ['goods_name'] . '%') : '';
        !empty($params ['pur_dep']) ? $conditions ['tb_pur_payable.payment_company'] = $params ['pur_dep'] : '';
        !empty($params ['prepared_by']) ? $conditions ['tb_pur_payable.prepared_by'] = array('like', '%' . $params ['prepared_by'] . '%') : '';
        !empty($params ['business_direction']) ? $conditions ['tb_pur_payable.business_direction'] = $params ['business_direction'] : '';
        !empty($params ['business_type']) ? $conditions ['tb_pur_payable.business_type'] = $params ['business_type'] : '';
        if($params ['start_time']&&$params ['end_time']){ //如果两个条件都存在说明需要区间查询，否则不需要区间查询
            !empty($params ['start_time']) ? $conditions ['sou_time'] = array('EGT', $params ['start_time']) : '';
            !empty($params ['end_time']) ? $conditions ['tb_pur_payable.sou_time'] = array('ELT', $params ['end_time']) : '';
        }else{
            !empty($params ['start_time']) ? $conditions ['sou_time'] = $params ['start_time'] : '';
            !empty($params ['end_time']) ? $conditions ['tb_pur_payable.sou_time'] =$params ['end_time'] : '';
        }
        return $conditions;
    }


    public function payable_list()
    {
        import('ORG.Util.Page');
        $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
        $order_detail = M('order_detail', 'tb_pur_'); //实例化采购信息表
        $sell_information = M('sell_information', 'tb_pur_'); //实例化销售信息表
        $drawback_information = M('drawback_information', 'tb_pur_'); //实例化退税信息表
        $goods_information = M('goods_information', 'tb_pur_'); //实例化商品信息表
        $predict_profit = M('predict_profit', 'tb_pur_');//实例化预计利润表
        $relevance_order = M('relevance_order', 'tb_pur_'); //实例化订单关联总表
        $payable = M('payable', 'tb_pur_'); //实例化采购应付表
        if (I("get.go") == 'go') {
            //$sou_info = I('post.'); //接收搜索信息
            $params['procurement_number'] = $procurement_number = I("get.procurement_number"); //接收应付单号
            // echo  $params['procurement_number'];die;
            $params['goods_name'] = $goods_name = I("get.goods_name"); //接收商品名称
            $params['pur_dep'] = $pur_dep = I("get.pur_dep"); //接受采购部门
            $params['prepared_by'] = $prepared_by = I("get.prepared_by"); //接收采购员
            $params['business_direction'] = $business_direction = I("get.business_direction"); //接收业务方向
            $params['business_type'] = $business_type = I("get.business_type"); //接收业务类型
            $params['start_time'] = $start_time = I('get.start_time'); //起始时间
            $params['end_time'] = $end_time = I('get.end_time'); //结束时间
            $where = $this->getConditions($params);
            //print_r($where);die;
            trace($this->_param(), '$where');
            $purchase_info = $payable
                ->join("left join tb_pur_goods_information on tb_pur_goods_information.payable_id = tb_pur_payable.payable_id")
                ->group('tb_pur_goods_information.payable_id')->where($where)
                ->order("creation_times desc")
                ->select();
            $counts = count($purchase_info);

            $Page = new Page($counts, 20); //每页显示20条数据

            $Page->setConfig('prev', '上一页');
            $Page->setConfig('next', '下一页');
            $show = $Page->show(); //分页显示
            $purchase_info = $payable
                ->join("left join tb_pur_goods_information on tb_pur_goods_information.payable_id = tb_pur_payable.payable_id")
                ->group('tb_pur_goods_information.payable_id')->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)->order("creation_times desc")
                ->select();
            $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
            $cmn_cd_info = $cmn_cd->where("CD_NM='采购团队'")->select();
            $business_directions = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_types = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
            //分配变量到模板
//            $this->assign('show',$show);
            $this->assign('params', $params);
            $this->assign('show', $show);
            $this->assign('firstRow',$Page->firstRow);
            $this->assign('purchase_info', $purchase_info);
            $this->assign('business_directions',$business_directions);
            $this->assign('business_types',$business_types);
            $this->assign('procurement_number',$procurement_number);
            $this->assign('goods_name',$goods_name);
            $this->assign('pur_dep',$pur_dep);
            $this->assign('prepared_by',$prepared_by);
            $this->assign('start_time',$start_time);
            $this->assign('end_time',$end_time);
            $this->assign('business_direction',$business_direction);
            $this->assign('business_type',$business_type);
            $this->assign('cmn_cd_info', $cmn_cd_info);
            $this->display();


        } else {
            $counts = $payable->count();

            $Page = new Page($counts, 20); //每页显示20条数据

            $Page->setConfig('prev', '上一页');
            $Page->setConfig('next', '下一页');
            $show = $Page->show(); //分页显示
            $purchase_info = $payable->limit($Page->firstRow . ',' . $Page->listRows)->order("creation_times desc")->select();
            $business_directions = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_types = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
            $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
            $cmn_cd_info = $cmn_cd->where("CD_NM='采购团队'")->select();
            //分配变量到模板
            $this->assign('show', $show);
            $this->assign('firstRow',$Page->firstRow);
            $this->assign('purchase_info', $purchase_info);
            $this->assign('business_directions', $business_directions);
            $this->assign('business_types', $business_types);
            $this->assign('cmn_cd_info', $cmn_cd_info);
            $this->display();
        }

    }


    public function payable_detail()
    {
        $payable_id = I('get.id');
        //print_r($relevance_id);die;
        $payable = M('payable', 'tb_pur_'); //实例化采购应付表
        $relevance_order = M('relevance_order', 'tb_pur_'); //实例化订单关联总表
        $goods_information = M('goods_information', 'tb_pur_'); //实例化商品信息表
        $payable_info = $payable->where("payable_id='$payable_id'")->find();
        $payable_status = $payable_info['payable_status'];
        $goods_info = $goods_information->where("payable_id='$payable_id'")->select();
        $relevance_info =  $relevance_order->where("payable_id='$payable_id'")->find();
        $total_profit_margin = M('predict_profit','tb_pur_')->where(['predict_id'=>$relevance_info['predict_id']])->getField('total_profit_margin');
        $order_info = M('order_detail','tb_pur_')->where(['order_id'=>$relevance_info['order_id']])->find();

        /*
        if($payable_status==1){
            $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
            $cmn_cd_info = $cmn_cd->where("CD_NM='付款公司'")->select();
            $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_type = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
            $currency = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
            $this->assign('cmn_cd_info',$cmn_cd_info);;
            $this->assign('currency',$currency);
            $this->assign('business_direction', $business_direction);
            $this->assign('business_type', $business_type);
            $this->assign('payable_info', $payable_info);
            $this->assign('goods_info', $goods_info);
            $this->assign('payable_id', $payable_id);
            $this->assign('relevance_info', $relevance_info);
            $this->display("update_detail");
        }else{
         */
            $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
            $our_companys = $cmn_cd->where("CD_NM='我方公司'")->getField('CD,CD_VAL',true);
            $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_type = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
            $currency = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
            $this->assign('our_companys',$our_companys);;
            $this->assign('currency',$currency);
            $this->assign('business_direction', $business_direction);
            $this->assign('business_type', $business_type);
            $this->assign('payable_info', $payable_info);
            $this->assign('goods_info', $goods_info);
            $this->assign('relevance_info', $relevance_info);
            $this->assign('total_profit_margin', $total_profit_margin);
        $this->assign('order_info', $order_info);
            $this->display();
        //}


    }

    public function payment_write_off() {
        if(IS_POST) {
            $post = I('post.');
            $payable = M('payable', 'tb_pur_')->where(['payable_id'=>$post['payable_id']])->find(); //实例化采购应付表
            if(!$payable) {
                $this->error("该采购应付不存在",U('payable_list'),true);
            }
            if($payable['payable_status'] == 1) {
                $this->error("该采购应付已经进行过支付核销",U('payable_list'),true);
            }
            if ($_FILES['payment_voucher']['name']) {
                // 图片上传
                $fd = new FileUploadModel();
                $ret = $fd->uploadFile();
                if($ret){
                    $post['payment_voucher'] = $fd->save_name;
                }else {
                    $this->error("修改失败：上传文件失败,".$fd->error,U('OrderDetail/order_list'),true);
                }
            }else {
                $this->error("支付凭证必须",U('OrderDetail/order_list'),true);
            }
            $post['payable_status'] = 1;
            $post['payment_submit_time'] = date('Y-m-d H:i:s');
            $post['payment_submit_by'] = $_SESSION['m_loginname'];
            $res = M('payable','tb_pur_')->where(['payable_id'=>$post['payable_id']])->save($post);
            if($res !== false) {
                $this->success("保存成功",U('payable_list'),true);
            }else {
                $this->error("支付核销保存失败",U('payable_list'),true);
            }
        }else {
            $payable_id = I('get.id');
            //print_r($relevance_id);die;
            $payable = M('payable', 'tb_pur_'); //实例化采购应付表
            $relevance_order = M('relevance_order', 'tb_pur_'); //实例化订单关联总表
            $goods_information = M('goods_information', 'tb_pur_'); //实例化商品信息表
            $payable_info = $payable->where("payable_id='$payable_id'")->find();
            $payable_status = $payable_info['payable_status'];
            $goods_info = $goods_information->where("payable_id='$payable_id'")->select();
            $relevance_info =  $relevance_order->where("payable_id='$payable_id'")->find();
            $total_profit_margin = M('predict_profit','tb_pur_')->where(['predict_id'=>$relevance_info['predict_id']])->getField('total_profit_margin');
            $order_info = M('order_detail','tb_pur_')->where(['order_id'=>$relevance_info['order_id']])->find();

            /*
            if($payable_status==1){
                $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
                $cmn_cd_info = $cmn_cd->where("CD_NM='付款公司'")->select();
                $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
                $business_type = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
                $currency = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
                $this->assign('cmn_cd_info',$cmn_cd_info);;
                $this->assign('currency',$currency);
                $this->assign('business_direction', $business_direction);
                $this->assign('business_type', $business_type);
                $this->assign('payable_info', $payable_info);
                $this->assign('goods_info', $goods_info);
                $this->assign('payable_id', $payable_id);
                $this->assign('relevance_info', $relevance_info);
                $this->display("update_detail");
            }else{
             */
            $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
            $our_companys = $cmn_cd->where("CD_NM='我方公司'")->getField('CD,CD_VAL',true);
            $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_type = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息
            $currency = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
            $this->assign('our_companys',$our_companys);;
            $this->assign('currency',$currency);
            $this->assign('business_direction', $business_direction);
            $this->assign('business_type', $business_type);
            $this->assign('payable_info', $payable_info);
            $this->assign('goods_info', $goods_info);
            $this->assign('relevance_info', $relevance_info);
            $this->assign('total_profit_margin', $total_profit_margin);
            $this->assign('order_info', $order_info);
            $this->display();
        }
    }




    public function  payable_add()
    {
        $payable = M('payable', 'tb_pur_'); //实例化采购应付表
        $goods_information = M('goods_information', 'tb_pur_'); //实例化商品信息表
        if($this->isPost()){
            $add_data = $_REQUEST;
            $add_data['payable_status'] = 1; //给手动插入的应付订单默认状态为1
            $prepared_time = date("Y-m-d H:i:s",time());
            $add_data['prepared_time'] = $prepared_time;
            $creation_times = date("Y-m-d H:i:s",time());
            $purchase_info['creation_times'] = $creation_times;
            /* $add_data['prepared_time'] =  $prepared_time; //加入制单时间为应付生成做准备*/
            $receipt_time = substr("$prepared_time",0,10); //订单编号的时间
            $receipt_time = explode('-',$receipt_time); //将时间格式进行转换
            $receipt_time = implode('',$receipt_time); //将时间格式进行转换
            $new_order = $payable->order("prepared_time desc")->find(); //查出最新的一条关联订单
            $receipt_number =  $new_order['receipt_number']; //查出的最新的订单编号
            $serial_number =  substr("$receipt_number",-4)+1; //流水号
            $serial_number = sprintf("%04d", $serial_number); //不够四位数用0自动补全
            $receipt_number = 'YF'.$receipt_time.$serial_number;
            $add_data['receipt_number'] = $receipt_number;
            $result1 = $payable->add($add_data);
            //将商品信息接收入库
            $sku_information = I("post.sku_information");
            $goods_name = I("post.goods_name");
            $goods_attribute = I("post.goods_attribute");
            $image = I("post.image");
            $unit_price = I("post.unit_price");
            $curType = I("post.curType");
            $goods_number = I("post.goods_number");
            $goods_money = I("post.goods_money");
            $remark = I("post.remark");
            $payable_info = $payable->order("prepared_time desc")->find();///查出最新一条订单关联数据


            //unset($payable_id); //删除上面的$payable_id；
            for($i=0;$i<count($remark);$i++){
                $create_time[] = date("Y-m-d H:i:s",time());  //形成一一对应关系所以进行循环
                $payable_id[] = $payable_info['payable_id'];
            }

            $data =  compact("sku_information","goods_name","goods_attribute","image","unit_price","curType","goods_number","goods_money","remark","create_time" ,"payable_id"
            );

            foreach($data as $key=>$v){

                foreach($v as  $key1=>$v1 ){
                    $goods[$key1][$key]=$v1;
                }
            }


            $result2 = $goods_information->addAll($goods);

            if($result1&&$result2){
                $this->success("保存成功",U('Payable/payable_list'),1);
            }else{
                $this->error("保存失败",U('Payable/payable_add'),1);
            }


        }else{
            $cmn_cd = M('cmn_cd', 'tb_ms_'); // 实例化数据字典表
            $cmn_cd_info = $cmn_cd->where("CD_NM='付款公司'")->select();
            $business_direction = $cmn_cd->where("CD_NM='业务方向'")->select(); //查出业务方向信息
            $business_type = $cmn_cd->where("CD_NM='业务类型'")->select();    //查出业务类型信息

            //print_r($delivery_type);die;
            $currency = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
            $this->assign('cmn_cd_info',$cmn_cd_info);;
            $this->assign('currency',$currency);
            $this->assign('business_direction', $business_direction);
            $this->assign('business_type', $business_type);
            $this->display();
        }


    }



    //sku信息查询
    public function payable_add_ajax(){
        $sku = I('post.sku');
        $guds_opt = M('guds_opt','tb_ms_'); //实例化商品属性表
        $guds = M('guds','tb_ms_'); //实例化商品表
        $guds_img = M('guds_img','tb_ms_'); //实例化商品图片表
        $sku_info = $guds_opt->where("GUDS_OPT_ID='$sku'")->find();
        if($sku_info==''){
            echo 1;   //如果sku查询为空，就返回1，并且程序停止执行
            exit;
        }
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
        $cmn_cd = M('cmn_cd','tb_ms_');
        $currency = $cmn_cd->where("CD_NM='기준환율종류코드'")->select(); //币种查询
        $this->assign('sku',$sku);
        $this->assign('goods_name',$goods_name);
        $this->assign('guds_img',$guds_img);
        $this->assign('val_str',$attr);
        $this->assign('currency',$currency);
        $this->display();



    }





    public function update_detail(){

        $drawback_information = M('drawback_information','tb_pur_'); //实例化退税信息表
        $goods_information = M('goods_information','tb_pur_'); //实例化商品信息表
        $payable = M('payable','tb_pur_'); //实例化采购应付表
        if($this->isPost()){
            //$add_data = $_REQUEST;
            $purchase_info = $_REQUEST;
            $payable_id = I('post.payable_id');
            $purchase_info['payable_status'] = 1; //给手动插入的应付订单默认状态为1
            $prepared_time = date("Y-m-d H:i:s",time());
            $purchase_info['prepared_time'] = $prepared_time;
            $payable_info = $payable->where("payable_id='$payable_id'")->save($purchase_info);

            //商品信息的修改
            $information_id = I("post.information_id");
            $sku_information = I("post.sku_information");
            $goods_name = I("post.goods_name");
            $goods_attribute = I("post.goods_attribute");
            $image = I("post.image");
            $unit_price = I("post.unit_price");
            $curType = I("post.curType");
            $goods_number = I("post.goods_number");
            $goods_money = I("post.goods_money");
            $remark = I("post.remark");
            unset($payable_id);
            for($i=0;$i<count($remark);$i++){
                $payable_id[] = I('post.payable_id');//形成一一对应关系所以进行循环
            }


            $data =  compact("information_id","sku_information","goods_name","goods_attribute","image","unit_price","curType","goods_number","goods_money","remark","payable_id"
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
                    $goods_info[] = $goods_information->where("information_id=$info_id")->save($v);
                }


            }

            if($goods_info>=0&&$payable_info>=0){
                $this->success("修改成功",U('Payable/payable_list'),1);
            }else{
                $this->error("修改失败",U("Payable/payable_detail"),1);
            }

        }


    }




    public function payableEmail($relevance_id) {
        $relevance          = M('relevance_order','tb_pur_')->where(['relevance_id'=>$relevance_id])->find();
        $this->relevance    = $relevance;
        $this->order        = M('order_detail','tb_pur_')->alias('t')->field('t.*,a.CD_VAL')->join('left join tb_ms_cmn_cd a on a.CD=t.our_company')->where(['order_id'=>$relevance['order_id']])->find();
        $this->predict      = M('predict_profit','tb_pur_')->where(['predict_id'=>$relevance['predict_id']])->find();
        $this->payable      = M('payable','tb_pur_')->where(['payable_id'=>$relevance['payable_id']])->find();

        $message_payable        = $this->fetch('Payable/payable_email');
        $email                  = new SMSEmail();
        return $email->sendEmail(C('purchase_payable_notice_email'),'应付提醒',$message_payable);
    }

}