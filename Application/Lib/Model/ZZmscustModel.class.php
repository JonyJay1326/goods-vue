<?php
class ZZmscustModel extends CommonModel{
    protected $trueTableName = "tb_ms_cust";

    public static $cust_stat_cd_all = array(
        'N000400000',//'未填资料',
        'N000400100',//'待审核',   待验证
        'N000400200',//'已审核',   正常
        'N000400300',//'会员解除',
        'N000400400',//'冻结',
        'N000400500',//'注销',
        // 'N000400600',//'冻结',
        // 'N000400700',//'注销',
        );

    public static $stat_cd_b5ckehu = array(
        'N000400000',
        'N000400100',
        'N000400200',
        'N000400300',
        'N000400400',
        'N000400500',
        );

    public static $stat_cd_thirdkehu = array(
        'N000400100',
        'N000400200',
        'N000400400',
        'N000400500',
        );

    public static $def_recieve = array(
        0=>'否',
        1=>'是',
        );

    public static $def_recieve_types = array(
        1=>'短信',
        2=>'邮箱',
        );


    public function __construct($name='',$tablePrefix='',$connection='') {
        if($name==''){
            $name = 'ms_cust';
            $tablePrefix = 'tb_';
        }

        self::$def_recieve = array(
        0=>L('否'),
        1=>L('是'),
        );
        self::$def_recieve_types = array(
        1=>L('短信'),
        2=>L('邮箱'),
        );

        parent::__construct($name,$tablePrefix,$connection);
    }

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }

    //分页
    public function getDatas($selectby='*', $whereby='', $orderby='', $offset=0, $pageNum=6)
    {
        $arr=$this->field ( $selectby )
                ->where ( $whereby )
                ->order( $orderby );
        if($offset or $pageNum){
            $arr = $arr->limit($offset.','.$pageNum);
        }
        $arr=$arr->select();
        for($i=0;$i<count($arr);$i++){
            array_push($arr[$i],$offset+$i+1); //这是第几条数据

        }
        return $arr;
    }

    public function getDatasCount($whereby='')
    {
        $count=$this
                ->where ( $whereby )
                ->count();
        return $count;
    }

    public function indexStatCdOfThirdkehu(){
        $cmn_cd_obj = D("ZZmscmncd");
        $stat_cd_thirdkehu = self::$stat_cd_thirdkehu;
        $arr = array();
        foreach($stat_cd_thirdkehu as $v){
            $arr[$v] = $cmn_cd_obj->getNameFromCode($v);
        }
        return $arr;
    }

    public function indexStatCdOfB5ckehu(){
        $cmn_cd_obj = D("ZZmscmncd");
        $stat_cd_b5ckehu = self::$stat_cd_b5ckehu;
        $arr = array();
        foreach($stat_cd_b5ckehu as $v){
            $arr[$v] = $cmn_cd_obj->getNameFromCode($v);
        }
        return $arr;
    }

    /**
     *
     *
     */
    public function relateGetPlats(){
        
    }

    public function showListStatusName($list){
        foreach($list as $k=>$v){
            $list[$k]['show_status_name'] = D("ZZmscmncd")->getNameFromCode($v['CUST_STAT_CD']);
        }
        return $list;
    }

    public function getUserByParentPlatCd($by_parent_plat_cd=''){
        //model
        $ms_cust_obj = $this;

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

        $count = $ms_cust_obj->getDatasCount($whereby);

        $orderby = " SYS_REG_DTTM DESC ";
        $list = $ms_cust_obj->getDatas('*',$whereby,$orderby,null,null);
        $list = $ms_cust_obj->showListStatusName($list);

        $ret = array();
        $ret['count'] = $count;
        $ret['list'] = $list;
        unset($list);
        return $ret;
    }
}
