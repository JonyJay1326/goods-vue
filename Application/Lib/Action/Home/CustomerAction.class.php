<?php

/**
 * 客户管理
 *
 * author: huaxin
 *
 */
class CustomerAction extends BaseAction {

    public function _initialize(){
        parent::_initialize();
        import('ORG.Util.Page');// 导入分页类
    }

    public function index() {

        die();
    }

    /**
     *  申请资质记录
     *
     */
    public function application(){
        $filterArr = array();
        $filterArr['keywords']     = I('keywords');
        $model = new ZZmsmerchantapplicationsModel();
        $conditions = $model->searchModel($filterArr);
        $count = $model->where($conditions)->count();
        $page = new Page($count, (empty(I('get.page_num'))?10:I('get.page_num')));
        $pages = $page->show();
        $list = $model->limit($page->firstRow, $page->listRows)->where($conditions)->order('id desc')->select();

        $this->assign('pages', $pages);
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('filterArr', $filterArr);
        $this->display();
    }

    /**
     *  Edit application
     *
     */
    public function editapplication(){
        $id = I('get.id');
        $edit_info = D('ZZmsmerchantapplications')->where(array("id"=>$id))->find();
        if(empty($edit_info)){
            echo 'None';
            die();
        }
        $action = I('post.action');
        if($action=='editsubmit'){
            $edit_data = array();
            $edit_data['apply_status'] = I('post.apply_status');
            $status = D('ZZmsmerchantapplications')->where(array("id"=>$id))->data($edit_data)->save();
            Mainfunc::backurl_goback();
            Mainfunc::show_msg('',1);
            die();
        }

        $this->assign('edit_info', $edit_info);
        $this->display();
    }

