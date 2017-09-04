<?php
/**
 * 
 * User: 
 * Date: 
 * Time: 
 */

class CodeAapi extends Action {

    public function index(){
        return 'test';
    }

    /**
     *  
     *
     */
    public function codeall(){
        // Response | 返回参数说明
        $ret = array();
        $ret['code'] = 0;
        $ret['msg'] = '';
        $ret['data'] = null;

        $ret['data'] = D('ZZmscmncd')->indexCode();

        return ZWebHttp::CallbackBegin(1).
                json_encode($ret).
                ZWebHttp::CallbackEnd(1);
    }

}

