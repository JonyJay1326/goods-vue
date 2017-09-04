<?php
class ContractAction extends BaseAction
{
    /**
     * 合同列表
     * 
     */
    public function index()
    {
        $params = $this->getParams();
        $contract = D('TbCrmContract');
        $this->getDataDirectory();
        import('ORG.Util.Page');// 导入分页类
        $condition = $contract->searchModel($params);
        $count = $contract->where($condition)->count();
        $page = new Page($count, 10);
        $show = $page->show();
        $ret = $contract->where($condition)->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('allUserInfo', BaseModel::getAdmin());
        $this->assign('allCountryInfo', BaseModel::getCountryInfo());
        $this->assign('count', $count);
        $this->assign('result', $ret);
        $this->assign('model', $contract);
        $this->assign('pages', $show);
        $this->assign('params', $params);
        
        $this->display();
    }
    
    /**
     * 根据合同编号查询是否有相应的合同
     * 
     */
    public function search_contracct_by_con_no($conNo, $type, $isSelectInfo)
    {
        // 如果只是查询
        if ($isSelectInfo) return false;
        $model = D('TbCrmContract');
        $ret = $model->where('CON_NO ="' . $conNo . '" and CRM_CON_TYPE = ' . $type)->find();
        if ($ret) return $ret;
        return false;
    }
    
    /**
     * 检查OA系统中，是否存在该合同
     * 
     */
    public function check_contract()
    {
        $CON_NO = $this->getParams()['CON_NO'];
        $type = $this->getParams()['type'];
        $isSelectInfo = $this->getParams()['isSelectInfo'];
        // 首先检查是否在系统中存在该合同
        if ($this->search_contracct_by_con_no($CON_NO, $type, $isSelectInfo)) {
            $this->ajaxReturn('编号为：' . $CON_NO .' 的合同已存在，请修改', '', 0);
        }
        // 首先检查合同编号是否在SMS系统中存在
        $model = M('_crm_contract', 'tb_');
        $con = $model->where('CON_NO = "' . $CON_NO . '"and CRM_CON_TYPE = ' . $type)->find();
        if ($isSelectInfo) {
            $data ['DFGSMCKESHANG']     = $con ['SP_NAME'];                     // 供应商名称
            $data ['SFZDXY']            = $con ['IS_RENEWAL'];                  // 是否自动续约 
            $data ['GSMC']              = $con ['CON_COMPANY_CD'];              // 我方公司
            $data ['HTLX']              = $con ['CON_TYPE'];                    // 合同类型
            $data ['CGBUSINESSLICENSE'] = $con ['SP_CHARTER_NO'];               // 营业执照号
            $data ['LASTNAME']          = explode('-', $con ['CONTRACTOR'])[1]; // 签约人
            $data ['SQR']               = explode('-', $con ['CONTRACTOR'])[0]; // 签约人编号
            $data ['PERIOD_FROM']       = $con ['START_TIME'];                  // 开始时间
            $data ['PERIOD_TO']          = $con ['END_TIME'];                   // 结束时间
            $data ['CGGYSKHH']          = $con ['SP_BANK_CD'];                  // 供应商开户行
            $data ['CGYHZH']            = $con ['BANK_ACCOUNT'];                // 银行账号
            $data ['CGSWIFTCODE']       = $con ['SWIFT_CODE'];                  // Swift Code
            $data ['CGDFLXR']           = $con ['CONTACT'];                     // 对方联系人
            $data ['CGEMAIL']           = $con ['CON_EMAIL'];                   // 电子邮箱
            $data ['CGLXDH']            = $con ['CON_PHONE'];                   // 联系手机
            $data ['CGLXDH']            = $con ['CON_PHONE'];                   // 联系手机
            $data ['CONTRACT_TYPE']     = $con ['CONTRACT_TYPE'];               // 是否长期合同
            $this->ajaxReturn($data, '', 1);
        } 
        
        $oci = new MeBYModel();
        //$sql = "SELECT * FROM ECOLOGY.FORMTABLE_MAIN_91 WHERE DJBH ='" . $CON_NO . "'";
        $sql = "SELECT * FROM ECOLOGY.FORMTABLE_MAIN_91 a left join ECOLOGY.HRMRESOURCE b on a.SQR = b.ID  WHERE DJBH='" . $CON_NO . "'";
        $checkSql = "SELECT wr.STATUS FROM ECOLOGY.FORMTABLE_MAIN_91 fm LEFT JOIN ECOLOGY.WORKFLOW_REQUESTBASE wr on fm.REQUESTID = wr.REQUESTID WHERE DJBH = '" . $CON_NO . "'";
        // $checkRet = $oci->testQuery($checkSql);
        // 检查合同是否已在OA系统中什么过
        //if ($checkRet[0]['STATUS'] != '结束') $this->ajaxReturn('编号为：' . $CON_NO .' 的合同不存在或尚未完成审核，请修改', '', 0);
        // 检查合同是否在OA系统中存在
        $ret = $oci->testQuery($sql);
        if ($ret) {
            $data = $ret [0];
            $searchCompanyNameSql = "SELECT fm.COMPANY FROM ECOLOGY.MMP fm WHERE ID = '" . $data['DFGSMCKESHANG'] . "'";
            $companyName = $oci->testQuery($searchCompanyNameSql);
            if ($companyName) {
                $data ['DFGSMCKESHANG'] = $companyName[0]['COMPANY'];
            }
            $this->ajaxReturn($data, '', 1);
        } else {
            $this->ajaxReturn('', '未查询到编号为：' . $CON_NO .' 的合同', 1);
        }
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
           
            if (!$data = $contract->create($this->getParams(), 1)) {
                $this->ajaxReturn($contract->getError(), '上传合同失败', 0);
            } else {
                if ($contract->data($data)->add()) {
                    $this->ajaxReturn(null, '上传合同成功', 1);
                }
            }
        }
        
