<?php

/**
 * 供应商模型
 * 
 */
class TbMsUserOperationLogModel extends BaseModel
{
    protected $trueTableName = 'tb_ms_user_operation_log';
    
    protected $_validate = [
        
    ];
    
    protected $_auto = [
        //['cTime', 'getTime', Model::MODEL_INSERT, 'callback'],
        //['uId', 'getName', Model::MODEL_INSERT, 'callback']
    ];
    
    protected $_map = [
        //'ID' => 'ID',
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
            'condition' => 'tb_crm_contract.CRM_CON_TYPE = 0',
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
        'user' => '操作用户',
        'noteType' => '日志类型',
        'source' => '系统来源',
        'ip' => 'IP地址',
        'space' => 'ES对应空间',
        'cTime' => '创建时间',
        'msg' => '信息体',
        'model' => '模块',
        'action' => '操作',
        //'cTimeStamp'=> '时间戳'
        //'TOTAL_MONEY' => '总金额',
        //'ING_MONEY' => '进行中金额',
        //'SP_JS_TEAM_CD' => '介绍团队',
        //'CREATE_USER_ID' => '创建人',
        
    ];
    
    public function test()
    {
        echo 'test';
    }
    
    public function searchModel($params, $opeartion)   //模糊查询条件
    {
        $rm = array_column($opeartion, 'NAME', 'CTL');
        $rm = array_flip($rm);    //翻转数组的键和值
        foreach ($opeartion as $k => $v) {
            $cm [$v['CTL'] . '/' . $v ['ACT']] = $v ['NAME'];
        }
        $cm = array_flip($cm);
        if ($params ['option']) {
            $where ['tb_ms_user_operation_log.ip'] = ['like', '%' . $params ['option'] . '%'];
            $where ['tb_ms_user_operation_log.user'] = ['like', '%' . $params ['option'] . '%'];
            $where ['tb_ms_user_operation_log.cTime'] = ['like', '%' . $params ['option'] . '%'];
            $where['_logic'] = 'or';
            $conditions['_complex'] = $where;
        }
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
            $_POST = parse_str($_POST['data'], $output);
            $_POST = $output;
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
                $log = A('Log');
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
