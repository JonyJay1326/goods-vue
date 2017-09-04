<?php

/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/23
 * Time: 16:29
 */
class BrandSllrAction extends BaseAction
{
    private $_model;

    public function _initialize()
    {
        //parent::_initialize();
        import('ORG.Util.Page');// 导入分页类
        header('Access-Control-Allow-Origin: *');
        header('Content-Type:text/html;charset=utf-8');
        $this->_model = D('@Model/Guds/BrandSllr');
    }

    /**
     * 判断品牌sllrId是否存在
     */
    public function isSllrIdExist()
    {
        $sllrId = trim(I('get.sllrId'));
        if (empty($sllrId)) {
            $result = array('code' => '40000100', 'msg' => '参数不正确', 'data' => false);
            $this->jsonOut($result);
        }
        $result = $this->_model->getSllrIdIsExist($sllrId);
        if (!empty($result)) {
            $result = array('code' => '40000101', 'msg' => '品牌名称已经存在！', 'data' => false);
            $this->jsonOut($result);
        }
        $result = array('code' => '2000', 'msg' => '品牌名称可用！', 'data' => true);
        $this->jsonOut($result);
    }

    /**
     * 判断品牌公司名称是否存在
     */
    public function isCompanyNameExit()
    {
        $companyName = trim(I('get.companyName'));
        if (empty($companyName)) {
            $result = array('code' => '40000103', 'msg' => '参数不正确', 'data' => false);
            $this->jsonOut($result);
        }
        $result = $this->_model->getCompanyNameIsExist($companyName);
        if (!empty($result)) {
            $result = array('code' => '40000104', 'msg' => '公司名称已经存在！', 'data' => false);
            $this->jsonOut($result);
        }
        $result = array('code' => '2000', 'msg' => '公司名称可用！', 'data' => true);
        $this->jsonOut($result);
    }
}