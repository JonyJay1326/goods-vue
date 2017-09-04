<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/1
 * Time: 17:18
 */

class NodeAction extends BaseAction {

	/**
	 * 节点列表
	 */
	public function node_list() {
		$Node = D("Node");
		$node_list = $Node->where('(LEVEL = 2 OR LEVEL = 3) and STATUS < 2')->select();
		$node_lists = genCates($node_list);
		$this->assign('node_lists', $node_lists);
		$this->display();
	}

	/**
	 * 新增模块
	 */
	public function node_add() {
		$Node = D("Node");
		if ($this->isPost()) {

			$add_data = array(
				'NAME' => I("post.mname"),
				'CTL' => I("post.ctl"),
				'ACT' => I("post.action"),
				'SORT' => I("post.sort"),
				'LEVEL' => 2,
				'PID' => I('post.pid'),
			);

			$result = $Node->add($add_data);
			if ($result) {
				$this->ajaxReturn(0,'新增成功', 1);
			} else {
				$this->ajaxReturn(0, '新增失败', 0);
			}
		}

		$menu_list = $Node->where('LEVEL = 1')->select();

		$this->assign('menu_list', $menu_list);
		$this->display();
	}

	/**
	 * 编辑节点
	 */
	public function node_edit() {

		$Node = D("Node");
		if ($this->isGet()) {
			$nid = I("get.ids");
			$detail = $Node->find($nid);
			$this->assign("detail", $detail);
		}

		if ($this->isPost()) {
			$id = I("post.id");
			$save_data = array(
				'NAME' => I("post.mname"),
				'CTL' => I("post.ctl"),
				'ACT' => I("post.action"),
				'SORT' => I("post.sort"),
				'LEVEL' => 2,
				'PID' => I('post.pid'),
				'STATUS' => 1,
				'UPDATED' => time(),
			);

			$result = $Node->where('ID = '. $id)->save($save_data);
			if ($result) {
				$this->ajaxReturn(0,'新增成功', 1);
			} else {
				$this->ajaxReturn(0, '新增失败', 0);
			}
		}

		$menu_list = $Node->where('LEVEL = 1')->select();

		$this->assign('menu_list', $menu_list);
		$this->display();
	}

	/**
	 * 删除节点
	 */
	public function node_dele() {

		if ($this->isPost()) {
			$id = I("post.id");
			$Node = D("Node");
			$save_data = array(
				'STATUS' => 2,
			);

			$result = $Node->where('ID = '. $id)->save($save_data);
			if ($result) {
				$this->ajaxReturn(0,'删除成功', 1);
			} else {
				$this->ajaxReturn(0, '删除失败', 0);
			}
		}
	}

	/**
	 * 添加节点下的模块
	 */
	public function modules_add() {
		$Node = D("Node");
		if ($this->isGet()) {
			$ids = I("get.ids");
			$detail = $Node->find($ids);
			$this->assign('detail', $detail);
		}

		if ($this->isPost()) {
			$add_data = array(
				'PID' => I("post.pid"),
				'NAME' => I("post.mname"),
				'CTL' => I("post.ctroller"),
				'ACT' => I("post.action"),
				'SORT' => I("post.sort"),
				'STATUS' => 1,
				'LEVEL' => 3
			);

			$result = $Node->add($add_data);
			if ($result) {
				$this->ajaxReturn(0,'新增成功', 1);
			} else {
				$this->ajaxReturn(0, '新增失败', 0);
			}

		}
		$this->display();
	}

	/**
	 * 模块下的节点编辑
	 */
	public function modules_edit() {

		if ($this->isGet()) {
			$ids = I("get.ids");
			$Node = D("Node");
			$detail = $Node->find($ids);

			$this->assign('detail', $detail);
			$this->display();
		}

		if ($this->isPost()) {
			$ids = I("post.id");
			$save_data = array(
				'NAME' => I("post.mname"),
				'ACT' => I("post.action"),
				'SORT' => I("post.sort"),
				'UPDATED' => time(),
			);

			$Node = D("Node");
			$result = $Node->where('ID = '. $ids)->save($save_data);
			if ($result) {
				$this->ajaxReturn(0,'编辑成功', 1);
			} else {
				$this->ajaxReturn(0, '编辑失败', 0);
			}
		}
	}

}