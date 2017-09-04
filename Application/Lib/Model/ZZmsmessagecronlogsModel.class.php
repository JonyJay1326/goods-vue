<?php
/**
 *  App push message send cron log
 *
 */
class ZZmsmessagecronlogsModel extends CommonModel{

    protected $trueTableName = "tb_ms_message_cron_logs";


    // 
    public function _before_insert(&$data,$options){
        parent::_before_insert($data, $options);
        $data['create_time'] = time();
    }

    // 
    public function _before_update(&$data,$options){
        parent::_before_update($data, $options);
        $data['update_time'] = time();
    }

    public function _after_select(&$resultSet,$options){
        parent::_after_select($resultSet, $options);
    }


}
