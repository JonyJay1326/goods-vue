<?php 
/**
 * Created by sublime.
 * User: b5m
 * Date: 17/8/7
 * Time: 17:55
 * By: huanzhu
 */

class HrAction extends BaseAction
{
	 private $HrModel;  //数据模型

	 public function responseData($code, $msg, $data)
    {
		$data = [
			'code' => $code,
			'msg' => $msg,
			'data' => $data
		];

		exit(json_encode($data));
    }

	 public function __construct()
    {
        parent::__construct();
        //初始化实例化模型
        $this->HrModel = new TbHrModel();
    }
	/**
     * 人员展示、搜索
     * @param 条件参数
     */
	public function showList(){
		$this->display('showList');
	}
	
	/**
	 *我的名片信息
	 *
	 */
	public function card(){
		$menu = $_SERVER['QUERY_STRING'];
		$menu .= $_REQUEST['id'];
		$this->assign('menu',$menu);
		$this->display('addPerson');
	}


	/**
	 *新增人员，同时数据存入名片 未完待续
	 * @PersonData 员工信息
	 * @childData 员工子信息
	 */
	public function addPerson(){
		$menu = $_SERVER['QUERY_STRING'];
		$menu .= $REQUEST['id'];
		$this->assign('menu',$menu);
		$this->display('addPerson');
	}


	/**
	*我的下属 完成
	*
	*/
	public function Subordinates()  
	{
		$Subordinates = $this->HrModel->SubData();
		$this->ajaxReturn($Subordinates);exit;
		//dump($Subordinates);die;
	}
	


}

 ?>