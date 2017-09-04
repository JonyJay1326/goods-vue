<?php 
/**
* User:
* Date:
* author:
*/
class HrAapi extends Action
{
    private $HrModel;
    public function __construct()
    {
        $this->HrModel = new TbHrModel();
    }
    
     public function responseData($code, $msg, $data)
    {
        $data = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];

        exit(json_encode($data));
    }

    /**
     * 人员展示、搜索
     * @param 条件参数
     */
    public function showList($Keyword){
        if ($ret = $this->HrModel->showPerson($Keyword)) {
            $code = 200;
            $msg = 'success';
        } else {
            $code = 500;
            $msg = 'error';
            $ret = '当前无人员信息';
        }
        $this->responseData($code, $msg, $ret);
    }

    /**
     * [Mycard]我的名片
     * 
     */
    public function Mycard($data){
        //var_dump($data);die;
        if($res = $this->HrModel->cardData($data)){
            $code = 200;
            $msg = 'success';
        }else{
            $code = 500;
            $msg = '显示失败';
        }
        $this->responseData($code,$msg,$res);
}

    /**
     * 我的下属
     * @param string $value [description]
     */
    public function Subordinates()  
    {
        $res = $this->HrModel->SubData();
        if ($res) {
            $code = 200;
            $msg = 'suceess';
        }else{
            $code = 500;
            $msg = '无数据显示';
        }
        $this->responseData($code,$msg,$res);
    }

    /**
     * 新增人员个人信息
     */
    public function addCustomer($data)
    {
            $data = json_decode($data['params'],true);


        $model = D('TbHrEmpl');
        $tmpData = $model->formatFields($data);
        $dataAll = $tmpData['dataAll'];
        $model->startTrans();

        if(empty($dataAll['card']['ERP_ACT'])){
            $code = 50001001;
            $msg = 'error';
            $res = 'ERP账号null';
            return $this->responseData($code,$msg,$res);
        }
        // check erp account
        $temp_check = $model->where(array("ERP_ACT"=>$dataAll['card']['ERP_ACT']))->limit(1)->select();
        if($temp_check){
            $code = 50001001;
            $msg = 'error';
            $res = 'ERP账号重复(该用户已存在)';
            return $this->responseData($code,$msg,$res); exit();
        }
        // check hua ming
        $temp_check = $model->where(array("EMP_SC_NM"=>$dataAll['card']['EMP_SC_NM']))->limit(1)->select();
        if($temp_check){
            $code = 50001000;
            $msg = 'error';
            $res = '花名已重复';
            return $this->responseData($code,$msg,$res); exit();
        }

        //check gong hao
        $temp_check = $model->where(array("WORK_NUM"=>$dataAll['card']['WORK_NUM']))->limit(1)->select();
        if($temp_check){
            $code = 50001002;
            $msg = 'error';
            $res = '工号已重复';
            return $this->responseData($code,$msg,$res); exit();
        }



        if ($ret = $model->create($dataAll, 1)) {
            //var_dump($dataAll);die;
            if ($isok = $model->relation(true)->add($dataAll)) {
                //echo $model->_sql();
                $code = 200;
                $msg = 'success';
                $res = [
                    'lastInsertId' =>$isok,
                    'res' =>'新建成功',
                ];

            }else{
                //echo  $model->_sql();die;
                $code = 500;
                $msg = 'error';

                $model->rollback();
            }
                $model->commit();
        }else{
            $code = 500;
            $msg = 'error';
            $reason = $model->getError();
            $res = $reason;
            
        }
       return $this->responseData($code,$msg,$res); exit();
    }

    public function upload($file)
    {
        $filename = '';
        foreach ($file as $key=>$value) {
            $filename = $key;
        }
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        //$upload->maxSize = -1;// 设置附件上传大小
        if ($file['perCardPic']||$file['graCert']||$file['degCert']) {
            $type  =  array('jpg', 'gif', 'png', 'jpeg','pdf');
        }
        if($file['resume']||$file['learnProve']) {
            $type  =  array('pdf', 'dot','dotx','docm','doc','docx','dotm','xml');
        }
        $upload->allowExts = $type;// 设置附件上传类型
        if (!file_exists(ATTACHMENT_DIR)) {
            mkdir(ATTACHMENT_DIR);
        }
        $upload->savePath = ATTACHMENT_DIR;// 设置附件上传目录
        $name = $filename;
        if (!$upload->upload()) {// 上传错误提示错误信息
             $code = 500;
             $msg = 'error';
             $res =[
                'name'=>$name,
                'res'=>$upload->getErrorMsg(),
             ];

        }
         if($info = $upload->getUploadFileInfo()){
            $filePath = $info[0]['savepath'];
            $fileName = $info[0]['name'];
            $savename = $info[0]['savename'];
            $code = 200;
            $msg = 'success';
            $res = [
                'filePath'=>$filePath,
                'filename'=>$fileName,
                'savename'=>$savename,
                'name' =>$name,
            ];
         }
         return $this->responseData($code,$msg,$res); exit();
    }

    public function editTrack($data)
    {
            $m = M('hr_empl_child','tb_');
            $emplcount = $m->field('EMPL_ID')->where('EMPL_ID='.$id)->count();

            if ($emplcount>=20) {
                $code = 500;
                $msg = 'error';
                $res = '重复提交';
                return $this->responseData($code,$msg,$res); exit();
            }
            foreach ($data as $key => $value) {
                $data = json_decode($value,true);
            }
            $id = $data['lastInsertId'];
            foreach ($data as $k => $v) {               //优化批量添加
                 if ($k=='contract') {
                        foreach ($v as $index => $val) {
                        $contract[] = array(
                            'V_STR1' =>$val['conCompany'],
                            'V_STR2' =>$val['natEmploy'],
                            'V_DATE1' =>cutting_time($val['trialEndTime']),
                            'V_DATE2' =>cutting_time($val['conStartTime']),
                            'V_DATE3'=>cutting_time($val['conEndtTime']),
                            'TYPE' => 3,
                            'EMPL_ID'=>$id,
                            );
                }
               $res = D('TbHrEmplChild')->addAll($contract);
               }
                 if ($k == 'reward') {
                   foreach ($v as $index => $val) {
                        $reward[] = array(
                            'V_STR1' =>$val['rewardName'],
                            'V_STR10' =>$val['rewardContent'],
                            'TYPE' => 7,
                            'EMPL_ID'=>$id,
                            );
                }
                $res = D('TbHrEmplChild')->addAll($reward);
               }

               if ($k == 'promo') {
                   foreach ($v as $index => $val) {
                        $promo[] = array(
                            'V_STR1' =>$val['promoType'],
                            'V_DATE1' =>cutting_time($val['promoTime']),
                            'V_STR10' =>$val['promoContent'],
                            'TYPE' => 8,
                            'EMPL_ID'=>$id,
                            );
                }
                $res = D('TbHrEmplChild')->addAll($promo);
               }

                if ($k == 'interArr') {
                   foreach ($v as $index => $val) {
                        $interArr[] = array(
                            'V_STR1' =>$val['interType'],
                            'V_DATE1' =>cutting_time($val['interTime']),
                            'V_STR2' =>$val['interObj'],
                            'V_STR3' =>$val['interPerson'],
                            'V_STR4' =>$val['interContent'],
                            'V_STR5' =>$val['afterCase'],
                            'TYPE' => 9,
                            'EMPL_ID'=>$id,
                            );
                }
                $res = D('TbHrEmplChild')->addAll($interArr);
               }
                if ($k == 'paperMiss') {
                   foreach ($v as $index => $val) {
                        $paperMiss[] = array(
                            'V_DATE1' =>cutting_time($val['paperMissTime']),
                            'V_STR10' =>$val['paperMissCon'],
                            'TYPE' => 10,
                            'EMPL_ID'=>$id,
                            );
                }
                $res = D('TbHrEmplChild')->addAll($paperMiss);
               } 
            }
            
            if ($res) {
                $code = 200;
                $msg = 'success';
                $res = array(
                    'res'=>'创建成功',
                    );
            }else{
                $code = 500;
                $msg = 'error';
                $res = '添加失败';
            }
            return $this->responseData($code,$msg,$res); exit();    
    }

    /**
     *状态更改
     */
    public function statusChange($params)
    {
        //dump($params);die;
        $selectedID = $params['EMPL_ID'];
        //$selectedID = [2,3,4]; 测试
        $m = M('hr_card','tb_');
        $m->startTrans();
        
            if ($params['perJobDate']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('PER_JOB_DATE',$params['perJobDate']);
            }
            if ($params['deptName']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('DEPT_NAME',$params['deptName']);
            }
            if ($params['emplGroup']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('DEPT_GROUP',$params['emplGroup']);
            }
            if ($params['jobCd']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('JOB_CD',$params['jobCd']);
            }
            if ($params['JobEnCd']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('JOB_EN_CD',$params['JobEnCd']);
            }
            if ($params['workPlace']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('WORK_PALCE',$params['workPlace']);
            }
            if ($params['directLeader']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('DIRECT_LEADER',$params['directLeader']);
            }
            if ($params['departHead']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('DEPART_HEAD',$params['departHead']);
            }
            if ($params['dockingHr']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('DOCKING_HR',$params['dockingHr']);
            }
            if ($params['rank']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('RANK',$params['rank']);
            }
            if ($params['status']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('STATUS',$params['status']);
            }
            if ($params['jobTypeCd']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('JOB_TYPE_CD',$params['jobTypeCd']);
            }
            if ($params['sex']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('SEX',(int)$params['sex']);
            }
            if ($params['perIsSmoking']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('PER_IS_SMOKING',$params['perIsSmoking']);
            }
            if ($params['perIsMarried']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('PER_IS_MARRIED',$params['perIsMarried']);
            }
            if ($params['perPolitical']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('PER_POLITICAL',$params['perPolitical']);
            }
            if ($params['hosehold']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('HOUSEHOLD',$params['hosehold']);
            }
            if ($params['perNational']) {
                $res=$m->where('EMPL_ID='.$selectedID)->setField('PER_NATIONAL',$params['perNational']);
            }
            //echo $m->_sql();die;
        
        if ($res) {
            $code = 200;
            $msg = 'success';
            $res = '修改状态成功'; 
        }else{
            $code = 500;
            $msg = 'error';
            $res = '修改失败';
            $m->rollback();
        }
        $m->commit();
        return $this->responseData($code,$msg,$res);

    }

    /**
     * 获取下拉框数据
     */
    public function acquireData($data)
    {
        //dump($data);die;
        $selectCity = $data['city']; 
        $m = M('ms_cmn_cd','tb_');
        //$where['CD_NM'] = array('in','工作地点,人员状态,职位类别,语言能力语种,工作内奖惩名称,晋升记录类型,面谈记录,职级,职位_1,职位_2,职位_3');
        //$acquireData = M('ms_cmn_cd', 'tb_')->field('CD_NM,CD_VAL')->where($where)->select();   //在数据字典表中查出物流公司
        $workPlace = $m->field('CD_VAL')->where("left(CD,6)='N00155'")->select();
        $status = $m->field('CD_VAL')->where("left(CD,6)='N00156'")->select();
        $jobCdType = $m->field('CD_VAL')->where("left(CD,6)='N00157'")->select();
        $langa = $m->field('CD_VAL')->where("left(CD,6)='N00158'")->select();
        $ThingName = $m->field('CD_VAL')->where("left(CD,6)='N00159'")->select();
        $recordType = $m->field('CD_VAL')->where("left(CD,6)='N00160'")->select();
        $sayType = $m->field('CD_VAL')->where("left(CD,6)='N00161'")->select();
        $rank = $m->field('CD_VAL')->where("left(CD,6)='N00162'")->select();
        $married = $m->field('CD_VAL')->where("left(CD,6)='N00166'")->select();
        $pot = $m->field('CD_VAL')->where("left(CD,6)='N00167'")->select();
        $job1 = $m->field('CD_VAL,ETC')->where("left(CD,6)='N00163'")->select();
        $job2 = $m->field('CD_VAL,ETC')->where("left(CD,6)='N00164'")->select();
        $job3 = $m->field('CD_VAL,ETC')->where("left(CD,6)='N00165'")->select();
        $peoples = $m->field('CD_VAL')->where("left(CD,6)='N00170'")->select();
        $employNat = $m->field('CD_VAL')->where("left(CD,6)='N00169'")->select();
        $eduback = $m->field('CD_VAL')->where("left(CD,6)='N00168'")->select();  //学历
        $validateRes = $m->field('CD_VAL')->where("left(CD,6)='N00171'")->select();


        $job = $job1;
        $jobzh = $data['jobzh'];  //选中的中文职位
        if ($jobzh) {
            $joben = $m->field('ETC')->where('CD_VAL='."'".$jobzh."'")->find();
        }
        
        foreach ($job2 as  $value) {
            array_push($job, $value);
        }
        foreach ($job3 as  $value) {
            array_push($job, $value);
        }
        $leader = M('hr_card','tb_')->field('EMP_SC_NM')->select();   //所有员工信息
        $provincedata = M('crm_site','tb_')->field('ID,NAME')->where('PARENT_ID=1')->select();
        $proId = $data['proId'];
        if ($proId) {
            $citydata = M('crm_site','tb_')->field('ID,NAME')->where('PARENT_ID='.$proId)->select();
        }
        $cityId = $data['cityId'];
        //echo $cityID;die;
        if ($cityId) {
            $areadata = M('crm_site','tb_')->field('ID,NAME')->where('PARENT_ID='.$cityId)->select();
            //dump($areadata);  
            //echo M('crm_site','tb_')->_sql(); 
        }

        $acquireData = array(
            'workPlace' =>$workPlace,
            'status' =>$status,
            'jobCdType' =>$jobCdType,
            'langa' =>$langa,
            'ThingName' =>$ThingName,
            'recordType' =>$recordType,
            'sayType' =>$sayType,
            'rank' =>$rank,
            'job' =>$job,
            'leader' => $leader,
            'provincedata'=>$provincedata,
            'citydata' =>$citydata,
            'areadata' =>$areadata,
            'joben' => $joben,
            'married' =>$married,
            'pot' => $pot,
            'peoples'=>$peoples,
            'employNat'=>$employNat,
            'validateRes' =>$validateRes,
            'employNat' =>$employNat,
            );
        return $acquireData;
    }

    /**
     * 编辑名片信息
     * @return [type] [description]
     */
    public function changeCard($data)
    {
            $data = json_decode($data['params'],true);
            /*echo "<pre>";
            var_dump($data);die;
            echo "</pre>";*/
            $data1['PIC'] = $data['Pic'];           //入职照片
            $data1['WORK_NUM'] = $data['workNum'];  //工号
            $data1['EMP_SC_NM'] = $data['EmpScNm'];  //花名
            $data1['PER_JOB_DATE']= cutting_time($data['perJobDate']);  //入职时间
            $data1['DEPT_NAME'] = $data['deptName']; //部门
            $data1['COMPANY_AGE'] = $data['companyAge'];  //司龄
            $data1['JOB_CD'] = $data['jobCd'];      //职位
            $data1['JOB_EN_CD'] = $data['JobEnCd']; //英文职位
            $data1['WORK_PALCE'] = $data['workPlace'];   //工作地点
            $data1['DIRECT_LEADER'] =$data['directLeader'];  //直接领导
            $data1['DEPART_HEAD'] = $data['departHead'];  //部门总监
            $data1['DOCKING_HR'] = $data['dockingHr'];  //对接hr
            $data1['STATUS'] = $data['status'];   //状态
            $data1['EMP_NM'] = $data['empNm'];
            $data1['DEP_JOB_DATE'] = cutting_time($data['depJobDate']);  //离职时间
            $data1['DEP_JOB_NUM'] =$data['depJobNum'];
            $data1['ERP_ACT'] = $data['erpAct'];
            $data1['ERP_PWD'] = $data['erpPwd'];
            $data1['PER_PHONE'] = $data['prePhone']; //手机号
            $data1['OFF_TEL'] = $data['offTel'];  //分机号
            $data1['JOB_TYPE_CD'] = $data['jobTypeCd'];  //职位类别
            $data1['PER_CART_ID'] = $data['perCartId'];   //身份证号
            $data1['SEX'] = $data['sex'];
            $data1['PER_IS_SMOKING'] =$data['perIsSmoking']; //是否吸烟
            $data1['PER_BIRTH_DATE'] =cutting_time($data['perBirthDate']); //出生日期
            $data1['AGE']=$data['age'];
            $data1['PER_ADDRESS'] = $data['perAddress'];  //籍贯
            $data1['PER_RESIDENT'] = $data['perResident'];    //户籍
            $data1['PER_IS_MARRIED'] = $data['perIsMarried'];  //婚姻状况
            $data1['CHILD_NUM'] = $data['childNum'];
            $data1['CHILD_BOY_NUM'] = $data['childBoyNum']; //孩子数量(男)
            $data1['CHILD_GIRL_NUM'] = $data['childGirlNum']; //孩子数量(女)
            $data1['PER_POLITICAL'] = $data['perPolitical'];//政治面貌
            $data1['HOUSEHOLD'] = $data['hosehold']; //户口性质
            $data1['PER_NATIONAL'] =$data['perNational']; //民族
            $data1['FUND_ACCOUNT'] =$data['fundAccount']; //公积金账号
            $data1['SC_EMAIL'] =$data['scEmail']; //花名邮箱
            $data1['EMAIL'] = $data['email'];   //私人邮箱
            $data1['WE_CHAT'] = $data['weChat']; //微信
            $data1['QQ_ACCOUNT'] = $data['qqAccount'];//qq账号
            $data1['HOU_ADDRESS'] = $data['houAdderss']; //户籍地址
            $data1['LIVING_ADDRESS'] = $data['livingAddress'];  //现居住地址
            $data1['FIRST_LAN'] = $data['firstLan']; //第一外语
            $data1['FIRST_LAN_LEVEL'] = $data['firstLanLevel']; //外语程度
            $data1['SECOND_LAN'] = $data['secondLan'];
            $data1['SECOND_LAN_LEVEL'] = $data['secondLanLevel'];
            $data1['HOBBY_SPA'] = $data['hobbySpa'];  //兴趣爱好
            $data1['PER_CARD_PIC'] = $data['perCardPic']; //身份证正反面
            $data1['RESUME'] =$data['resume'];  //简历
            $data1['GRA_SCHOOL'] =$data['eduExp'][0]['schoolName'];
            $data1['EDU_BACK'] =$data['eduExp'][0]['eduDegNat'];
            $data1['DEPT_GROUP'] =$data['deptGroup'];
            $data1['RANK'] = $data['rank'];
            $data1['MAJORS'] = $data['eduExp'][0]['eduMajors'];
            $data1['PROVINCE'] = $data['houAdderss']['proh'];
            $data1['CITY'] = $data['houAdderss']['cityH'];
            $data1['AREA'] = $data['houAdderss']['areaH'];
            $data1['DETAIL'] = $data['houAdderss']['detailH'];

            $data1['PROVINCE_LIVING'] = $data['livingAddress']['provL'];
            $data1['CITY_LIVING'] = $data['livingAddress']['cityL'];
            $data1['AREA_LIVING'] = $data['livingAddress']['areaL'];
            $data1['DETAIL_LIVING'] = $data['livingAddress']['detailL'];
            //$m = D('hr');
          $id = $data['emplid'];
          $loginName = $_SESSION['m_loginname'];
          
          //echo M('hr_empl_child','tb_')->_sql();die;
        if ($_SESSION['m_loginname']==$data['erpAct']) {    //员工编辑

            $m = D('TbHrCard');
            if ($ret = $m ->create($data1,1)) {
                 $res = $m ->where('EMPL_ID='.$id)->save($ret);    
            }else{
                $code = 500;
                $msg ='error';
                $reason = $m->getError();
                $res = $reason;
                return $this->responseData($code,$msg,$res);die;
            }
           }else{
            $m = M('hr_card','tb_');
            $res = $m->where('EMPL_ID='.$id)->save($data1);
           }
            $resDel = M('hr_empl_child','tb_')->where('TYPE  in (0,1,2,4,5,11,12) AND EMPL_ID = '.$id)->delete();
                $m1 = D('TbHrEmplChild');
                $m1->startTrans();
            
                //echo  $data['friInfo']['concatName'];die;
                //var_dump($data);die;
                $value2['V_STR1'] = $data['concatName'];
                $value2['V_STR2'] = $data['concatWay'];
                $value2['V_STR3'] = $data['concatRel'];
                $value2['TYPE'] = 0;
                $value2['EMPL_ID'] = $id;
                $res = $m1->add($value2);
                //echo $m1->_sql();die;
                 

            
                
                $eduExp = $data['eduExp'];
                $home = $data['home'];
                $workExp = $data['workExp'];
                $training = $data['training'];
                $certificate = $data['certificate'];
                $bankCard = $data['bankCard'];
            foreach ($eduExp as $key => $eduvalue) {
                $edu['V_DATE1'] =cutting_time($eduExp[$key]['eduStartTime']);
                $edu['V_DATE2'] =cutting_time($eduExp[$key]['eduEndTime']);
                $edu['V_STR1'] = $eduExp[$key]['schoolName'];
                $edu['V_STR2'] = $eduExp[$key]['eduMajors'];
                $edu['V_STR3'] = $eduExp[$key]['eduDegNat'];
                $edu['V_INT1'] = $eduExp[$key]['isDegree'];
                $edu['V_STR4'] = $eduExp[$key]['certiNo'];
                $edu['V_STR5'] = $data['graCert'];
                $edu['V_STR6'] = $data['degCert']; 
                $edu['V_STR7'] = $data['learnProve'];
                $edu['V_STR8'] = $eduExp[$key]['validateRes'];
                $edu['TYPE'] = 2;
                $edu['EMPL_ID'] = $id;
                $res = $m1->add($edu);
            }

            foreach ($home as $key => $value) {
                $value3 = array();
                $value3['V_STR1'] = $value['homeRes'];
                $value3['V_STR2'] = $value['homeName'];
                $value3['V_STR3'] = $value['homeAge'];
                $value3['V_STR4'] = $value['occupa'];
                $value3['V_STR8'] = $value['workUnits'];
                $value3['TYPE'] = 1;
                $value3['EMPL_ID'] = $id;
                $res3 = $m1->add($value3);
            }

            foreach ($training  as $key => $value) {
                $value4 = array();
                $value4['V_STR1'] = $value['trainingName'];
                $value4['V_DATE1'] =cutting_time($value['trainingStartTime']);
                $value4['V_DATE2'] =cutting_time($value['trainingEndTime']);
                $value4['V_STR2'] = $value['trainingIns'];
                $value4['V_STR9'] = $value['trainingDes'];
                $value4['TYPE'] = 4;
                $value4['EMPL_ID'] = $id;
                $res4 = $m1->add($value4);
            }

            foreach ($certificate as $key => $value) {
                $value5 = array();
                $value5['V_STR1'] = $value['certiName'];
                $value5['V_DATE1'] =cutting_time($value['certifiTime']);
                $value5['V_STR2'] = $value['certifiunit'];
                    $value5['TYPE'] = 5;
                    $value5['EMPL_ID'] = $id;
                    $res5 = $m1->add($value5);
            }

            foreach ($workExp as $key => $value) {
                $value11 = array();
                $value11['V_DATE1'] = cutting_time($value['wordStartTime']);
                $value11['V_DATE2'] =cutting_time($value['wordEndTime']);
                $value11['V_STR1'] = $value['companyName'];
                $value11['V_STR2'] = $value['posi'];
                $value11['V_STR3'] = $value['depReason'];
                    $value11['TYPE'] = 11;
                    $value11['EMPL_ID'] = $id;
                    $res11 = $m1->add($value11);

            }
            foreach ($bankCard as $key => $value) {
                $value12 = array();
                $value12['V_STR1'] = $value['bankAct'];
                $value12['V_STR2'] = $value['bankName'];
                $value12['V_STR3'] = $value['swiftCood'];
                $value12['V_STR4'] = $value['bankDeposit'];
                $value12['V_STR5'] = $value['BankEndeposit'];

                    $value12['TYPE'] = 12;
                    $value12['EMPL_ID'] = $id;
                    $res12 = $m1->add($value12);
            }


                

                if (1) {
                    $code = 200;
                    $msg = 'success';
                    $res = '编辑成功';
                    $m1->commit();
                    return $this->responseData($code,$msg,$res);die;
                }else{
                    $code = 500;
                    $msg = 'error';
                    $res = '编辑失败';
                    return $this->responseData($code,$msg,$res);die;
                }

        
        
    }

    public function changeTrack($data)
    {  //继续
        //var_dump($data);die;
        foreach ($data as $key => $value) {
            //var_dump($value);die;
            $data = json_decode($value,true);
        }
        //var_dump($data);die;
        $conInfo = $data['contract'];
        //var_dump($conInfo);die;
        $reward = $data['reward'];
        $promo = $data['promo'];
        $paper = $data['paperMiss'];
        $inter = $data['interArr'];
        $id = $data['emplid'];
        //var_dump($id);die;
        //var_dump($conInfo);die;
        $m = M('hr_empl_child','tb_');
        $m->startTrans(); 
        $resDel = $m->where('TYPE  in (3,7,8,9,10) AND EMPL_ID = '.$id)->delete();  
        //echo $m->_sql();die;
        //var_dump($data['contract']);die;
         
          foreach ($conInfo as $key => $value) {
                $value3 = array();
                $value3['V_STR1'] = $value['conCompany'];
                $value3['V_STR2'] = $value['natEmploy'];
                $value3['V_DATE1'] = cutting_time($value['trialEndTime']);
                $value3['V_DATE2'] = cutting_time($value['conStartTime']);
                $value3['V_DATE3'] =cutting_time($value['conEndTime']);

                    $value3['TYPE'] = 3;
                    $value3['EMPL_ID'] = $id;
                    $res3 = $m->add($value3);
            }
        
        
            foreach ($reward as $key => $value) {
                $value7 = array();
                $value7['V_STR1'] = $value['rewardName'];
                $value7['V_STR10'] = $value['rewardContent'];
                $value7['TYPE'] = 7;
                $value7['EMPL_ID'] = $id;
                $res7 = $m->add($value7);
                //echo $m->_sql();die; 
                //$res7 = $m->where('TYPE =7 AND EMPL_ID='.$id)->save($value7);
                
            }
            foreach ($promo as $key => $value) {
                $value8 = array();
                $value8['V_STR1'] = $value['promoType'];
                $value8['V_DATE1'] = cutting_time($value['promoTime']);
                $value8['V_STR10'] = $value['promoContent'];
                $value8['TYPE'] = 8;
                $value8['EMPL_ID'] = $id;
                $res8 = $m->add($value8);
                //$res8 = $m->where('TYPE =8 AND EMPL_ID='.$id)->save($value8);
                
            }
            foreach ($paper as $key => $value) {
                $value10 = array();
                $value10['V_DATE1'] = cutting_time($value['paperMissTime']);
                $value10['V_STR10'] = $value['paperMissCon'];
                $value10['TYPE'] = 10;
                $value10['EMPL_ID'] = $id;
                $res10 = $m->add($value10);
                //$res10 = $m->where('TYPE =10 AND EMPL_ID='.$id)->save($value10);
                
            }
            foreach ($inter as $key => $value) {
                $value9 = array();
                $value9['V_STR1'] = $value['interType'];
                $value9['V_DATE1'] = cutting_time($value['interTime']);
                $value9['V_STR2'] = $value['interObj'];
                $value9['V_STR3'] = $value['interPerson'];
                $value9['V_STR4'] = $value['interContent'];
                $value9['V_STR5'] = $value['afterCase'];
                $value9['TYPE'] = 9;
                $value9['EMPL_ID'] = $id;
                $res9 = $m->add($value9);
                //$res9 = $m->where('TYPE =9 AND EMPL_ID='.$id)->save($value9);             
            }

            if ($res3&&$res7&&$res8&&$res9&&$res10) {
                 $m->commit();
                    $code = 200;
                    $msg = 'success';
                    $res = '编辑成功';
                    return $this->responseData($code,$msg,$res);die;
            }else{
                 $m->rollback();
                    $code = 500;
                    $msg = 'error';
                    $res = '编辑失败';
                    return $this->responseData($code,$msg,$res);die;
            }

    }

    public function address($data)
    {
        
        if ($data['areaNo']) {
            $params = $data['areaNo'];
            $data=curl_request("http://b5caiapi.stage.com/index/area.json?parentNo={$params}");
        }else{
            $data=curl_request("http://b5caiapi.stage.com/index/area.json?parentNo=1");
        }
        return $data;
    }

    public function export($id)
    {
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->createSheet();//创建新的内置表
        $objPHPExcel->setActiveSheetIndex(1);//把新创建的sheet设定为当前活动sheet
        $objSheet=$objPHPExcel->getActiveSheet();//获取当前活动sheet
        $objSheet->setTitle("员工导出数据");//给当前活动sheet起个名称
        $arrId = explode(',', $id);
        $m = M('hr_card','tb_');
        $dataEmpl = array();
        foreach ($arrId as  $v) {
            $dataEmpl[] =  $m->where('EMPL_ID='.$v)->find();
        }

        $objSheet->setCellValue("A1","工号")->setCellValue("B1","入职时间")->setCellValue("C1","司龄")->setCellValue("D1","花名")->setCellValue("E1","中文职位")
        ->setCellValue("F1","部门")->setCellValue("G1","组别")->setCellValue("H1","工作地点")->setCellValue("I1","身份证号")->setCellValue("J1","出生日期")
        ->setCellValue("K1","性别")->setCellValue("L1","户籍")->setCellValue("M1","手机号码")->setCellValue("N1","毕业院校")->setCellValue("O1","学历");//填充数据
        $j=2;
        foreach($dataEmpl as $key=>$val){
                if ($val['SEX']=='1') {
                $val['SEX'] = '女';
            }
                $objSheet->setCellValue("A".$j,"'".$val['WORK_NUM'])->setCellValue("B".$j,$val['PER_JOB_DATE'])->setCellValue("C".$j,$val['COMPANY_AGE']."年")
                ->setCellValue("D".$j,$val['EMP_SC_NM'])->setCellValue("E".$j,$val['JOB_CD'])->setCellValue("F".$j,$val['DEPT_NAME'])
                ->setCellValue("G".$j,$val['DEPT_GROUP'])->setCellValue("H".$j,$val['WORK_PALCE'])->setCellValue("I".$j,$val['PER_CART_ID'])
                ->setCellValue("J".$j,$val['PER_BIRTH_DATE'])->setCellValue("K".$j,$val['SEX'])->setCellValue("L".$j,$val['PER_RESIDENT'])
                ->setCellValue("M".$j,$val['PER_PHONE'])->setCellValue("N".$j,$val['GRA_SCHOOL'])->setCellValue("O".$j,$val['EDU_BACK']);
                $j++;
        }
        $objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
    //$objWriter->save($dir."/export_1.xls");//保存文件
        function browser_export($type,$filename){
            if($type=="Excel5"){
                    header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件
            }else{
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
            }
            header('Content-Disposition: attachment;filename="'.$filename.'"');//告诉浏览器将输出文件的名称
            header('Cache-Control: max-age=0');//禁止缓存
        }
         browser_export('Excel5','empl.xls');//输出到浏览器
         return $objWriter->save("php://output");
    }

    public function delete($data)
    {
        $id = $data['emplId'];
        //关联删除
        $res = D('TbHrEmpl')->relation(true)->delete($id);
        //echo D('');
        if ($res) {
            $code = 200;
            $msg = 'success';
            $res = '删除成功';
        }else{
            $code = 500;
            $msg = 'error';
            $res = '删除失败';
        }
        return $this->responseData($code,$msg,$res);exit();
    }
}



