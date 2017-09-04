<?php
/**
 *  App push message
 *
 */
class ZZmsmessagesModel extends CommonModel{

    protected $trueTableName = "tb_ms_messages";

    public $url_push = '/dataB5C/publishNotification.json'; // bk '/dataB5C/publishNotification.json' '/index.php/index/publishEvent.json'

    const Msg_del = 0;
    const Msg_draft = 1;
    const Msg_ongoing = 2;
    const Msg_sent = 3;
    public static function getStatusForMsg($key = null)
    {
        $items = [
            self::Msg_del => '已删除', 
            self::Msg_draft => '草稿',
            self::Msg_ongoing => '进行中',
            self::Msg_sent => '已发送',
        ];
        return DataMain::getItems($items, $key);
    }

    const Send_part = 0;
    const Send_all = 1;
    public static function getSendForUser($key = null)
    {
        $items = [
            self::Send_part => '部分客户', 
            self::Send_all => '所有用户',
        ];
        return DataMain::getItems($items, $key);
    }

    // 
    public function _before_insert(&$data,$options){
        parent::_before_insert($data, $options);
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        if(empty($data['update_admin_id'])){
            if(!empty($_SESSION['user_id'])){
                $data['update_admin_id'] = $_SESSION['user_id'];
            }
        }
    }

    // 
    public function _before_update(&$data,$options){
        parent::_before_update($data, $options);
        $data['update_time'] = time();
        if(!empty($_SESSION['user_id'])){
            $data['update_admin_id'] = $_SESSION['user_id'];
        }
    }

    public function _after_select(&$resultSet,$options){
        parent::_after_select($resultSet, $options);
    }

    /**
     *  check data : if ok, format data . if error , return error.
     * @param array $info 
     * @return array (is_error,err_msg,data)
     */
    public function check_message_pass($info,$edit_info=null){
        $ret = array();
        $is_error = 0;
        $err_msg = '';

        $info['plat_cd'] = isset($info['plat_cd'])?$info['plat_cd']:'';
        $info['title'] = isset($info['title'])?$info['title']:'';
        $info['content'] = isset($info['content'])?$info['content']:'';
        $info['pic'] = isset($info['pic'])?$info['pic']:'';
        $info['user_num'] = isset($info['user_num'])?$info['user_num']:'';
        $info['send_status'] = isset($info['send_status'])?$info['send_status']:'';
        $info['send_time'] = isset($info['send_time'])?$info['send_time']:'';
        $info['is_all_send'] = isset($info['is_all_send'])?$info['is_all_send']:'';

        $info['pic_format'] = isset($info['pic_format'])?$info['pic_format']:'';

        //check edit status
        if(isset($edit_info['send_status'])){
            if(in_array($edit_info['send_status'], array(self::Msg_ongoing,self::Msg_sent))){
                $is_error = 1;
                $err_msg = L('[进行中、已发送]状态的数据应不可再编辑');
            }
        }

        //check
        if(!$is_error){
            if(empty($info['plat_cd'])){
                $is_error = 1;
                $err_msg = L('Empty plat cd');
            }
        }
        if(!$is_error){
            if(empty($info['title'])){
                $is_error = 1;
                $err_msg = L('Empty title');
            }
        }
        if(!$is_error){
            if(empty($info['send_time'])){
                $is_error = 1;
                $err_msg = L('Empty time');
            }
        }

        $info['send_time'] = strtotime($info['send_time']);
        $info['user_num'] = intval($info['user_num']);
        $info['send_status'] = intval($info['send_status']);
        $info['is_all_send'] = intval($info['is_all_send']);
        if(empty($info['pic'])) unset($info['pic']);
        $info['pic'] = $info['pic_format'];

        if(!$is_error){
            if($info['user_num']<=0){
                $is_error = 1;
                $err_msg = L('发送对象用户不可为0');
            }
        }
        if(!$is_error){
            if(empty($info['content']) and empty($info['pic'])){
                $is_error = 1;
                $err_msg = L('Empty content or pic');
            }
        }
        if(!$is_error){
            if(!empty($info['target_url'])){
                if(!ZUtils::url($info['target_url'])){
                    $is_error = 1;
                    $err_msg = L('Wrong url');
                }
            }
        }

        $ret['is_error'] = $is_error;
        $ret['err_msg'] = $err_msg;
        $ret['data'] = $info;
        return $ret;
    }

