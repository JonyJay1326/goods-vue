<?php

/**
 * B2B客户管理
 * 
 */
class BTBCustomerManagementAction extends BaseAction
{
    /**
     * 客户列表
     * 
     */
    public function index()
    {
        $model = D('BTBCustomerManagement');
        $contract = D('TbCrmContract');
        $data = $model->parseFieldsMap();
        $condition = $model->searchModel($this->getParams());
        $this->getDataDirectory();
        import('ORG.Util.Page');// 导入分页类
        $count = $model->relation(true)->where($condition)->count();
        $page = new Page($count, 10);
        $show = $page->show();
        $ret = $model->relation(true)->where($condition)->order('tb_crm_sp_supplier.CREATE_TIME desc')->limit($page->firstRow.','.$page->listRows)->select();
        //echo '<pre/>';var_dump($ret);exit;
        // 审核权限
        !isset($this->access['BTBCustomerManagement/audit']) or $this->assign('auditRule', 'BTBCustomerManagement/audit');
        $this->assign('allUserInfo', BaseModel::getAdmin());
        $this->assign('allCountryInfo', BaseModel::getCountryInfo());
        $this->assign('count', $count);
        $this->assign('result', $ret);
        $this->assign('model', $model);
        $this->assign('contract', $contract);
        $this->assign('pages', $show);
        $this->assign('params', $this->getParams());
        
        $this->display();
    }
    
    /**
     * 客户详情
     * 
     */
    public function show()
    {
        $ourCompany = BaseModel::conCompanyCd();
        $this->getDataDirectory();
        
        $model = D('BTBCustomerManagement');
        $ret = $model->relation(true)->where('ID = "' . $this->getParams()['ID'] . '" and DATA_MARKING = 1')->find();
        $this->assign('allUserInfo', BaseModel::getAdmin());
        $this->assign('ourCompany', $ourCompany);
        $this->assign('result', $ret);
        $this->assign('audit', $ret['audit']);
        $this->assign('model', $model);
        $this->assign('title', '客户详情');
        $logField = 'ORD_HIST_REG_DTTM, ORD_STAT_CD, ORD_HIST_WRTR_EML, ORD_HIST_HIST_CONT';
        $ModelLog = M('ms_ord_hist', 'sms_');
        $logList = $ModelLog->field($logField)->where('ORD_NO = "'. $ret['SP_CHARTER_NO'] .'"')->order('ORD_HIST_SEQ desc')->select();
        $this->assign('logList', $logList);
        
        $this->display();
    }
    
    /**
     * 新增客户
     * 
     */
    public function add_customer()
    {
        $this->getDataDirectory();
        $params = $this->getParams();
        $edit_url = 'BTBCustomerManagement/add_customer';
        if (IS_POST) {
            $customer = D('BTBCustomerManagement');
            if ($this->getCustomer($params['SP_CHARTER_NO'])) {
                exit(json_encode(['status' => 0, 'msg' => '该客户已存在，请修改营业执照号', 'data' => '该客户已存在，请修改营业执照号']));   
            }
            if ($_FILES) {
                // 图片上传
                $fd = new FileUploadModel();
                 if ($ret = $fd->uploadFile()) {
                    $params ['SP_ANNEX_NAME'] = $fd->save_name;
                    $params ['SP_ANNEX_ADDR'] = $fd->filePath;
                } else {
                    exit(json_encode(['data' => $fd->error, 'msg' => '新增客户失败', 'status' => 0]));
                }
            }
            $SP_TEAM_CD = $params['SP_TEAM_CD'];
            if ($SP_TEAM_CD) {
                $temp = null;
                foreach ($SP_TEAM_CD as $k => $v) {
                    $temp .= $v . ',';
                }
                $temp = rtrim($temp, ',');
            }
            if ($temp) {
                $params['SP_TEAM_CD'] = $temp;
            }
            if (!$data = $customer->create($params, 1)) {
                //FileUploadModel::unlinkFile($params ['SP_ANNEX_NAME']);
                exit(json_ecode(['data' => $customer->getError(), 'msg' => '新增客户失败', 'status' => 0]));
            } else {
                if ($customer->data($data)->add()) {
                    $m = new BaseModel();
                    $m->customerSendMail($data);
                    $log = A('Home/Log');
                    if ($m->checkNeedAudit($data['SP_ADDR3'])) {
                        $message = '新增客户成功，已发送邮件提醒法务审核!';
                    } else {
                        $message = '新增客户成功，非大陆客户暂不需审核!';
                    }
                    $log->index($data['SP_CHARTER_NO'], 1, $message);
                    exit(json_encode(['status' => 1, 'msg' => $message]));
                }
            }
        }
        $this->assign('must_need_upload_file', 1);
        $this->assign('title', '新增客户');
        $this->display();
    }
    
