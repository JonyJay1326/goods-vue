<?php
class SupplierAction extends BaseAction
{
    /**
     * 供应商列表
     * 
     */
    public function index()
    {
        $model = D('TbCrmSpSupplier');
        $contract = D('TbCrmContract');
        $data = $model->parseFieldsMap();
        $condition = $model->searchModel($this->getParams());
        $this->getDataDirectory();
        import('ORG.Util.Page');// 导入分页类
        $count = $model->relation(true)->where($condition)->count();
        $page = new Page($count, 20);
        $show = $page->show();
        $ret = $model->relation(true)->where($condition)->order('tb_crm_sp_supplier.CREATE_TIME desc')->limit($page->firstRow.','.$page->listRows)->select();
        // 审核权限
        !isset($this->access['Supplier/audit']) or $this->assign('auditRule', 'Supplier/audit');
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
     * 新增供应商
     * 
     */
    public function newly_added()
    {
        $this->getDataDirectory();
        $params = $this->getParams();
        $edit_url = 'Supplier/newly_added';
        if (IS_POST) {
            $supplier = D('TbCrmSpSupplier');
            if ($this->search_supplier_by_sp_no($params['SP_CHARTER_NO'])) {
                exit(json_encode(['status' => 0, 'msg' => '该供应商已存在，请修改营业执照号', 'data' => '该供应商已存在，请修改营业执照号']));   
            }
            if ($_FILES) {
                // 图片上传
                $fd = new FileUploadModel();
                if ($ret = $fd->uploadFile()) {
                    $params ['SP_ANNEX_NAME'] = $fd->save_name;
                    $params ['SP_ANNEX_ADDR'] = $fd->filePath;
                } else {
                    exit(json_encode(['data' => $fd->error, 'msg' => '新增供应商失败', 'status' => 0]));
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
            if (!$data = $supplier->create($params, 1)) {
                //FileUploadModel::unlinkFile($params ['SP_ANNEX_NAME']);
                exit(json_encode(['data' => $supplier->getError(), 'msg' => '新增供应商失败', 'status' => 0]));
            } else {
                if ($supplier->data($data)->add()) {
                    $log = A('Home/Log');
                    $m = new BaseModel();
                    $m->supplierSendMail($data);
                    if ($m->checkNeedAudit($data['SP_ADDR3'])) {
                        $message = '新增供应商成功，已发送邮件提醒法务审核!';
                    } else {
                        $message = '新增供应商成功，非大陆客户暂不需审核!';
                    }
                    $log->index($data['SP_CHARTER_NO'], 0, $message);
                    exit(json_encode(['status' => 1, 'msg' => $message]));
                }
            }
        }
        $this->assign('must_need_upload_file', 1);
        $this->assign('title', '新增供应商');
        
        $this->display("add_supplier");
    }
    
    /**
     * 更新供应商
     * 
     */
    public function update_supplier()
    {
        $this->getDataDirectory();
        $supplier = D("TbCrmSpSupplier");
        $edit_url = 'Supplier/update_supplier';
        
        // 更新供应商
        if (IS_POST) {
            $ret = $supplier->updateSupplierOrCustomer();
            exit(json_encode($ret));
        }
        // 获取供应商数据
        if ($ID = $this->getParams()['ID']) {
            $result = $supplier->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('must_need_upload_file', 0);
        $this->assign('title', '更新供应商');
        $this->assign('edit_url', $edit_url);
        $this->display('add_supplier');
    }
    
    
    /**
     * 删除供应商
     * 
     */
    public function del_supplier()
    {
        $id = $this->getParams()['ID'];
        if ($id) {
            $model = D('TbCrmSpSupplier');
            $ret = $model->relation(true)->find($id);
            if ($ret['contracts']) {
                $info = [
                    'msg' => '删除失败，该供应商已签订合同',
                ];
                $this->ajaxReturn($data, $info, 0);
            }
            if ($model->delete($id)) {
                $audit = M('_ms_forensic_audit', 'tb_');
                $audit->where('CRM_CON_TYPE = 0 and SP_CHARTER_NO = "' . $ret ['SP_CHARTER_NO'] . '"')->delete();
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
     * 法务审核
     * 
     */
    public function audit()
    {
        $supplier = D('TbCrmSpSupplier');
        $ret = $supplier->relation(true)->find($this->getParams()['ID']);
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
            
            if ($isok = $model->where('CRM_CON_TYPE = 0 and SP_CHARTER_NO = "' . $post['SP_CHARTER_NO'] .'"')->find()) exit(json_encode(['data' => '审核失败，重复审核', 'msg' => '审核失败，重复审核', 'status' => 0]));
            if (!$data = $model->create($post, 1)) {
                exit(json_encode(['data' => $model->getError(), 'msg' => '法务审核失败', 'status' => 0]));
            } else {
                if ($model->data($data)->add()) {
                    $supplier = M('_crm_sp_supplier', 'tb_');
                    $ret = $supplier->where('DATA_MARKING = 0 and SP_CHARTER_NO = "'. $post['SP_CHARTER_NO'] .'"')->find();
                    $ret ['AUDIT_STATE'] = 2;
                    $ret ['RISK_RATING'] = $post['RISK_RATING'];
                    $supplier->save($ret);
                    $sdata = [];
                    foreach ($data as $key => $value) {
                        if (!$value) $sdata [$key] = null;
                        $sdata [$key] = $value;
                    }
                    $log = A('Home/Log');
                    $log->index($data['SP_CHARTER_NO'], 0, '法务审核完成');
                    exit(json_encode(['status' => 1, 'msg' => '法务审核完成']));
                } else {
                    exit(json_encode(['data' => $model->getError(), 'status' => 0, 'msg' => '法务审核失败']));
                }
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
     * 法务审核更新
     * 
     */
    public function update_audit()
    {   
        $supplier = D('TbCrmSpSupplier');
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
                $log->index($data['SP_CHARTER_NO'], 0, '更新审核完成');
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
        $edit_url = 'Supplier/upload_contract';
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
            //查询合同表中是否已有该合同
            $model = M('_crm_contract', 'tb_');
            $con = $model->where('CON_NO = "' . $params['CON_NO'] . '"')->find();
            if ($con) $this->ajaxReturn('编号为：' . $params['CON_NO'] .' 的合同已存在，请修改', '', 0);
            //根据模型进行数据验证
            if (!$data = $contract->create($params, 1)) {
                //empty($params ['SP_ANNEX_ADDR1']) or FileUploadModel::unlinkFile($params ['SP_ANNEX_ADDR1']);
                //empty($params ['SP_ANNEX_ADDR2']) or FileUploadModel::unlinkFile($params ['SP_ANNEX_ADDR2']);
                $this->ajaxReturn($contract->getError(), '上传合同失败', 0);
            } else {
                if ($params ['NEED_ADD_AUDIT'] == 0) {
                    
                } else {
                    //供应商审核信息检查
                    $model = M('_ms_forensic_audit', 'tb_');
                    $ret = $model->where('SP_CHARTER_NO = "' . $params['SP_CHARTER_NO'] . '"')->find();
                    if (!$ret) $this->ajaxReturn('该供应商审核未通过，请核对', '上传合同失败', 0);
                }
                //手机号加密
                if ($data ['CON_PHONE']) {
                    $data ['BAK_CON_PHONE'] = $data ['CON_PHONE'];
                    $con_phone_ret = CrypMobile::enCryp($data ['CON_PHONE']);
                    if ($con_phone_ret ['code'] == 200) $data ['CON_PHONE'] = $con_phone_ret ['data'];
                }
                //固话加密
                if ($data ['CON_TEL']) {
                    $data ['BAK_CON_TEL'] = $data ['CON_TEL'];
                    $con_tel_ret = CrypMobile::enCryp($data ['CON_TEL']);
                    if ($con_tel_ret ['code'] == 200) $data ['CON_TEL'] = $con_tel_ret ['data'];
                }
                //写进数据库
                if ($result = $contract->relation(true)->add($data)) {
                    $log = A('Home/Log');
                    $log->index($params['SP_CHARTER_NO'], 0, '上传合同成功');
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
        $this->assign('must_need_upload_file', 1);
        $this->assign('edit_url', $edit_url);
        $this->assign('chinaMainlandAndHMT', BaseModel::regionalClassification());
        $this->assign('isSelectInfo', false);
        $this->assign('title', '上传合同');
        $this->display('contract');
    }
    
    /**
     * 查看合同
     * 
     */
    public function show_contract()
    {
        $this->getDataDirectory();
        $contract = D("TbCrmContract");
        
        if ($ID = $this->getParams()['ID']) {
            $result = $contract->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('title', '合同详情');
        
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
        $edit_url = 'Supplier/update_contract';
        if (IS_POST) {
            if ($_FILES) {
                // 图片上传
                $fd = new FileUploadModel();
                if ($_FILES['SP_ANNEX_ADDR1'] and $fd->saveFile($_FILES['SP_ANNEX_ADDR1'])) {
                    $_POST ['SP_ANNEX_ADDR1'] = $fd->info [0]['savename'];
                } else {
                    unset($_POST['SP_ANNEX_ADDR1']);
                }
                if ($_FILES['SP_ANNEX_ADDR2'] and $fd->saveFile($_FILES['SP_ANNEX_ADDR2'])) {
                    $_POST ['SP_ANNEX_ADDR2'] = $fd->info [0]['savename'];
                } else {
                    unset($_POST['SP_ANNEX_ADDR2']);
                }
                if ($fd->error) {
                    exit(json_encode(['data' => $fd->error, 'msg' => '更新供应商失败', 'status' => 0]));
                }
            } else {
                unset($_POST['SP_ANNEX_ADDR1']);
                unset($_POST['SP_ANNEX_ADDR2']);
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
                //exit(json_encode(['status' => 0, 'msg' => '更新失败', 'data' => $contract->getError()]));
            } else {
                $log = A('Home/Log');
                $log->index($_POST['SP_CHARTER_NO'], 0, '更新合同成功');
                $this->ajaxReturn('', '更新成功', 1);
                //exit(json_encode(['status' => 1, 'msg' => '更新成功', '']));
            }
        }
        if ($ID = $this->getParams()['ID']) {
            $result = $contract->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('must_need_upload_file', 0);
        $this->assign('chinaMainlandAndHMT', BaseModel::regionalClassification());
        $this->assign('isSelectInfo', true);
        $this->assign('edit_url', $edit_url);
        $this->assign('title', '更新合同');
        $this->display('contract');
    }
    
    /**
     * 根据执照号查询是否有相应的供应商
     * 
     */
    public function search_supplier_by_sp_no($spNo)
    {
        $model = D('TbCrmSpSupplier');
        $ret = $model->relation(true)->where('SP_CHARTER_NO="' . $spNo . '" and DATA_MARKING = 0')->find();
        if ($ret) return $ret;
        return false;
    }
    
    /**
     * 动态加载供应商模块
     * 
     */
    public function autoload_supplier()
    {
        if (IS_AJAX) {
            // 如果查询到供应商，则加载显示供应商模块
            if ($ret = $this->search_supplier_by_sp_no($this->getParams()['sp_charter_no'])) {
                $this->ajaxReturn('已获取到供应商信息', $ret, 1);
            } else {// 未查询到供应商，则显示新增供应商模块
                $this->ajaxReturn('未查询到该供应商', '', 0);
            }  
        } else {
            $this->ajaxReturn('异常请求', '', 0);
        }
    }
    
    /**
     * 查看
     * 
     */
    public function show()
    {
        $ourCompany = BaseModel::conCompanyCd();
        $this->getDataDirectory();
        
        $model = D('TbCrmSpSupplier');
        $ret = $model->relation(true)->where('ID = "' . $this->getParams()['ID'] . '"')->find();
        $this->assign('allUserInfo', BaseModel::getAdmin());
        $this->assign('ourCompany', $ourCompany);
        $this->assign('result', $ret);
        $this->assign('audit', $ret['audit']);
        $this->assign('model', $model);
        $this->assign('title', '供应商详情');
        $logField = 'ORD_HIST_REG_DTTM, ORD_STAT_CD, ORD_HIST_WRTR_EML, ORD_HIST_HIST_CONT';
        $ModelLog = M('ms_ord_hist', 'sms_');
        $logList = $ModelLog->field($logField)->where('ORD_NO = "'. $ret['SP_CHARTER_NO'] .'"')->order('ORD_HIST_SEQ desc')->select();
        $this->assign('logList', $logList);

        $this->display();
    }
    
    public function get_province()
    {
        $data = BaseModel::getProvince(1);
        $info = [
            'msg' => 'success',
        ];
        $this->ajaxReturn($data, $info, 1);
    }
    
    public function get_city()
    {
        $parent_ids = $this->getParams()['id'];
        $data = BaseModel::getCity($parent_ids);
        $info = [
            'msg' => 'success',
        ];
        $this->ajaxReturn($data, $info, 1);
    }
    
    public function get_county()
    {
        $parent_ids = $this->getParams()['id'];
        $data = BaseModel::getCounty($parent_ids);
        $info = [
            'msg' => 'success',
        ];
        $this->ajaxReturn($data, $info, 1);
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
     * 供应商批量导入
     * 
     */
    public function mult_import_supplier()
    {
        $im = new ImportMulSupplierModel();
        $ret = $im->import();
        $this->assign('title', '批量导入供应商');
        if ($ret ['state'] == 1) {
            $this->assign('show', true);
            $this->assign('ret_supplier', $ret ['msg']);
        } else {
            $this->assign('show', false);
            $this->assign('errorinfo', $ret ['msg']);
        }
        
        $this->display('Log/mul_import');
    }
    
    /**
     * 测试基类
     * 
     */
    public function test_import_excel()
    {
        $im = new BaseImportExcelModel();
        $ret = $im->import();
        echo '<pre/>';var_dump($im->data);exit;
    }
}