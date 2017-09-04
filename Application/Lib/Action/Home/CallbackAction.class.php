<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/2
 * Time: 13:14
 */

class CallbackAction extends BaseAction {

	public function call_list() {
		$call = D("Callback");

		$where = array();

		if ($this->isPost()) {
			if (I("post.status")) {
				$where['status'] = I("post.status");
			}

			if (I("post.channel")) {
				$where['channel'] = strtolower(I("post.channel"));
			}
			if (I('post.starttime') && I('post.endtime')) {
				$where['complete_time'] = array(array('gt',I('post.starttime')),array('lt',I('post.endtime')));
			} elseif(I('post.starttime')) {
				$where['complete_time'] = array('gt', I('post.starttime'));
			} elseif(I('post.endtime')) {
				$where['complete_time'] = array('lt', I('post.endtime'));
			}

			if (I('post.payid')) {
				$where['pay_id'] = I("post.payid");
			}

			if (I('post.serialnumber')) {
				$where['serial_number'] = I("post.serialnumber");
			}
		}
                
                if($this->isGet()) {
                    if (I('get.payid')) {
                        $where['pay_id'] = I("get.payid");
                    }
                    if (I('get.id')) {
                        $where['id'] = I("get.id");
                    }
                }
                
		import('ORG.Util.Page');// 导入分页类
		$count = $call->where($where)->count();
		$page = new Page($count, 15);
		$show = $page->show();

		$result = $call->limit($page->firstRow.','.$page->listRows)->where($where)->order('id desc')->select();

		$this->assign('result', $result);
		$this->assign('pages', $show);

		$this->assign('total', $count);
		$this->display();
	}

	public function call_view() {

		if ($this->isGet()) {
			$id = I("get.vid");

			$call = D("Callback");
			$detail = $call->find($id);

			$this->assign('detail', $detail);
		}
		$this->display();
	}
}