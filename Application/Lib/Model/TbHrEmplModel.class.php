<?php 

class TbHrEmplModel extends BaseModel
{
	
    protected $trueTableName = 'tb_hr_empl';
    
    protected $_link = [
    	'child' => [
    		'mapping_type' => HAS_MANY,
            'class_name' => 'TbHrEmplChild',
            'foreign_key' => 'EMPL_ID',     //关联模型的字段
            'relation_foreign_key' => 'ID',  //自己的关联字段
            'mapping_name' => 'empl_child', //存放关联模型数据的键,用来获取数据
    	],
    	'card' =>[
    		'mapping_type' => HAS_ONE,
    		'class_name' => 'TbHrCard',
    		'foreign_key' => 'EMPL_ID',
    		'relation_foreign_key' => 'ID',
    		'mapping_name' => 'card',
    	]
    ];
    
    protected $_auto = [
        ['CREATE_TIME', 'getTime', Model:: MODEL_INSERT, 'callback'],
        ['UPDATE_TIME', 'getTime', Model::MODEL_BOTH, 'callback'],
        ['CREATE_USER_ID', 'getName', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_USER_ID', 'getName', Model::MODEL_BOTH, 'callback']
    ];
    
    protected $_validate = [
        ['WORK_NUM','require','请输入工号'],//默认情况下用正则进行验证
        ['EMP_SC_NM','require','请输入花名'],//默认情况下用正则进行验证
        ['PER_JOB_DATE','require','请输入入职时间'],//默认情况下用正则进行验证
        ['DEPT_NAME','require','请输入部门'],//默认情况下用正则进行验证
        ['COMPANY_AGE','require','请输入司龄'],//默认情况下用正则进行验证
        ['JOB_CD','require','请输入职中文职位'],//默认情况下用正则进行验证
        ['JOB_EN_CD','require','请输入英文职位'],//默认情况下用正则进行验证
        ['WORK_PALCE','require','请输入工作地点'],//默认情况下用正则进行验证
        ['DOCKING_HR','require','请输入对接hr'],//默认情况下用正则进行验证
        ['DOCKING_HR','require','请输入职级'],//默认情况下用正则进行验证
        ['RANK','require','请输入职级'],//默认情况下用正则进行验证
        ['STATUS','require','请输入状态']//默认情况下用正则进行验证
    ];

    /**
     *  In all , check and make data
     *  @param  $data  array
     *  @param  $old_data  mix
     *  @return array
     *
     */
    public function formatFields($data, $old_data=array()){

            $datainfo['PIC'] = $data['Pic'];         //入职照片
            $datainfo['WORK_NUM'] = $data['workNum'];   //工号
            $datainfo['EMP_SC_NM'] = $data['EmpScNm'];  //花名
            $datainfo['PER_JOB_DATE']= cutting_time($data['perJobDate']);;  //入职时间
            $datainfo['DEPT_NAME'] = $data['deptName']; //部门
            $datainfo['COMPANY_AGE'] = $data['companyAge'];  //司龄
            $datainfo['JOB_CD'] = $data['jobCd'];      //职位
            $datainfo['JOB_EN_CD'] = $data['JobEnCd'];    //英文职位
            $datainfo['WORK_PALCE'] = $data['workPlace'];   //工作地点
            $datainfo['DIRECT_LEADER'] = $data['directLeader'];  //直接领导
            $datainfo['DEPART_HEAD'] = $data['departHead'];  //部门总监
            $datainfo['DOCKING_HR'] = $data['dockingHr'];  //对接hr
            $datainfo['STATUS'] = $data['status'];   //状态
            $datainfo['EMP_NM'] = $data['empNm'];
            $datainfo['DEP_JOB_DATE'] =cutting_time($data['depJobDate'])?cutting_time($data['depJobDate']):'';  //离职时间
            $datainfo['DEP_JOB_NUM'] =$data['depJobNum'];
            $datainfo['ERP_ACT'] = $data['erpAct'];
            $datainfo['ERP_PWD'] = $data['erpPwd'];
            $datainfo['PER_PHONE'] = $data['prePhone']; //手机号
            $datainfo['OFF_TEL'] = $data['offTel'];  //分机号
            $datainfo['JOB_TYPE_CD'] = $data['jobTypeCd'];  //职位类别
            $datainfo['PER_CART_ID'] = $data['perCartId'];   //身份证号
            $datainfo['SEX'] = $data['sex']?$data['sex']:'';
            $datainfo['PER_IS_SMOKING'] =(isset($data['perIsSmoking']) and $data['perIsSmoking']!=='')?$data['perIsSmoking']:2; //是否吸烟
            $datainfo['PER_BIRTH_DATE'] =cutting_time($data['perBirthDate']); //出生日期

            $datainfo['AGE']=$data['age'];
            $datainfo['PER_ADDRESS'] = $data['perAddress'];  //籍贯
            $datainfo['PER_RESIDENT']=$data['perResident'];    //户籍
            $datainfo['PER_IS_MARRIED']=$data['perIsMarried'];  //婚姻状况
            $datainfo['CHILD_NUM'] =$data['childNum'];
            $datainfo['CHILD_BOY_NUM']=$data['childBoyNum']; //孩子数量(男)
            $datainfo['CHILD_GIRL_NUM']=$data['childGirlNum']; //孩子数量(女)
            $datainfo['PER_POLITICAL']=$data['perPolitical']; //政治面貌    perPolitical
            $datainfo['HOUSEHOLD']=(isset($data['hosehold']) and $data['hosehold']!=='')?$data['hosehold']:3; //户口性质
            $datainfo['PER_NATIONAL'] =$data['perNational'];
            $datainfo['FUND_ACCOUNT'] =$data['fundAccount']; //公积金账号
            $datainfo['SC_EMAIL'] =$data['scEmail']; //花名邮箱
            $datainfo['EMAIL'] = $data['email'];   //私人邮箱
            $datainfo['WE_CHAT'] = $data['weChat']; //微信
            $datainfo['QQ_ACCOUNT'] = $data['qqAccount'];//qq账号---------------------------
            $datainfo['HOU_ADDRESS'] = $data['houAdderss']; //户籍地址
            $datainfo['LIVING_ADDRESS'] = $data['livingAddress'];  //现居住地址
            $datainfo['FIRST_LAN'] = $data['firstLan']; //第一外语
            $datainfo['FIRST_LAN_LEVEL'] = (isset($data['firstLanLevel']) and $data['firstLanLevel']!=='')?$data['firstLanLevel']:3; //外语程度
            $datainfo['SECOND_LAN'] =$data['secondLan'];
            $datainfo['SECOND_LAN_LEVEL'] = (isset($data['secondLanLevel']) and $data['secondLanLevel']!=='')?$data['secondLanLevel']:3;
            $datainfo['HOBBY_SPA'] = $data['hobbySpa'];  //兴趣爱好--------------------------
            $datainfo['PER_CARD_PIC'] = $data['perCardPic']; //身份证正反面
            $datainfo['RESUME'] =$data['resume'];  //简历
            $datainfo['GRA_SCHOOL'] = $data['eduExp'][0]['schoolName'];  //毕业学校
            $datainfo['EDU_BACK'] = $data['eduExp'][0]['eduDegNat'];  //毕业学校---------------
            $datainfo['DEPT_GROUP'] =$data['deptGroup'];
            $datainfo['RANK'] = $data['rank'];
            $datainfo['MAJORS'] = $data['eduExp'][0]['eduMajors'];
            $datainfo['PROVINCE'] = $data['houAdderss']['proh'];
            $datainfo['CITY'] = $data['houAdderss']['cityH'];
            $datainfo['AREA'] = $data['houAdderss']['areaH'];
            $datainfo['DETAIL'] = $data['houAdderss']['detailH'];

            $datainfo['PROVINCE_LIVING'] = $data['livingAddress']['provL'];
            $datainfo['CITY_LIVING'] = $data['livingAddress']['cityL'];
            $datainfo['AREA_LIVING'] = $data['livingAddress']['areaL'];
            $datainfo['DETAIL_LIVING'] = $data['livingAddress']['detailL'];


            $edu = $data['eduExp'];
            $workExp = $data['workExp'];
            $home = $data['home'];
            $training = $data['training'];
            $certificate = $data['certificate'];
            $bankCard = $data['bankCard'];

            $datainfo['PER_CARD_PIC'] = $data['perCardPic']; //身份证正反面
            $datainfo['RESUME'] =$data['resume']; //简历

            $datainfo['GRA_CERT'] = $data['graCert'];
            $datainfo['DEG_CERT'] = $data['degCert'];
            $datainfo['LEARN_PROVE'] = $data['learnProve'];

            $datainfo['DEPT_GROUP'] =$data['deptGroup'];
            $datainfo['RANK'] = $data['rank'];


            $dataAll = D('TbHrEmpl')->create($datainfo);
            //var_dump($dataAll);die;
            $cardData = D('TbHrCard')->create($datainfo);

            $dataAll['card'] = $cardData;
            $dataAll['empl_child'][] = array(
                'V_STR1'=>$data['concatName'],
                'V_STR2'=>$data['concatWay'],
                'V_STR3'=>$data['concatRel'],
                 'TYPE' => 0,
                );
            //var_dump($dataAll);die;
            //dump($edu);die;
            foreach ($edu as $key => $value) {
                //var_dump($value);die;
                $dataAll['empl_child'][] = array(
                    'V_STR1'=>$value['schoolName'],
                    'V_DATE1'=>cutting_time($value['eduStartTime']),
                    'V_DATE2'=>cutting_time($value['eduEndTime']),
                    'V_INT1'=>$value['isDegree'],
                    'V_STR2'=>$value['eduMajors'],
                    'V_STR3'=>$value['certiNo'],
                    'V_STR4'=>$value['eduDegNat'],
                    'V_STR5'=>$data['graCert'],
                    'V_STR6'=>$data['degCert'],
                    'V_STR7'=>$data['learnProve'],
                    'V_STR8' => $value['validateRes'],
                    'TYPE' => 2,
                    );
            }

            foreach ($home as $key => $value) {
                $dataAll['empl_child'][] = array(
                    'V_STR1'=>$value['homeRes'],
                    'V_STR2'=>$value['homeName'],
                    'V_STR3'=>$value['homeAge'],
                    'V_STR4'=>$value['occupa'],
                    'V_STR8'=>$value['workUnits'],
                    'TYPE' => 1,
                    );
            }

            foreach ($training as $key => $value) {
                $dataAll['empl_child'][] = array(
                    'V_STR1'=>$value['trainingName'],
                    'V_DATE1'=>cutting_time($value['trainingStartTime']),
                    'V_DATE2'=>cutting_time($value['trainingEndTime']),
                    'V_STR2'=>$value['trainingIns'],
                    'V_STR9'=>$value['trainingDes'],
                    'TYPE' => 4,
                    );
            }

            foreach ($certificate as $key => $value) {
                $dataAll['empl_child'][] = array(
                    'V_STR1'=>$value['certiName'],
                    'V_DATE1'=>cutting_time($value['certifiTime']),
                    'V_STR2'=>$value['certifiunit'],
                    'TYPE' => 5,
                    );
            }

           

            foreach ($workExp as $key => $value) {
                $dataAll['empl_child'][] = array(
                    'V_DATE1'=>cutting_time($value['wordStartTime']),
                    'V_DATE2'=>cutting_time($value['wordEndTime']),
                    'V_STR1'=>$value['companyName'],
                    'V_STR2'=>$value['posi'],
                    'V_STR3'=>$value['depReason'],
                    'TYPE' => 11,
                    );
            }

             foreach ($bankCard as $key => $value) {
                $dataAll['empl_child'][] = array(
                    'V_STR1'=>$value['bankAct'],
                    'V_STR2'=>$value['bankName'],
                    'V_STR3'=>$value['swiftCood'],
                    'V_STR4'=>$value['bankDeposit'],
                    'V_STR5'=>$value['BankEndeposit'],
                    'TYPE' => 12,
                    );
            }


            $ret_data = array();
            $ret_data['dataAll']=$dataAll;
            return $ret_data;
    }
}

