<?php

/**
 * 系统首页
 * Class IndexAction
 */
class IndexAction extends BaseAction {

	public function index() {
        $url =  LANG_SET == 'zh-cn'?U('',['l'=>'en-us']):U('',['l'=>'zh-cn']);
        $langbut =  LANG_SET == 'zh-cn'?L('切换英文'):L('切换中文');
        $oa = '';
        $Admin = M('admin', 'bbm_');
        $role_id = $this->get_role_ID();
        //dump($role_id);
        if($Admin->where('M_NAME = \''.session('m_loginname').'\'')->getField('ROLE_ID') == $role_id){
            $oa = '请邮箱联系相嫌：&lt;xiangxian@Gshopper.com&gt;，抄送给 华黎：&lt;huali@Gshopper.com&gt;，开放权限';
        }
        $_SESSION['oa'] = $oa;
        $this->assign('langchange_url',$url);
        $this->assign('langbut',$langbut);
        $this->assign('host',$_SERVER['HTTP_HOST']);
        $this->display();
	}

    private function get_role_ID()
    {
        $Role = M('role', 'bbm_');
        $where['ROLE_NAME'] = 'OA用户';
        return $Role->where($where)->getField('ROLE_ID');
    }
}
