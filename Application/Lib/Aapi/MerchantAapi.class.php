<?php
/**
 * 
 * User: 
 * Date: 
 * Time: 
 */

class MerchantAapi extends Action {

    public function index(){
        return '';
    }

    /**
     *  Input Api - register
     *  Post data : name , tel
     *
     */
    public function register(){
        // Response | 返回参数说明
        $ret = array();
        $ret['code'] = 0;
        $ret['msg'] = '';
        $ret['data'] = null;

        $post_data = $_POST;

        $ZZmsmerchantapplications = new ZZmsmerchantapplicationsModel();

        $post_data['name']  = isset($post_data['name'])?$post_data['name']:'';
        $post_data['tel']   = isset($post_data['tel'])?$post_data['tel']:'';
        $post_data['name']  = trim($post_data['name']);
        $post_data['tel']   = trim($post_data['tel']);

        $verify=new Verify();
        $check = $verify->isUsername($post_data['name']);
        if(!$check){
            $ret['code'] = 50000000;
            $ret['msg'] = '名称不规范';
            return $ret;
        }
        $check1 = $verify->isMobile($post_data['tel']);
        $check2 = $verify->isTelephone($post_data['tel']);
        $check3 = $verify->isPhoneOfUs($post_data['tel']);
        $check4 = $verify->isTelephoneAll($post_data['tel']);
        if($check1 or $check2 or $check3 or $check4){
        }else{
            $ret['code'] = 50000000;
            $ret['msg'] = '电话不规范';
            return $ret;
        }

        $post_data['ip'] = ZUtils::getClientIP();

        if(!$ZZmsmerchantapplications->create($post_data)){
            $errTxt = $ZZmsmerchantapplications->getError();
            $ret['code'] = 50000000;
            $ret['msg'] = $errTxt;
            return $ret;
        }
        $status = $ZZmsmerchantapplications->add();
        $ret['data'] = $status;
        if($status===false){
            $ret['code'] = 50000001;
            $ret['msg'] = 'Sys error';
            return $ret;
        }

        return $ret;
    }

}