        if ($ID = $this->getParams()['ID']) {
            $result = $contract->find($ID);
            $this->assign('result', $result);
        }
        
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
            $contract->create($_POST, 0);
            if ($contract->where('ID =' . $_POST['ID'])->save() === false) {
                $this->ajaxReturn($contract->getError(), '更新失败', 0);
            } else {
                $this->ajaxReturn('更新成功', '更新成功', 1);
            }
        }
        
        if ($ID = $this->getParams()['ID']) {
            $result = $contract->find($ID);
            $this->assign('result', $result);
        }
        $this->assign('edit_url', $edit_url);
        $this->display('contract');
    }
    
    /**
     * 查看
     * 
     */
    public function show()
    {
        $ourCompany = BaseModel::conCompanyCd();
        $this->getDataDirectory();
        
        $model = D('TbCrmContract');
        $ret = $model->find($this->getParams()['ID']);
        $this->assign('result', $ret);
        
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
     * 获取基础配置数据
     * 
     */
    public function getDataDirectory()
    {
        $spTeamCd = BaseModel::spTeamCd();
        $spJsTeamCd = BaseModel::spJsTeamCd();
        $copanyTypeCd = BaseModel::conType();
        $spYearScaleCd = BaseModel::spYearScaleCd();
        $copanyTypeCd = BaseModel::conType();
        $isAutoRenew = BaseModel::isAutoRenew();
        $country = BaseModel::getCountry();
        $this->assign('copanyTypeCd', $copanyTypeCd);
        $this->assign('isAutoRenew', $isAutoRenew);
        $this->assign('spTeamCd', $spTeamCd);
        $this->assign('spJsTeamCd', $spJsTeamCd);
        $this->assign('copanyTypeCd', $copanyTypeCd);
        $this->assign('spYearScaleCd', $spYearScaleCd);
        $this->assign('country', $country);
    }
    
    /**
     * 文件上传
     * 
     */
    public function upload_file()
    {
        if ($_FILES) {
            $fd = new FileUploadModel();
            if ($fd->uploadFile()) {
                exit(json_encode(['name' => $fd->save_name]));
            }
        }
    }
    
    /**
     * 供应商批量导入
     * 
     */
    public function mult_import_contract()
    {
        $params = $this->getParams();
        if ($params ['check_contract_type'] == 0) {
            $this->assign('title', '供应商合同导入');
            // 供应商合同导入
            $im = new ImportMulSupplierContractModel();
            $ret = $im->import();
        } else {
            $this->assign('title', '客户合同导入');
            // 客户合同导入
            $im = new ImportMulCustomerContractModel();
            $ret = $im->import();
        }
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