    public function relate_message_send($info, $msg_id){
        $ret = null;
        if(!$msg_id){
            return $ret;
        }
        // is all user
        if(1==$info['is_all_send']){
            //get all user
            // $all = D('ZZmscust')->getUserByParentPlatCd($info['plat_cd']);
            $all = D('ZZmsthrdcust')->getThrdCustByParentPlatCd($info['plat_cd']);
            //mark to db table - send user
            $user_list = array();
            foreach($all['list'] as $k=>$v){
                $user_list[] = $v['CUST_ID'];
            }
            D('ZZmsmessagesend')->batchRelateMsgUser($msg_id,$user_list);
        }

        if(!empty($info['select_user_ids'])){
            $user_list = explode('|',$info['select_user_ids']);
            D('ZZmsmessagesend')->batchRelateMsgUser($msg_id,$user_list);
        }
        return $ret;
    }

    public function check_data_img($onefile){
        $ret = array();
        $ret['is_error'] = 0;
        $ret['err_msg'] = null;
        $ret['data'] = '';

        $outputs = array();

        //img
        $image = new cls_image();
        /* 检查图片：如果有错误，检查文件类型 */
        if (isset($onefile['file_pic']['error'])) // php 4.2 版本才支持 error
        {
            // 图片
            if ($onefile['file_pic']['error'] == 0)
            {
                if (!$image->check_img_type($onefile['file_pic']['type']))
                {

                    $ret['is_error'] = 1;
                    $ret['err_msg'] = '图片格式不正确!';

                }
            }
        }
        // 如果上传了图片，相应处理
        if ($onefile['file_pic']['tmp_name'] != '' && $onefile['file_pic']['tmp_name'] != 'none'){

            $files_content=array(
                'name'=>$onefile['file_pic']['name'],
                'type'=>$onefile['file_pic']['type'],
                'tmp_name'=>$onefile['file_pic']['tmp_name'],
                'error'=>$onefile['file_pic']['error'],
                'size'=>$onefile['file_pic']['size'],
            );
            $filedir=sys_get_temp_dir().'/';
            $save_img = $image->upload_image($files_content, $filedir, '');
            if($save_img==false) {

                $ret['is_error'] = 1;
                $ret['err_msg'] = '图片上传错误!';
            }else{
                // yun
                $full_name = date('Ym').'/'.basename($save_img);
                $ToQiniuYun = new ToQiniuYun();
                $upload_yun_result = $ToQiniuYun->doToYunOfUrl($save_img, $full_name);

                if(!empty($upload_yun_result['msg']['yun_url'])){
                    $ret['data'] = $upload_yun_result['msg']['yun_url'];
                }else{

                    $ret['is_error'] = 1;
                    $ret['err_msg'] = 'File upload stopped by qiniu yun.';
                }
            }

        }

        return $ret;
    }

    /**
     *  cron to send msg
     *
     */
    public function do_cron_send(){
        $ret = array();
        $timenow = time();

        $whereby = array('send_status'=>'2','send_time'=>array(array('elt',time())),);
        $orderby = array('send_time'=>'asc','id');
        // debug - just test - warning
        // $whereby = array('id'=>'1',);

        $list = $this->field ( '*' )
                ->where ( $whereby )
                ->order( $orderby )
                ->limit(1)
                ->select();

        foreach($list as $key=>$val){
            $this->one_msg_to_send($val);
            $ret[] = $val['id'];
        }

        return $ret;
    }

    public function one_msg_to_send($val){
        $ret = array();
        $timenow = time();

        $can_do = false;
        $msg_id = $val['id'];
        // msg
        $msg_info = $this->where(array('id'=>$msg_id))->find();
        if(!$msg_info){
            continue(1);
        }
        // check select
        $chk_data = D("ZZmsmessagecronlogs")->where(array('msg_id'=>$msg_id))->find();

        if(!isset($chk_data)){
            $can_do = true;
            // insert mark
            $one_data = array('msg_id'=>$msg_id,'send_status'=>2,);
            $status = D('ZZmsmessagecronlogs')->data($one_data)->add();
        }else{
            if($chk_data['send_status']!=3){
                if($timenow-$chk_data['create_time']>3600){
                    $can_do = true;
                }
            }
        }

        if($can_do){
            // try to judge all or not
            if($msg_info['is_all_send']==1){
                $status = $this->do_send_push($msg_info,array());
                $send_status = D('ZZmsmessagesend')
                    ->where(array('msg_id'=>$msg_id))
                    ->data(array('has_send'=>1))
                    ->save();
            }else{
                // tb_ms_message_send has_send
                $arr=D('ZZmsmessagesend')->field ("*")
                    ->where(array('msg_id'=>$msg_id,'has_send'=>0,))
                    ->select();
                $arr=is_array($arr)?$arr:array();
                // use loop
                $max = 100;
                do{

                    $now_array  = array_slice($arr, 0, $max, true);
                    $arr   = array_slice($arr, $max, null, true);

                    $user_ids = array();
                    foreach($now_array as $k_send=>$v_send){
                        $user_ids[] = $v_send['CUST_ID'];
                    }
                    $status = $this->do_send_push($msg_info,$user_ids);

                    $send_status = D('ZZmsmessagesend')
                        ->where(array('msg_id'=>$msg_id,'CUST_ID'=>array('IN',$user_ids)))
                        ->data(array('has_send'=>1))
                        ->save();

                    if(empty($arr)){
                        break;
                    }
                }while(1);
            }

            $status = D("ZZmsmessagecronlogs")->where(array('msg_id'=>$msg_id))->delete();

            $ret[] = $msg_id;
        }

        if($can_do)
            $status = $this->where(array('id'=>$msg_id))->data(array('send_status'=>3))->save();

        return $ret;
    }

