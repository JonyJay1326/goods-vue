<?php

/**
 * 日志展示
 * 
 */
class UserLogAction extends BaseAction
{
    /**
     * 日志列表
     * 
     */
    public function index()
    {
        $model = D('TbMsUserOperationLog');
        $params = $this->getParams();
        $condition = $model->searchModel($params, self::$rule_menu);
        $count = $model->where($condition)->count();   //日志条数
        import('ORG.Util.Page');// 导入分页类
        $page = new Page($count, 20);
        $show = $page->show();
        $result = $model->where($condition)->order('cTime desc')->limit($page->firstRow.','.$page->listRows)->select();
        
        $this->assign('count', $count);
        $this->assign('params', $params);
        $this->assign('result', $result);
        $this->assign('pages', $show);
        $this->assign('model', $model);
        $this->display();
    }
}