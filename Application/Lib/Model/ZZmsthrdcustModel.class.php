<?php
class ZZmsthrdcustModel extends CommonModel{
    protected $trueTableName = "tb_ms_thrd_cust";

    const Certified_No = 'N';
    const Certified_Yes = 'Y';
    public static function getStatusForCertified($key = null)
    {
        $items = [
            self::Certified_No => '未认证',
            self::Certified_Yes => '已认证',
        ];
        return DataMain::getItems($items, $key);
    }

    const Recieve_Msg = 1;
    const Recieve_Mail = 2;
    const Recieve_APP = 3;
    public static function getTypeForRecieve($key = null)
    {
        $items = [
            self::Recieve_Msg => '短信',
            self::Recieve_Mail => '邮箱',
            self::Recieve_APP => 'APP',
        ];
        return DataMain::getItems($items, $key);
    }

    const Push_No = 'N';
    const Push_Yes = 'Y';
    public static function getStatusForPush($key = null)
    {
        $items = [
            self::Push_No => '未订阅',
            self::Push_Yes => '已订阅',
        ];
        return DataMain::getItems($items, $key, true);
    }

    public function getThrdCustByParentPlatCd($by_parent_plat_cd=''){

        $where = array();
        $whereby = array();
        $orderby = "";

        $filterArr = array();
        $filterArr['by_parent_plat_cd']     = $by_parent_plat_cd;

        if($filterArr['by_parent_plat_cd']){
            $where['parent_plat_cd'] = array('IN', ZFun::arrToIdsArr(array($filterArr['by_parent_plat_cd'])));
        }

        //default where
        $whereby = array_merge($whereby,$where);

        $count = $this
                ->where ( $whereby )
                ->count();
        $orderby = " CREATE_TIME DESC ";
        $list = $this->field ( '*' )
                ->where ( $whereby )
                ->order( $orderby );
        $list = $list->select();

        $ret = array();
        $ret['count'] = $count;
        $ret['list'] = $list;
        unset($list);
        return $ret;
    }

    public function check_thrd_cust_data($kehu){
        $kehu['CUST_PWD'] = isset($kehu['CUST_PWD'])?$kehu['CUST_PWD']:null;
        $kehu['CUST_EML'] = isset($kehu['CUST_EML'])?$kehu['CUST_EML']:null;
        $kehu['recieve_types'] = isset($kehu['recieve_types'])?$kehu['recieve_types']:null;
        $kehu['recieve_types']  = is_array($kehu['recieve_types'])?$kehu['recieve_types']:array();
        if(empty($kehu['CUST_PWD'])){
            unset($kehu['CUST_PWD']);
        }else{
            $kehu['CUST_PWD'] = strtoupper(md5($kehu['CUST_PWD']));
        }

        $kehu['receive_sms'] = 'N';
        $kehu['receive_email'] = 'N';
        $kehu['receive_push'] = 'N';
        if(in_array(1,$kehu['recieve_types'])){
            $kehu['receive_sms'] = 'Y';
        }
        if(in_array(2,$kehu['recieve_types'])){
            $kehu['receive_email'] = 'Y';
        }
        if(in_array(3,$kehu['recieve_types'])){
            $kehu['receive_push'] = 'Y';
        }
        $kehu['RECIEVE_TYPES']  = implode(',',$kehu['recieve_types']);
        unset($kehu['recieve_types']);

        return $kehu;
    }

    /**
     *  get user list for choosing msg
     *
     */
    public function find_msg_user($only_count=0, $firstRow=0, $listRows=0, $where=array(), $msg_id=null){
        $ret = array();
        $count = $list = null;

        $whereby = array();
        $orderby = "";
        $whereby['receive_push'] = 'Y';
        //default where
        $whereby = array_merge($whereby,$where);

        if($msg_id){

            $tmpQuery = D('ZZmsthrdcust')->field ( '*' )
                    ->where ( $whereby )
                    ->buildSql();
            $sql ="
                SELECT COUNT(*) as num FROM (
                    ".substr($tmpQuery,1,-1)."
                    UNION 
                    SELECT ".$this->trueTableName.".* 
                    FROM ".$this->trueTableName." 
                    LEFT JOIN ".D('ZZmsmessagesend')->getTableName()." ON (".D('ZZmsmessagesend')->getTableName().".CUST_ID=".$this->trueTableName.".CUST_ID COLLATE utf8_unicode_ci)
                    WHERE 1 AND ".D('ZZmsmessagesend')->getTableName().".msg_id=1
                ) aa
            ";
            $Model = new Model();
            $queryData = $Model->query($sql);
            $count = $queryData[0]['num'];

            if($only_count==0){
                $tmpQuery = D('ZZmsthrdcust')->field ( '*' )
                        ->where ( $whereby )
                        ->buildSql();
                $sql ="
                    ".substr($tmpQuery,1,-1)."
                    UNION 
                    SELECT ".$this->trueTableName.".* 
                    FROM ".$this->trueTableName." 
                    LEFT JOIN ".D('ZZmsmessagesend')->getTableName()." ON (".D('ZZmsmessagesend')->getTableName().".CUST_ID=".$this->trueTableName.".CUST_ID COLLATE utf8_unicode_ci)
                    WHERE 1 AND ".D('ZZmsmessagesend')->getTableName().".msg_id=1
                ";
                $orderby = " CREATE_TIME DESC ";
                $sql .= " ORDER BY ".$orderby;
                $sql .= " LIMIT ".$firstRow.",".$listRows." ";
                $Model = new Model();
                $list = $Model->query($sql);

            }

        }else{

            $count = D('ZZmsthrdcust')
                    ->where ( $whereby )
                    ->count();

            if($only_count==0){
                $orderby = " CREATE_TIME DESC ";
                $arr = D('ZZmsthrdcust')->field ( '*' )
                        ->where ( $whereby )
                        ->order( $orderby );
                $arr = $arr->limit($firstRow.','.$listRows);
                $list = $arr->select();
            }

        }

        $ret['count'] = $count;
        $ret['list'] = $list;
        return $ret;
    }


}
