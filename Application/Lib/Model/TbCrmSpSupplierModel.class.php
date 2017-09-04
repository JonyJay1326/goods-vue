<?php

/**
 * 供应商模型
 * 
 */
class TbCrmSpSupplierModel extends BaseModel
{
    const IS_AUDIT_YES = 2; // 已审核
    const IS_AUDIT_NO  = 1; // 未审核
    const DATA_MARKING = 0; // 供应商类型，为1是客户管理类型
    
    protected $trueTableName = 'tb_crm_sp_supplier';
    
    protected $_validate = [
        ['SP_NAME', 'require', '请输入供应商名称'],
        ['SP_CHARTER_NO', 'require', '请输入营业执照号'],
        ['COPANY_TYPE_CD', 'require', '请选择企业类型']
    ];
    
    protected $_auto = [
        ['CREATE_TIME', 'getTime', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_TIME', 'getTime', Model::MODEL_BOTH, 'callback'],
        ['CREATE_USER_ID', 'getName', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_USER_ID', 'getName', Model::MODEL_BOTH, 'callback'],
        ['DEL_FLAG', '1', Model::MODEL_INSERT],
        ['SP_STATUS', '1', Model::MODEL_INSERT],
    ];
    
    protected $_map = [
        'ID' => 'ID',
        //'SP_NAME' => 'SP_NAME',
    ];
   
    protected $_link = [
        'TbCrmContract' => [
            'mapping_type' => HAS_MANY,
            'class_name' => 'TbCrmContract',
            'foreign_key' => 'SP_CHARTER_NO',
            'relation_foreign_key' => 'SP_CHARTER_NO',
            'mapping_name' => 'contracts',
            'mapping_key' => 'SP_CHARTER_NO',
            'condition' => 'tb_crm_contract.CRM_CON_TYPE = 0 and SP_CHARTER_NO is not null and SP_CHARTER_NO != ""',
        ],
        'TbMsForensicAudit' => [
            'mapping_type' => HAS_ONE,
            'class_name' => 'TbMsForensicAudit',
            'foreign_key' => 'SP_CHARTER_NO',
            'relation_foreign_key' => 'SP_CHARTER_NO',
            'mapping_name' => 'audit',
            'mapping_key' => 'SP_CHARTER_NO',
            'condition' => 'CRM_CON_TYPE = 0',
        ]
    ];
    
    public $attributes = [
        //'ID' => '客户ID',
        'SP_NAME' => '供应商名称',
        'SP_ADDR1' => '国别',
        'SP_TEAM_CD' => '采购团队',
        'CREATE_TIME' => '创建时间',
        'AUDIT_STATE' => '审核状态',
        'RISK_RATING' => '风险评级',
        'audit_time' => '审核时间',
        'CREATE_USER_ID' => '创建人',
        'contract' => '合同数',
        'COUNT_ORDERS' => '总订单数',
        'ING_ORDER' => '进行中订单',
        //'TOTAL_MONEY' => '总金额',
        //'ING_MONEY' => '进行中金额',
        //'SP_JS_TEAM_CD' => '介绍团队',
        //'CREATE_USER_ID' => '创建人',
        
    ];
    
    public function searchModel($params)
    {
        if ($params ['ID']) {
            $conditions ['tb_crm_sp_supplier.ID'] = $params['ID'];
        }
        if ($params ['SP_NAME']) {
            $where ['tb_crm_sp_supplier.SP_NAME'] = ['like', '%' . $params ['SP_NAME'] . '%'];
            $where ['tb_crm_sp_supplier.SP_NAME_EN'] = ['like', '%' . $params ['SP_NAME'] . '%'];
            $where['_logic'] = 'or';
            $conditions['_complex'] = $where;
        }
        if ($params ['CON_NO']) {
            $conditions ['tb_crm_contract.CON_NO'] = $params['CON_NO'];
        }
        if ($params ['SP_TEAM_CD']) {
            $conditions ['tb_crm_sp_supplier.SP_TEAM_CD'] = ['like', '%' . $params ['SP_TEAM_CD'] . '%'];
        }
        if ($params ['SP_ADDR1']) {
            $conditions ['tb_crm_sp_supplier.SP_ADDR1'] = $params['SP_ADDR1'];
        }
        if ($params ['AUDIT_STATE']) {
            $conditions ['AUDIT_STATE'] = $params['AUDIT_STATE'];
        }
        if ($params ['RISK_RATING']) {
            $conditions ['RISK_RATING'] = $params['RISK_RATING'];
        }
        if ($params ['CREATE_USER_ID']) {
            $model = M('_admin', 'bbm_');
            $where ['M_NAME'] = ['like', '%' . $params ['CREATE_USER_ID'] . '%'];
            $ret = $model->field('M_ID')->where($where)->select();
            $ret = array_column($ret, 'M_ID');
            $conditions ['CREATE_USER_ID'] = ['in', $ret];
        }
        
        $conditions ['DATA_MARKING'] = self::DATA_MARKING;
        return $conditions;
    }
    
    /**
     * 更新供应商信息
     * 
     */
    public function updateSupplierOrCustomer()
    {
        // 如果有文件上传，则前段数据全部组装成了String，需要转换成数组
        if ($_FILES) {
            // 图片上传
            $fd = new FileUploadModel();
            $ret = $fd->fileUploadExtend();
            
            $_POST ['SP_ANNEX_NAME'] = $ret;
            $_POST ['SP_ANNEX_ADDR'] = $fd->filePath;
        }
        // 没有文件上传，前段数据则为默认数据类型，可直接使用
        if ($_POST['SP_TEAM_CD']) {
            $temp = null;
            foreach ($_POST['SP_TEAM_CD'] as $k => $v) {
                $temp .= $v . ',';
            }
            $_POST['SP_TEAM_CD'] = rtrim($temp, ',');
        }
        $ret = $this->relation(true)->find($_POST['ID']);
        // 历史营业执照号
        $historySpCharterNo = $ret['SP_CHARTER_NO'];
        // 新营业执照号
        $newSpCharterNo = $_POST['SP_CHARTER_NO'];
        $this->startTrans();
        $logC = '';
        try {
            // 营业执照号发生变化，则更新审核信息与合同的营业执照号
            if ($historySpCharterNo != $newSpCharterNo) {
                // 查询新营业执照号是否已存在
                if ($this->where('SP_CHARTER_NO = "' . $newSpCharterNo . '" and DATA_MARKING = ' . self::DATA_MARKING)->find()) {
                    throw new \Exception('该营业执照号已存在，请更换');
                }
                // 如果已审核，则更新审核信息的营业执照号
                if ($ret ['AUDIT_STATE'] == self::IS_AUDIT_YES) {
                    $model = D('TbMsForensicAudit');
                    if (!$model->updateAuditSpCharterNo($historySpCharterNo, self::DATA_MARKING, $newSpCharterNo)) {
                        throw new \Exception('更新审核信息失败:' . $model->getError());
                    }
                }
                // 如果有合同，且更换了营业执照号，则同时更新合同的营业执照号
                if ($ret ['contracts']) {
                    $m = D('TbCrmContract');
                    if (!$m->updateContractSpCharterNo($historySpCharterNo, self::DATA_MARKING, $newSpCharterNo)) {
                        throw new \Exception('更新合同信息失败:' . $m->getError());
                    }
                }
                // 更新日志信息
                $log = A('Home/Log');
                if ($isok = $log->updateLog($historySpCharterNo, self::DATA_MARKING, $newSpCharterNo) !== true) throw new \Exception('更新日志失败:' . $isok);
                $logC = '供应商更换营业执照号';
            }
            // 更新供应商
            $this->create($_POST, 2);
            if ($this->where('ID =' . $_POST['ID'])->save() === false) {
                throw new \Exception('更新失败:' . $this->getError());
            } else {
                if ($errMsg = $this->insertLog($newSpCharterNo, self::DATA_MARKING, '更新供应商成功' . $logC) !== true) throw new \Exception('更新失败(日志写入失败):' . $errMsg);
            }
            $this->commit();
            return ['status' => 1, 'msg' => '更新成功', 'data' => null];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 0, 'msg' => '更新失败', 'data' => $e->getMessage()];
        }
    }
}
