<?php

/**
 * 审核信息模型
 * 
 */
class TbMsForensicAuditModel extends BaseModel
{
    protected $trueTableName = 'tb_ms_forensic_audit';
    
    protected $_validate = [
        ['RISK_RATING', 'require', '请进行风险评级']
    ];
    
    protected $_auto = [
        ['REV_TIME', 'getTime', Model::MODEL_BOTH, 'callback'],
        ['REVIEWER', 'getName', Model::MODEL_BOTH, 'callback']
    ];
    
    protected $_map = [
        //'ID' => 'ID',
        //'SP_NAME' => 'SP_NAME',
    ];
   
    protected $_link = [
        'TbCrmContract' => [
            'mapping_type' => HAS_MANY,
            'class_name' => 'TbCrmSpSupplier',
            'foreign_key' => 'SP_CHARTER_NO',
            'relation_foreign_key' => 'SP_CHARTER_NO',
            'mapping_name' => 'audit',
            'mapping_key' => 'SP_CHARTER_NO',
        ]
    ];
    
    /**
     * 更新审核信息营业执照号
     * @param $spNo 营业执照号
     * @param $type 0 供应商, 1 客户管理
     * @param $nSpNo 新营业执照号 
     */
    public function updateAuditSpCharterNo($spNo, $type, $nSpNo)
    {
        $ret = $this->where('CRM_CON_TYPE = ' . $type . ' and SP_CHARTER_NO = "' . $spNo . '"')->find();
        if ($ret) {
            $data ['ID'] = $ret ['ID'];
            $data ['SP_CHARTER_NO'] = $nSpNo;
            if ($this->save($data)) {
                return true;
            } 
        }
        return false;
    }
}