    /**
     *  B5C客户
     *
     */
    public function b5ckehu(){

        // all params
        $filterArr = array();
        $filterArr['time_type']     = I('time_type');
        $filterArr['starttime']     = I('starttime');
        $filterArr['endtime']       = I('endtime');
        $filterArr['keyword_type']  = I('keyword_type');
        $filterArr['keywords']      = I('keywords');
        $filterArr['by_stat_cd']    = I('by_stat_cd');
        $filterArr['by_kehu_type']  = I('by_kehu_type');
        $filterArr['by_sale_ch']    = I('by_sale_ch');
        $filterArr['by_cat']        = I('by_cat');
        $filterArr['by_price_type'] = I('by_price_type');

        //model
        $ms_cust_obj = new ZZmscustModel();

        $where = array();
        $whereby = array();
        $orderby = "";
        $orderby = " SYS_REG_DTTM DESC ";

        //default where
        // $whereby['CUST_STAT_CD'] = array('IN', $ms_cust_obj::$stat_cd_b5ckehu);
        // $whereby['_string'] = " parent_store_id=0 ";

        if($filterArr['by_stat_cd']){
            $where['CUST_STAT_CD'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_stat_cd'])));
        }

        if($filterArr['by_kehu_type']){
            $where['CUST_DIV_CD'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_kehu_type'])));
        }

        if($filterArr['by_sale_ch']){
            $where['DISTRI_CHANNEL'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_sale_ch'])));
        }

        if($filterArr['by_cat']){
            $where['INTENT_CAT'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_cat'])));
        }

        if($filterArr['by_price_type']){
            $where['PRICE_TYPE'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_price_type'])));
        }

        if($filterArr['keyword_type']!='' and $filterArr['keywords']!='' ) {
            $keywords = $filterArr['keywords'];
            $keywords = array('like','%'.trim($keywords).'%');
            switch($filterArr['keyword_type']){
                case 1:
                     $where['CUST_CP_NO'] = $keywords;
                    break;
                case 2:
                     $where['CUST_NICK_NM'] = $keywords;
                    break;
                case 3:
                     $where['CUST_EML'] = $keywords;
                    break;
                case 4:
                     $where['QQ_NUM'] = $keywords;
                    break;
                case 5:
                    $kehu_type_list = D("ZZmscmncd")->getValueFromName('上海负责人');
                    $key = array_search($filterArr['keywords'],$kehu_type_list);
                    if($key){
                        $where['SH_SALER'] = $key;
                    }else{
                        $where['SH_SALER'] = array('lt', 0);
                    }
                    break;
                case 6:
                     $where['CUST_ID'] = $keywords;
                    break;
            }
        }

        if($filterArr['time_type']){
            $where_temp = array();
            if ($filterArr['starttime'] && $filterArr['endtime']) {
                $where_temp = array(array('gt',$filterArr['starttime']),array('lt',$filterArr['endtime'].' 23:59:59'));
            } elseif($filterArr['starttime']) {
                $where_temp = array('gt', $filterArr['starttime']);
            } elseif($filterArr['endtime']) {
                $where_temp = array('lt', $filterArr['endtime'].' 23:59:59');
            }
            if($where_temp){
                if($filterArr['time_type']==1){
                    $where['SYS_REG_DTTM'] = $where_temp;
                }
                elseif($filterArr['time_type']==2){
                    $where['JOIN_APRV_DT'] = array(
                        array('egt',ZFun::keepNumber($filterArr['starttime'])),
                        array('elt',ZFun::keepNumber($filterArr['endtime'])),
                    );
                }
            }
        }



        $whereby = array_merge($whereby,$where);

        $count = $ms_cust_obj->getDatasCount($whereby);
        $page = new Page($count, 50);
        $pages = $page->show();

        $kehu_list = $ms_cust_obj->getDatas('*',$whereby,$orderby,$page->firstRow,$page->listRows);
        $kehu_list = $ms_cust_obj->showListStatusName($kehu_list);
       /* foreach ($kehu_list as $key => $value) {
            $kehu_list[$key]['CUST_CP_NO'] = CrypMobile::enCryp($kehu_list[$key]['CUST_CP_NO'])['data'];    //给它加密
        }*/

        //$kehu_list['CUST_CP_NO'] = CrypMobile::enCryp($kehu_list['CUST_CP_NO']);
        //定价类型
        $price_type_list = D("ZZmscmncd")->getValueFromName('PriceType');
        $this->assign('price_type_list',$price_type_list);

        //商品类目
        $cat_list = D("ZZmscmncat")->gainListByLevel(1);
        $this->assign('cat_list',$cat_list);

        //销售渠道
        $sale_ch_list = D("ZZmscmncd")->getValueFromName('SaleChannel');
        $this->assign('sale_ch_list',$sale_ch_list);

        //客户类型
        $kehu_type_list = D("ZZmscmncd")->getValueFromName('고객구분코드');

        $this->assign('kehu_type_list',$kehu_type_list);

        //客户状态
        $stat_cd_b5ckehu = $ms_cust_obj->indexStatCdOfB5ckehu();
        $this->assign('stat_cd_b5ckehu',$stat_cd_b5ckehu);

        // form url
        $form_base_url = Mainfunc::replace_url_by_arr($_SERVER['REQUEST_URI'],array('p',));
        $this->assign('form_base_url', $form_base_url);
        $form_base_arr = ZUtils::requestFromUrl($form_base_url);
        $this->assign('form_base_arr', $form_base_arr);
        // assign
        $this->assign('kehu_list',$kehu_list);
        $this->assign('count', $count);
        $this->assign('pages', $pages);
        $this->assign('filterArr', $filterArr);

        $this->display();

    }

    /**
     *  第三方客户
     *
     */
    public function thirdkehu(){
        // all params
        $filterArr = array();
        $filterArr['starttime']     = I('starttime');
        $filterArr['endtime']       = I('endtime');
        $filterArr['keyword_type']  = I('keyword_type');
        $filterArr['keywords']      = I('keywords');
        $filterArr['by_stat_cd']    = I('by_stat_cd');
        $filterArr['by_plat_cd']    = I('by_plat_cd');
        $filterArr['by_store']      = I('by_store');


        //model
        $ms_cust_obj = new ZZmscustModel();

        $where = array();
        $whereby = array();
        $orderby = "";

        //default where
        $whereby['CUST_STAT_CD'] = array('IN', $ms_cust_obj::$stat_cd_thirdkehu);
        $whereby['_string'] = " parent_store_id!=0 ";

        if (I('starttime') && I('endtime')) {
            $where['SYS_REG_DTTM'] = array(array('gt',I('starttime')),array('lt',I('endtime').' 23:59:59'));
        } elseif(I('starttime')) {
            $where['SYS_REG_DTTM'] = array('gt', I('starttime'));
        } elseif(I('endtime')) {
            $where['SYS_REG_DTTM'] = array('lt', I('endtime').' 23:59:59');
        }

        if($filterArr['keyword_type']!='' and $filterArr['keywords']!='' ) {
            $keywords = $filterArr['keywords'];
            $keywords = array('like','%'.trim($keywords).'%');
            switch($filterArr['keyword_type']){
                case 1:
                     $where['CUST_CP_NO'] = $keywords;
                    break;
                case 2:
                     $where['CUST_NICK_NM'] = $keywords;
                    break;
                case 3:
                     $where['CUST_EML'] = $keywords;
                    break;
                case 4:
                     $where['CUST_NM'] = $keywords;
                    break;
                case 5:
                     $where['CUST_ID'] = $keywords;
                    break;
                case 6:
                     $where['parent_cust_id'] = $keywords;
                    break;
                case 7:
                     $where['THIRD_UID'] = array('eq',$filterArr['keywords']);
                    break;
            }
        }

        if($filterArr['by_stat_cd']){
            $where['CUST_STAT_CD'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_stat_cd'])));
        }

        if($filterArr['by_plat_cd']){
            $where['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_plat_cd'])));
        }

        if($filterArr['by_store']){
            $where['parent_store_id'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_store'])));
        }

        $whereby = array_merge($whereby,$where);

        $count = $ms_cust_obj->getDatasCount($whereby);
        $page = new Page($count, 50);
        $pages = $page->show();

        $orderby = " SYS_REG_DTTM DESC ";
        $kehu_list = $ms_cust_obj->getDatas('*',$whereby,$orderby,$page->firstRow,$page->listRows);
        $kehu_list = $ms_cust_obj->showListStatusName($kehu_list);

        //客户状态
        $stat_cd_thirdkehu = $ms_cust_obj->indexStatCdOfThirdkehu();
        $this->assign('stat_cd_thirdkehu',$stat_cd_thirdkehu);

        //所属平台
        $cmn_cd_obj = D("ZZmscmncd");
        $plat_list = $cmn_cd_obj->getValueFromName(SALE_CHANNEL);
        $this->assign('plat_list',$plat_list);

        //所属店铺
        $store_list = D("ZZmsstore")->findLastStore();
        $this->assign('store_list',$store_list);

        // form url
        $form_base_url = Mainfunc::replace_url_by_arr($_SERVER['REQUEST_URI'],array('p',));
        $this->assign('form_base_url', $form_base_url);
        $form_base_arr = ZUtils::requestFromUrl($form_base_url);
        $this->assign('form_base_arr', $form_base_arr);

        // assign
        $this->assign('kehu_list',$kehu_list);
        $this->assign('count', $count);
        $this->assign('pages', $pages);
        $this->assign('filterArr', $filterArr);

        $this->display();
    }

    /**
     *  See and edit , kehu info
     *
     */
    public function editthirdkehu(){
        $cust_id = I('get.cust_id');
        $kehu_info = D('ZZmscust')->where(array("CUST_ID"=>$cust_id))->find();
        if(empty($kehu_info)){
            echo 'None';
            die();
        }


        $kehu_addr_list = D('ZZmscustaddr')->gainCustList($cust_id);
        $this->assign('kehu_addr_list', $kehu_addr_list);
        $this->assign('kehu_info', $kehu_info);
        $this->display();
    }

    /**
     *  Submit , kehu info
     *
     */
    public function submitthirdkehu(){
        $cust_id = I('post.cust_id');
        $kehu_info = D('ZZmscust')->where(array("CUST_ID"=>$cust_id))->find();
        if(empty($kehu_info)){
            echo 'None';
            die();
        }

        // do submit action
        $issubmit = I('post.issubmit');
        $kehu = isset($_POST['kehu'])?$_POST['kehu']:null;
        $kehu_addr = isset($_POST['kehu_addr'])?$_POST['kehu_addr']:null;
        if($issubmit==1){
            //User Info
            if($kehu){
                $kehu['is_recieve']     = isset($kehu['is_recieve'])?$kehu['is_recieve']:null;
                $kehu['recieve_types']  = isset($kehu['recieve_types'])?$kehu['recieve_types']:null;
                $kehu['is_recieve']     = intval($kehu['is_recieve']);
                $kehu['recieve_types']  = is_array($kehu['recieve_types'])?$kehu['recieve_types']:array();
                $kehu['recieve_types']  = implode(',',$kehu['recieve_types']);
                $data = $kehu;
                $status = D('ZZmscust')->where(array("CUST_ID"=>$cust_id))->data($data)->save();
            }
            //Receiving Info
            if($kehu_addr){
                $ids = isset($kehu_addr['ADDR_ID'])?$kehu_addr['ADDR_ID']:null;
                if($ids and is_array($ids)){
                    foreach($ids as $key=>$val){
                        $id = $val;
                        $data = array();
                        $data['RCVR_NM'] = isset($kehu_addr['RCVR_NM'][$key])?$kehu_addr['RCVR_NM'][$key]:null;
                        $data['RCVR_TEL'] = isset($kehu_addr['RCVR_TEL'][$key])?$kehu_addr['RCVR_TEL'][$key]:null;
                        $data['ADDR_DTL'] = isset($kehu_addr['ADDR_DTL'][$key])?$kehu_addr['ADDR_DTL'][$key]:null;
                        $status = D('ZZmscustaddr')->where(array("ADDR_ID"=>$id))->data($data)->save();
                    }
                }
            }
            Mainfunc::show_msg('',1);
            die();
        }
    }

    /**
     *  See and edit , kehu info (old, from java)
     *
     */
    public function editb5ckehu(){
        $cust_id = I('get.cust_id');
        $kehu_info = D('ZZmscust')->where(array("CUST_ID"=>$cust_id))->find();
        if(empty($kehu_info)){
            echo L('None');
            die();
        }

        //销售渠道
        $sale_ch_list = D("ZZmscmncd")->getValueFromName('SaleChannel');
        $this->assign('sale_ch_list',$sale_ch_list);
        //商品类目
        $cat_list = D("ZZmscmncat")->gainListByLevel(1);
        $this->assign('cat_list',$cat_list);
        //客户类型
        $kehu_type_list = D("ZZmscmncd")->getValueFromName('고객구분코드');
        $this->assign('kehu_type_list',$kehu_type_list);
        //定价类型
        $price_type_list = D("ZZmscmncd")->getValueFromName('PriceType');
        $this->assign('price_type_list',$price_type_list);
        //上海负责人
        $fuzeren_list = D("ZZmscmncd")->getValueFromName('上海负责人');
        $this->assign('fuzeren_list',$fuzeren_list);
        //所属平台
        $plat_list = D("ZZmscmncd")->getValueFromName(SALE_CHANNEL);
        $this->assign('plat_list',$plat_list);
        $kehu_info['CUST_CP_NO'] = CrypMobile::enCryp($kehu_info['CUST_CP_NO'])['data'];
        $this->assign('kehu_info', $kehu_info);
        $this->display();
    }

    /**
     *  Submit , submitb5ckehu
     *
     */
    public function submitb5ckehu(){
        $ajaxoutputs= array('info'=>'','status'=>'n');
        $cust_id = I('post.cust_id');
        $kehu_info = D('ZZmscust')->where(array("CUST_ID"=>$cust_id))->find();
        if(empty($kehu_info)){
            echo L('None');
            die();
        }

        // do submit action
        $issubmit = I('post.issubmit');
        $kehu = isset($_POST['kehu'])?$_POST['kehu']:null;
        if($issubmit==1){
            //User Info
            if($kehu){
                $kehu['store_name_arr'] = isset($kehu['store_name_arr'])?$kehu['store_name_arr']:null;
                if(is_array($kehu['store_name_arr'])){
                    $kehu['store_name_arr'] = ZFun::arrNoEmptyMix($kehu['store_name_arr']);
                    $kehu['STORE_NAME'] = implode(',',$kehu['store_name_arr']);
                }
                unset($kehu['store_name_arr']);

                $kehu['DISTRI_CHANNEL'] = isset($kehu['DISTRI_CHANNEL'])?$kehu['DISTRI_CHANNEL']:null;
                if(is_array($kehu['DISTRI_CHANNEL'])){
                    $kehu['DISTRI_CHANNEL'] = implode(',',$kehu['DISTRI_CHANNEL']);
                }

                $kehu['INTENT_CAT'] = isset($kehu['INTENT_CAT'])?$kehu['INTENT_CAT']:null;
                if(is_array($kehu['INTENT_CAT'])){
                    $kehu['INTENT_CAT'] = implode(',',$kehu['INTENT_CAT']);
                }
                
                $kehu['AUTH_SITE'] = isset($kehu['AUTH_SITE'])?$kehu['AUTH_SITE']:null;
                if(is_array($kehu['AUTH_SITE'])){
                    $kehu['AUTH_SITE'] = implode(',',$kehu['AUTH_SITE']);
                }

                //“审核时间”记录第一次将客户状态从“待提交”改为其他状态时的时间，之后再更改状态该时间不变
                if($kehu_info['CUST_STAT_CD']=='N000400100'){
                    if(empty($kehu_info['JOIN_APRV_DT'])){
                        if($kehu_info['CUST_STAT_CD']!=$kehu['CUST_STAT_CD']){
                            $kehu['JOIN_APRV_DT'] = date('Ymd');
                            $kehu['SYS_CHG_DTTM'] = date('Y-m-d H:i:s');
                        }
                    }
                }

                $status = D('ZZmscust')->where(array("CUST_ID"=>$cust_id))->data($kehu)->save();
                $ajaxoutputs['status_info'] = $status;
                if($status!==false){
                    $ajaxoutputs['status'] = 'y';
                }
            }
        }

        // var_dump($_REQUEST);
        if($this->isAjax()){
            $this->ajaxReturn($ajaxoutputs,'JSON');
        }

    }
    
    /**
     *  客户列表201705
     *
     */
    public function kehulist(){
        // all params
        $filterArr = array();
        $filterArr['regstarttime']      = I('regstarttime');
        $filterArr['regendtime']        = I('regendtime');
        $filterArr['loginstarttime']    = I('loginstarttime');
        $filterArr['loginendtime']      = I('loginendtime');
        $filterArr['by_stat_cd']    = I('by_stat_cd');
        $filterArr['by_plat_cd']    = I('by_plat_cd');
        $filterArr['by_store']      = I('by_store');
        $filterArr['keyword_type']  = I('keyword_type');
        $filterArr['keywords']      = I('keywords');

        //model
        $list_obj = new ZZmsthrdcustModel();

        $where = array();
        $whereby = array();
        $orderby = "";
        $orderby = " CREATE_TIME DESC ";
        $whereby['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array(PLAT_GSHOPPER_KR)));

        if($filterArr['by_stat_cd']){
            $where['CUST_STAT_CD'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_stat_cd'])));
        }

        if($filterArr['by_plat_cd']){
            $where['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_plat_cd'])));
        }

        if($filterArr['by_store']){
            $where['parent_store_id'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_store'])));
        }

        //JOIN_REQ_DT
        if($filterArr['regstarttime'] or $filterArr['regendtime']) {
            if($filterArr['regstarttime'] && $filterArr['regendtime']) {
                $where['JOIN_REQ_DT'] = array(array('gt',($filterArr['regstarttime'])),array('lt',($filterArr['regendtime'])));
            } elseif($filterArr['regstarttime']) {
                $where['JOIN_REQ_DT'] = array('gt', ($filterArr['regstarttime']));
            } elseif($filterArr['regendtime']) {
                $where['JOIN_REQ_DT'] = array('lt', ($filterArr['regendtime']));
            }
        }

        if($filterArr['loginstarttime'] or $filterArr['loginendtime']) {
            if($filterArr['loginstarttime'] && $filterArr['loginendtime']) {
                $where['SYS_REG_DTTM'] = array(array('gt',($filterArr['loginstarttime'])),array('lt',($filterArr['loginendtime'])));
            } elseif($filterArr['loginstarttime']) {
                $where['SYS_REG_DTTM'] = array('gt', ($filterArr['loginstarttime']));
            } elseif($filterArr['loginendtime']) {
                $where['SYS_REG_DTTM'] = array('lt', ($filterArr['loginendtime']));
            }
        }

        if($filterArr['keyword_type']!='' and $filterArr['keywords']!='' ) {
            $keywords = $filterArr['keywords'];
            $keywords = array('like','%'.trim($keywords).'%');
            switch($filterArr['keyword_type']){
                case 1:
                    $where['CUST_EML'] = $keywords;
                    break;
                case 2:
                    $where['CUST_CP_NO'] = $keywords;
                    break;
            }
        }

        $whereby = array_merge($whereby,$where);

        $count = $list_obj
                ->where ( $whereby )
                ->count();
        $page = new Page($count, 10);
        $pages = $page->show();
        $list = $list_obj->field ( '*' )
                ->where ( $whereby )
                ->order( $orderby )
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        $helper_pagination = new helper_pagination(array('total'=>$count,'perpage'=>(empty(I('get.page_num'))?10:I('get.page_num')),'page_name'=>'p'), array());
        $show_perpage_block = $helper_pagination->show_perpage_block('page_num', (empty(I('get.page_num'))?10:I('get.page_num')));
        $this->assign('show_perpage_block',$show_perpage_block);

        //客户状态
        $stat_cd_list = D("ZZmscmncd")->getValueFromName('고객상태코드');
        $this->assign('stat_cd_list',$stat_cd_list);

        //所属平台
        $plat_list = D("ZZmscmncd")->getValueFromName(SALE_CHANNEL);
        foreach($plat_list as $k=>$v){
            if($k!=PLAT_GSHOPPER_KR){
                unset($plat_list[$k]);
            }
        }



        $this->assign('plat_list',$plat_list);

        //所属店铺
        $store_list = D("ZZmsstore")->findLastStore();
        $this->assign('store_list',$store_list);

        // form url
        $form_base_url = Mainfunc::replace_url_by_arr($_SERVER['REQUEST_URI'],array('p',));
        $this->assign('form_base_url', $form_base_url);
        $form_base_arr = ZUtils::requestFromUrl($form_base_url);
        $this->assign('form_base_arr', $form_base_arr);

        // assign
        $this->assign('list',$list);
        $this->assign('count', $count);
        $this->assign('pages', $pages);
        $this->assign('filterArr', $filterArr);

        $this->display();

    }

    public function editnewkehu(){
        $cust_id = I('get.cust_id');
        $kehu_info = D('ZZmsthrdcust')->where(array("CUST_ID"=>$cust_id))->find();
        if(empty($kehu_info)){
            echo L('None');
            die();
        }
        $this->assign('kehu_info', $kehu_info);
        $this->display();
    }

    public function submitnewkehu(){
        $ajaxoutputs= array('info'=>'','status'=>'n');
        $cust_id = I('post.cust_id');
        $kehu_info = D('ZZmsthrdcust')->where(array("CUST_ID"=>$cust_id))->find();
        if(empty($kehu_info)){
            $ajaxoutputs['info'] = 'None';
            if($this->isAjax()){
                $this->ajaxReturn($ajaxoutputs,'JSON');
            }
            echo $ajaxoutputs['info'];
            die();
        }

        // do submit action
        $issubmit = I('post.issubmit');
        $kehu = isset($_POST['kehu'])?$_POST['kehu']:null;
        if($issubmit==1){
            //User Info
            if($kehu){
                $kehu = D('ZZmsthrdcust')->check_thrd_cust_data($kehu);

                if(!empty($kehu['CUST_EML'])){
                    if(!isEmail($kehu['CUST_EML'])){
                        $ajaxoutputs['info'] = 'Wrong email';
                        if($this->isAjax()){
                            $this->ajaxReturn($ajaxoutputs,'JSON');
                        }
                        echo $ajaxoutputs['info'];
                        die();
                    }
                }

                $status = D('ZZmsthrdcust')->where(array("CUST_ID"=>$cust_id))->data($kehu)->save();
                $ajaxoutputs['status_info'] = $status;
                if($status!==false){
                    $ajaxoutputs['status'] = 'y';
                }
            }
        }

        // var_dump($_REQUEST);
        if($this->isAjax()){
            $this->ajaxReturn($ajaxoutputs,'JSON');
        }

    }

    /**
     * B2B2C客户管理
     * 
     */
    public function btbtc()
    {
        $model = new BTBTCModel();
        $queryParams = $this->getParams();
        $store = BaseModel::getBtbtcCustomerStore();
        $conditions = $model->searchModel($queryParams);
        $count = $model->where($conditions)->count();
        $page = new Page($count, 20);
        $pages = $page->show();
        $ret = $model->limit($page->firstRow, $page->listRows)->where($conditions)->order('CREATE_TIME desc')->select();
        //手机号加密
        foreach ($ret as $key => $value) {
            $ret[$key]['REL_TEL_NUM'] = CrypMobile::transformation($ret[$key]['REL_TEL_NUM']);;
        }
        
        $this->assign('store', $store);
        $this->assign('pages', $pages);
        $this->assign('count', $count);
        $this->assign('params', $queryParams);
        $this->assign('result', $ret);
        $this->display();
    }


    /**
     * B2B2C手机号解密
     *
     */

    public function decryption(){
        $num = $_POST['phone'];
        $back_tel_num = CrypMobile::deCryp($num);   
        echo  json_encode($back_tel_num); die;  //返回解密后的手机号
    }
    
    /**
     * 客户信息展示
     * 
     */
    public function show_customer()
    {
        $store = BaseModel::getBtbtcCustomerStore();
        $this->assign('store', $store);
        $model = new BTBTCModel();
        $queryParams = $this->getParams();
        $conditions ['receiver_cust_id'] = $queryParams['ID'];
        if ($queryParams['ORD_STAT_CD']) $conditions ['ORD_STAT_CD'] = $queryParams ['ORD_STAT_CD'];
        
        $orderModel = M('_ms_ord', 'tb_');
        $count = $orderModel->where($conditions)->count();
        
        $ret = $model->where('id = '. $queryParams['ID'])->find();
        $page = new Page($count, 20);
        $pages = $page->show();
        
        $orders = $orderModel->join('left join bbm_pay_order on tb_ms_ord.ORD_ID=bbm_pay_order.order_id')->order('tb_ms_ord.ESTM_RCP_REQ_DT desc')->limit($page->firstRow, $page->listRows)->where($conditions)->select();
        $m = M('ms_ord_guds_opt','tb_');
        
        $getOrderInfo = $orderModel->where('receiver_cust_id = '.$queryParams['ID'])->select();
        $n_orders = [];    
        foreach($orders as $key=>$ord){
            $guds = [];
            $guds = $m->join('left join tb_ms_guds on tb_ms_ord_guds_opt.GUDS_ID = tb_ms_guds.GUDS_ID')->where('tb_ms_ord_guds_opt.ORD_ID = "'.$ord['ORD_ID'].'"')->field('sum(tb_ms_ord_guds_opt.RMB_PRICE*tb_ms_ord_guds_opt.ORD_GUDS_QTY) as total_price,count("tb_ms_ord_guds_opt.ORD_ID") as GUDS_NUM,tb_ms_ord_guds_opt.RMB_PRICE,tb_ms_ord_guds_opt.ORD_GUDS_QTY,tb_ms_ord_guds_opt.ORD_GUDS_SALE_PRC,tb_ms_ord_guds_opt.RMB_PRICE,tb_ms_ord_guds_opt.GUDS_ID,tb_ms_ord_guds_opt.GUDS_OPT_ID,tb_ms_guds.GUDS_NM')->group('tb_ms_ord_guds_opt.ORD_ID')->select();
            $orders[$key]['total_price'] = $guds[0]['total_price'];
            $orders[$key]['GUDS_NUM'] = $guds[0]['GUDS_NUM'];
            $orders[$key]['RMB_PRICE'] = $guds[0]['RMB_PRICE'];
            $orders[$key]['ORD_GUDS_QTY'] = $guds[0]['ORD_GUDS_QTY'];
            $orders[$key]['ORD_GUDS_SALE_PRC'] = $guds[0]['ORD_GUDS_SALE_PRC'];
            $orders[$key]['GUDS_ID'] = $guds[0]['GUDS_ID'];
            $orders[$key]['GUDS_OPT_ID'] = $guds[0]['GUDS_OPT_ID'];
            $orders[$key]['GUDS_NM'] = $guds[0]['GUDS_NM'];
        }
        // b5c YT CN,其他US
        $wait_pay_num = $wait_pay_total_money = $cancel_order_num = $cancel_order_total_money = $paid_order_num = $paid_order_total_money = 0;
        $Xchr = M('_ms_xchr', 'tb_');
        $XchrRs = $Xchr->field('DATE_FORMAT(updated_time, "%Y%m%d") as time,USD_XCHR_AMT as uxa')->select();
        foreach ($getOrderInfo as $key => $order) {
            // 待付款，待确认、确认中及待付款订单
            if ($order ['ORD_STAT_CD'] == 'N000550200' or $order ['ORD_STAT_CD'] == 'N000550100' or $order ['ORD_STAT_CD'] == 'N000550300') {
                $wait_pay_num ++;
                if ($order['PLAT_FORM' == 'N000830200']) {
                        $wait_pay_total_money += $order ['PAY_AMOUNT'] * $XchrRs [$order ['ESTM_RCP_REQ_DT']];
                    } else {
                        $wait_pay_total_money += $order ['PAY_AMOUNT'];
                    }
            }
            // 已付款，待发货、待收货、已收货、已付尾款、交易成功订单
            if ($order ['ORD_STAT_CD'] == 'N000550400' or $order ['ORD_STAT_CD'] == 'N000550500' or $order ['ORD_STAT_CD'] == 'N000550600' or $order ['ORD_STAT_CD'] == 'N000550700' or $order ['ORD_STAT_CD'] == 'N000550800') {
                $paid_order_num ++;
                if ($order['PLAT_FORM' == 'N000830200']) {
                    $paid_order_total_money += $order ['PAY_AMOUNT'] * $XchrRs [$order ['ESTM_RCP_REQ_DT']];
                } else {
                    $paid_order_total_money += $order ['PAY_AMOUNT'];
                }
            }
            // 已取消交易关闭和交易取消订单
            if ($order ['ORD_STAT_CD'] == 'N000551000' or $order ['ORD_STAT_CD'] == 'N000550900') {
                 $cancel_order_num ++;
                if ($order['PLAT_FORM' == 'N000830200']) {
                    $cancel_order_total_money += $order ['PAY_AMOUNT'] * $XchrRs [$order ['ESTM_RCP_REQ_DT']];
                } else {
                    $cancel_order_total_money += $order ['PAY_AMOUNT'];
                }
            }
        }
        $ret ['wait_pay_num'] = $wait_pay_num;
        $ret ['wait_pay_total_money'] = $wait_pay_total_money;
        $ret ['cancel_order_num'] = $cancel_order_num;
        $ret ['cancel_order_total_money'] = $cancel_order_total_money;
        $ret ['paid_order_num'] = $paid_order_num;
        $ret ['paid_order_total_money'] = $paid_order_total_money;
        
        $detail_url = 'Customer/orderdetail';
        $this->assign('detail_url', $detail_url);
        $this->assign('params', $queryParams);
        $this->assign('pages', $pages);
        $this->assign('count', $count);
        $this->assign('model', $model);
        $this->assign('result', $ret);
        $this->assign('orders', $orders);
        $this->display();
    }
    
    /**
     * 订单详情
     * 
     */
    public function orderdetail()
    {
        if(empty($_GET['ordId'])){
            redirect(U('Public/error'),2,'无订单号');
            return false;
        }
        
        //print_r($_GET);exit();
        $orderWhere['tb_ms_ord.ORD_ID'] = I('get.ordId');
        $order = M('ms_ord','tb_');
        $orderField = 'tb_ms_ord.REMARK_MSG, tb_ms_ord.ORD_ID,tb_ms_ord.THIRD_ORDER_ID, tb_ms_ord.ORD_STAT_CD, tb_ms_ord.ORD_TYPE_CD, bbm_pay_order.cashier_version, bbm_pay_order.order_currency,
                        tb_ms_ord.PAY_AMOUNT, tb_ms_ord.DISCOUNT_MN, tb_ms_ord.DLV_AMT, tb_ms_ord.TARIFF, tb_ms_ord.PAY_AMOUNT, bbm_pay_order.payer_name,bbm_pay_callback.channel,
                        tb_ms_ord.PAY_ID,tb_ms_ord.PAY_SER_NM, tb_ms_ord.PAY_WAY,tb_ms_ord.CUST_ID, tb_ms_ord.ORD_CUST_NM, tb_ms_ord.ORD_CUST_NM,tb_ms_ord.ORD_CUST_CP_NO,
                        tb_ms_ord.ADPR_ADDR, tb_ms_ord.SENDER_INFO, tb_ms_ord.REQ_CONT, tb_ms_ord.REC_ID_CARD, tb_ms_ord.DLV_MODE_CD,
                        tb_ms_ord.BUYER_NM,tb_ms_ord.DELIVERY_WAREHOUSE,tb_ms_cust.CUST_NICK_NM,tb_ms_ord.REFUND_STAT_CD, tb_ms_ord.OA_NUM';

        $detail = $order->field($orderField)->join('left join bbm_pay_order on tb_ms_ord.ORD_ID=bbm_pay_order.order_id')->
        join('left join bbm_pay_callback  on bbm_pay_callback.pay_id = bbm_pay_order.pay_id')->
                  join('left join tb_ms_cust on tb_ms_ord.CUST_ID=tb_ms_cust.CUST_ID')->where($orderWhere)->find();
        $type = 0;
        if($detail['ORD_STAT_CD'] == 'N000550400﻿'){
            $type = 1;
        }else{
            $type = 2;
        }
        $this->assign('type',$type);
        $detail['ORD_STAT_CD_NAME'] = L($detail['ORD_STAT_CD']);
        $detail['ORD_TYPE_CD_NAME'] = C($detail['ORD_TYPE_CD']);
        //订单商品list
        $gud =  M('ms_ord_guds_opt','tb_');
        $gudField = 'tb_ms_ord_guds_opt.SLLR_ID,tb_ms_ord_guds_opt.GUDS_ID,tb_ms_ord_guds_opt.GUDS_OPT_ID, tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_ord_guds_opt.THIRD_SUPPLIER,tb_ms_ord_guds_opt.THIRD_SERIAL_NUMBER,tb_ms_ord_guds_opt.THIRD_PAY_AMOUNTS, tb_ms_guds.GUDS_NM, tb_ms_ord_guds_opt.RMB_PRICE, tb_ms_ord_package.TRACKING_NUMBER,
                    tb_ms_ord_guds_opt.ORD_GUDS_QTY, tb_ms_ord_package.EXPE_COMPANY, tb_ms_guds_img.GUDS_IMG_CDN_ADDR, tb_ms_guds_opt.GUDS_OPT_VAL_MPNG';
        $gudWhere['tb_ms_ord_guds_opt.ORD_ID'] = I('get.ordId');
        $gudWhere['tb_ms_guds_img.GUDS_IMG_CD'] = 'N000080200';
        
        $gud_list = $gud->field($gudField)->join('left join tb_ms_guds_opt on tb_ms_ord_guds_opt.GUDS_OPT_ID=tb_ms_guds_opt.GUDS_OPT_ID')->
                    join('left join tb_ms_guds on tb_ms_ord_guds_opt.GUDS_ID=tb_ms_guds.GUDS_ID')->
                    join('left join tb_ms_guds_img on tb_ms_ord_guds_opt.GUDS_ID=tb_ms_guds_img.GUDS_ID')->
                    join('left join tb_ms_ord_package on tb_ms_ord_guds_opt.ORD_ID=tb_ms_ord_package.ORD_ID')->where($gudWhere)->
                    group('tb_ms_ord_guds_opt.GUDS_OPT_ID')->select();
                    
        foreach ($gud_list as $k => $v){
            $gud_list[$k]['SKU']= $this->getGudval($v['GUDS_OPT_VAL_MPNG']);
            $detail['gudAmount'] += $v['RMB_PRICE']*$v['ORD_GUDS_QTY'];
            $gud_list[$k]['ORD_GUDS_QTY'] = intval($v['ORD_GUDS_QTY']);
        }
        //订单log记录
        $logWhere['ORD_NO'] = I('get.ordId');
        $ModelLog = M('ms_ord_hist','sms_');
        $logField = 'ORD_HIST_REG_DTTM, ORD_STAT_CD, ORD_HIST_WRTR_EML, ORD_HIST_HIST_CONT';
        $logList = $ModelLog->field($logField)->where($logWhere)->order('ORD_HIST_SEQ desc')->select();
        $ret = $logList;
        $ret = array_column($ret, 'ORD_HIST_HIST_CONT', 'ORD_STAT_CD');
        $this->assign('refund_reason', $ret);
        $array['detail'] = $detail;
        $array['gudList'] = $gud_list;
        $array['logList'] = $logList;
        $EXPE_COMPANY = M('ms_cmn_cd','tb_')->where('CD_NM = "LOGISTICS_COMPANY"')->select();
        
        $this->assign('EXPE_COMPANY',$EXPE_COMPANY);
        $this->assign('detail', $array['detail']);
        $this->assign('gudList', $array['gudList']);
        $this->assign('logList', $array['logList']);
        $this->assign('type','1');
        $this->display();
    }
    
    function getGudval($str = ''){
        if(empty($str)){
            return false;
        }
        if(strstr($str, ';') != false) {
            $temp = explode(';', $str);
            foreach($temp as $key => $value){
                $array[] = explode(':', $value);
            }
        }else{
            $array = explode(':', $str);
        }
        $val = M('ms_opt_val', 'tb_');
        $attr = '';
        foreach ($array as $key => $value){
            if(is_array($value)){
                $valList = $val->join('left join tb_ms_opt on tb_ms_opt.OPT_ID=tb_ms_opt_val.OPT_ID')->
                        where('tb_ms_opt.OPT_ID='.$value[0].' and tb_ms_opt_val.OPT_VAL_ID='.$value[1])->find();
                $valList['OPT_CNS_NM'] = $value[0] == 8000? '标配' : $valList['OPT_CNS_NM'];
                $valList['OPT_VAL_CNS_NM'] = $value[1] == 800000? '标配' : $valList['OPT_VAL_CNS_NM'];
                $attr .= $valList['OPT_CNS_NM'].':'.$valList['OPT_VAL_CNS_NM'].'<br>';
            }else{
                $valList = $val->join('left join tb_ms_opt on tb_ms_opt.OPT_ID=tb_ms_opt_val.OPT_ID')->
                        where('tb_ms_opt.OPT_ID='.$array[0].' and tb_ms_opt_val.OPT_VAL_ID='.$array[1])->find();
                $valList['OPT_CNS_NM'] = $array[0] == 8000? '标配' : $valList['OPT_CNS_NM'];
                $valList['OPT_VAL_CNS_NM'] = $array[1] == 800000? '标配' : $valList['OPT_VAL_CNS_NM'];
                $attr = $valList['OPT_CNS_NM'].':'.$valList['OPT_VAL_CNS_NM'].'<br>';
                break;
            }
        }
        return $attr;
    }
}
