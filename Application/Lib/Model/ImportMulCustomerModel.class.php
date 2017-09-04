<?php

class ImportMulCustomerModel extends BaseImportExcelModel
{
    
    const IS_AUDIT_YES = 2; // 已审核
    const IS_AUDIT_NO  = 1; // 未审核
    const DATA_MARKING = 1; // 供应商类型，为1是客户管理类型
    
    protected $trueTableName = 'tb_crm_sp_supplier';
    public static $allCustomer;
    
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
        ['DATA_MARKING', '1', Model::MODEL_INSERT],
    ];
   
    protected $_link = [
        'TbCrmContract' => [
            'mapping_type' => HAS_MANY,
            'class_name' => 'TbCrmContract',
            'foreign_key' => 'SP_CHARTER_NO',
            'relation_foreign_key' => 'SP_CHARTER_NO',
            'mapping_name' => 'contracts',
            'mapping_key' => 'SP_CHARTER_NO',
            'condition' => 'tb_crm_contract.CRM_CON_TYPE = 1',
        ],
        'TbMsForensicAudit' => [
            'mapping_type' => HAS_ONE,
            'class_name' => 'TbMsForensicAudit',
            'foreign_key' => 'SP_CHARTER_NO',
            'relation_foreign_key' => 'SP_CHARTER_NO',
            'mapping_name' => 'audit',
            'mapping_key' => 'SP_CHARTER_NO',
            'condition' => 'CRM_CON_TYPE = 1',
        ]
    ];
    
    public function fieldMapping()
    {
        return [
            'SP_NAME' => ['field_name' => '客户名称/Client name', 'required' => true],
            'SP_RES_NAME' => ['field_name' => '简称(选填)/Client abbr.(optional)', 'required' => false],
            'SP_NAME_EN' => ['field_name' => '英文名称/English name', 'required' => false],
            'SP_RES_NAME_EN' => ['field_name' => '英文简称/English abbr.', 'required' => false],
            'SP_CHARTER_NO' => ['field_name' => '营业执照号/Business license No.', 'required' => false],
            'COPANY_TYPE_CD' => ['field_name' => '企业类型/Enterprise type', 'required' => false],
            'SP_YEAR_SCALE_CD' => ['field_name' => '年业务规模/Annual business scale', 'required' => false],
            'SALE_TEAM' => ['field_name' => '销售团队/Sale team', 'required' => false],
            'COMPANY_ADDR_INFO' => ['field_name' => '办公详细地址/Address', 'required' => false],
            'WEB_SITE' => ['field_name' => '网址(选填)/Website(Option)', 'required' => false],
            'COMPANY_MARKET_INFO' => ['field_name' => '公司与市场地位简述/Company notes', 'required' => false],
            'SP_REMARK' => ['field_name' => '备注(选填)/Remark(optional)', 'required' => false],
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
        if ($db_field == 'SP_CHARTER_NO' and !empty($value)) {        
            if (in_array($value, $this->getAllCustomer())) $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 已存在';
        } elseif (in_array($db_field, $this->commonField())) {
            if ($db_field == 'SALE_TEAM') {
                $cds = BaseModel::saleTeamCd();
                $k = array_search($value, $cds);
                if ($k) $this->data [$row_index][$column_index]['value'] = $k;
                else $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 数据库无相应的code'; 
            } elseif ($db_field == 'SP_YEAR_SCALE_CD') {
                $cds = BaseModel::spYearScaleCd();
                $value = htmlspecialchars($value);
                $k = array_search($value, $cds);
                if ($k) $this->data [$row_index][$column_index]['value'] = $k;
                else $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 数据库无相应的code'; 
            } elseif ($db_field == 'COPANY_TYPE_CD') {
                $cds = BaseModel::companyTypeCd();
                $value = htmlspecialchars($value);
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
            'SALE_TEAM',
            'SP_YEAR_SCALE_CD',
            'COPANY_TYPE_CD',
        ];
    }
    
    /**
     * 获取所有的供应商营业执照号
     * 
     */
    public function getAllCustomer()
    {
        if (self::$allCustomer) return self::$allCustomer;
        $ret = $this->field('SP_CHARTER_NO')->where('DATA_MARKING = 1')->select();
        if ($ret) self::$allCustomer = array_column($ret, 'SP_CHARTER_NO');
        return self::$allCustomer;
    }
    
    /**
     * 数据再组装
     * 对采购商进行组装，去重验证
     */
    public function packData()
    {
        $data = [];
        foreach ($this->data as $index => $info) {
            $spt1 = null;
            $spt2 = null;
            $spt3 = null;
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