    /**
     * 修改客户
     * 
     */
    public function update_customer()
    {
        $this->getDataDirectory();
        $btb = D("BTBCustomerManagement");
        $edit_url = 'BTBCustomerManagement/update_customer';
        if (IS_POST) {
            $ret = $btb->updateCustomerInfo();
            exit(json_encode($ret));
        }
        if ($ID = $this->getParams()['ID']) {
            $result = $btb->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('must_need_upload_file', 0);
        $this->assign('title', '更新客户');
        $this->assign('edit_url', $edit_url);
        $this->display('add_customer');
    }
    
    /**
     * 删除客户
     * 
     */
    public function del_supplier()
    {
        $id = $this->getParams()['ID'];
        if ($id) {
            $model = D('BTBCustomerManagement');
            $ret = $model->relation(true)->find($id);
            if ($ret['contracts']) {
                $info = [
                    'msg' => '删除失败，该客户已签订合同',
                ];
                $this->ajaxReturn($data, $info, 0);
            }
            if ($model->delete($id)) {
                $audit = M('_ms_forensic_audit', 'tb_');
                $audit->where('CRM_CON_TYPE = 1 and SP_CHARTER_NO = "' . $ret ['SP_CHARTER_NO'] . '"')->delete();
                $info = [
                    'msg' => '删除成功',
                ];
                $this->ajaxReturn($data, $info, 1);
            } else {
                $info = [
                    'msg' => '删除失败'
                ];
                $this->ajaxReturn($data, $info, 0);
            }
        }
        $info = [
            'msg' => '访问异常'
        ];
        $this->ajaxReturn($data, $info, 0);
    }
    
    /**
     * 客户审核
     * 
     */
    public function audit()
    {
        $model = D('BTBCustomerManagement');
        $ret = $model->relation(true)->find($this->getParams()['ID']);
        $this->getDataDirectory();
        $nagetiveOptions = BaseModel::getNagetiveOptions();
        if (IS_AJAX) {
            $model = D("TbMsForensicAudit");
            $post = $this->getParams();
            // 如果有负面信息
            if ($post['IS_HAVE_NAGETIVE_INFO']) {
                $checkedNagetiveOptions = $post ['C_NAGETIVE_OPTIONS']; // 负面信息选项
                $list = explode(',', $checkedNagetiveOptions);
            }
            $temp = [];
            foreach ($list as $k => $v) {
                $temp ['TIME_' . $v] = $post ['TIME_' . $v];
                $temp ['DUC_' . $v] = $post ['DUC_' . $v];
            }
            
            $post ['C_NAGETIVE_VAL'] = json_encode($temp);
            if ($isok = $model->where('CRM_CON_TYPE = 1 and SP_CHARTER_NO = "' . $post['SP_CHARTER_NO'] .'"')->find()) exit(json_encode(['data' => '审核失败，重复审核', 'msg' => '审核失败，重复审核', 'status' => 0]));
            if (!$data = $model->create($post, 1)) {
                exit(json_encode(['data' => $model->getError(), 'msg' => '法务审核失败', 'status' => 0]));
            } else {
                if ($model->data($data)->add()) {
                    $supplier = M('_crm_sp_supplier', 'tb_');
                    $ret = $supplier->where('DATA_MARKING = 1 and SP_CHARTER_NO = "'. $post['SP_CHARTER_NO'] .'"')->find();
                    $ret ['AUDIT_STATE'] = 2;
                    $ret ['RISK_RATING'] = $post['RISK_RATING'];
                    $supplier->save($ret);
                    $sdata = [];
                    foreach ($data as $key => $value) {
                        if (!$value) $sdata [$key] = null;
                        $sdata [$key] = $value;
                    }
                    $log = A('Home/Log');
                    $log->index($data['SP_CHARTER_NO'], 1, '法务审核完成');
                    exit(json_encode(['status' => 1, 'msg' => '法务审核完成']));
                } else {
                    exit(json_encode(['data' => $model->getError(), 'status' => 0, 'msg' => '法务审核失败']));
                }
            }
        }
        $this->assign('currency', BaseModel::getCurrency()); // 币种
        $this->assign('creditGrade', BaseModel::getCreditGrade()); // 信用评级
        $this->assign('nagetiveOptions', $nagetiveOptions); // 负面信息选项
        $this->assign('auditGradeStandard', BaseModel::auditGradeStandard()); // 评级标准
        $this->assign('isHaveNagetive', BaseModel::isHaveNagetive());
        $this->assign('riskRating', BaseModel::riskRating());
        $this->assign('result', $ret);
        
        $this->display();
    }
    
    /**
     * 法务审核更新
     * 
     */
    public function update_audit()
    {   
        $supplier = D('BTBCustomerManagement');
        $ret = $supplier->relation(true)->find($this->getParams()['ID']);
        $this->getDataDirectory();
        $nagetiveOptions = BaseModel::getNagetiveOptions();
        if (IS_POST) {
            $model = D("TbMsForensicAudit");
            $post = $this->getParams();
            // 如果有负面信息
            if ($post['IS_HAVE_NAGETIVE_INFO']) {
                $temp = [];
                $checkedNagetiveOptions = $post ['C_NAGETIVE_OPTIONS']; // 负面信息选项
                $list = explode(',', $checkedNagetiveOptions);
                foreach ($list as $k => $v) {
                    $temp ['TIME_' . $v] = $post ['TIME_' . $v];
                    $temp ['DUC_' . $v] = $post ['DUC_' . $v];
                }
                $post ['C_NAGETIVE_VAL'] = json_encode($temp);
            } else {
                $post ['C_NAGETIVE_VAL'] = '';
            }
            $model->create($post, 2);
            if ($model->save()) {
                $log = A('Home/Log');
                $log->index($data['SP_CHARTER_NO'], 1, '更新审核完成');
                exit(json_encode(['status' => 1, 'msg' => '更新审核完成']));
            } else {
                exit(json_encode(['data' => $model->getError(), 'status' => 0, 'msg' => '更新审核失败']));
            }
        }
        $this->assign('isHaveNagetive', BaseModel::isHaveNagetive());
        $this->assign('riskRating', BaseModel::riskRating());
        $this->assign('currency', BaseModel::getCurrency()); // 币种
        $this->assign('creditGrade', BaseModel::getCreditGrade()); // 信用评级
        $this->assign('nagetiveOptions', $nagetiveOptions); // 负面信息选项
        $this->assign('auditGradeStandard', BaseModel::auditGradeStandard()); // 评级标准
        $this->assign('result', $ret);
        $this->display();
    }
    
    /**
     * 上传合同
     * 
     */
    public function upload_contract()
    {
        $this->getDataDirectory();
        $edit_url = 'BTBCustomerManagement/upload_contract';
        $contract = D("TbCrmContract");
        if (IS_POST) {
            $params = $this->getParams();
            if ($_FILES) {
                // 图片上传
                $fd = new FileUploadModel();
                if ($_FILES['SP_ANNEX_ADDR1'] and $fd->saveFile($_FILES['SP_ANNEX_ADDR1'])) {
                    $params ['SP_ANNEX_ADDR1'] = $fd->info [0]['savename'];
                } else {
                    exit(json_encode(['data' => $fd->error, 'msg' => '新增供应商失败', 'status' => 0]));
                }
                
                if ($_FILES['SP_ANNEX_ADDR2'] and $fd->saveFile($_FILES['SP_ANNEX_ADDR2'])) {
                    $params ['SP_ANNEX_ADDR2'] = $fd->info [0]['savename'];
                }
            }
            $params ['PAY_NODS'] = json_encode($params ['PAY_NODS']);
            if ($params ['PAY_TYPE'] == 0) {
                $params ['PAY_NODS'] = null;
            }
            // 首先检查合同编号是否在SMS系统中存在
            $model = M('_crm_contract', 'tb_');
            $con = $model->where('CON_NO = "' . $params['CON_NO'] .'" and CRM_CON_TYPE = 1')->find();
            if ($con) $this->ajaxReturn('编号为：' . $params['CON_NO'] .' 的合同已存在，请修改', '', 0);
            // CRM_CON_TYPE合同所属类型
            if (!$data = $contract->create($params, 1)) {
                //empty($params ['SP_ANNEX_ADDR1']) or FileUploadModel::unlinkFile($params ['SP_ANNEX_ADDR1']);
                //empty($params ['SP_ANNEX_ADDR2']) or FileUploadModel::unlinkFile($params ['SP_ANNEX_ADDR2']);
                $this->ajaxReturn($contract->getError(), '上传合同失败', 0);
            } else {
                // 无审核信息，则新增审核信息
                if ($params['NEED_ADD_AUDIT'] == 0) {
                //    $this->ajaxReturn('该客户审核未通过，请核对！', '上传合同失败', 0);
                //    $data ['audit'] = $params['audit'];
                    //验证是否是不需要审核的区域
                    //BaseModel::regionalClassification();
                } else {
                    // 后台验证是否有审核信息，防止绕过前端验证
                    $model = M('_ms_forensic_audit', 'tb_');
                    $ret = $model->where('SP_CHARTER_NO = "' . $params['SP_CHARTER_NO'] . '"')->find();
                    if (!$ret) $this->ajaxReturn('该客户审核未通过，请核对！', '上传合同失败', 0); 
                }
                if ($data ['CON_PHONE']) {
                    $data ['BAK_CON_PHONE'] = $data ['CON_PHONE'];
                    $con_phone_ret = CrypMobile::enCryp($data ['CON_PHONE']);
                    if ($con_phone_ret ['code'] == 200) {
                        $data ['CON_PHONE'] = $con_phone_ret ['data'];
                    }
                }
                if ($data ['CON_TEL']) {
                    $data ['BAK_CON_TEL'] = $data ['CON_TEL'];
                    $con_tel_ret = CrypMobile::enCryp($data ['CON_TEL']);
                    if ($con_tel_ret ['code'] == 200) {
                        $data ['CON_TEL'] = $con_tel_ret ['data'];
                    }
                }
                if ($result = $contract->relation(true)->add($data)) {
                    $log = A('Home/Log');
                    $log->index($params['SP_CHARTER_NO'], 1, '上传合同成功');
                    $this->ajaxReturn($result, '上传合同成功', 1);
                } else {
                    $this->ajaxReturn($contract->getError(), '上传合同失败', 0);
                }
            }
        }
        if ($ID = $this->getParams()['ID']) {
            $result = $contract->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('must_need_select_node', 1);
        $this->assign('must_need_upload_file', 1);
        $this->assign('isSelectInfo', false);
        $this->assign('edit_url', $edit_url);
        $this->assign('chinaMainlandAndHMT', BaseModel::regionalClassification());
        $this->assign('paymentMode', BaseModel::paymentMode());
        $this->assign('title', '上传合同');
        $this->display('contract');
    }
    
    /**
     * 合同下载
     * 
     */
    public function contract_download()
    {
        // SP_ANNEX_ADDR1 合同地址
        $model = M('_crm_contract', 'tb_');
        $ret = $model->where('ID = ' . $_GET['ID'])->find();
        if ($ret and $ret['SP_ANNEX_ADDR1']) {
            $fd = new FileDownloadModel();
            $fd->fname = $ret['SP_ANNEX_ADDR1'];
            try{
                if (!$fd->downloadFile()) {
                    $this->error("文件不存在");
                }
            }catch (exception $e) {
                $this->error('文件不存在');
            }
        }
        
        return false;
    }
    
    /**
     * 查看合同
     * 
     */
    public function show_contract()
    {
        $this->getDataDirectory();
        $contract = D("TbCrmContract");
        
        $edit_url = 'BTBCustomerManagement/update_contract';
        
        if ($ID = $this->getParams()['ID']) {
            $result = $contract->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('title', '合同详情');
        $this->assign('edit_url', $edit_url);
        
        $this->display();
    }
    
    /**
     * 更新合同
     * 
     */
    public function update_contract()
    {
        $this->getDataDirectory();
        $contract = D("TbCrmContract");
        $edit_url = 'BTBCustomerManagement/update_contract';
        if (IS_POST) {
            if ($_FILES) {
                // 图片上传
                $fd = new FileUploadModel();
                if ($_FILES['SP_ANNEX_ADDR1'] and $fd->saveFile($_FILES['SP_ANNEX_ADDR1'])) {
                    $_POST ['SP_ANNEX_ADDR1'] = $fd->info [0]['savename'];
                } else {
                    exit(json_encode(['data' => $fd->error, 'msg' => '新增供应商失败', 'status' => 0]));
                }
                
                if ($_FILES['SP_ANNEX_ADDR2'] and $fd->saveFile($_FILES['SP_ANNEX_ADDR2'])) {
                    $_POST ['SP_ANNEX_ADDR2'] = $fd->info [0]['savename'];
                }
            } else {
                unset($_POST['SP_ANNEX_ADDR1']);
                unset($_POST['SP_ANNEX_ADDR2']);
            }
            if ($_POST ['PAY_NODS']) {
                $_POST ['PAY_NODS'] = json_encode($_POST ['PAY_NODS']);
            }
            if ($_POST['PAY_TYPE'] == 0) {
                $_POST ['PAY_NODS'] = Null;
            }
            // 是否是长期合同，1为长期合同
            if ($_POST['CONTRACT_TYPE'] == 1) {
                $_POST['END_TIME'] = Null;
            }
            if ($_POST ['CON_PHONE']) {
                $_POST ['BAK_CON_PHONE'] = $_POST ['CON_PHONE'];
                $con_phone_ret = CrypMobile::enCryp($_POST ['CON_PHONE']);
                if ($con_phone_ret ['code'] == 200) {
                    $_POST ['CON_PHONE'] = $con_phone_ret ['data'];
                }
            }
            if ($_POST ['CON_TEL']) {
                $_POST ['BAK_CON_TEL'] = $_POST ['CON_TEL'];
                $con_tel_ret = CrypMobile::enCryp($_POST ['CON_TEL']);
                if ($con_tel_ret ['code'] == 200) {
                    $_POST ['CON_TEL'] = $con_tel_ret ['data'];
                }
            }
            $contract->create($_POST, 0);
            if ($contract->where('ID =' . $_POST['ID'])->save() === false) {
                $this->ajaxReturn($contract->getError(), '更新失败', 0);
                //exit(json_encode(['status' => 0, 'msg' => '更新成功', 'data' => $contract->getError()]));
            } else {
                $log = A('Home/Log');
                $log->index($_POST['SP_CHARTER_NO'], 1, '更新合同成功');
                $this->ajaxReturn('', '更新成功', 1);
                //exit(json_encode(['status' => 1, 'msg' => '更新成功', '']));
            }
        }
        if ($ID = $this->getParams()['ID']) {
            $result = $contract->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('must_need_select_node', 0);
        $this->assign('must_need_upload_file', 0);
        $this->assign('chinaMainlandAndHMT', BaseModel::regionalClassification());
        $this->assign('isSelectInfo', true);
        $this->assign('edit_url', $edit_url);
        $this->assign('paymentMode', BaseModel::paymentMode());
        $this->assign('title', '更新合同');
        $this->display('contract');
    }
    
    /**
     * 营业执照下载
     * 
     */
    public function business_license_download()
    {
        // SP_ANNEX_ADDR1 合同地址
        $model = M('_crm_sp_supplier', 'tb_');
        $ret = $model->where('ID = ' . $_GET['ID'])->find();
        if ($ret and $ret['SP_ANNEX_NAME']) {
            $fd = new FileDownloadModel();
            $fd->fname = $ret['SP_ANNEX_NAME'];
            try{
                if (!$fd->downloadFile()) {
                    $this->error("文件不存在");
                }
            }catch (exception $e) {
                $this->error('文件不存在');
            }
        }
        
        return false;
    }
    
    /**
     * 名片下载
     * 
     */
    public function business_card_download()
    {
        // SP_ANNEX_ADDR1 合同地址
        $model = M('_crm_contract', 'tb_');
        $ret = $model->where('ID = ' . $_GET['ID'])->find();
        if ($ret and $ret['SP_ANNEX_ADDR1']) {
            $fd = new FileDownloadModel();
            $fd->fname = $ret['SP_ANNEX_ADDR2'];
            try{
                if (!$fd->downloadFile()) {
                    $this->error("文件不存在");
                }
            }catch (exception $e) {
                $this->error('文件不存在');
            }
        }
        
        return false;
    }
    
    /**
     * 动态加载客户模块
     * 
     */
    public function autoload_supplier()
    {
        if (IS_AJAX) {
            // 如果查询到客户，则加载显示客户模块
            if ($ret = $this->getCustomer($this->getParams()['sp_charter_no'])) {
                $this->ajaxReturn('已获取到客户信息', $ret, 1);
            } else {// 未查询到客户，则显示新增客户模块
                $this->ajaxReturn('未查询到该客户', '', 0);
            }  
        } else {
            $this->ajaxReturn('异常请求', '', 0);
        }
    }
    
    /**
     * 根据营业执照号查询客户
     * 通过执照号再数据标示码做数据的唯一性验证
     * 
     */
    public function getCustomer($sp_charter_no, $data_marking = 1)
    {
        $model = D('BTBCustomerManagement');
        $ret = $model->relation(true)->where('SP_CHARTER_NO = "' . $sp_charter_no . '" and DATA_MARKING = ' . $data_marking)->find();
        return $ret;
    }
    
    /**
     * 客户批量导入
     * 
     */
    public function mult_import_customer()
    {
        $im = new ImportMulCustomerModel();
        $ret = $im->import();
        $this->assign('title', '批量导入客户');
        if ($ret ['state'] == 1) {
            $this->assign('show', true);
            $this->assign('ret_supplier', $ret ['msg']);
        } else {
            $this->assign('show', false);
            $this->assign('errorinfo', $ret ['msg']);
        }
        
        $this->display('Log/mul_import');
    }
}