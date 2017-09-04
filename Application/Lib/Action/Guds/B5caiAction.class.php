<?php

/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/8
 * Time: 19:30
 */
class B5caiAction extends BaseAction
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 通过父类目id获取子分类列表
     *
     */
    public function getB5caiListByLevel()
    {
        $pId = I('pId');
        $leverId = I('levId');
        $B5caiCate = D('@Model/Guds/B5caiCate');
        $data = $B5caiCate->getB5caiCateList($leverId, $pId);
        $this->jsonOut(array('code' => 2000, 'msg' => 'success', 'data' => $data));
    }
}