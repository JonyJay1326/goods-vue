<?php
class PrivateAction extends Action
{
    public function index()
    {
        
        $updateSelfOrder = new UpdateSelfOrderModel();
        
        if ($runTest = I('get.test')) $updateSelfOrder->runTest = $runTest;
        $excelReader = new ExcelRead($updateSelfOrder);
        if ($filePath = $_FILES['expe']['tmp_name']) {
            $excelReader = new ExcelRead($updateSelfOrder);
            $excelReader->inputFileName = $filePath;
            $excelReader->main();
        }
        $excelReader->main();
        $this->display();
    }
    
    public function test()
    {
        $model = D('TbCrmSpSupplier');
        $ret = $model->relation(true)->select();
        //echo '<pre/>';var_dump($model);exit;
//        var_dump($model->getLastSql());
        
        echo '<pre/>';var_dump($ret);exit;
    }
}