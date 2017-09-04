<?php

/**
 * 供应商模型
 * 
 */
class ImportMulSupplierModel extends BaseImportExcelModel
{
    const IS_AUDIT_YES = 2; // 已审核
    const IS_AUDIT_NO  = 1; // 未审核
    const DATA_MARKING = 0; // 供应商类型，为1是客户管理类型
    protected $trueTableName = 'tb_crm_sp_supplier';
    public static $allSupplier;
    
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
    
    public function fieldMapping()
    {
        return [
            'SP_NAME' => ['field_name' => '供应商名称/Supplier name', 'required' => true],
            'SP_RES_NAME' => ['field_name' => '供应商简称(选填)/Supplier abbr.(optional)', 'required' => false],
            'SP_NAME_EN' => ['field_name' => '英文名称/English name', 'required' => false],
            'SP_RES_NAME_EN' => ['field_name' => '英文简称/English abbr.', 'required' => false],
            'SP_CHARTER_NO' => ['field_name' => '营业执照号/Business license No.', 'required' => false],
            'COPANY_TYPE_CD' => ['field_name' => '企业类型/Enterprise type', 'required' => false],
            'SP_YEAR_SCALE_CD' => ['field_name' => '供应商年业务规模/Annual business scale', 'required' => false],
            'SP_TEAM_CD1' => ['field_name' => '采购团队1/Procurement team1', 'required' => false],
            'SP_TEAM_CD2' => ['field_name' => '采购团队2/Procurement team2', 'required' => false],
            'SP_TEAM_CD3' => ['field_name' => '采购团队3/Procurement team3', 'required' => false],
            'SP_JS_TEAM_CD' => ['field_name' => '介绍团队/Recommend team', 'required' => false],
            'COMPANY_ADDR_INFO' => ['field_name' => '办公详细地址/Office Detail address', 'required' => false],
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
            if (in_array($value, $this->getAllSupplier())) $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 已存在';
        }
        // 基础常量验证
        if (in_array($db_field, $this->commonField()) and !empty($value)) {
            if ($db_field == 'SP_JS_TEAM_CD') $cds = BaseModel::spJsTeamCd();
            if ($db_field == 'SP_TEAM_CD1' or $db_field == 'SP_TEAM_CD2' or $db_field == 'SP_TEAM_CD3') $cds = BaseModel::spTeamCd();
            if ($db_field == 'SP_YEAR_SCALE_CD') {
                $value = htmlspecialchars($value);
                $cds = BaseModel::spYearScaleCd();
                $k = array_search($value, $cds);
            }
            if ($db_field == 'COPANY_TYPE_CD') $cds = BaseModel::companyTypeCd();
            $k = array_search($value, $cds);
            if ($k) $this->data [$row_index][$column_index]['value'] = $k;
            else $this->errorinfo [][$row_index.$column_index] = $this->title [$column_index]['en_name'] . ': ' . $value . ' 数据库无相应的code';
        }
    }
    
    /**
     * 字典表配置字段
     * 
     */
    public function commonField()
    {
        return [
            'SP_TEAM_CD1',
            'SP_TEAM_CD2',
            'SP_TEAM_CD3',
            'SP_JS_TEAM_CD',
            'SP_YEAR_SCALE_CD',
            'COPANY_TYPE_CD',
        ];
    }
    
    /**
     * 获取所有的供应商营业执照号
     * 
     */
    public function getAllSupplier()
    {
        if (self::$allSupplier) return self::$allSupplier;
        $ret = $this->field('SP_CHARTER_NO')->where('DATA_MARKING = 0')->select();
        if ($ret) self::$allSupplier = array_column($ret, 'SP_CHARTER_NO');
        return self::$allSupplier;
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
                if ($value ['db_field'] == 'SP_TEAM_CD1') {
                    $spt1 = $value ['value'];
                } elseif ($value ['db_field'] == 'SP_TEAM_CD2' and !empty($value ['value'])) {
                    $spt2 = $value ['value'];
                    if ($spt2 == $spt1) $this->errorinfo [][$key.$index] = $this->title [$key]['en_name'] . ' 采购团队重复';
                } elseif ($value ['db_field'] == 'SP_TEAM_CD3' and !empty($value ['value'])) {
                    $spt3 = $value ['value'];
                    if ($spt3 == $spt1 or $spt3 == $spt2) $this->errorinfo [][$key.$index] = $this->title [$key]['en_name'] . ' 采购团队重复'; 
                } else {
                    $temp [$value ['db_field']] = $value ['value']; 
                }
            }
            $temp ['SP_TEAM_CD'] = $spt1;
            is_null($spt2) or $temp ['SP_TEAM_CD'] .= ','.$spt2;
            is_null($spt3) or $temp ['SP_TEAM_CD'] .= ','.$spt3;
            $autoData = $this->autoComplete();
            unset($temp ['SP_TEAM_CD2'], $temp ['SP_TEAM_CD3']); 
            $temp = array_merge($temp, $autoData);
            $data [] = $temp;
        }
        $this->data = $data;
    }
    
    /**
     * 模型填充
     * 
     */
    public function autoComplete()
    {
        return $this->create();
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
