<?php

/**
 * 合同模型
 * 
 */
class TbCrmContractModel extends BaseModel
{
    
    protected $trueTableName = 'tb_crm_contract';
    
    protected $_validate = [
        ['CON_NO', 'require', '请输入合同编号'],
        ['CON_NAME', 'require', '请输入合同简称'],
    ];
    
    protected $_auto = [
        ['CREATE_TIME', 'getTime', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_TIME', 'getTime', Model::MODEL_BOTH, 'callback'],
        ['CREATE_USER_ID', 'getName', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_USER_ID', 'getName', Model::MODEL_BOTH, 'callback'],
        ['CON_STAT', '1', Model::MODEL_INSERT],
    ];
    
    protected $_link = [
        'TbCrmSpSupplier' => [
            'mapping_type' => HAS_ONE,
            'class_name' => 'TbCrmSpSupplier',
            'foreign_key' => 'SP_CHARTER_NO',
            'relation_foreign_key' => 'SP_CHARTER_NO',
            'mapping_name' => 'supplier',
            'mapping_key' => 'SP_CHARTER_NO'
        ],
        'TbMsForensicAudit' => [
            'mapping_type' => HAS_ONE,
            'class_name' => 'TbMsForensicAudit',
            'foreign_key' => 'SP_CHARTER_NO',
            'relation_foreign_key' => 'SP_CHARTER_NO',
            'mapping_name' => 'audit',
            'mapping_key' => 'SP_CHARTER_NO'
        ]
    ];
    

    public $attributes = [
        'CON_NO' => '合同号/OA流程号',
        'CONTRACTOR' => '签约人',
        'CON_STAT' => '合同状态',
        'START_TIME' => '合同起始时间',
        'END_TIME' => '合同结束时间',
        'IS_RENEWAL' => '是否自动续约',
        'CREATE_TIME' => '提交时间',
        
    ];
    
    public $attributesExtends = [
        'CON_NO' => '合同编号',
        'CON_NAME' => '合同简称',
        'SP_NAME' => '合作方名称',
        'CON_TYPE' => '合同类型',
        'CON_COMPANY_CD' => '我方公司',
        'Team' => '合作方所属团队',
        'CONTRACTOR' => '签约人',
        'START_TIME' => '起始时间',
        'END_TIME' => '结束时间',
        'IS_RENEWAL' => '自动续约',
        'CREATE_USER_ID' => '归档人',
        'CREATE_TIME' => '归档时间',
    ];
    
    public function searchModel($params)
    {
        if ($params ['CON_NO']) {
            $conditions ['CON_NO'] = $params['CON_NO'];
        }
        if ($params ['CON_NAME']) {
            $conditions ['tb_crm_contract.CON_NAME'] = ['like', '%' . $params ['CON_NAME'] . '%'];
        }
        if ($params ['SP_NAME']) {
            $conditions ['tb_crm_contract.SP_NAME'] = ['like', '%' . $params ['SP_NAME'] . '%'];
        }
        if ($params ['CON_TYPE']) {
            $conditions ['CON_TYPE'] = $params ['CON_TYPE'] - 1;
        }
        if ($params ['CON_COMPANY_CD']) {
            $conditions ['CON_COMPANY_CD'] = $params ['CON_COMPANY_CD'] - 1;
        }
        if ($params ['SP_TEAM_CD']) {
            $conditions ['SP_TEAM_CD'] = $params ['SP_TEAM_CD'];
        }
        if ($params ['CONTRACTOR']) {
            $conditions ['CONTRACTOR'] = ['like', '%' . $params ['CONTRACTOR'] . '%'];
        }
        if ($params ['TIME_TYPE']) {
            switch ($params ['TIME_TYPE']) {
                case 1:
                    $conditions ['tb_crm_contract.CREATE_TIME'] = [['gt', $params ['CONTRACT_START_TIME'] . ' 00:00:00'], ['lt', $params ['CONTRACT_END_TIME']. ' 23:59:59']];
                    break;
                case 2:
                    $conditions ['START_TIME'] = [['gt', $params ['CONTRACT_START_TIME'] . ' 00:00:00'], ['lt', $params ['CONTRACT_END_TIME']. ' 23:59:59']];
                    break;
                case 3:
                    $conditions ['END_TIME'] = [['gt', $params ['CONTRACT_START_TIME'] . ' 00:00:00'], ['lt', $params ['CONTRACT_END_TIME']. ' 23:59:59']];
                    break;
            }
        }
        
        //$conditions ['DATA_MARKING'] = 0;
        return $conditions;
    }
    
    /**
     * 更新合同营业执照号
     * @param $spNo 营业执照号
     * @param $type 0 供应商, 1 客户管理
     * @param $nSpNo 新营业执照号
     */
    public function updateContractSpCharterNo($spNo, $type, $nSpNo)
    {
        $ret = $this->where('CRM_CON_TYPE = ' . $type . ' and SP_CHARTER_NO = "' . $spNo . '"')->select();
        if ($ret) {
            $ret = array_column($ret, 'ID');
            $where ['ID'] = ['in', $ret];
            //$data ['ID'] = $ret ['ID'];
            $data ['SP_CHARTER_NO'] = $nSpNo;
            if ($this->where($where)->save($data)) {
                return true;
            }
        }
        return false;
    }
}