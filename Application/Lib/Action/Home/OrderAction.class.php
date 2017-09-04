<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/2
 * Time: 13:14
 */

class OrderAction extends BaseAction {

	public function order_list() {

		$call = M("ms_ord","tb_");
        $where = array();
        $where['tb_ms_ord.ORD_STAT_CD'] = "N000550400";
        $where['DELIVERY_WAREHOUSE'] = "N000680300";


		if ($this->isPost()) {
            if (I("post.ORD_PUSH_STAT_CD")) {
				$where['ORD_PUSH_STAT_CD'] = I("post.ORD_PUSH_STAT_CD");
			}

			if (I("post.ORD_STAT_CD")) {
				$where["tb_ms_ord.ORD_STAT_CD"] = strtolower(I("post.ORD_STAT_CD"));
			}
			if (I('post.starttime') && I('post.endtime')) {
				$where['ORD_PUSH_DT'] = array(array('gt',I('post.starttime')),array('lt',I('post.endtime')));
			} elseif(I('post.starttime')) {
				$where['ORD_PUSH_DT'] = array('gt', I('post.starttime'));
			} elseif(I('post.endtime')) {
				$where['ORD_PUSH_DT'] = array('lt', I('post.endtime'));
			}

			if (I('post.ORD_ID')) {
				$where['ORD_ID'] = I("post.ORD_ID");
			}

			if (I('post.CUST_ID')) {
				$where['CUST_ID'] = I("post.CUST_ID");
			}
		}
                
        if($this->isGet()) {
            if (I("get.ORD_PUSH_STAT_CD")) {
                $where['ORD_PUSH_STAT_CD'] = I("get.ORD_PUSH_STAT_CD");
            }
            if (I('get.starttime') && I('get.endtime')) {
                $where['ORD_PUSH_DT'] = array(array('gt',I('get.starttime')),array('lt',I('get.endtime')));
            } elseif(I('get.starttime')) {
                $where['ORD_PUSH_DT'] = array('gt', I('get.starttime'));
            } elseif(I('get.endtime')) {
                $where['ORD_PUSH_DT'] = array('lt', I('get.endtime'));
            }

            if (I('get.ORD_ID')) {
                $where['ORD_ID'] = I("get.ORD_ID");
            }

            if (I('get.CUST_ID')) {
                $where['CUST_ID'] = I("get.CUST_ID");
            }
        }

        import('ORG.Util.Page');// 导入分页类
		$count = $call->where($where)->count();
		$page = new Page($count, 15);
        foreach($where as $key=>$val) {
            $page->parameter   .=   "$key=".urlencode($val).'&';
        }
        $show = $page->show();
        $where['sms_ms_ord_hist.ORD_HIST_PUSH_CD'] = array('neq', ' ');
		$result = $call->join('left join sms_ms_ord_hist on tb_ms_ord.ORD_ID = sms_ms_ord_hist.ORD_NO')->
                join('left join bbm_pay_callback on tb_ms_ord.PAY_ID=bbm_pay_callback.pay_id')->join('left join tb_ms_cust on tb_ms_cust.CUST_ID = tb_ms_ord.CUST_ID')->limit($page->firstRow.','.$page->listRows)->
                where($where)->field('tb_ms_ord.*,count(sms_ms_ord_hist.ORD_HIST_SEQ) as ORD_PUSH_NUMBER,bbm_pay_callback.serial_number,tb_ms_cust.CUST_NICK_NM')->
                order('ORD_PUSH_DT desc')->group('tb_ms_ord.ORD_ID')->select();
//        echo $call->getLastSql();
        //echo "<pre>";print_r($result);echo "</pre>";exit();
		$this->assign('result', $result);
		$this->assign('pages', $show);

		$this->assign('total', $count);
		$this->display();
	}

    public function order_log_list() {

        $call = M("ms_ord_hist","sms_");
        $where = array();
        $where['ORD_HIST_PUSH_CD'] = array('neq', ' ');

        if ($this->isPost()) {
            if (I("post.ORD_PUSH_STAT_CD")) {
                $where['ORD_PUSH_STAT_CD'] = I("post.ORD_PUSH_STAT_CD");
            }

            if (I("post.ORD_STAT_CD")) {
                $where["tb_ms_ord.ORD_STAT_CD"] = strtolower(I("post.ORD_STAT_CD"));
            }
            if (I('post.starttime') && I('post.endtime')) {
                $where['ORD_HIST_REG_DTTM'] = array(array('gt',I('post.starttime')),array('lt',I('post.endtime')));
            } elseif(I('post.starttime')) {
                $where['ORD_HIST_REG_DTTM'] = array('gt', I('post.starttime'));
            } elseif(I('post.endtime')) {
                $where['ORD_HIST_REG_DTTM'] = array('lt', I('post.endtime'));
            }

            if (I('post.ORD_ID')) {
                $where['ORD_ID'] = I("post.ORD_ID");
            }

            if (I('post.CUST_ID')) {
                $where['CUST_ID'] = I("post.CUST_ID");
            }
        }

        if($this->isGet()) {
            if (I('get.orderid')) {
                $where['ORD_NO'] = I("get.orderid");
            }
        }

        import('ORG.Util.Page');// 导入分页类
        $count = $call->where($where)->count();
        $page = new Page($count, 15);
        $show = $page->show();
        $result = $call->join('right join tb_ms_ord on tb_ms_ord.ORD_ID = sms_ms_ord_hist.ORD_NO')->limit($page->firstRow.','.$page->listRows)->
                where($where)->field('sms_ms_ord_hist.*, sms_ms_ord_hist.updated_time as ORD_PUSH_DT')->
                order('sms_ms_ord_hist.updated_time desc')->select();
        //echo $call->getLastSql();
        //echo "<pre>";print_r($result);echo "</pre>";exit();
        $this->assign('result', $result);
        $this->assign('pages', $show);

        $this->assign('total', $count);
        $this->display();
    }

	public function call_view() {
	    echo '暂未上线，功能不可用';exit();

		if ($this->isGet()) {
			$id = I("get.vid");

			$call = D("Callback");
			$detail = $call->find($id);

			$this->assign('detail', $detail);
		}
		$this->display();
	}
}