<?php
/**
 * 合同模型
 * 
 */
class ImportEmpModel extends BaseImportExcelModel
{
    protected $trueTableName = 'tb_hr_empl';
    protected $_link = [
       'card' => [
            'mapping_type' => HAS_ONE,
           'class_name' => 'TbHrCard',
           'foreign_key' => 'EMPL_ID',
           'relation_foreign_key' => 'ID',
            'mapping_name' => 'child'
        ],
        'child' => [
            'mapping_type' => HAS_MANY,
           'class_name' => 'TbHrEmplChild',
           'foreign_key' => 'EMPL_ID',
           'relation_foreign_key' => 'ID',
            'mapping_name' => 'child'
        ],
        ];

    public function fieldMapping()
    {
        return [
            'WORK_NUM' => ['field_name' => '工号', 'required' => true],
            'PER_JOB_DATE' => ['field_name' => '入职时间', 'required' => true],
            'COMPANY_AGE' => ['field_name' => '司龄', 'required' => false],
            'EMP_NM' => ['field_name' => '真名', 'required' => true],
            'EMP_SC_NM' => ['field_name' => '花名', 'required' => true],
            'JOB_CD' => ['field_name' => '中文职位', 'required' => true],
            'DEPT_NAME' => ['field_name' => '部门', 'required' => true],
            'DEPT_GROUP' => ['field_name' => '组别', 'required' => true],
            'WORK_PALCE' => ['field_name' => '工作地点', 'required' => true],
            'PER_CART_ID' => ['field_name' => '身份证号', 'required' => true],
            'PER_BIRTH_DATE' => ['field_name' => '出生日期', 'required' => false],
            'AGE' => ['field_name' => '年龄', 'required' => false],
            'SEX' => ['field_name' => '性别', 'required' => false],
            'PER_RESIDENT' => ['field_name' => '户籍', 'required' => true],
            'PER_IS_MARRIED' => ['field_name' => '婚姻状况', 'required' => true],
            'PER_PHONE' => ['field_name' => '手机号码', 'required' => true],
            'PER_IS_SMOKING' => ['field_name' => '抽烟', 'required' => true],
            'GRA_SCHOOL' => ['field_name' => '毕业学院', 'required' => true],
            'EDU_BACK' => ['field_name' => '学历', 'required' => true],
            'MAJORS' => ['field_name' => '专业', 'required' => true],
            'FUND_ACCOUNT' => ['field_name' => '公积金账号', 'required' => true],
            'JOB_EN_CD' => ['field_name' => '英文职位', 'required' => false],
            'PER_POLITICAL' => ['field_name' => '政治面貌', 'required' => true],
            'EMAIL' => ['field_name' => '个人邮箱', 'required' => true],
            'SC_EMAIL' => ['field_name' => '花名邮箱', 'required' => true],
            'DEP_JOB_NUM' => ['field_name' => '离职编号', 'required' => true],
            'DEP_JOB_DATE' => ['field_name' => '离职时间', 'required' => true],
            'STATUS' => ['field_name' => '状态', 'required' => true],
            'V_STR1' => ['field_name' => '关系', 'required' => true],
            'V_STR3' => ['field_name' => '联系方式', 'required' => true],
            'EMPL_ID' => ['field_name' => 'ttt', 'required' => true],
            'V_DATE2' => ['field_name' => '毕业时间', 'required' => true],
            'V_STR4' => ['field_name' => '毕业证书编号', 'required' => true],
            'V_STR2' => ['field_name' => '姓名', 'required' => true],
            'V_STR5' => ['field_name' => '学历', 'required' => true],
            'V_STR6' => ['field_name' => '专业', 'required' => true],
            'V_STR7' => ['field_name' => '毕业学院', 'required' => true],

            'V_STR8' => ['field_name' => '前一家公司', 'required' => true],
            'V_STR9' => ['field_name' => '再前一家公司', 'required' => true],
            'V_STR10' => ['field_name' => '银行', 'required' => true],
            'V_STR11' => ['field_name' => '卡号', 'required' => true],

            'DETAIL' => ['field_name' => '户籍地址', 'required' => true],
            'DETAIL_LIVING' => ['field_name' => '现住址', 'required' => true],
            'ERP_ACT' => ['field_name' => 'erp账号(花名拼音)', 'required' => true],
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
    }   
    /**
     * 数据再组装
     * 对采购商进行组装，去重验证
     */
    public function packData()
    {
        //echo '<pre/>';var_dump($this->data);exit;
        $data = [];
        foreach ($this->data as $index => $info) {
           //echo "<pre>";  var_dump($info);
            $temp = [];
            foreach ($info as $key => $value) {
                
                $temp [$value ['db_field']] = $value ['value']; 
            }

            $autoData = $this->create();
            if ($temp['PER_JOB_DATE']) {   //计算司龄(按月计算)
                $mon = substr($temp['PER_JOB_DATE'],5,1);
               $comAge = (12-$mon)+ (date("Y") -1 - substr($temp['PER_JOB_DATE'],0,4))*12+date('m');   
            }
            
            $temp['COMPANY_AGE'] = $comAge;
            $cardID = $temp['PER_CART_ID'];
            $temp['PER_BIRTH_DATE'] = substr($cardID, 6,4).'-'.substr($cardID, 10,2).'-'.substr($cardID, 12,2);
            $temp['AGE'] = date("Y")-substr($cardID,6,4);
            if (substr($cardID, 15,16)%2!=0) {
                $temp['SEX'] ='男';
            }else{
                $temp['SEX'] = '女';
            }

            $empl = M('ms_cmn_cd','tb_')->where('CD_VAL='."'".$temp['JOB_CD']."'")->find();
            $temp['JOB_EN_CD'] = $empl['ETC'];

            $data [] = $temp;
        }
        //var_dump($data);die;
        foreach ($data as $key => $value) {
            $p = $c = null;
            $p = $this->create($value);
            //var_dump($p);
            $worknum = $p['WORK_NUM'];
            $Actdata = M('hr_card','tb_')->field('WORK_NUM')->select();
            foreach ($Actdata as $key => $v1) {
                foreach ($v1 as $k => $v) {
                    $actData[] = $v1[$k];
                }
        }
            if (in_array($worknum, $actData)) {
                $code = 500;
                $msg = 'error';
                $res = '工号重复,';
                return $res.$worknum; exit();
            }
            

            $p['GRA_SCHOOL'] = $value['V_STR7'];
            $p['EDU_BACK'] = $value['V_STR5'];
            $c = D('TbHrEmplChild')->create($value);
            //var_dump($c);die;
            $tmp [$value['WORK_NUM']]['parent'] = $p;
            $tmp [$value['WORK_NUM']]['child'] = $c;
        
            $parens = array_column($tmp, 'parent');
            $childrens = array_column($tmp, 'child');
            //echo "<pre>"; var_dump($parens);
            
            foreach ($parens as $key => $value) {
                $ret = M('hr_empl','tb_')->add($value);
                $parens[$key]['EMPL_ID'] = $ret;
                $childrens[$key]['EMPL_ID'] = $ret;
                $childrens[$key]['EMPL_ID'] = $ret;
                //$childrens[$]
                //var_dump($value);die;
            }
            $m = D('TbHrEmpl');
            $temp = [];
            foreach ($parens as $key => $value) {
                $ret = M('hr_card','tb_')->add($value);
                
            }
            foreach ($childrens as $key => $value) {
                $v1 = array();
                $v1['V_STR1'] = $value['V_STR2'];
                $v1['V_STR2'] = $value['V_STR3'];
                $v1['V_STR3'] = $value['V_STR1'];
                $v1['EMPL_ID'] = $value['EMPL_ID'];
                $v1['TYPE'] = 0; 
                M('hr_empl_child','tb_')->add($v1);
                $v2 = array();
                $v2['V_STR4'] = $value['V_STR4'];
                $v2['V_DATE2'] = $value['V_DATE2'];
                $v2['V_STR1'] = $value['V_STR7'];
                $v2['V_STR2'] = $value['V_STR6'];
                $v2['V_STR3'] = $value['V_STR5'];
                $v2['EMPL_ID'] = $value['EMPL_ID'];
                $v2['TYPE'] = 2; 
                $res = M('hr_empl_child','tb_')->add($v2);


                $v3 = array();
                $v3['V_STR1'] = $value['V_STR8'];
                $v3['EMPL_ID'] = $value['EMPL_ID'];
                $v3['TYPE'] = 11; 
                $res = M('hr_empl_child','tb_')->add($v3);
                
                $v4 = array();
                $v4['V_STR1'] = $value['V_STR9'];
                 $v4['EMPL_ID'] = $value['EMPL_ID'];
                $v4['TYPE'] = 11; 
                $res = M('hr_empl_child','tb_')->add($v4);


                $v5 = array();
                $v5['V_STR4'] = $value['V_STR10'];
                $v5['V_STR1'] = $value['V_STR11'];
                 $v5['EMPL_ID'] = $value['EMPL_ID'];
                $v5['TYPE'] = 12; 
                $res = M('hr_empl_child','tb_')->add($v5);
            }
        }
            
            return $res;       
      
    }
    
    public function import()
    {
        parent::import();
        $sub = $this->packData();
        if (substr($sub, 0,12)=='工号重复') {
             $data = [
                'code' => 500,
                'msg' => 'error',
                'data' => $sub,
            ];
        }else{
            $data = [
                'code' => 200,
                'msg' => 'success',
                'data' => '导入成功',
            ];
        }
       return $data;
    }
}