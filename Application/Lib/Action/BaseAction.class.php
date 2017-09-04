<?php
/**
 * 基础控制器
 * User: muzhitao
 * Date: 2016/7/28
 * Time: 14:22
 */
load('@.otherbase');
header('Access-Control-Allow-Origin:*');
class BaseAction extends Action {

	/**
	 * 初始化入口
	 */
	public $access;
    public $serviceName;
    public $params;
    protected $module = 'BaseAction';   //存储所有url
    public static $rule_menu;
    
	public function _initialize()
	{
        $this->_catchMe();
        $this->generateMethod();
        $this->setParams();
        $this->access = $_SESSION['actlist_value'];
		// 用户权限检查
		if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {

            //check and login by cookie
            if (!$_SESSION [C('USER_AUTH_KEY')]) {
                $_identity_sms = cookie('_identity_sms');
                $_identity_key = cookie('_identity_key');
                if($_identity_sms and $_identity_key){
                    if(md5(C('PASSKEY').$_identity_sms)==$_identity_key){
                        $tmpIdentity = unserialize(base64_decode(base64_decode($_identity_sms)));
                        $role = M('role')->field('ROLE_ACTLIST')->find($tmpIdentity['role_id']);
                        $tmpIdentity['actlist'] = $role['ROLE_ACTLIST']== 0? 0:A('Public')->getRoleInfo($role['ROLE_ACTLIST']);
                        $tmpIdentity['actlist_value'] = $role['ROLE_ACTLIST']== 0? 0:A('Public')->getRoleInfo($role['ROLE_ACTLIST'], true);
                        foreach($tmpIdentity as $k=>$v){
                            $_SESSION[$k] = $v;
                        }
                        $this->access = $_SESSION['actlist_value'];
                        cookie('_identity_sms',$_identity_sms,array('expire'=>3600*24));
                        cookie('_identity_key',$_identity_key,array('expire'=>3600*24));
                        // var_dump($_SESSION); die();
                    }
                }
            }

			//检查认证识别号
			if (!$_SESSION [C('USER_AUTH_KEY')]) {
				//跳转到认证网关
                js_redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
				// redirect(PHP_FILE . C('USER_AUTH_GATEWAY'), 2,'请先登陆');
				//redirect(U('Public/login'));
			}
			if (!$this->_get_access_list($_SESSION['role_id'])) {
			    //redirect(U('Public/error'));

//                echo "没有权限";
//				exit;
                if(IS_AJAX){
                    $this->ajaxReturn(0,L('没有权限'), 0);
					exit;
                }else{
                    header('Content-Type:text/html; charset=utf-8');
					echo L("没有权限");
					exit;
                }
			}
		}
		$module = $this->_get_menu();
        //dump($module);
        //echo "<pre>"; print_r($this->_get_menu_all());exit();
        //echo "<pre>"; print_r($_SESSION);exit();

        // lang by self check
        if(empty($_GET[C('VAR_LANGUAGE')])){
            $langSet = cookie('think_language');
            if($langSet){
                $_GET[C('VAR_LANGUAGE')] = $langSet;
                cookie('think_language',$langSet,3600*24);
            }
        }
        $rm = array_column(self::$rule_menu, 'NAME', 'CTL');
        $cm = array_column(self::$rule_menu, 'NAME', 'ACT');
        $this->assign('rule_menu', $rm);
        $this->assign('c_name', $cm);
		$this->assign('module', $module);
	}


       public function getParams(){
            return $_REQUEST;
        }

