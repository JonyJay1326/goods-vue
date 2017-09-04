<?php

class BaseModel extends RelationModel
{
    //public function __construct()
//    {
//        parent::__construct();
//    }
    public static $conCompanyCd;
    private $_requestData = null;
    private $_responseData = null;
    // 我方公司
    public static function conCompanyCd()
    {
        //'CON_COMPANY_CD' => 
        // N00140 tb_ms_cmn_cd
        // SELECT * FROM `tb_ms_cmn_cd` WHERE `CD` LIKE '%N00124%' LIMIT 0, 1000
        if (static::$conCompanyCd) return static::$conCompanyCd;
        $model = M('_ms_cmn_cd', 'tb_');
        $conditions ['CD'] = ['like', '%N00124%'];
        $ret = $model->where($conditions)->select();
        return static::$conCompanyCd = array_column($ret, 'CD_VAL', 'ETC');
        return [
            'N001240100' => 'iZENEhk,Limited',
            'N001240200' => '载信软件（上海）有限公司',
            'N001240300' => '上海载和网络科技有限公司',
            'N001240400' => '上海载和网络科技有限公司第一分公司',
            'N001240500' => '载鸿贸易（上海）有限公司',
            'N001240600' => '载鸿贸易（上海）有限公司第一分公司',
            'N001240700' => '载鸿贸易（上海）有限公司第二分公司',
            'N001240800' => '载运供应链管理（上海）有限公司',
            'N001240900' => 'IZENEhk,Limited(Sales Branch)',
            'N001241000' => 'iZene Japan G.K.',
            'N001241100' => 'iZENEtech,Inc.',
            'N001241200' => '杭州如杰网络科技有限公司',
            'N001241300' => '杭州英邦网络科技有限公司',
            'N001241400' => '北京极智网络科技有限公司',
            'N001241500' => '苏州华花网络科技有限公司',
            'N001241600' => '苏州畅有网络科技有限公司',
            'N001241700' => '苏州青娇网络科技有限公司',
            'N001241800' => '苏州紫藤网络科技有限公司',
            'N001241900' => '杭州誉繁网络科技有限公司',
            'N001242000' => '杭州海跃网络科技有限公司',
            'N001242100' => '杭州宏贯网络科技有限公司',
            'N001242200' => '杭州良币网络科技有限公司',
        ];
    }
    
    // 企业类型
    public static function companyTypeCd()
    {
        //'COPANY_TYPE_CD' => N00119
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->where('CD like "N00119%"')->select();
        if ($ret) {
            $ret = array_column($ret, 'CD_VAL', 'CD');
            return $ret;
        } else {
            return [
                'N001190100' => '品牌方',
                'N001190200' =>	'代理商',
                'N001190300' => '电商平台',
                'N001190400' => '生产商',
                'N001190500' => '综合集团',
                'N001190600' => '其他'
            ];
        }
    }
    
    public static $spYearScaleCds;
    // 供应商年业务规模
    public static function spYearScaleCd()
    {
        if (static::$spYearScaleCds) return static::$spYearScaleCds;
        //'SP_YEAR_SCALE_CD' => ,
        //'COPANY_TYPE_CD' => N00119
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->where('CD like "N00120%"')->select();
        if ($ret) {
            static::$spYearScaleCds = array_column($ret, 'CD_VAL', 'CD');
            return static::$spYearScaleCds;
        } else {
            return [
                'N001200100' => '<$1M',
                'N001200200' => '$1~5M',
                'N001200300' => '$5~10M',
                'N001200400' => '$10~50M',
                'N001200500' => '$50~100M',
                'N001200600' => '>=$100M',
        
            ];
        }
    }
    
    public static $spTeamCds;
    // 采购团队
    public static function spTeamCd()
    {
        if (static::$spTeamCds) return static::$spTeamCds;
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->where('CD like "N00129%"')->select();
        if ($ret) {
            $ret = array_column($ret, 'CD_VAL', 'CD');
            static::$spTeamCds = $ret;
            return static::$spTeamCds;
        } else {
            return [
                '' => '请选择采购团队',
                'N001290100' => 'AY-阿月',
                'N001290200' => 'TMZ-铁木真',
                'N001290300' => 'XY-轩辕',
            ];
        }
    }
    
