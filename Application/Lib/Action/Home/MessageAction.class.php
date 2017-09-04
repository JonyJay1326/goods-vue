<?php

/**
 * 消息列表 Message
 *
 * author: huaxin
 *
 */
class MessageAction extends BaseAction {

    public function _initialize(){
        parent::_initialize();
        import('ORG.Util.Page');// 导入分页类
    }

    public function index() {

        die();
    }

    public function testsend(){
        $tmp = D('ZZmsmessages')->do_cron_send();
        var_dump($tmp);
        $this->display();
    }

    public function chooseuserall() {
        // //model
        // $ms_cust_obj = new ZZmscustModel();

        // $where = array();
        // $whereby = array();
        // $orderby = "";

        // $filterArr = array();
        // $filterArr['by_parent_plat_cd']     = I('by_parent_plat_cd');

        // if($filterArr['by_parent_plat_cd']){
        //     $where['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_parent_plat_cd'])));
        // }

        // //default where
        // $whereby = array_merge($whereby,$where);

        // $count = $ms_cust_obj->getDatasCount($whereby);
        // $page = new Page($count, 50);
        // $pages = $page->show();


        // $ajaxoutputs= array('info'=>'','status'=>'n');
        // if($this->isAjax()){
        //     $ajaxoutputs['status'] = 'y';
        //     $ajaxoutputs['info'] = $count;
        //     $this->ajaxReturn($ajaxoutputs,'info',1);
        // }

    }

    public function chooseuserlist() {

        // //model
        // $ms_cust_obj = new ZZmscustModel();

        // $where = array();
        // $whereby = array();
        // $orderby = "";

        // $filterArr = array();
        // $filterArr['by_parent_plat_cd']     = I('by_parent_plat_cd');

        // if($filterArr['by_parent_plat_cd']){
        //     $where['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_parent_plat_cd'])));
        // }

        // //default where
        // $whereby = array_merge($whereby,$where);

        // $count = $ms_cust_obj->getDatasCount($whereby);
        // $page = new Page($count, 10);
        // $pages = $page->show();

        // $orderby = " SYS_REG_DTTM DESC ";
        // $list = $ms_cust_obj->getDatas('*',$whereby,$orderby,$page->firstRow,$page->listRows);
        // $list = $ms_cust_obj->showListStatusName($list);

        // // assign
        // $this->assign('list',$list);
        // $this->assign('count', $count);
        // $this->assign('pages', $pages);

        // $this->display();
    }

