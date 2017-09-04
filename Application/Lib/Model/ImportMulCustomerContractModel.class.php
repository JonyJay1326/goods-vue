<?php

/**
 * 合同模型
 * 
 */
class ImportMulCustomerContractModel extends BaseImportExcelModel
{
    
    protected $trueTableName = 'tb_crm_contract';
    public static $allCustomerContract;
    
    protected $_auto = [
        ['CREATE_TIME', 'getTime', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_TIME', 'getTime', Model::MODEL_BOTH, 'callback'],
        ['CREATE_USER_ID', 'getName', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_USER_ID', 'getName', Model::MODEL_BOTH, 'callback'],
        ['CON_STAT', '1', Model::MODEL_INSERT],
        ['CRM_CON_TYPE', 1, Model::MODEL_INSERT],
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
    
    
    public function fieldMapping()
    {
        return [
            'CON_NO' => ['field_name' => '合同编号/Contract number', 'required' => true],
            'CON_NAME' => ['field_name' => '合同简称/Contract abbr', 'required' => false],
            'CON_TYPE' => ['field_name' => '合作类型/Cooperration type', 'required' => false],
            'SP_NAME' => ['field_name' => '供应商名称/Supplier name', 'required' => false],
            'SP_CHARTER_NO' => ['field_name' => '营业执照号/Business license No.', 'required' => false],
            'CONTRACT_TYPE' => ['field_name' => '合同类型/Contract Type', 'required' => false],
            'START_TIME' => ['field_name' => '开始时间/Start time', 'required' => false],
            'END_TIME' => ['field_name' => '结束时间/Ending time', 'required' => false],
            'IS_RENEWAL' => ['field_name' => '自动续约/Automatic renewal', 'required' => false],
            'CONTRACTOR' => ['field_name' => '签约人/Signatory', 'required' => false],
            'CON_COMPANY_CD' => ['field_name' => '我方公司/Our company', 'required' => false],
            'SP_BANK_CD' => ['field_name' => '开户行/opening bank', 'required' => false],
            'BANK_ACCOUNT' => ['field_name' => '银行账户/Bank account', 'required' => false],
            'SWIFT_CODE' => ['field_name' => 'SWIFT CODE', 'required' => false],
            'CONTACT' => ['field_name' => '联系人/Contacts', 'required' => false],
            'CON_EMAIL' => ['field_name' => 'E-mail', 'required' => false],
            'CON_TEL' => ['field_name' => '联系电话/Contact phone', 'required' => false],
            'CON_PHONE' => ['field_name' => '手机号码/Telephone', 'required' => false],
            'REMARK' => ['field_name' => '备注/Remarks', 'required' => false],
        ];
    }
    
    /**
     * 校验是否不能为空
     * @param $row 行坐标
     * @param $column 列坐标
     * @param $value 值
     */
    public function valid($row_index, $column_index, $value)
    {
        parent::valid($row_index, $column_index, $value);
        $db_field = $this->title [$column_index]['db_field'];
        if ($db_field == 'CON_NO' and !empty($value)) {
            if (in_array($value, $this->getAllCustomerContract())) $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 已存在';
        } elseif (in_array($db_field, $this->commonField())) {
            if ($db_field == 'CON_TYPE') {
                $cds = BaseModel::conType();
                $k = array_search($value, $cds);
                if ($k !== false) $this->data [$row_index][$column_index]['value'] = $k;
                else $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 数据库无相应的code';
            } elseif ($db_field == 'CONTRACT_TYPE') {
                $cds = BaseModel::contractType();
                $value = $value;
                $k = array_search($value, $cds);
                if ($k !== false) $this->data [$row_index][$column_index]['value'] = $k;
                else $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 数据库无相应的code'; 
            } elseif ($db_field == 'IS_RENEWAL') {
                $cds = BaseModel::isAutoRenew();
                $value = $value;
                $k = array_search($value, $cds);
                if ($k !== false) $this->data [$row_index][$column_index]['value'] = $k;
                else $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 数据库无相应的code'; 
            } elseif ($db_field == 'CON_COMPANY_CD') {
                $cds = BaseModel::conCompanyCd();
                $value = $value;
                $k = array_search($value, $cds);
                if ($k) $this->data [$row_index][$column_index]['value'] = $k;
                else $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 数据库无相应的code'; 
            }  
        }
    }
    
    /**
     * 字典表配置字段
     * 
     */
    public function commonField()
    {
        return [
            'CON_TYPE', // 合作类型
            'CONTRACT_TYPE',//合同类型，长短合同
            'IS_RENEWAL',// 是否自动续约
            'CON_COMPANY_CD'// 我方公司
        ];
    }
    
    /**
     * 获取所有的供应商营业执照号
     * 
     */
    public function getAllCustomerContract()
    {
        if (self::$allCustomerContract) return self::$allCustomerContract;
        $ret = $this->field('CON_NO')->where('CRM_CON_TYPE = 1')->select();
        if ($ret) self::$allCustomerContract = array_column($ret, 'CON_NO');
        return self::$allCustomerContract;
    }
    
    /**
     * 数据再组装
     * 对采购商进行组装，去重验证
     */
    public function packData()
    {
        $data = [];
        foreach ($this->data as $index => $info) {
            $temp = [];
            foreach ($info as $key => $value) {
                $temp [$value ['db_field']] = $value ['value']; 
            }
            $autoData = $this->create();
            $temp = array_merge($temp, $autoData);
            $data [] = $temp;
        }
        $this->data = $data;
    }
    
    /**
     * 导入主入口函数
     * 
     */
    public function import()
    {
        parent::import();
        $this->packData();
        if (!$this->errorinfo) {
            if ($this->addAll($this->data)) {
                $ret = ['state' => 1, 'msg' => count($this->data)];    
            } else {
                $this->errorinfo []['db_error'] = $this->getError();
                $ret = ['state' => 0, 'msg' => $this->errorinfo];
            }
            
        } else {
            $ret = ['state' => 0, 'msg' => $this->errorinfo];
        }
        return $ret;
    }
}