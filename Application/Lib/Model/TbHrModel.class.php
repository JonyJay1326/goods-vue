<?php 
/**
* Hr人员管理模型
*/
class TbHrModel extends BaseModel
{

	private $returndata = [
        'empNm' => '',
        'info' => null,
        'status' => null,
    ];

	

	/**
     * 人员管理人员列表数据
     * @param $Keyword 人员搜索关键字条件
     * @return 
     */
	public function showPerson($Keyword){    
		$m= M('hr_card','tb_');
		$where = $this->getwhere($Keyword);
		$Person = $m
		->field('tb_hr_card.EMPL_ID,tb_hr_card.ERP_ACT,tb_hr_card.WORK_NUM,tb_hr_card.PER_JOB_DATE,tb_hr_card.COMPANY_AGE,tb_hr_card.EMP_SC_NM,tb_hr_card.JOB_CD,tb_hr_card.DEPT_NAME,tb_hr_card.WORK_PALCE,tb_hr_card.PER_CART_ID,tb_hr_card.PER_BIRTH_DATE,tb_hr_card.SEX,tb_hr_card.PER_RESIDENT,tb_hr_card.PER_PHONE,tb_hr_card.GRA_SCHOOL,tb_hr_card.EDU_BACK,tb_hr_card.DEPT_GROUP')
		->order('EMPL_ID desc')
		->where($where)
		->select();
		//echo $m->_sql();die;
		//var_dump($Person);die; 
		foreach ($Person as $key => $v) {
			//var_dump($value);die;
			if ($Person[$key]['PER_BIRTH_DATE'] == '0000-00-00 00:00:00') {
				$Person[$key]['PER_BIRTH_DATE'] = '';
			}
			if ($Person[$key]['PER_JOB_DATE'] == '0000-00-00 00:00:00') {
				$Person[$key]['PER_JOB_DATE'] = '';
			}
			$Person[$key]['PER_JOB_DATE'] = $Person[$key]['PER_JOB_DATE']?substr($Person[$key]['PER_JOB_DATE'], 0,10):'';
			$Person[$key]['PER_BIRTH_DATE'] = $Person[$key]['PER_BIRTH_DATE']?substr($Person[$key]['PER_BIRTH_DATE'], 0,10):'';
		$LEADERID = $v['LEADER_MNG_ID'];
		$LEADER = $m->where('EMPL_ID='."'".$LEADERID."'")->find();

		$Person[$key]['LEADER_MNG_PR'] = $LEADER['EMP_SC_NM'];
			if (is_null($Person[$key]['LEADER_MNG_PR'])) {
				$Person[$key]['LEADER_MNG_PR'] = '空';
			}
			if ($Person[$key]['SEX']=='0') 
				$Person[$key]['SEX'] = '男';
			if ($Person[$key]['SEX']=='1') 
				$Person[$key]['SEX'] = '女';
			
		}
		//var_dump($Person);die;
		return $Person;
	}
	/**
	 *人员筛选条件
	 */
	public function getwhere($Keyword){
		$key = '';
		$where = array();
		$Keyword =json_decode($Keyword['params'],true);	
		$key = $Keyword['seKey'];  //关键字查询
		if ($key) {
			$where['EMP_SC_NM'] = array("like","%$key%");
			$where['DIVISON_NAME'] = array("like","%$key%");
			$where['DEPT_NAME'] = array("like","%$key%");
			$where['PHONE_NUM'] = array("like","%$key%");
			$where['OFF_TEL'] = array("like","%$key%"); 
			$where['LEADER_MNG_ID'] = array("like","%$key%");
			$where['_logic'] = 'or';
		}
		if ($Keyword['seWorkNum']) {
			$where['WORK_NUM'] = array('like',"%{$Keyword['seWorkNum']}%");
		}
		if ($Keyword['seDept']) {
			$where['DEPT_NAME'] = $Keyword['seDept'];
		}
		if ($Keyword['seWorkplace']) {
			$where['WORK_PALCE'] = $Keyword['seWorkplace'];
		}
		if ($Keyword['seStatus']) {
			$where['STATUS'] = $Keyword['seStatus'];
		}
		if ($Keyword['seScNm']) {
			$where['EMP_SC_NM'] = array('like',"%{$Keyword['seScNm']}%");
		}
		if ($Keyword['seLeader']) {
			$where['DIRECT_LEADER'] = $Keyword['seLeader'];
		}
		if ($Keyword['seComPh']) {
			$where['OFF_TEL'] = array('like',"%{$Keyword['seComPh']}%");
		}
		if ($Keyword['seJobCd']) {
			$where['JOB_CD'] = $Keyword['seJobCd'];
		}
		if ($Keyword['seTrName']) {
			$where['EMP_NM'] = array('like',"%{$Keyword['seTrName']}%");
		}
		if ($Keyword['seEmail']) {
			$where['EMAIL'] = array('like',"%{$Keyword['seEmail']}%");
		}
		if ($Keyword['seCellPh']) {
			$where['PHONE_NUM'] = array('like',"%{$Keyword['seCellPh']}%");
		}
		if ($Keyword['seJobType']) {
			$where['JOB_TYPE_CD'] = $Keyword['seJobType'];
		}
		if ($Keyword['seName']) {
			$where['SEX'] = $Keyword['seName'];
		}/*elseif ($Keyword['seMonk']) {
			$where['WORK_NUM'] = array('like',"%$Keyword['seWorkNum']%");
		}*/
		
		return $where;
	}
	/**
	 *名片
	 */
	public function cardData($data){
		//var_dump($data);die;
		$emplId = $data['emplID'];
		if ($emplId) {
			$cardInfo1 = M('hr_card','tb_')->where('tb_hr_card.EMPL_ID='."'".$emplId."'")->find();
		}else{
			$cardInfo1 = M('hr_card','tb_')->where('tb_hr_card.ERP_ACT='."'".$_SESSION['m_loginname']."'")->find();
		}
		foreach ($cardInfo1 as $key => $value) {
			if ($cardInfo1[$key]=='0000-00-00 00:00:00') {
				$cardInfo1[$key] = '';
			}
		}

		//var_dump($cardInfo1);die;
		$cardInfo['Pic'] = 'index.php?m=Api&a=show&filename='.$cardInfo1['PIC'];
		$cardInfo['workNum'] = $cardInfo1['WORK_NUM'];
		$cardInfo['EmpScNm'] = $cardInfo1['EMP_SC_NM'];
		$cardInfo['perJobDate'] =$cardInfo1['PER_JOB_DATE']?substr($cardInfo1['PER_JOB_DATE'],0,10):'';
		$cardInfo['deptName'] = $cardInfo1['DEPT_NAME'];
		$cardInfo['deptGroup'] = $cardInfo1['DEPT_GROUP'];
		$cardInfo['companyAge'] = $cardInfo1['COMPANY_AGE'];
		$cardInfo['jobCd'] = $cardInfo1['JOB_CD'];
		$cardInfo['JobEnCd'] = $cardInfo1['JOB_EN_CD'];
		$cardInfo['workPlace'] = $cardInfo1['WORK_PALCE'];
		$cardInfo['directLeader'] = $cardInfo1['DIRECT_LEADER'];   
		$cardInfo['departHead'] = $cardInfo1['DEPART_HEAD'];
		$cardInfo['dockingHr'] = $cardInfo1['DOCKING_HR'];
		$cardInfo['rank'] = $cardInfo1['RANK'];
		$cardInfo['depJobDate'] = $cardInfo1['DEP_JOB_DATE']?substr($cardInfo1['DEP_JOB_DATE'],0,10):'';
		$cardInfo['depJobNum'] = $cardInfo1['DEP_JOB_NUM'];
		$cardInfo['erpAct'] = $cardInfo1['ERP_ACT'];
		$cardInfo['erpPwd'] = $cardInfo1['ERP_PWD'];
		$cardInfo['status'] = $cardInfo1['STATUS'];
		$cardInfo['empNm'] = $cardInfo1['EMP_NM'];
		$cardInfo['prePhone'] = $cardInfo1['PER_PHONE'];
		$cardInfo['offTel'] = $cardInfo1['OFF_TEL'];
		$cardInfo['jobTypeCd'] = $cardInfo1['JOB_TYPE_CD'];
		$cardInfo['perCartId'] = $cardInfo1['PER_CART_ID'];
		$cardInfo['sex'] = $cardInfo1['SEX'];
		$cardInfo['perIsSmoking'] = $cardInfo1['PER_IS_SMOKING'];
		$cardInfo['perBirthDate'] = $cardInfo1['PER_BIRTH_DATE']?substr($cardInfo1['PER_BIRTH_DATE'],0,10):'';
		$cardInfo['age'] = $cardInfo1['AGE'];
		$cardInfo['perAddress'] = $cardInfo1['PER_ADDRESS'];
		$cardInfo['perResident'] = $cardInfo1['PER_RESIDENT'];
		$cardInfo['perIsMarried'] = $cardInfo1['PER_IS_MARRIED'];
		$cardInfo['childNum'] = $cardInfo1['CHILD_NUM'];
		$cardInfo['childBoyNum'] = $cardInfo1['CHILD_BOY_NUM'];
		$cardInfo['childGirlNum'] = $cardInfo1['CHILD_GIRL_NUM'];
		$cardInfo['perPolitical'] = $cardInfo1['PER_POLITICAL'];
		$cardInfo['hosehold'] = $cardInfo1['HOUSEHOLD'];
		$cardInfo['fundAccount'] = $cardInfo1['FUND_ACCOUNT'];
		$cardInfo['scEmail'] = $cardInfo1['SC_EMAIL'];
		$cardInfo['email'] = $cardInfo1['EMAIL'];
		$cardInfo['weChat'] = $cardInfo1['WE_CHAT'];
		$cardInfo['qqAccount'] = $cardInfo1['QQ_ACCOUNT'];
		$cardInfo['livingAddress'] = $cardInfo1['LIVING_ADDRESS'];
		$cardInfo['firstLan'] = $cardInfo1['FIRST_LAN'];
		$cardInfo['firstLanLevel'] = $cardInfo1['FIRST_LAN_LEVEL'];
		$cardInfo['secondLan'] = $cardInfo1['SECOND_LAN'];
		$cardInfo['secondLanLevel'] = $cardInfo1['SECOND_LAN_LEVEL'];
		$cardInfo['hobbySpa'] = $cardInfo1['HOBBY_SPA'];
		$cardInfo['perCardPic'] = $cardInfo1['PER_CARD_PIC'];
		$cardInfo['resume'] = $cardInfo1['RESUME'];
		$cardInfo['perNational'] = $cardInfo1['PER_NATIONAL'];
		$cardInfo['emplid'] = $cardInfo1['EMPL_ID'];

		$cardInfo['graCert'] = $cardInfo1['GRA_CERT'];
		$cardInfo['degCert'] = $cardInfo1['DEG_CERT'];
		$cardInfo['learnProve'] = $cardInfo1['LEARN_PROVE'];


		$provH = $cardInfo1['PROVINCE'];
		$cityH = $cardInfo1['CITY'];
		$areaH = $cardInfo1['AREA'];
		$detailH = $cardInfo1['DETAIL'];

		$provL = $cardInfo1['PROVINCE_LIVING'];
		$cityL = $cardInfo1['CITY_LIVING'];
		$areaL = $cardInfo1['AREA_LIVING'];
		$detailL = $cardInfo1['DETAIL_LIVING'];
		$cardInfo['houAdderss'] = array(
			'proh' =>$provH,
			'cityH' =>$cityH,
			'areaH' =>$areaH,
			'detailH' =>$detailH
			);
		$cardInfo['livingAddress'] = array(
			'provL' =>$provL,
			'cityL' =>$cityL,
			'areaL' =>$areaL,
			'detailL' =>$detailL
			);

		$emplid = $cardInfo1['EMPL_ID'];

		$dataTest = D('TbHrEmplChild')->where('EMPL_ID='.$emplid)->select();

		foreach ($dataTest as $k => $v) {
			if ($v['TYPE']==0) {
				$friInfo['concatName'] = $dataTest[$k]['V_STR1'];
				$friInfo['concatWay'] = $dataTest[$k]['V_STR2'];
				$friInfo['concatRel'] = $dataTest[$k]['V_STR3'];
			}
			/*if ($v['TYPE']==1) {
				//var_dump($v);

				
				$home[]['homeRes'] = $dataTest[$k]['V_STR1'];
			}*/
		}
		$home2 = M('hr_empl_child','tb_')->field('V_STR1,V_STR2,V_STR3,V_STR4,V_STR8')->where('TYPE = 1 AND EMPL_ID='.$emplid)->select();
		$home =array();
		foreach ($home2 as $key => $value) {
			$value1['homeRes'] = $value['V_STR1'];
			$value1['homeName'] = $value['V_STR2'];
			$value1['homeAge'] = $value['V_STR3'];
			$value1['occupa'] = $value['V_STR4'];
			$value1['workUnits'] = $value['V_STR8'];
				$home[] = $value1;
		}



		$eduInfo2 = M('hr_empl_child','tb_')->field('V_DATE1,V_DATE2,V_STR1,V_STR2,V_STR3,V_INT1,V_STR4,V_STR5,V_STR6,V_STR7,V_STR8')->where('TYPE = 2 AND EMPL_ID='.$emplid)->select();
		//var_dump($eduInfo2);die;

		$eduInfo =array();
		foreach ($eduInfo2 as $key => $value) {
			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}
			//var_dump($value);die;
			$value2['eduStartTime'] = $value['V_DATE1']?substr($value['V_DATE1'],0,10):'';
			$value2['eduEndTime'] = $value['V_DATE2']?substr($value['V_DATE2'],0,10):'';
			$value2['schoolName'] = $value['V_STR1'];
			$value2['eduMajors'] = $value['V_STR2'];
			$value2['certiNo'] = $value['V_STR4'];
			$value2['isDegree'] = $value['V_INT1'];
			$value2['eduDegNat'] = $value['V_STR3'];
			$value2['graCert'] = $value['V_STR5'];
			$value2['degCert'] = $value['V_STR6'];
			$value2['learnProve'] = $value['V_STR7'];
			$value2['validateRes'] = $value['V_STR8'];
			//if ($value1['certiNo']) {
				$eduInfo[] = $value2;
			//}
		}


