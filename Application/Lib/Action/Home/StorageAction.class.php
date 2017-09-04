<?php

/**
 * 入库模块
 * http://sms.com/index.php?m=Storage&a=request_do&serviceName=index
 */
 
class StorageAction extends BaseAction
{
    protected $module = 'StorageAction';
    
    /**
     * 初始化
     * 
     */
    public function _initialize()
    {
        parent::_initialize();
    }
    
    /**
     * 入库列表
     * 
     */
    public function index()
    {
        $trace = debug_backtrace(0);
        echo '<Pre/>';var_dump($trace);exit;
        $model = M('_b2b_warehouse_list', 'tb_');
        $ret = $model->select();
        $responseData = [
            'total' => 500,
            'pages' => 25,
            'data' => $ret
        ];
        echo '<pre/>';var_dump($_SERVER);exit;
        return ['code' => 200, 'message' => 'success', 'ResponseData' => $responseData, 'RequestData' => $this->params['RequestData']];
    }
    
    /**
     * 入库确认
     * 
     */
    public function storage_confirm()
    {
        return ['code' => 200, 'message' => 'access function storage_confirm'];
    }
    
    /**
     * 入库详情
     * 
     */
    public function storage_info()
    {
        return ['code' => 200, 'message' => 'access function storage_info'];
    }
}