	/**
	 * 权限判断
	 * @param $role
	 * @return bool
	 */
	private function _get_access_list($role) {

		if (empty($role)) {
			return false;
		}
		/* 公共或者系统控制器则开放所有权限 */
		/*if(MODULE_NAME == 'System' || MODULE_NAME == 'Public' || MODULE_NAME == 'Admin') {
			return true;
		}*/
        $temp = $this->_get_menu_all();
        if (!in_array(MODULE_NAME . '/' . ACTION_NAME, $temp) && !in_array(GROUP_NAME . '/' . MODULE_NAME . '/' . ACTION_NAME, $temp)) {
            return true;
        }

        if (MODULE_NAME == 'Index') {
            if (ACTION_NAME == 'index' || ACTION_NAME == 'welcome') {
                return true;
            }
        }

        /* 如果是超级管理员则开放所有权限  */
        if ($role == 1) {
            return true;
        }

        $Node = D("Node");
        $Role = D("Role");

        $where = array(
            'CTL' => !empty(GROUP_NAME) && GROUP_NAME == "Home" ? MODULE_NAME : GROUP_NAME .'/' . MODULE_NAME,
            'ACT' => ACTION_NAME
        );
        $node_detail = $Node->where($where)->find();
        $role_detail = $Role->find($role);
        if ($role_detail['ROLE_ACTLIST'] == null) {
            return false;
        }
        $access = explode(",", $role_detail['ROLE_ACTLIST']);
        if (in_array($node_detail['ID'], $access)) {
            return true;
        }

        return false;
    }

    /**
     * 功能导航菜单的构建，这里要进行接口和页面区分。
     * @return array
     */
    public function _get_menu()
    {

        $Node = D("Node");
        $modules = $rolemenu = array();
        //Changed By Afanti @2017.08.07, 添加类型筛选，这里之筛选页面类型。
        $menu = $Node->where('LEVEL != 3 AND TYPE =0')->order('SORT DESC')->select();

		if (empty($menu)) {
			return $modules;
		}
		foreach($menu as $key => $s) {
			if ($s['PID'] == 0) {
				$modules[$s["ID"]] = $s;
			} else {
				$rolemenu[] = $s;
			}
		}
        self::$rule_menu = $rolemenu;
		if (!empty($rolemenu)) {
			foreach($rolemenu as $key => $s) {
				$rolemenu[$key]['url'] = U($s['CTL'].'/'.$s['ACT']);
			}
			foreach($rolemenu as $vs) {
				if (isset($modules[$vs['PID']])) {
					$modules[$vs['PID']]['child'][] = $vs;
				} else {
                //$modules[]['child'] = array();
				}
			}
		}
		return $modules;
	}

	//获得全部信息
    public function _get_menu_all() {

        $Node = D("Node");
        $modules = $rolemenu = array();
        $menu = $Node->order('SORT DESC')->select();

        if (empty($menu)) {
            return $modules;
        }

        foreach($menu as $key => $s) {
            $modules[$s['ID']] = $s['CTL'] . '/' . $s['ACT'];
        }
            /*if ($s['ID'] == 0) {
                $modules[$s["ID"]] = $s;
            } else {
                $rolemenu[] = $s;
            }
        }

        if (!empty($rolemenu)) {
            foreach($rolemenu as $key => $s) {
                $rolemenu[$key]['url'] = U($s['CTL'].'/'.$s['ACT']);
            }
            foreach($rolemenu as $vs) {
                if (isset($modules[$vs['PID']])) {
                    $modules[$vs['PID']]['child'][] = $vs;
                } else {
                    $modules[]['child'] = array();
                }
            }
        }*/
        return $modules;
    }
    
    private function getLogPath($time = '')
    {
        $logFilePath = __DIR__ . '../../../Runtime/Logs/';
        if (!$time) $time = date('Y-m-d');
        if (I('get.type') == 'sendout') {    //生成的日志记录格式,做什么操作记录什么名字
            $logName = $time . 'sendout.log';    
        } else if (I('get.type') == 'refund') {
            $logName = $time . 'refund.log';    
        } else {
            $logName = $time . I('get.type') . '.log';
        }
        return $logFilePath . $logName;
    }
    
    /**
     * 显示日志
     */
    public function show_log()
    {
        $time = I('get.time');
        $file = $this->getLogPath($time);

        $content = file_get_contents($file);
        var_dump($content);
        if (!$content) $content = 'Not and more!';
        //header("Content-type: text/html; charset=utf-8");
        $this->assign('content', $content);
        $this->display('Log/show_log');
    }
    
