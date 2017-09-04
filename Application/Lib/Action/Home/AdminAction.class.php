<?php
/**
 * 管理员控制器
 * User: muzhitao
 * Date: 2016/8/1
 * Time: 13:07
 */

class AdminAction extends BaseAction {

	/**
	 * 管理员列表
	 */
	public function admin_list() {

		$condition = array();
		$condition['M_STATUS'] = array('lt', 2);
		if (IS_POST) {

			if (I('login_name')) {
				$condition['M_NAME'] =array('like',"%".I('login_name')."%");
			}
			if (I('start_time') && I('end_time')) {
				$condition['M_ADDTIME'] = array(array('gt',strtotime(I('start_time')." 00:00:00")),array('lt',strtotime(I('end_time')." 23:59:59")));
			} elseif(I('start_time')) {
				$condition['M_ADDTIME'] = array('gt', strtotime(I('start_time')));
			} elseif(I('end_time')) {
				$condition['M_ADDTIME'] = array('lt', strtotime(I('end_time')));
			}
		}

		$Admin = M('Admin');
		import('ORG.Util.Page');// 导入分页类
		$count = $Admin->where($condition)->count();
		$page = new Page($count, 20);
		$show = $page->show();

		$list = $Admin->where($condition)->order('M_ID DESC')->limit($page->firstRow.','.$page->listRows)->select();

		$role = D('Role');
		$role_list = $role->select();

		$temp_role = array();
		if ($role_list) {
			foreach($role_list as $l) {
				$temp_role[$l['ROLE_ID']] = $l['ROLE_NAME'];
			}
		}
		$this->assign('role_list', $temp_role);
		// 记录总数
		$this->assign('count', $count);
		// 条件下的列表数据
		$this->assign('list', $list);
		// 分页
		$this->assign('pages', $show);
		$this->display();
	}

	/**
	 * 新增管理员
	 */
	public function admin_add() {

		$role = D('Role');
		$role_list = $role->select();

		if ($this->isPost()) {

			$Admin = D("Admin");

			$where = array(
				'M_NAME'   => I("post.login_name"),
				'M_STATUS' => ['neq',2]
			);
			$detail = $Admin->where($where)->find();

			/* 判断当前输入的用户名是否存在 */
			if ($detail) {
				$this->ajaxReturn(0, '用户名已存在', "n");
			}

			$add_data = array(
				'M_NAME' => I("post.login_name"),
				'M_PASSWORD' => md5(I("post.newpassword").C("PASSKEY")),
				'M_SEX' => I("post.m_sex"),
				'M_MOBILE' => I("post.m_mobile"),
				'M_EMAIL' => I("post.email"),
				'M_REMARK' => I("post.m_remark"),
				'ROLE_ID' => I("post.role_id"),
				'M_ADDTIME' => time(),
			);

			$result = $Admin->add($add_data);
                        /*获得新插入的用户id*/
                        $userid = $Admin->getLastInsID();
                        $user_detail = D("Detail");
                        $add_data = array(
				'uid' => $userid,
				'group_id' => I("post.role_id"),
			);
                        $result = $user_detail->add($add_data);
			if ($result) {
				$this->ajaxReturn(0, '添加成功', "y");
				//$this->success("添加成功", U("Admin/admin_list"));
			} else {
				$this->ajaxReturn(0, '添加失败', "n");
				//$this->error("添加失败");
			}
		}

		$this->assign('role_list', $role_list);
		$this->display();
	}


	/**
	 * 更新管理员的状态操作
	 */
	public function update_admin_status() {

		// 判断是否来源POST请求
		if (IS_POST) {

			$u_id = I('post.u_id');

			// 如果参数不能为空
			if (empty($u_id)) {
				$this->ajaxReturn(0, '参数不能为空', 0);
			}

			$Admin = M('Admin');
			$status = I('post.is_use');
			if ($status == 1) {
				$data['IS_USE'] = 1;
			} else{
				$data['IS_USE'] = 0;
			}

			$Admin->where('M_ID = '.$u_id)->save($data);
			$this->ajaxReturn(0, '更新成功', 1);
		}
	}

	/**
	 * 编辑信息
	 */
	public function admin_edit() {
		$Admin = D("Admin");
		if ($this->isGet()) {
			$uid = I("get.m_id");

			$role = D('Role');
			$role_list = $role->select();

			$detaail = $Admin->find($uid);

			$this->assign('role_list', $role_list);
			$this->assign('detail', $detaail);
			$this->display();
		}

		if ($this->isPost()) {

			$m_uid = I("post.m_uid");
			$add_data = array(
				'M_NAME' => I("post.login_name"),
				'M_SEX' => I("post.m_sex"),
				'M_MOBILE' => I("post.m_mobile"),
				'M_EMAIL' => I("post.email"),
				'M_REMARK' => I("post.m_remark"),
				'ROLE_ID' => I("post.role_id"),
				'M_UPDATED' => time(),
				'M_STATUS' => 1
			);

			$result = $Admin->where('M_ID = '. $m_uid)->save($add_data);
			$user_detail = D("Detail");
			$add_data = array(
				'uid' => $m_uid,
				'group_id' => I("post.role_id"),
			);

			$result = $user_detail->where('uid = '. $m_uid)->save($add_data);
//			if ($result) {
				$this->ajaxReturn(0, '编辑成功', 1);
//			} else {
//				$this->ajaxReturn(0, '编辑失败', 0);
//
//			}
		}
	}


	/**
	 * 删除管理员 逻辑删除 更改状态
	 */
	public function delete_admin() {

		// 判断数据来源是否正常
		if (IS_POST) {
			$uid = I('post.u_id');

			// 参数不能为空
			if (empty($uid)) {
				$this->ajaxReturn(0, '参数不能为空', 0);
			}
			$Admin = M('Admin');
			if (!is_array($uid)) {
				$detail = $Admin->find($uid);

				// 如果数据不存在
				if(empty($detail)) {
					$this->ajaxReturn(0, '数据不存在', 0);
				}
				$save_data = array(
					'M_STATUS' => 2,
					'M_DELETED' => time(),
				);
				$Admin->where("M_ID =".$uid)->save($save_data);
			} else {
				$uids = implode(',', $uid);
				$Admin->delete($uids);
			}

			$this->ajaxReturn(0, '删除成功', 1);
		}
	}

	/**
	 * 修改密码
	 */
	public function admin_password() {

		$Admin = D("Admin");

            /* 获取用户的详情 */
            if ($this->isGet()) {

                $mid = I("get.m_id");
                $detail = $Admin->find($mid);

                $this->assign('detail', $detail);
                $this->display();
            }

            /* 编辑提交的数据 */
            if ($this->isPost()) {
                if($Admin->where('M_NAME = \''.session('m_loginname').'\'')->getField('oa_user_state') == 1) {
                    $this->ajaxReturn(0,'OA用户请在OA上修改，稍等后数据同步', 0);
                }else{
                $m_id = I("post.m_id");
                $save_data = array(
                    'M_PASSWORD' => md5(I("post.passwords").C("PASSKEY")),
                    'M_STATUS' => 1,
                    'M_UPDATED' => time()
                );

                $reuslt = $Admin->where('M_ID = '. $m_id)->save($save_data);
                if ($reuslt) {
                    $this->ajaxReturn(0, '修改成功,请重新登录',1);
                } else {
                    $this->ajaxReturn(0,'修改失败，请重新修改', 0);
                }
            }
        }


	}
}