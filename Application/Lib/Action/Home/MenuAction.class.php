<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/1
 * Time: 18:50
 */

class MenuAction extends BaseAction {

	public function menu_list() {
		//load('@.function');
		$cat = D("Node");
		$list = $cat->order('SORT DESC')->select();
        $tree = genCates($list, 0);
		$this->assign('tree', $tree);
		$this->display();
	}




	/**
	 * 新增菜单
	 */
	public function menu_add() {
		//load('@.function');
		$Node = D("Node");
		$list = $Node->where('LEVEL = 1 AND PID = 0 or LEVEL = 2')->select();
		$tree = genCate($list);
		$icon = $this->_get_icon();
		if ($this->isPost()) {
            //echo "<pre>";print_r($_POST);exit();
            $arr = explode(',', I("post.pid"));
			$add_data = array(
				'NAME' => I("post.name"),
				'TITLE' => $arr[1],
                'CTL'   => I("post.controller", ""),
                'ACT'   => I("post.action", ""),
				'PID' => $arr[0],
				'SORT' => I("post.sort", "0"),
				'ICON' => $icon[I('post.icon')],
				"LEVEL" => intval(I("post.menu_level", ""))+1,
				"TYPE" => intval(I("post.type", "0")),
			);
            //print_r($add_data);exit();
			$result = $Node->add($add_data);
			if ($result) {
				$this->ajaxReturn(0, '新增成功', 1);
			} else {
				$this->ajaxReturn(0, '新增失败', 0);
			}
		}
        //cho "<pre>";print_r($tree);exit();
		$this->assign('tree', $tree);
		$this->display();
	}

	/**
	 * 获取icon
	 * @return array
	 */
	public function _get_icon() {

		$icon_arr = array(
			1 => '&#xe616;',
			2 => '&#xe613;',
			3 => '&#xe620',
			4 => '&#xe626;',
			5 => '&#xe622;',
			6 => '&#xe63a;',
			7 => '&#xe717;',
			8 => '&#xe601;',
		);

		return $icon_arr;
	}

	/**
	 * 获取菜单编辑数据
	 */
	public function menu_edit() {
		$Node = D("Node");
		$list = $Node->where('LEVEL = 1 AND PID = 0 OR LEVEL = 2')->select();
        //echo "<pre>";print_r($list);echo "</pre>";
		$tree = genCate($list, 0);
        //echo "<pre>";print_r($tree);echo "</pre>";
        $icon = $this->_get_icon();
        if ($this->isGet()) {
			$id = I("get.id");
			$detail = $Node->find($id);
            //echo "<pre>";print_r($detail);echo "</pre>";
			$iconid = 1;
			foreach($icon as $key => $s) {
				if ($s == $detail['ICON']) {
					$iconid = $key;
					break;
				}
			}
			$this->assign('iconid', $iconid);
			$this->assign('detail', $detail);
		}

		if ($this->isPost()) {
            //print_r($_POST);exit();
		    $arr = explode(',', I("post.pid"));
			$save_data = array(
				'NAME' => I("post.name"),
				'TITLE' => $arr[1],
                'CTL'   => I("post.controller", ""),
                'ACT'   => I("post.action", ""),
				'PID' => $arr[0],
				'SORT' => I("post.sort", '',"0"),
				'ICON' => $icon[I('post.icon')],
				"LEVEL" => intval(I("post.menu_level", ""))+1,
                "TYPE" => intval(I("post.type", "0")),
			);
//            print_r($save_data);exit();
			$nid = I("post.nid");
			$result = $Node->where('ID = '.$nid)->save($save_data);

			if ($result) {
				$this->ajaxReturn(0,'编辑成功', 1);
			} else {
				$this->ajaxReturn(0, '编辑失败', 0);
			}
		}

		$this->assign('tree', $tree);
		$this->display();
	}

	public function menu_dele() {
            // 判断数据来源是否正常
            if (IS_POST) {
                $id = I('post.id');
                // 参数不能为空
                if (empty($id)) {
                    $this->ajaxReturn(0, '参数不能为空', 0);
                }
                $Node = D("Node");
                $Node->delete($id);
                $this->ajaxReturn(0, '删除成功', 1);
            }
	}
}