    /**
     *  send push msg , some users
     *
     */
    public function do_send_push($msg_info,$user_ids){
        $sentToCusts = array();
        if(is_array($user_ids)){
            foreach($user_ids as $val){
                $sentToCusts[] = array('custId'=>$val);
            }
        }
        // push api format data
        $arr=array();
        $arr['platCode'] = $msg_info['plat_cd'];
        $arr['processId'] = ZFun::create_guid();
        $arr['data'] = array();
        $arr['data']['pushs'] = array();
        $pushs = array(
            'id'=>$msg_info['id'],
            'type'=>$this->code4type($msg_info['is_all_send']),
            'state'=>$this->code4state($msg_info['send_status']),
            'msgHeader'=>$msg_info['title'],
            'msgContent'=>$msg_info['content'],
            'url'=>$msg_info['target_url'],
            'sendingTime'=>$this->fmt_date($msg_info['send_time']),
            'pictureUrl'=>$msg_info['pic'],
            'createDate'=>$this->fmt_date($msg_info['create_time']),
            'sentToCusts'=>$sentToCusts,
            'author'=>DataMain::gainAdminName($msg_info['update_admin_id']),
        );
        $arr['data']['pushs'][] = $pushs;
        // echo json_encode($arr);
        $post_str = json_encode($arr);
        $url = GSHOPPER . $this->url_push;
        $postArr = array('is_post'=>2,'post_str'=>$post_str,);
        trace('create api - start', json_encode(array('url'=>$url,'postArr'=>$postArr)));
        $res = ZWebHttp::getCurlHtml($url,$postArr);
        trace('create api - end', json_encode($res));
        $htm = $res['html'];
        return $htm;
    }

    public function fmt_date($t){
        return date('Y-m-d H:i:s',$t);
    }

    // type for is_all_send
    public function code4type($is_all_send){
        $code = $is_all_send==1?'B':'A';
        return $code;
    }

    // state for send_status
    public function code4state($state){
        $code = $state==2?'W':'N';
        return $code;
    }

    /**
     *  send push msg one by one
     *
     */
    public function do_send_api($msg_info,$user_info){
        $ret = null;

        // push api format data
        $arr=array();
        $arr['processId'] = ZFun::create_guid();
        // $arr['processCode'] = '';
        $arr['platCode'] = $msg_info['plat_cd'];
        $arr['data'] = array();
        $tmpArr=array(
            'CUST_ID'=>$user_info['CUST_ID'],
            'plat_cd'=>$msg_info['plat_cd'],
            'title'=>$msg_info['title'],
            'content'=>$msg_info['content'],
            'pic'=>$msg_info['pic'],
            'send_time'=>$msg_info['send_time'],
            'target_url'=>$msg_info['target_url'],
        );
        $arr['data']['message'][] = $tmpArr;
        
        $post_str = json_encode($arr);
        // echo $post_str;
        $url = GSHOPPER . '/dataB5C/publishNotification.json';
        $postArr = array('is_post'=>2,'post_str'=>$post_str,);

        trace('create api - start', json_encode(array('url'=>$url,'postArr'=>$postArr)));

        $res = ZWebHttp::getCurlHtml($url,$postArr);

        trace('create api - end', json_encode($res));

        $htm = $res['html'];
        // var_dump( json_decode($htm,true) );
        return $htm;
    }


}
