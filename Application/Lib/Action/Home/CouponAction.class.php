<?php

/**
 * 营销管理 coupon
 *
 * author: huaxin
 *
 */
class CouponAction extends BaseAction {

    public function _initialize(){
        parent::_initialize();
        import('ORG.Util.Page');// 导入分页类
    }

    public function index() {

        die();
    }

    public function manage() {
        // all params
        $filterArr = array();
        $filterArr['starttime']     = I('starttime');
        $filterArr['endtime']       = I('endtime');
        $filterArr['keyword_type']  = I('keyword_type');
        $filterArr['keywords']      = I('keywords');
        $filterArr['by_plat_cd']    = I('by_plat_cd');
        $filterArr['by_scope']      = I('by_scope');
        $filterArr['by_deliver_way']= I('by_deliver_way');


        //model
        $ms_coupon_obj = D("ZZmscoupons");

        $where = array();
        $whereby = array();
        $orderby = " create_time DESC ";

        // search
        if ($filterArr['starttime'] && $filterArr['endtime']) {
            $where['create_time'] = array(
                array('gt',strtotime($filterArr['starttime'])),
                array('lt',strtotime($filterArr['endtime'].' 23:59:59'))
            );
        } elseif($filterArr['starttime']) {
            $where['create_time'] = array('gt', strtotime($filterArr['starttime']));
        } elseif($filterArr['endtime']) {
            $where['create_time'] = array('lt', strtotime($filterArr['endtime'].' 23:59:59'));
        }

        if($filterArr['keyword_type']!='' and $filterArr['keywords']!='' ) {
            $keywords = $filterArr['keywords'];
            $keywords = array('like','%'.trim($keywords).'%');
            switch($filterArr['keyword_type']){
                case 1:
                     $where['name'] = $keywords;
                    break;
                case 2:
                     $where['code'] = $keywords;
                    break;
                case 3:
                    $Admin = D("Admin");
                    $where = array(
                        'M_NAME' => trim($filterArr['keywords']),
                    );
                    $detail = $Admin->where($where)->find();
                    if($detail['M_ID']){
                        $where['create_admin_id'] = $detail['M_ID'];
                    }else{
                        $where['create_admin_id'] = array('lt', 0);
                    }
                    break;
            }
        }

        if($filterArr['by_plat_cd']){
            $where['plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_plat_cd'])));
        }

        if($filterArr['by_scope']){
            $where['use_scope_type'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_scope'])));
        }

        if($filterArr['by_deliver_way']){
            $where['delivering_way_type'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_deliver_way'])));
        }


        $whereby = array_merge($whereby,$where);

        $count = $ms_coupon_obj
                ->where ( $whereby )
                ->count();
        $page = new Page($count, 50);
        $pages = $page->show();

        $list = $ms_coupon_obj->field ( '*' )
                ->where ( $whereby )
                ->order( $orderby )
                ->limit($page->firstRow.','.$page->listRows)
                ->select();


        //所属平台
        $cmn_cd_obj = D("ZZmscmncd");
        $plat_list = $cmn_cd_obj->getValueFromName('自营平台');
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
        $cmn_cd_obj = D("ZZmscmncd");
        $plat_list = $cmn_cd_obj->getValueFromName('自营平台');
        $this->assign('plat_list',$plat_list);

        $this->assign('is_new',1);
        $this->display();
    }

    public function edit() {
        $edit_id = isset($_REQUEST['edit_id'])?$_REQUEST['edit_id']:null;

        $edit_data = D("ZZmscoupons")->find($edit_id);
        if(empty($edit_data)){
            ZFun::js_back('None',1);
        }

        //所属平台
        $cmn_cd_obj = D("ZZmscmncd");
        $plat_list = $cmn_cd_obj->getValueFromName('自营平台');
        $this->assign('plat_list',$plat_list);

        $this->assign('is_new',0);
        $this->assign('edit_id',$edit_id);
        $this->assign('edit_data',$edit_data);
        $this->display('add');
    }

    public function submitadd() {
        $ajaxoutputs= array('info'=>'','status'=>'n');
        $coupon = array();
        // do submit action
        $isAjax = $this->isAjax();
        $issubmit = I('post.issubmit');
        if($issubmit==1){

            $coupon = D('ZZmscoupons')->check_data_pass($_POST);
            if($coupon['is_error']){
                if($this->isAjax()){
                    $ajaxoutputs['info'] = $coupon['err_msg'];
                    echo json_encode($ajaxoutputs);
                    die();
                }
                ZFun::js_back($coupon['err_msg'],1);
                die();
            }
            $coupon = $coupon['data'];
            $status = D('ZZmscoupons')->data($coupon)->add();
            if($status){
                $insertId = D('ZZmscoupons')->getLastInsID();
                D('ZZmscoupons')->check_remain_num($insertId);
            }
            if($this->isAjax()){
                $ajaxoutputs['status'] = 'y';
                $ajaxoutputs['url'] = U('Coupon/manage');
                echo json_encode($ajaxoutputs);
                die();
            }
            ZUtils::js_redirect(U('Coupon/manage'));
            die();
        }

    }

    public function submitedit() {
        $ajaxoutputs= array('info'=>'','status'=>'n');
        $edit_id = isset($_REQUEST['edit_id'])?$_REQUEST['edit_id']:null;
        $edit_data = D("ZZmscoupons")->find($edit_id);
        if(empty($edit_data)){
            ZFun::js_back('None',1);
        }
        $coupon = array();
        // do submit action
        $issubmit = I('post.issubmit');
        if($issubmit==1){
            $coupon = D('ZZmscoupons')->check_data_pass($_POST,$edit_data);
            if($coupon['is_error']){
                if($this->isAjax()){
                    $ajaxoutputs['info'] = $coupon['err_msg'];
                    echo json_encode($ajaxoutputs);
                    die();
                }
                ZFun::js_back($coupon['err_msg'],1);
                die();
            }

            $coupon = $coupon['data'];
            $status = D('ZZmscoupons')->where(array("id"=>$edit_id))->data($coupon)->save();
            D('ZZmscoupons')->check_remain_num($edit_id);
            if($this->isAjax()){
                $ajaxoutputs['status'] = 'y';
                echo json_encode($ajaxoutputs);
                die();
            }
            Mainfunc::backurl_goback();
            Mainfunc::show_msg('',1);
            die();
        }

    }





}