    /**
     *  get ms thrd cust data count
     *
     */
    public function choosenewuserall() {
        $msg_id = I('msg_id');
        $where = array();
        $whereby = array();
        $orderby = "";
        $filterArr = array();
        $filterArr['by_parent_plat_cd']     = I('by_parent_plat_cd');
        if($filterArr['by_parent_plat_cd']){
            $where['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_parent_plat_cd'])));
        }
        //default where
        $whereby = array_merge($whereby,$where);
        $datas = D('ZZmsthrdcust')->find_msg_user(1,null,null,$where,$msg_id);
        $count = $datas['count'];
        $ajaxoutputs= array('info'=>'','status'=>'n');
        if($this->isAjax()){
            $ajaxoutputs['status'] = 'y';
            $ajaxoutputs['info'] = $count;
            $this->ajaxReturn($ajaxoutputs,'info',1);
        }
    }

    public function choosenewuserlist() {
        $msg_id = I('msg_id');

        $where = array();
        $whereby = array();
        $orderby = "";

        $filterArr = array();
        $filterArr['by_parent_plat_cd']     = I('by_parent_plat_cd');

        if($filterArr['by_parent_plat_cd']){
            $where['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_parent_plat_cd'])));
        }

        $perpage    = (empty(I('get.page_num'))?10:I('get.page_num'));
        $page       = isset($_GET['p'])?$_GET['p']:1;
        $page       = (intval($page)>=1)?intval($page):1;
        $firstRow = ($page-1)*$perpage;
        $listRows = $perpage;

        $datas = D('ZZmsthrdcust')->find_msg_user(0,$firstRow,$listRows,$where,$msg_id);
        $count = $datas['count'];
        $list = $datas['list'];

        $page = new Page($count, (empty(I('get.page_num'))?10:I('get.page_num')));
        $pages = $page->show();

        $helper_pagination = new helper_pagination(array('total'=>$count,'perpage'=>(empty(I('get.page_num'))?10:I('get.page_num')),'page_name'=>'p'), array());
        $show_perpage_block = $helper_pagination->show_perpage_block('page_num', (empty(I('get.page_num'))?10:I('get.page_num')));
        $this->assign('show_perpage_block',$show_perpage_block);

        // assign
        $this->assign('list',$list);
        $this->assign('count', $count);
        $this->assign('pages', $pages);

        $this->display();
    }

    public function msglist() {
        // all params
        $filterArr = array();
        $filterArr['by_plat_cd']    = I('by_plat_cd');
        $filterArr['by_title']      = I('by_title');
        $filterArr['by_status']     = I('by_status');
        $filterArr['starttime']     = I('starttime');
        $filterArr['endtime']       = I('endtime');
        $filterArr['by_is_all']     = I('by_is_all');
        $filterArr['by_choose_time']= I('by_choose_time');

        if($filterArr['by_plat_cd']==''){
            if(!isset($_REQUEST['by_plat_cd'])){
                $filterArr['by_plat_cd'] = PLAT_GSHOPPER_KR;
            }
        }

        //model
        $ms_coupon_obj = D("ZZmsmessages");

        $where = array();
        $whereby = array();
        $orderby = " id DESC ";

        // search
        if($filterArr['by_plat_cd']){
            $where['plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_plat_cd'])));
        }
        if($filterArr['by_title']){
            $keywords = $filterArr['by_title'];
            $where['title'] = array('like','%'.trim($keywords).'%');
        }
        if(strlen($filterArr['by_status'])>0){
            $filterArr['by_status'] = intval($filterArr['by_status']);
            $where['send_status'] = $filterArr['by_status'];
        }
        if($filterArr['starttime'] or $filterArr['endtime']) {
            if($filterArr['starttime'] && $filterArr['endtime']) {
                $where['send_time'] = array(array('gt',strtotime($filterArr['starttime'])),array('lt',strtotime($filterArr['endtime'])));
            } elseif(I('starttime')) {
                $where['send_time'] = array('gt', strtotime($filterArr['starttime']));
            } elseif(I('endtime')) {
                $where['send_time'] = array('lt', strtotime($filterArr['endtime']));
            }
        }
        if(strlen($filterArr['by_is_all'])>0){
            $filterArr['by_is_all'] = intval($filterArr['by_is_all']);
            $where['is_all_send'] = $filterArr['by_is_all'];
        }
        if(strlen($filterArr['by_choose_time'])>0){
            $filterArr['by_choose_time'] = intval($filterArr['by_choose_time']);
            if($filterArr['by_choose_time']==1){
                $filterArr['starttime'] = date('Y-m-d').' 00:00:00';
                $filterArr['endtime'] = date('Y-m-d').' 23:59:59';
                $where['send_time'] = array(array('gt',strtotime($filterArr['starttime'])),array('lt',strtotime($filterArr['endtime'])));
            }
            elseif($filterArr['by_choose_time']==2){
                $filterArr['starttime'] = date('Y-m-d',time()-7*24*3600).' 00:00:00';
                $filterArr['endtime'] = date('Y-m-d').' 23:59:59';
                $where['send_time'] = array(array('gt',strtotime($filterArr['starttime'])),array('lt',strtotime($filterArr['endtime'])));
            }
            elseif($filterArr['by_choose_time']==3){
                $filterArr['starttime'] = date('Y-m-d', mktime(0,0,0,date('m')-1,date('d')+1,date('Y'))).' 00:00:00';
                $filterArr['endtime'] = date('Y-m-d').' 23:59:59';
                $where['send_time'] = array(array('gt',strtotime($filterArr['starttime'])),array('lt',strtotime($filterArr['endtime'])));
            }
            elseif($filterArr['by_choose_time']==4){
                $filterArr['starttime'] = date('Y-m-d', mktime(0,0,0,date('m')-3,date('d')+1,date('Y'))).' 00:00:00';
                $filterArr['endtime'] = date('Y-m-d').' 23:59:59';
                $where['send_time'] = array(array('gt',strtotime($filterArr['starttime'])),array('lt',strtotime($filterArr['endtime'])));
            }
            elseif($filterArr['by_choose_time']==5){
                $filterArr['starttime'] = date('Y-m-d', mktime(0,0,0,date('m')-6,date('d')+1,date('Y'))).' 00:00:00';
                $filterArr['endtime'] = date('Y-m-d').' 23:59:59';
                $where['send_time'] = array(array('gt',strtotime($filterArr['starttime'])),array('lt',strtotime($filterArr['endtime'])));
            }
            elseif($filterArr['by_choose_time']==6){
                $filterArr['starttime'] = date('Y-m-d', mktime(0,0,0,date('m')-12,date('d')+1,date('Y'))).' 00:00:00';
                $filterArr['endtime'] = date('Y-m-d').' 23:59:59';
                $where['send_time'] = array(array('gt',strtotime($filterArr['starttime'])),array('lt',strtotime($filterArr['endtime'])));
            }
        }


        $whereby = array_merge($whereby,$where);

        $count = $ms_coupon_obj
                ->where ( $whereby )
                ->count();
        $page = new Page($count, (empty(I('get.page_num'))?10:I('get.page_num')));
        $pages = $page->show();

        $list = $ms_coupon_obj->field ( '*' )
                ->where ( $whereby )
                ->order( $orderby )
                ->limit($page->firstRow.','.$page->listRows)
                ->select();

        $helper_pagination = new helper_pagination(array('total'=>$count,'perpage'=>(empty(I('get.page_num'))?10:I('get.page_num')),'page_name'=>'p'), array());
        $show_perpage_block = $helper_pagination->show_perpage_block('page_num', (empty(I('get.page_num'))?10:I('get.page_num')));
        $this->assign('show_perpage_block',$show_perpage_block);

        //所属平台
        $plat_list = D("ZZmscmncd")->getValueFromName(SALE_CHANNEL);
        $this->assign('plat_list',$plat_list);

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

    public function add() {

        //所属平台
        $plat_list = D("ZZmscmncd")->getValueFromName(SALE_CHANNEL);
        $this->assign('plat_list',$plat_list);

        $this->assign('is_new',1);
        $this->display();
    }

    public function edit() {
        $edit_id = isset($_REQUEST['edit_id'])?$_REQUEST['edit_id']:null;

        $edit_data = D("ZZmsmessages")->find($edit_id);
        if(empty($edit_data)){
            ZFun::js_back('None',1);
        }

        //所属平台
        $plat_list = D("ZZmscmncd")->getValueFromName(SALE_CHANNEL);
        $this->assign('plat_list',$plat_list);

        $this->assign('is_new',0);
        $this->assign('edit_id',$edit_id);
        $this->assign('edit_data',$edit_data);
        $this->display('add');
    }

    public function submitadd() {
        $ajaxoutputs= array('info'=>'','status'=>'n');

        // do submit action
        $isAjax = $this->isAjax();
        $issubmit = I('post.issubmit');
        if($issubmit==1){

            $one_data = D('ZZmsmessages')->check_message_pass($_POST);
            if($one_data['is_error']){
                if($this->isAjax()){
                    $ajaxoutputs['info'] = $one_data['err_msg'];
                    echo json_encode($ajaxoutputs);
                    die();
                }
                ZFun::js_back($one_data['err_msg'],1);
                die();
            }

            //check img
            $img_data = D('ZZmsmessages')->check_data_img($_FILES);
            if($img_data['is_error']){
                if($this->isAjax()){
                    $ajaxoutputs['info'] = $img_data['err_msg'];
                    echo json_encode($ajaxoutputs);
                    die();
                }
                ZFun::js_back($img_data['err_msg'],1);
                die();
            }

            $one_data = $one_data['data'];
            if($img_data['data']){
                $one_data['pic'] = $img_data['data'];
            }

            $status = D('ZZmsmessages')->data($one_data)->add();
            if(!$status){
            }
            // relate mark msg send tb
            $msg_id = D('ZZmsmessages')->getLastInsID();
            $temp = D('ZZmsmessages')->relate_message_send($one_data, $msg_id);

            if($this->isAjax()){
                $ajaxoutputs['status'] = 'y';
                echo json_encode($ajaxoutputs);
                die();
            }
            ZUtils::js_redirect(U('Message/msglist'));
            die();
        }

    }

    public function submitedit() {
        $ajaxoutputs= array('info'=>'','status'=>'n');

        $edit_id = isset($_REQUEST['edit_id'])?$_REQUEST['edit_id']:null;

        $edit_data = D("ZZmsmessages")->find($edit_id);
        if(empty($edit_data)){
            ZFun::js_back('None',1);
        }

        // do submit action
        $issubmit = I('post.issubmit');
        if($issubmit==1){

            $one_data = D('ZZmsmessages')->check_message_pass($_POST,$edit_data);
            if($one_data['is_error']){
                if($this->isAjax()){
                    $ajaxoutputs['info'] = $one_data['err_msg'];
                    echo json_encode($ajaxoutputs);
                    die();
                }
                ZFun::js_back($one_data['err_msg'],1);
                die();
            }

            //check img
            $img_data = D('ZZmsmessages')->check_data_img($_FILES);
            if($img_data['is_error']){
                if($this->isAjax()){
                    $ajaxoutputs['info'] = $img_data['err_msg'];
                    echo json_encode($ajaxoutputs);
                    die();
                }
                ZFun::js_back($img_data['err_msg'],1);
                die();
            }

            $one_data = $one_data['data'];
            if($img_data['data']){
                $one_data['pic'] = $img_data['data'];
            }

            $status = D('ZZmsmessages')->where(array("id"=>$edit_id))->data($one_data)->save();
            if(!$status){
            }
            // relate mark msg send tb
            $msg_id = $edit_id;
            $temp = D('ZZmsmessages')->relate_message_send($one_data, $msg_id);

            if($this->isAjax()){
                $ajaxoutputs['status'] = 'y';
                echo json_encode($ajaxoutputs);
                die();
            }

            // Mainfunc::backurl_goback();
            Mainfunc::show_msg('',1);
            die();
        }

    }

    public function submitdelete() {
        $ajaxoutputs= array('info'=>'','status'=>'n');

        $edit_id = isset($_REQUEST['edit_id'])?$_REQUEST['edit_id']:null;

        $edit_data = D("ZZmsmessages")->find($edit_id);
        if(empty($edit_data)){
            ZFun::js_back('None',1);
        }

        $del_data = array();
        $del_data['send_status'] = 0;
        $status = D('ZZmsmessages')->where(array("id"=>$edit_id))->data($del_data)->save();

        Mainfunc::backurl_goback();
        Mainfunc::show_msg('',1);
        die();
    }

    public function deletepic(){
        $ajaxoutputs= array('info'=>'','status'=>'n');
        $msgId = isset($_REQUEST['msgId'])?$_REQUEST['msgId']:null;
        $check_data = D("ZZmsmessages")->find($msgId);

        if($check_data){
            $update_data = array();
            $update_data['pic'] = '';
            $status = D('ZZmsmessages')->where(array("id"=>$msgId))->data($update_data)->save();
        }

        if($this->isAjax()){
            $ajaxoutputs['status'] = 'y';
            $this->ajaxReturn($ajaxoutputs,'info',1);
        }

    }

    public function uploadpic(){
        $ajaxoutputs= array('info'=>'','status'=>'n');
        $ajaxoutputs['pic'] = '';

        if(!empty($_FILES['upload_file'])){
            $onefile['file_pic'] = $_FILES['upload_file'];
            $img_data = D('ZZmsmessages')->check_data_img($onefile);

            if($img_data['is_error']){
                $ajaxoutputs['info'] = $img_data['err_msg'];
            }else{
                $ajaxoutputs['pic'] = $img_data['data'];
                $ajaxoutputs['status'] = 'y';
            }

        }

        if($this->isAjax()){
            echo json_encode($ajaxoutputs);
            die();
        }
        ZFun::js_back('',1);
        die();

    }


}