    public static $saleTeamCd;
    // 销售
    public static function saleTeamCd()
    {
        if (static::$saleTeamCd) return static::$saleTeamCd;
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->where('CD like "N00129%"')->select();
        if ($ret) {
            $ret = array_column($ret, 'CD_VAL', 'CD');
            static::$saleTeamCd = $ret;
            return static::$saleTeamCd;
        } else {
            return [
                '' => '请选择销售团队',
                'N001290100' => 'AY-阿月',
                'N001290200' => 'TMZ-铁木真',
                'N001290300' => 'XY-轩辕',
            ];
        }
    }
    
    public static $spJsTeamCds;
    // 介绍团队
    public static function spJsTeamCd()
    {
        if (static::$spJsTeamCds) return static::$spJsTeamCds;
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->where('CD like "N00130%"')->select();
        if ($ret) {
            $ret = array_column($ret, 'CD_VAL', 'CD');
            static::$spJsTeamCds = $ret;
            return static::$spJsTeamCds;
        } else {
            return [
                '' => '请选择介绍团队',
                'N001300100' => 'AY-阿月',
                'N001300200' => 'CYC-常遇春',
                'N001300300' => 'SY-商鞅',
                'N001300400' => 'TMZ-铁木真',
                'N001300500' => 'LG-李广',
                'N001300600' => 'WQ-卫青',
                'N001300700' => 'DY-段翳',
                'N001300800' => 'QQ-秦琼',
                'N001300900' => 'JP-日本',
                'N001301000' => 'WL-蔚缭',
                'N001301100' => 'XY-轩辕',
            ];
        }
    }
    public static $conType;
    // 合作类型
    public static function conType()
    {
        if (static::$conType) return static::$conType;
        $model = M('_ms_cmn_cd', 'tb_');
        $conditions ['CD'] = ['like', '%N00123%'];
        $ret = $model->where($conditions)->select();
        return static::$conType = array_column($ret, 'CD_VAL', 'ETC');
        // 'CON_TYPE' => 
        return [
            'N001230100' => '供货合同',
            'N001230200' => '仓储合同',
            'N001230300' => '营销服务',
            'N001230400' => 'IT',
            'N001230500' => 'HR',
            'N001230600' => '法务',
            'N001230700' => '财务',
            'N001230800' => '其他',
        ];
    }
    
    // 是否自动续约
    public static function isAutoRenew()
    {
        return [
            0 => '自动续约',
            1 => '不自动续约'
        ];
    }
    
    // 条件筛选，国家
    public static $country;
    public static function getCountries()
    {
        if (static::$country) return static::$country;
        $model = M('_crm_site', 'tb_');
        $ret = $model->field('ID, CONCAT(RES_NAME, NAME) AS NAME')->where('PARENT_ID = 0')->order('sort asc')->select();
        
        if ($ret) {
            $ret = array_column($ret, 'NAME', 'ID');
            static::$country = $ret;
            return static::$country;
        }
    }
    
    
    // 获得国家
    public static function getCountry()
    {
        $model = M('_crm_site', 'tb_');
        return $model->field('ID, CONCAT(RES_NAME, NAME) AS NAME, PARENT_ID')->where('PARENT_ID = 0')->order('sort asc')->select();
    }
    
    // 获得省
    public static function getProvince($parent_id)
    {
        $model = M('_crm_site', 'tb_');
        return $model->field('NAME, PARENT_ID')->where('PARENT_ID = 1')->select();
    }
    
    // 获得市
    public static function getCity($parent_id)
    {
        $model = M('_crm_site', 'tb_');
        $ret = $model->field('NAME, ID')->where('PARENT_IDS = "' . $parent_id . '"')->select();
        return $ret;
    }
    
    // 获得县
    public static function getCounty($parent_id)
    {
        $model = M('_crm_site', 'tb_');
        return $model->field('NAME, ID')->where('PARENT_IDS = "' . $parent_id . '"')->select();
    }
    
    public static $localName;
    /**
     * 根据ID返回城市的名字
     * 
     */
    public static function getLocalName()
    {
        if (static::$localName) return static::$localName;
        $model = M('_crm_site', 'tb_');
        $result = $model->field('ID, NAME')->select();
        return static::$localName = array_column($result, 'NAME', 'ID');
    }
    
