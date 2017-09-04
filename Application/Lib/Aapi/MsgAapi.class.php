<?php
/**
 * 
 * User: 
 * Date: 
 * Time: 
 */

class MsgAapi extends Action {

    public function index(){
        return 'test';
    }

    /**
     *  对接 /mesage/appPushRes.json
     *
     */
    public function apppushresult(){
        $example = array(
            'code' => 2000,
            'msg' => 'success',
            'data' => array(
                'pushs' => array(
                    array('msgId'=>'1','custId'=>'123',),
                    array('msgId'=>'1','custId'=>'124',),
                ),
                'fail_pushs' => array(
                    array('msgId'=>'2','custId'=>'1023',),
                    array('msgId'=>'2','custId'=>'1024',),
                )
            ),
        );
        // Response | 返回参数说明
        // 名称  是否必须    类型  描述
        // code    not null    String  status code
        // msg     not null    string  message tips
        // data    can be null     string  data which response form b5c 
        $ret = array();
        $ret['code'] = 0;
        $ret['msg'] = '';
        $ret['data'] = null;

        $code = Mainfunc::chooseParam('code');
        $msg = Mainfunc::chooseParam('msg');
        $data = Mainfunc::chooseParam('data');
        $pushs = isset($data['pushs'])?$data['pushs']:null;
        $fail_pushs = isset($data['fail_pushs'])?$data['fail_pushs']:null;

        $is_empty = 0;
        if($code!=2000){
            $is_empty = 1;
        }
        if(!is_array($pushs)){
            $is_empty = 1;
        }
        if(!is_array($fail_pushs)){
            $is_empty = 1;
        }
        if($is_empty){
            $ret['code'] = 50000001;
            $ret['msg'] = 'Wrong request data.'.' '.'Example : '.json_encode($example);
            return ZWebHttp::CallbackBegin(1).
                json_encode($ret).
                ZWebHttp::CallbackEnd(1);
        }

        foreach($fail_pushs as $key=>$val){
            $status = D('ZZmsmessagepushres')->addPushResUser($val['msgId'],$val['custId'],1);
        }

        $ret['code'] = 2000;
        $ret['msg'] = 'success';
        return ZWebHttp::CallbackBegin(1).
                json_encode($ret).
                ZWebHttp::CallbackEnd(1);
    }

}