    /**
     * 清理日志
     * 
     */
    public function clean()
    {   
        $file = $this->getLogPath();
        fclose(fopen($file, 'a+'));
        $_fo = fopen($file, 'rb');
        $old = fread($_fo, 1024 * 1024);
        fclose($_fo);
        file_put_contents($file, '');
        
        $content = file_get_contents($file);
        if (!$content) $content = 'clean done';
        else $content = 'clean fail';
        $this->display('Log/show_log', 'utf-8', 'html', $content, $prefix='');
    }
    
    /**
     * 翻译类容导入
     * 
     */
    public function import_translation()
    {
        if ($_FILES) {
            $lang_set = 'en-us';
            header("content-type:text/html;charset=utf-8");
            $filePath = $_FILES['file']['tmp_name'];
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new PHPExcel();
            //默认用excel2007读取excel，若格式不对，则用之前的版本进行读取
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($filePath)) {
                    echo 'no Excel';
                    return;
                }
            }
            //读取Excel文件
            $PHPExcel = $PHPReader->load($filePath);
            //读取excel文件中的第一个工作表
            $sheet = $PHPExcel->getSheet(0);
            //取得最大的列号
            $allColumn = $sheet->getHighestColumn();
            //取得最大的行号
            $allRow = $sheet->getHighestRow();
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $en = trim((string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue());
                $ch = trim((string)$PHPExcel->getActiveSheet()->getCell("C" . $currentRow)->getValue());
                if (empty($ch)) continue;
                $temp [$en] = $ch;
            }
            $common = $this->getCommonFileContent(include LANG_PATH.$lang_set.'/common.php');
            $ret = array_merge($temp, $common);
            $newAdded = [];
            foreach ($ret as $k => $v) {
                if (array_key_exists ($k, $common)) {
                    continue;
                } else {
                    $newAdded [$k] = $v;
                }
            }
            if ($newAdded) {
                // 备份
                $this->backCommonLanguagePackage(LANG_PATH.$lang_set.'/common.php', LANG_PATH.$lang_set.'/common_back' . time() . '.php');
                $save_text = '<?php return ' . var_export($ret, true) . ';';
                $isok = file_put_contents(LANG_PATH.$lang_set.'/common.php', $save_text);
            }
            $this->assign('is_translation', true);
            $this->assign('newAddedLength', count($newAdded));
            $this->assign('newAdded', $newAdded);
        }
        
        $this->display('Log/import_translation');
    }
    
    /**
     * 供应商客户数据导入
     * 
     */
    public function import_supplier_customer()
    {
        if ($_FILES) {
            $lang_set = 'en-us';
            header("content-type:text/html;charset=utf-8");
            $filePath = $_FILES['file']['tmp_name'];
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new PHPExcel();
            //默认用excel2007读取excel，若格式不对，则用之前的版本进行读取
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($filePath)) {
                    echo 'no Excel';
                    return;
                }
            }
            //读取Excel文件
            $PHPExcel = $PHPReader->load($filePath);
            //读取excel文件中的第一个工作表
            $sheet = $PHPExcel->getSheet(0);
            //取得最大的列号
            $allColumn = $sheet->getHighestColumn();
            //取得最大的行号
            $allRow = $sheet->getHighestRow();
            $temp = [];
            //Excel导入的供应商客户数据
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $flag = false;
                $data = [];
                $name = trim((string)$PHPExcel->getActiveSheet()->getCell("C" . $currentRow)->getValue());//供应商名称
                $type = trim((string)$PHPExcel->getActiveSheet()->getCell("D" . $currentRow)->getValue());//类型（供应商&客户）需要做拆分处理
                $exists = trim((string)$PHPExcel->getActiveSheet()->getCell("F" . $currentRow)->getValue());//是否已存在（中文类型，是或者否）
                $address = trim((string)$PHPExcel->getActiveSheet()->getCell("I" . $currentRow)->getValue());//详细办公地址
                if ($exists == '是') continue;
                if ($type == '供应商') {
                    $type = 0;
                } elseif ($type == '客户') {
                    $type = 1;
                } elseif ($type == '供应商&客户') {
                    $type = 3;
                }
                $data ['SP_NAME'] = $name;
                $data ['CREATE_TIME'] = date('Y-m-d H:i:s');
                $data ['CREATE_USER_ID'] = $_SESSION['userId'];
                $data ['DATA_MARKING'] = $type;
                $data ['COMPANY_ADDR_INFO'] = $address;
                if ($type == 0) {
                    $temp ['supplier'][md5($name)] = $data;
                } elseif ($type == 1) {
                    $temp ['customer'][md5($name)] = $data;
                } else if ($type == 3) {
                    $data ['DATA_MARKING'] = 0;
                    $temp ['supplier'][md5($name)] = $data;
                    $data ['DATA_MARKING'] = 1;
                    $temp ['customer'][md5($name)] = $data;
                }
            }
            //ERP中已有数据
            $model = M('_crm_sp_supplier', 'tb_');
            $ret = $model->field('SP_NAME, DATA_MARKING')->select();
            if ($ret) {
                $existsData = [];
                foreach ($ret as $key => $value) {
                    if ($value ['DATA_MARKING'] == 0) {
                        $existsData ['supplier'][] = md5($value ['SP_NAME']); 
                    } else {
                        $existsData ['customer'][] = md5($value ['SP_NAME']);
                    }
                }
            }
            //再次筛选是否在ERP中存在数据
            if ($temp) {
                foreach ($temp as $key => &$value) {
                    foreach ($value as $k => $v) {
                        if (in_array($k, $existsData [$key])) {
                            unset($value [$k]);
                        }
                    }
                }
                
                foreach ($temp ['supplier'] as $k => $v) {
                    $stemp [] = $v;
                }
                
                foreach ($temp ['customer'] as $k => $v) {
                    $ctemp [] = $v;
                }
            }
            $ret_supplier = $model->addAll($stemp);
            $ret_customer = $model->addAll($ctemp);
            $this->assign('show', true);
            $this->assign('ret_supplier', count($stemp));
            $this->assign('ret_customer', count($ctemp));
        }
        
        $this->display('Log/import_supplier_customer');
    }
    
    /**
     * 获取已翻译的语言包
     * 
     */
    public function getCommonFileContent($name=null) {
        return $name;
    }
    
    /**
     * 备份已翻译的语言包
     * 
     */
    public function backCommonLanguagePackage($oname, $nname)
    {
        $lang_set = 'en-us';
        $oc = file_get_contents($oname);
        $back = file_put_contents(LANG_PATH.$lang_set.'/common_back' . time() . '.php', $oc);
    }
    
    /**
     * 获取基础配置数据
     * 
     */
    public function getDataDirectory()
    {
        $this->assign('isAutoRenew', BaseModel::isAutoRenew());
        $this->assign('spTeamCd', BaseModel::spTeamCd());
        $this->assign('spJsTeamCd', BaseModel::spJsTeamCd());
        $this->assign('copanyTypeCd', BaseModel::conType());
        $this->assign('spYearScaleCd', BaseModel::spYearScaleCd());
        $this->assign('country', BaseModel::getCountry());
        $this->assign('cmnCat', BaseModel::getCmnCat());
        $this->assign('saleTeamCd', BaseModel::saleTeamCd());
    }
    
    public function __get($name) {
        if (isset($this->access[$name])) {
            return true;
        }
        
        return false;
    }
    
    public function generateMethod()
    {
        $this->serviceName = $this->getParams()['serviceName']; 
    }
    
    public function setParams()
    {
        $this->params = $this->getParams();
    }
    
    public function request_do()
    {
        $class = new ReflectionClass($this->module);
        if ($class->hasMethod($this->module . $this->serviceName)) {
            $method = new ReflectionMethod($this->module, $this->module . $this->serviceName);
            if ($method->isPublic()) {
                $back = $method->invoke($this, $this->params);
            } else {
                $back = ['code' => 400, 'message' => 'Bad Request'];
            }
        } else {
            $back = ['code' => 405, 'message' => 'Method Not Allowed'];
        }
        
        $this->ajaxReturn($back);
    }
    
    /**
     * 记录接口日志
     */
    private function _catchMe()
    {
        $filePath = '/opt/logs/logstash/';
        $fileName = 'logstash_' . date('Ymd') . '_erp_json.log';
        $a = parse_url($_SERVER["REQUEST_URI"]);
        parse_str($a["query"], $s);
        $a = $s['a'];
        $m1 =  $s['m'];
        $m = M('');
//获取操作日志
        $res = $m->query("SELECT CONCAT(bbm_node.TITLE,bbm_node.NAME) as opt from bbm_node where lower(bbm_node.CTL)='$m1' AND lower(bbm_node.ACT)='$a' ");
        $action = $s ['a'];
        $tlog = D('TbMsUserOperationLog');
        $data ['uId']           = create_guid();
        $data ['noteType']      = 'N001940200';
        $data ['source']        = 'N001950500';
        $data ['ip']            = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
        $data ['space']         = null;
        $data ['cTime']         = date('Y-m-d H:i:s');
        $data ['cTimeStamp']    = time();
        $data ['action']        = $s ['a'];
        $data ['model']         = $s ['m'];
        $data ['msg']           = json_encode([
            'model' => MODULE_NAME,
            'msg'   => [
                'GET' => $_GET,
                'POST'=> $_POST,
                'action' => $s ['m'],
                'operation' => $res[0]['opt'],
                'uri' => $_SERVER["REQUEST_URI"],
            ]
        ]);
        $data ['user'] = $_SESSION['m_loginname'];
        $tlog->add($data);
        //echo $tlog->_sql();
        //var_dump($tlog->getError());
//        $logFilePath = __DIR__ . '/../../Runtime/Logs/';
//        // 日志对象
//        $log = new \stdClass();
//        $trace = debug_backtrace(0);
//        $logName = date('Y-m-d') . @$trace[2]['function'] . '.log';
//        $txt = "\n------------------------------------------------------------------";
//        $txt .= "\n@@@用户：".$log->datetime = $_SESSION['m_loginname'];
//        $txt .= "\n@@@时间：".$log->datetime = date('Y-m-d H:i:s');
//        $txt .= "\n@@@来源：".$log->ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
//        $txt .= "\n@@@方法：".$log->method = $_SERVER["REQUEST_METHOD"];
//        $txt .= "\n@@@目标：".$log->url = $_SERVER["REQUEST_URI"];
//        $txt .= "\n@@@调用：".$log->callback = sprintf('%s::%s (line:%s)', @$trace[2]['class'], @$trace[2]['function'], @$trace[1]['line']);
//        $txt .= "\n@@@成功：".$log->apiIsok = 'SUCCESS';
//        $txt .= "\n@@@页面变量(GET)：\n".$log->varGet = print_r($_GET, true);
//        $txt .= "\n@@@页面变量(POST)：\n".$log->varPost = print_r($_POST, true);
//        $txt .= "\n------------------------------------------------------------------";
//        // 保存到日志文件

        $data ['id'] = $tlog->getLastInsID();
        $data ['msg'] = json_decode($data['msg']);
        $txt = json_encode($data);
        $file = $filePath . $fileName;
        fclose(fopen($file, 'a+'));
        $_fo = fopen($file, 'rb');
        //$old = fread($_fo, 1024 * 1024);
        fclose($_fo);
        //file_put_contents($file, $txt.$old);
        file_put_contents($file, $txt . "\n", FILE_APPEND);
    }

    /**
     * Json格式输出，同时判定是否为JSON-P请求，如果是JSON-P请求则会输出JSON-P格式数据。
     * @param $result
     */
    protected function jsonOut($result)
    {
        $callback = I('jsonCallBack');
        if (!empty($callback)){
            echo $callback . '(' . json_encode($result) . ')';
        } else {
            echo json_encode($result);
        }
        exit;
    }
}