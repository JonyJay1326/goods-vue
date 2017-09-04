<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/2
 * Time: 13:15
 */

class NotifyAction extends BaseAction {

    private static $notify_url = '/online_pay/send_msg.json?payId=';

	public function notify_list() {

		$Notify = D("Notify");
		$where = array();

		if ($this->isPost()) {
			if (I("post.status")) {
				$where['back.status'] = strtolower(I("post.status"));
			}

			if (I("post.channel")) {
				$where['bbm_pay_result_notify.channel'] = strtolower(I("post.channel"));
			}
			if (I('post.starttime') && I('post.endtime')) {
				$where['bbm_pay_result_notify.created_time'] = array(array('gt',I('post.starttime')),array('lt',I('post.endtime')));
			} elseif(I('post.starttime')) {
				$where['bbm_pay_result_notify.created_time'] = array('gt', I('post.starttime'));
			} elseif(I('post.endtime')) {
				$where['bbm_pay_result_notify.created_time'] = array('lt', I('post.endtime'));
			}

			if (I('post.payid')) {
				$where['bbm_pay_result_notify.pay_id'] = I("post.payid");
			}

			if (I('post.serialnumber')) {
				$where['back.serial_number'] = I("post.serialnumber");
			}
		}
                
                if($this->isGet()) {
                    if (I('get.payid')) {
                        $where['bbm_pay_result_notify.pay_id'] = I("get.payid");
                    }
                }
                
		import('ORG.Util.Page');// 导入分页类
		$count = $Notify->where($where)->count();
		$page = new Page($count, 15);
		$show = $page->show();

		$result = $Notify->Distinct(true)->field("bbm_pay_result_notify.*, back.serial_number")->join('bbm_pay_callback as back on back.pay_id = bbm_pay_result_notify.pay_id')->limit($page->firstRow.','.$page->listRows)->where($where)->order('id desc')->select();

		$this->assign('result', $result);
		$this->assign('pages', $show);

		$this->assign('total', $count);
		$this->display();
	}

	public function notify() {
	    $pay_id = I('request.pay_id');
        $res = json_decode(curl_request(PAY_URL_API.$this::$notify_url.$pay_id));
        $this->ajaxReturn(0,$res,0);
    }
}