		$conInfo2 = M('hr_empl_child','tb_')->field('V_STR1,V_STR2,V_DATE1,V_DATE2,V_DATE3')->where('TYPE = 3 AND EMPL_ID='.$emplid)->select();
		//var_dump($conInfo2);die;
		$conInfo =array();
		foreach ($conInfo2 as $key => $value) {

			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}

			$value3['conCompany'] = $value['V_STR1'];
			$value3['natEmploy'] = $value['V_STR2'];
			$value3['trialEndTime'] = $value['V_DATE1']?substr($value['V_DATE1'],0,10):'';
			$value3['conStartTime'] = $value['V_DATE2']?substr($value['V_DATE2'],0,10):'';
			$value3['conEndTime'] = $value['V_DATE3']?substr($value['V_DATE3'],0,10):'';
			//if ($value2['conCompany']) {
				$conInfo[] = $value3;
			//}
		}


		$training2 = M('hr_empl_child','tb_')->field('V_STR1,V_STR2,V_DATE1,V_DATE2,V_STR9')->where('TYPE = 4 AND EMPL_ID='.$emplid)->select();
		$training =array();
		foreach ($training2 as $key => $value) {

			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}
			$value4['trainingName'] = $value['V_STR1'];
			$value4['trainingStartTime'] = $value['V_DATE1']?substr($value['V_DATE1'],0,10):'';
			$value4['trainingEndTime'] = $value['V_DATE2']?substr($value['V_DATE2'],0,10):'';
			$value4['trainingIns'] = $value['V_STR2'];
			$value4['trainingDes'] = $value['V_STR9'];
			//if ($value2['conCompany']) {
				$training[] = $value4;
			//}
		}


		$certificate2 = M('hr_empl_child','tb_')->field('V_STR1,V_DATE1,V_STR2')->where('TYPE = 5 AND EMPL_ID='.$emplid)->select();
		$certificate =array();
		foreach ($certificate2 as $key => $value) {

			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}

			$value5['certiName'] = $value['V_STR1'];
			$value5['certifiTime'] = $value['V_DATE1']?substr($value['V_DATE1'],0,10):'';
			$value5['certifiunit'] = $value['V_STR2']?substr($value['V_STR2'],0,10):'';
				$certificate[] = $value5;
		}

		$reward2 = M('hr_empl_child','tb_')->field('V_STR1,V_STR10')->where('TYPE = 7 AND EMPL_ID='.$emplid)->select();
		$reward =array();
		foreach ($reward2 as $key => $value) {
			$value7['rewardName'] = $value['V_STR1'];
			$value7['rewardContent'] = $value['V_STR10'];
			//if ($value3['rewardName']) {
				$reward[] = $value7;
			//}
		}





		$promo2 = M('hr_empl_child','tb_')->field('V_STR1,V_DATE1,V_STR10')->where('TYPE = 8 AND EMPL_ID='.$emplid)->select();
		$promo =array();
		foreach ($promo2 as $key => $value) {

			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}

			$value8['promoType'] = $value['V_STR1'];
			$value8['promoTime'] = $value['V_DATE1']?substr($value['V_DATE1'], 0,10):'';
			$value8['promoContent'] = $value['V_STR10'];
			//if ($value4['promoType']) {
				$promo[] = $value8;
			//}
		}


		$inter2 = M('hr_empl_child','tb_')->field('V_STR1,V_DATE1,V_STR2,V_STR3,V_STR4,V_STR5')->where('TYPE = 9 AND EMPL_ID='.$emplid)->select();
		$inter =array();
		foreach ($inter2 as $key => $value) {

			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}

			$value9['interType'] = $value['V_STR1'];
			$value9['interTime'] = $value['V_DATE1']?substr($value['V_DATE1'], 0,10):'';
			$value9['interObj'] = $value['V_STR2'];
			$value9['interPerson'] = $value['V_STR3'];
			$value9['interContent'] = $value['V_STR4'];
			$value9['afterCase'] = $value['V_STR5'];
			//var_dump($value5);die;
			//if ($value5['interType']) {
				$inter[] = $value9;
			//}
		}



		$paper2 = M('hr_empl_child','tb_')->field('V_DATE1,V_STR10')->where('TYPE = 10 AND EMPL_ID='.$emplid)->select();
		$paper =array();
		foreach ($paper2 as $key => $value) {

			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}

			$value10['paperMissTime'] = $value['V_DATE1']?substr($value['V_DATE1'], 0,10):'';
			$value10['paperMissCon'] = $value['V_STR10'];
			//if ($value1['paperMissTime']) {
				$paper[] = $value10;
			//}
		}


		$workExp2 = M('hr_empl_child','tb_')->field('V_DATE1,V_STR1,V_STR2,V_STR3,V_DATE2')->where('TYPE = 11 AND EMPL_ID='.$emplid)->select();
		$workExp =array();
		foreach ($workExp2 as $key => $value) {
			//var_dump($value);
			foreach ($value as $k => $v) {
				if ($v=='0000-00-00 00:00:00') {
					$value[$k] = '';
				}
			}

//var_dump($value);die;
			$value11['wordStartTime'] = $value['V_DATE1']?substr($value['V_DATE1'], 0,10):'';
			$value11['wordEndTime'] = $value['V_DATE2']?substr($value['V_DATE2'], 0,10):'';
			$value11['companyName'] = $value['V_STR1'];
			$value11['posi'] = $value['V_STR2'];
			$value11['depReason'] = $value['V_STR3'];
			//if ($value1['paperMissTime']) {
				$workExp[] = $value11;
			//}
		}

		$bankCard2 = M('hr_empl_child','tb_')->field('V_STR1,V_STR2,V_STR3,V_STR4,V_STR5')->where('TYPE = 12 AND EMPL_ID='.$emplid)->select();
		$bankCard =array();
		foreach ($bankCard2 as $key => $value) {
			$value12['bankAct'] = $value['V_STR1'];
			$value12['bankName'] = $value['V_STR2'];
			$value12['swiftCood'] = $value['V_STR3'];
			$value12['bankDeposit'] = $value['V_STR4'];
			$value12['BankEndeposit'] = $value['V_STR5'];
				$bankCard[] = $value12;
		}

		$Mycard = array(
			'cardInfo' =>$cardInfo,
			'workExp' =>$workExp,
			'home' =>$home,
			'friInfo' =>$friInfo,
			'eduInfo' =>$eduInfo,
			'conInfo' => $conInfo,
			'reward' =>$reward,
			'training' =>$training,
			'promo' =>$promo,
			'inter' =>$inter,
			'workExp' =>$workExp,
			'certificate' =>$certificate,
			'bankCard' =>$bankCard,
			'paper' => $paper,
			);
		return $Mycard;
	}


	
	/**
	 *个人信息编辑
	 *@param $personalData  要修改的个人信息数据
	 */
	public function editPersonal($personalData)
	{
		$m = D('hr_empl','tb_');
		
	}

	/**
	 *我的下属
	 *
	 */
	public function SubData()
	{
		$myAccount = $_SESSION['m_loginname'];
		$m = M('hr_card','tb_');
		$MyData = $m->where('ERP_ACT='."'".$myAccount."'")->find();
		$myId = $MyData['EMPL_ID'];
		$res = M('hr_card','tb_')->where('LEADER_MNG_ID = '."'".$myId."'")->select();
		return $res;
	}

}
?>