<?php
/**
 *  App push message send
 *
 */
class ZZmsmessagesendModel extends CommonModel{

    protected $trueTableName = "tb_ms_message_send";


    // 
    public function _before_insert(&$data,$options){
        parent::_before_insert($data, $options);
        $data['add_time'] = time();
    }

    // 
    public function _before_update(&$data,$options){
        parent::_before_update($data, $options);

    }

    public function _after_select(&$resultSet,$options){
        parent::_after_select($resultSet, $options);
    }

    /**
     *  select users , by message id
     *
     */
    public function getUsersByMsgID($msg_id){
        $arr = array();
        $arr=$this->field ("*")
                ->where (array('msg_id'=>$msg_id))
                ->select();
        $arr = is_array($arr)?$arr:array();
        $ret = array();
        foreach($arr as $k=>$v){
            $ret[] = $v['CUST_ID'];
        }
        return $ret;
    }

    /**
     *  do relate : msg and user
     *
     */
    public function addRelateMsgUser($msg_id,$cust_id){
        $new = array();
        $new['msg_id'] = $msg_id;
        $new['CUST_ID'] = $cust_id;
        //check exist , if not then add
        $arr=$this->field ("count(*) as num")
                ->where (array('msg_id'=>$msg_id,'CUST_ID'=>$cust_id,))
                ->select();
        $num = isset($arr[0]['num'])?$arr[0]['num']:null;
        if($num>0){
            return null;
        }

        $status = $this->data($new)->add();
        return $status;
    }

    public function batchRelateMsgUser($msg_id,$user_list){
        //del
        $this->where(
            array(
                'msg_id'=>$msg_id,
                'CUST_ID'=>array('NOT IN', $user_list),
            )
        )->delete();
        //add
        foreach($user_list as $val){
            $tmpStatus = $this->addRelateMsgUser($msg_id,$val);
        }
    }

    public function countUserSendNumByMsg($msg_id){
        $num=$this->where(array('msg_id'=>$msg_id,'has_send'=>1))
                ->count();
        return $num;
    }

}