    public function getName()
    {
        return $_SESSION['userId'];
    }
    
    public function getTime()
    {
        return date('Y-m-d H:i:s');
    }
    
    public function upload() {
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = 3145728 ;// 设置附件上传大小
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath =  './Public/Uploads/';// 设置附件上传目录
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功
            $this->success('上传成功！');
        }
    }
    // 获取所有的用户信息
    public static $allUserInfo;
    
    public static function getAdmin()
    {
        if (static::$allUserInfo) return static::$allUserInfo;
        
        $model = M('_admin', 'bbm_');
        $ret = $model->field('M_ID, M_NAME')->select();
        
        return static::$allUserInfo = array_column($ret, 'M_NAME', 'M_ID');
    }
    
    // 获取所有的国别信息
    public static $allCountryInfo;
    
    public static function getCountryInfo()
    {
        if (static::$allCountryInfo) return static::$allCountryInfo;
        
        $model = M('_crm_site', 'tb_');
        $ret = $model->field('ID, NAME')->where('PARENT_ID = 0')->select();
        
        return static::$allCountryInfo = array_column($ret, 'NAME', 'ID');
    }
    
    // 商品类目
    public static $cmnCat;
    
    public static function getCmnCat()
    {
        if (static::$cmnCat) return static::$cmnCat;
        
        $model = M('ms_cmn_cat', 'tb_');
        $ret = $model->field('CAT_CNS_NM, CAT_CD')->where('CAT_LEVEL = 1')->select();
        
        return static::$cmnCat = array_column($ret, 'CAT_CNS_NM', 'CAT_CD');   
    }
    
    public static $currency;
    
    // 币种
    public static function getCurrency()
    {
        if (static::$currency) return static::$currency;
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N000590%"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return static::$currency = $ret;
//        
//        return [
//            'CNY' => '人民币',
//            'USD' => '美元',
//            'YEN' => '日元',
//            'WON' => '韩元',
//            'HKD' => '港币'
//        ];
    }
    
    public static $chinaMainlandAndHMT;
    
    /**
     * 供应商地区分类
     * 1、为中国大陆
     * 2、港澳台
     * 3、海外区域
     */
    public static function regionalClassification()
    {
        if (static::$chinaMainlandAndHMT) return static::$chinaMainlandAndHMT;
        $model = M('_crm_site', 'tb_');
        // 取得中国大陆与港澳台数据
        $ret = $model->field('ID')->where('PARENT_ID = ' . 1)->select();
        $ret = array_column($ret, 'ID');
        // 港澳台、HongKong、Macao、Taiwan
        $hmt = $model->field('ID')->where("NAME IN ('香港', '澳门', '台湾')")->select();
        $hmt = array_column($hmt, 'ID');
        // 求差集，获取到中国大陆数据，并缓存起来
        $ret = array_diff($ret, $hmt);
        return static::$chinaMainlandAndHMT = [
            2 => $hmt,
            1 => $ret,
        ];
    }
    
    /**
     * 付款方式
     * 
     */
    public static function paymentMode()
    {
        return [
            '1' => '1期付清',
            '2' => '2期付清',
            '3' => '3期付清',
            '0' => '未约定',
        ];
    }
    
    /**
     * 期数
     * 
     */
    public static function periods()
    {
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N001390%"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return $ret;
        //return [
//            "N9001" => "合同后",
//            'N910'  => "发货后",
//            "N911"  => "到港中",
//            "N912"  => "入库后",
//            "N913"  => "销售后",
//        ];
        //{"N9001":"合同后", 'N910':"发货后", "N911":"到港中", "N912":"入库后", "N913":"销售后", 'A':'B', 'C':'D', 'E':'F', 'G':'H'}
    }
    
    /**
     * 天数
     * 
     */
    public static function day()
    {
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N00142%"')->order('SORT_NO asc')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return $ret;
        // {"1":"1", '2':"2", "3":"3", "4":"4", "5":"5"}
        //return [
//            1 => 1,
//            2 => 2,
//            3 => 3,
//            4 => 4,
//            5 => 5
//        ];
    }
    
    /**
     * 付款百分比
     * 
     */
    public static function percentage()
    {
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N00141%"')->order('SORT_NO asc')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return $ret;
        // {"10":"10%", '20':"20%", "30":"30%", "40":"40%", "50":"50%", "60":"60%", "70":"70%", "80":"80%", "90":"90%", "100":"100%"}
        //return [
//            10 => '10%',
//            20 => '20%',
//            30 => '30%',
//            40 => '40%',
//            50 => '50%',
//            60 => '60%',
//            70 => '70%',
//            80 => '80%',
//            90 => '90%',
//            100 => '100%',
//        ];
    }
    
    /**
     * 审核状态
     * 
     */
    public static function auditState()
    {
        return [
            1 => '未审核',
            2 => '已审核'
        ];
    }
    
    /**
     * 风险评级
     * 
     */
    public static function riskRating()
    {
        return [
            1 => '低风险',
            2 => '中等风险',
            3 => '重大风险'
        ];
    }
    
    /**
     * 检查是否需要审核
     * update 2017/6/26 所有供应商客户均需要发送法务邮件审核 by benyin
     */
    public function checkNeedAudit($addr)
    {
        return true;
        //foreach (static::regionalClassification() as $key => $value) {
//            if (array_search($addr, $value) !== false and $key == 1) {
//                return true;
//            }
//        }
//        
//        return false;
    }
    
    /**
     * 审核评级标准
     * 
     */
    public static function auditGradeStandard()
    {
        return [
            4 => '中国大陆公司:<br />',
            3 => '高：  资金履行或货物供应能力有较严重问题。  <br />
                    如以下情况之一：（1）认缴资本100万以内（含），连续2次工商异常；（2）主要股东或企业被列为过失信人；（3）3年内2次应货款或货物问题被列为被告；（4）严重的行政处罚、产品曝光，包括对其声誉有较大影响。<br />
                    <br />',
            2 => '中： 资金履行或货物供应能力可能发生不太严重的问题。<br /> 
                    如以下情况都满足：（1）认缴资本在100万以上，工商异常很少；（2）虽有过民事案件纠纷被告，但不严重也没有列为失信人； （3）很少发生作为被告的案件； （4）虽有处罚，但不严重，产品也没有曝光有什么声誉影响。<br />
                    <br />',
            1 => '低： 查无行政处罚、最近2年无司法案件，且资本额在500万以上。<br /><br />',
            0=> '大陆以外公司：以专业报告中的信用评分和信用评级为主要评级依据。',
        ];
    }
    
    /**
     * 审核评级标准纯文本
     * 
     */
    public static function auditGradeStandardText()
    {
        return [
            3 => '高：资金履行或货物供应能力有较严重问题。如以下情况之一：（1）认缴资本100万以内（含），连续2次工商异常；（2）主要股东或企业被列为过失信人；（3）3年内2次应货款或货物问题被列为被告；（4）严重的行政处罚、产品曝光，包括对其声誉有较大影响。',
            2 => '中： 资金履行或货物供应能力可能发生不太严重的问题。如以下情况都满足：（1）认缴资本在100万以上，工商异常很少；（2）虽有过民事案件纠纷被告，但不严重也没有列为失信人； （3）很少发生作为被告的案件； （4）虽有处罚，但不严重，产品也没有曝光有什么声誉影响。',
            1 => '低： 查无行政处罚、最近2年无司法案件，且资本额在500万以上。'
        ];
    }
    
    /**
     * 邮件发送、供应商
     * 
     */
    public function supplierSendMail($data)
    {
        $address = C('supplier_customer_forensic_email_address');
        $web_site = C('redirect_audit_addr');
        if (!$this->checkNeedAudit($data['SP_ADDR3'])) return;
        $data['web_site'] = $web_site;
        $template = new SendMailMessageTemplateModel();
        
        $sps = explode(',', $data['SP_TEAM_CD']);
        $str = '';
        if (count($sps) > 1) {
            foreach ($sps as $key => $value) {
                $str .= static::spTeamCd()[$value] . ',';
            }
            $str = rtrim($str, ',');
        } else {
            $str = static::spTeamCd()[$data['SP_TEAM_CD']];
        }
        $content = sprintf($template->firstTrial(), $data['SP_NAME'], static::getLocalName()[$data['SP_ADDR1']], static::getLocalName()[$data['SP_ADDR3']], static::getLocalName()[$data['SP_ADDR4']], static::getAdmin()[$data['CREATE_USER_ID']], $str, $data['CREATE_TIME'], $data['web_site']);
        $mail = new ExtendSMSEmail();
        $mail->sendEmail($address, sprintf($template->supplierTitle(), $data ['SP_NAME']), $content);
    }
    
    /**
     * 邮件发送、供应商年度审核
     * 需添加上次审核人
     * 
     */
    public function supplierYearSendMail($data, $audit)
    {
        $address = C('supplier_customer_forensic_email_address');
        $web_site = C('redirect_audit_addr');
        if (!$this->checkNeedAudit($data['SP_ADDR3'])) return;
        $data['web_site'] = $web_site;
        $template = new SendMailMessageTemplateModel();
        
        $sps = explode(',', $data['SALE_TEAM']);
        $str = '';
        if (count($sps) > 1) {
            foreach ($sps as $key => $value) {
                $str .= static::spTeamCd()[$value] . ',';
            }
            $str = rtrim($str, ',');
        } else {
            $str = static::spTeamCd()[$data['SP_TEAM_CD']];
        }
        
        $content = sprintf($template->supplierYearExamine(), $data['SP_NAME'], static::getLocalName()[$data['SP_ADDR1']], static::getLocalName()[$data['SP_ADDR3']], static::getLocalName()[$data['SP_ADDR4']], static::getAdmin()[$data['CREATE_USER_ID']], $str, $data['CREATE_TIME'], static::getAdmin()[$audit['REVIEWER']], $audit['REV_TIME'] ,$data['web_site']);
        $mail = new ExtendSMSEmail();
        if (C('is_start_cc')) $mail->cAddr = static::getAdmin()[$v['REVIEWER']].'@gshopper.com';
        $mail->sendEmail($address, sprintf($template->supplierYearTitle(), $data ['SP_NAME']), $content);
    }
    
    /**
     * 邮件发送、客户管理
     * 
     */
    public function customerSendMail($data)
    {
        $address = C('supplier_customer_forensic_email_address');
        $web_site = C('redirect_audit_addr');
        if (!$this->checkNeedAudit($data['SP_ADDR3'])) return;
        $data['web_site'] = $web_site;
        $template = new SendMailMessageTemplateModel();
        
        $sps = explode(',', $data['SALE_TEAM']);
        $str = '';
        if (count($sps) > 1) {
            foreach ($sps as $key => $value) {
                $str .= static::saleTeamCd()[$value] . ',';
            }
            $str = rtrim($str, ',');
        } else {
            $str = static::saleTeamCd()[$data['SALE_TEAM']];
        }
        $content = sprintf($template->customerFirstTrial(), $data['SP_NAME'], static::getLocalName()[$data['SP_ADDR1']], static::getLocalName()[$data['SP_ADDR3']], static::getLocalName()[$data['SP_ADDR4']], static::getAdmin()[$data['CREATE_USER_ID']], $str, $data['CREATE_TIME'], $data['web_site']);
        $mail = new ExtendSMSEmail();
        $mail->sendEmail($address, sprintf($template->customerTitle(), $data ['SP_NAME']), $content);
    }
    
    /**
     * 邮件发送、客户年度审核
     * 
     */
    public function customerYearSendMail($data, $audit)
    {   
        $address = C('supplier_customer_forensic_email_address');
        $web_site = C('redirect_audit_addr');
        if (!$this->checkNeedAudit($data['SP_ADDR3'])) return;
        $data['web_site'] = $web_site;
        $template = new SendMailMessageTemplateModel();
        
        $sps = explode(',', $data['SALE_TEAM']);
        $str = '';
        if (count($sps) > 1) {
            foreach ($sps as $key => $value) {
                $str .= static::saleTeamCd()[$value] . ',';
            }
            $str = rtrim($str, ',');
        } else {
            $str = static::saleTeamCd()[$data['SALE_TEAM']];
        }
        $content = sprintf($template->customerYearExamine(), $data['SP_NAME'], static::getLocalName()[$data['SP_ADDR1']], static::getLocalName()[$data['SP_ADDR3']], static::getLocalName()[$data['SP_ADDR4']], static::getAdmin()[$data['CREATE_USER_ID']], $str, $data['CREATE_TIME'], static::getAdmin()[$audit['REVIEWER']], $audit['REV_TIME'],$data['web_site']);
        $mail = new ExtendSMSEmail();
        if (C('is_start_cc')) $mail->cAddr = static::getAdmin()[$v['REVIEWER']].'@gshopper.com';
        $mail->sendEmail($address, sprintf($template->customerYearTitle(), $data ['SP_NAME']), $content);
    }
    
    public static $allPlat;
    /**
     * 获取所有的平台
     * 
     */
    public static function getPlat()
    {
        if (static::$allPlat) {
            return static::$allPlat;
        }
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N00083%"')->select();   //获取平台数据WORK_NUM
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return static::$allPlat = $ret;
    }
    
    public static $creditGrade;
    
    /**
     * 信用评级
     * 
     */
    public static function getCreditGrade()
    {
        if (static::$creditGrade) return static::$creditGrade;
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N00137%"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return static::$creditGrade = $ret;
        //return static::$creditGrade = [
//            0 => 'A',
//            1 => 'B',
//            2 => 'C',
//            3 => 'D',
//            4 => 'E',
//        ];
    }
    
    public static $nagetiveOptions;
    
    /**
     * 负面信息项
     * 
     */
    public static function getNagetiveOptions()
    {
        if (static::$nagetiveOptions) return static::$nagetiveOptions;
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N00138%"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return static::$nagetiveOptions = $ret;
        //return static::$nagetiveOptions = [
//            0 => '工商异常',
//            1 => '行政处罚',
//            2 => '司法赔偿',
//            3 => '破产信息',
//            4 => '自然人事件',
//        ];
    }
    
    public static $btbtcCustomerSotre;
    /**
     * B2B2C客户管理，店铺获取
     * 根据配置文件的Available状态判定是否屏蔽某个店铺
     * 
     */
    public static function getBtbtcCustomerStore()
    {
        if (static::$btbtcCustomerSotre) return static::$btbtcCustomerSotre;
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL, USE_YN')->where('CD like "N00083%"')->select();
        $temp [-1] = 'ALL';
        foreach ($ret as $key => $value) {
            if ($value ['CD'] == 'N000831400') continue;
            if ($value ['USE_YN'] == 'N') continue;
            $temp [$value ['CD']] = $value ['CD_VAL']; 
        }
        return static::$btbtcCustomerSotre = $temp;
    }
    
   

    /**
     * 是否有负面信息
     * 
     */
    public static function isHaveNagetive()
    {
        return [
            1 => '有',
            0 => '无',
        ];
    }
    
    /**
     * 自然日工作日
     * 
     */
    public static function workday()
    {
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N001400%"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return $ret;
    }
    
    /**
     * 日志写入
     * 
     */
    public function insertLog($ORD_ID = '',$ORD_STAT_CD = '',$content = '')
    {
        $log = A('Log');
        if ($log->index($ORD_ID, $ORD_STAT_CD, $content)) {
            return true;
        }
        return $log->errorMsg;
    }
    
    /**
     * 合同状态
     * 
     */
    public static function contractState()
    {
        return [
            1 => '有效合同',
            2 => '合同已到期',
            3 => '合同作废',
        ];
    }
    
    /**
     * 合同类型
     * 
     */
    public static function contractType()
    {
        return [
            0 => '年度合同',
            1 => '长期合同',
        ];
    }
    
    /**
     * 管理员、联系人
     * 
     */
    public static function warehouseContacts()
    {

        //return [
        //    0 => '张三',
        //    1 => '李四',
        //    2 => '王五'
        //];
        $model = M('_admin', 'bbm_');
        $ret = $model->field('M_ID, M_NAME')->select();
        $ret = array_column($ret, 'M_NAME', 'M_ID');

        return [
            0 => '张三',
            1 => '李四',
            2 => '王五'
        ];
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N00142%"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return $ret;
    }
    
    /**
     * 天数
     * 
     */
    public static function getPayDays()
    {
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "N00142%"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return $ret;
    }
    
    /**
     * 根据传递的cd开头值获取数据字典表配置的参数值
     * @param $cd_prefix
     * @return Array
     */
    public static function getCd($cd_prefix)
    {
        $model = M('_ms_cmn_cd', 'tb_');
        $ret = $model->field('CD, CD_VAL')->where('CD like "'. $cd_prefix .'%" and USE_YN = "Y"')->select();
        $ret = array_column($ret, 'CD_VAL', 'CD');
        return $ret;
    }
    
    public function setRequestData($requestData)
    {
        $this->_requestData = $requestData;
    }
    
    public function setResponseData($responseData)
    {
        $this->_responseData = $responseData;
    }
    
    public function getRequestData()
    {
        return $this->_requestData;
    }
    
    public function getResponseData()
    {
        return $this->_responseData;
    }
    
    public static $gudsOpt;
    public static function getGudsOpt()
    {
        if (static::$gudsOpt) return static::$gudsOpt;
        $model = M('ms_opt', 'tb_');
        $ret = $model->cache(true, 300)
            ->join('left join tb_ms_opt_val on tb_ms_opt_val.OPT_ID = tb_ms_opt.OPT_ID')
            ->field('tb_ms_opt.OPT_ID, tb_ms_opt_val.OPT_VAL_ID,tb_ms_opt.OPT_CNS_NM,tb_ms_opt_val.OPT_VAL_CNS_NM,tb_ms_opt.OPT_ID')
            ->select();
        foreach ($ret as $key => $value) {
            $tmp [$value['OPT_ID'] . ':' . $value ['OPT_VAL_ID']] = $value;
        }
        $tmp [''] = [
            'OPT_CNS_NM' => '',
            'OPT_VAL_CNS_NM' => '标配'
        ];
        $tmp ['8000:800000'] = [
            'OPT_CNS_NM' => '',
            'OPT_VAL_CNS_NM' => '标配'
        ];
        static::$gudsOpt = $tmp;
        return $tmp;
    }
    
    public static function getRuleStorage()
    {
        return [
            0 => '虚拟入库(直接发给客户)',
            1 => '实际入库',
            2 => '默认规则(先进先出/效期敏感商品将以效期优先)',
            3 => '指定采购批次出库',
            5 => '虚拟出库'
        ];
    }
   
    
    /**
     * 记录接口日志
     */
    public function _catchMe()
    {
        $filePath = '/opt/logs/logstash/';
        $fileName = 'logstash_' . date('Ymd') . '_erp_json.log';
        $a = parse_url($_SERVER["REQUEST_URI"]);
        parse_str($a["query"], $s);
        $a = $s['a'];
        $m1 =  $s['m'];
        $m = M('');
        //获取操作日志
        $res = $m->query("SELECT CONCAT(bbm_node.TITLE,bbm_node.NAME) as opt from bbm_node where lower(bbm_node.CTL)='$m1' AND lower(bbm_node.ACT)='$a' ");
        $action = $s ['a'];
        $tlog = D('TbMsUserOperationLog');
        $data ['uId']           = create_guid();
        $data ['noteType']      = 'N001940200';
        $data ['source']        = 'N001950500';
        $data ['ip']            = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
        $data ['space']         = null;
        $data ['cTime']         = date('Y-m-d H:i:s');
        $data ['cTimeStamp']    = time();
        $data ['action']        = $s ['a'];
        $data ['model']         = $s ['m'];
        $data ['msg']           = json_encode([
            'model' => MODULE_NAME,
            'msg'   => [
                'GET' => $_GET,
                'POST'=> $_POST,
                'action' => $s ['m'],
                'operation' => $res[0]['opt'],
                'uri' => $_SERVER["REQUEST_URI"],
                'request_data' => $this->getRequestData(),
                'response_data' => $this->getResponseData(),
            ]
        ]);
        $data ['user'] = $_SESSION['m_loginname'];
        $tlog->add($data);
        $data ['id'] = $tlog->getLastInsID();
        $data ['msg'] = json_decode($data['msg']);
        $txt = json_encode($data);
        $file = $filePath.$fileName;
        fclose(fopen($file, 'a+'));
        $_fo = fopen($file, 'rb');
        fclose($_fo);
        file_put_contents($file, $txt . "\n", FILE_APPEND);
    }
}