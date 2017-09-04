<?php
/**
 * 公共控制器
 * User: Muzhitao
 * Date: 2016/1/15 0015
 * Time: 18:09
 * Email：muzhitao@vchangyi.com
 */

class PublicAction extends BaseAction {

	/**
	 * 登录界面
	 */
	public function login() {
		$this->display();
	}

	/**
	 * 验证码
	 */
	public function verify() {

		ob_clean();
		import('ORG.Util.Image');
		Image::buildImageVerify($length=4, $mode=1, $type='png', $width=58, $height=38);
	}

	public function error() {

	}

	/**
	 * 用户登录检测
	 */
	public function checkLogin() {
		import('ORG.Util.RBAC');
		$map = array();
		$map['M_NAME']      = trim(I("post.username"));
		$map['IS_USE']      = 0;
        $map['M_STATUS']    = ['neq',2];
		C('USER_AUTH_MODEL','Admin');
		$authInfo = RBAC::authenticate($map);

		if(empty($authInfo)){
			$this->ajaxReturn(0, 'Account does not exist or is disabled', 0);
		}else{
            if($authInfo['M_PASSWORD'] !== strtoupper(md5(I("post.password"))) && $authInfo['oa_user_state'] == 1){
                $this->ajaxReturn(0, 'OA Account Or Password error', 0);
            }elseif($authInfo['M_PASSWORD'] != md5(I("post.password").C('PASSKEY')) && $authInfo['oa_user_state'] != 1){
				$this->ajaxReturn(0, 'Account Or Password error', 0);
			}else{
				$_SESSION[C('USER_AUTH_KEY')] = $authInfo['M_ID'];
				$_SESSION['role_id'] = $authInfo['ROLE_ID'];
				$_SESSION['user_id'] = $authInfo['M_ID'];
				$_SESSION['m_loginip'] = get_client_ip();
				$_SESSION['m_logintime'] = $authInfo['M_LOGINTIME'];
                $_SESSION['m_loginname'] = $authInfo['M_NAME'];
				$role = M('role')->field('ROLE_ACTLIST')->find($authInfo['ROLE_ID']);
				$_SESSION['actlist'] = $role['ROLE_ACTLIST']== 0? 0:$this->getRoleInfo($role['ROLE_ACTLIST']);
                $_SESSION['actlist_value'] = $role['ROLE_ACTLIST']== 0? 0:$this->getRoleInfo($role['ROLE_ACTLIST'], true);
				if($authInfo['ROLE_ID'] == 1){
					$_SESSION[C('ADMIN_AUTH_KEY')] = true;
				}
				/* 更新登录数据 */
				$data['M_LOGINIP'] = get_client_ip();
				$data['M_LOGINTIME'] = time();
				$data['M_LOGINNUMS'] = $authInfo['M_LOGINNUMS'] + 1;

				M(C('USER_AUTH_MODEL'))->where('M_ID = '.$authInfo['M_ID'])->save($data);

                // to mark in cookie field
                if(I("post.is_remember")){
                    $tmpCookie = $_SESSION;
                    unset($tmpCookie['actlist']);
                    unset($tmpCookie['actlist_value']);
                    $tmpCookie = base64_encode(base64_encode(serialize($tmpCookie)));
                    cookie('_identity_sms',$tmpCookie,array('expire'=>3600*24));
                    cookie('_identity_key',md5(C('PASSKEY').$tmpCookie),array('expire'=>3600*24));
                }

				$this->ajaxReturn(0, 'Login success！', 1);
			}
		}

	}

	/**
	 * 退出登录操作
	 */
	public function logout() {

		if(!empty($_SESSION[C('USER_AUTH_KEY')])){
			$this->login_log($_SESSION['real_name'], '退出成功');
			unset($_SESSION[C('USER_AUTH_KEY')]);
			unset($_SESSION);
			session_destroy();
            cookie('_identity_sms',null);
            cookie('_identity_key',null);
			$this->assign('jumpUrl',U("Public/login"));
			$this->success('退出成功');
		}else{
			$this->error('已经登出了');
		}
	}

	/**
	 * 后台用户登录日志
	 * @param string $username
	 * @param string $option
	 */
	protected function login_log($username = '', $option = '登录失败') {

		$System = M('SystemLog');
		$data['ip'] = get_client_ip();
		$data['time'] = time();
		$data['username'] = $username;
		$data['options'] = $option;

		// 插入数据
		$System->add($data);
	}
	//权限
    public function getRoleInfo($role, $flag = false){
        if(empty($role)){
            return false;
        }
        $role = array_flip(explode(',', $role));
        $node = M('Node');
        foreach ($role as $key => $value){
            $temp = $node->where('ID='.$key)->find();
            if($flag){
                if(!empty($temp['CTL'])) {
                    $value = $temp['CTL'] . '/' . $temp['ACT'];
                    $role_value[$value] = $value;
                    $role = $role_value;
                }
            } else {
                $role[$key] = !empty($temp['CTL']) ? $temp['CTL'] . '/' . $temp['ACT'] : null;
            }
        }
        return $role;
    }
}