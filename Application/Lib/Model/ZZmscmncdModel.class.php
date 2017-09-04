<?php

class ZZmscmncdModel extends CommonModel
{

    protected $trueTableName = "tb_ms_cmn_cd";


    // public function __construct($name='',$tablePrefix='',$connection='') {
    //     if($name==''){
    //         $name = 'ms_cmn_cd';
    //         $tablePrefix = 'tb_';
    //     }
    //     parent::__construct($name,$tablePrefix,$connection);
    // }

    /**
     *  gain all code
     *  ps: need cache
     *
     */
    public function indexCode()
    {
        $cache_name = 'db_tb_ms_cmn_cd_all';
        $cache_data = S($cache_name);
        if (!empty($cache_data)) {
            return $cache_data;
        }

        $arr = $this->field("*")
            ->where("")
            ->order(" CD ASC ")
            ->limit('0,100000')
            ->select();
        $count = count($arr);
        $list = array();
        for ($i = 0; $i < $count; $i++) {
            $list[$arr[$i]['CD']] = $arr[$i];
        }
        //set cache data
        S($cache_name, $list, 300);
        return $list;
    }

    /**
     *  find one key value
     *
     */
    public function getNameFromCode($code)
    {
        $ret = null;
        $alldatas = $this->indexCode();
        if (isset($alldatas[$code])) {
            $ret = $alldatas[$code]['CD_VAL'];
        }
        return $ret;
    }

    /**
     *  find list of data by name (CD_NM)
     *
     */
    public function getValueFromName($name)
    {
        $ret = array();
        $alldatas = $this->indexCode();
        foreach ($alldatas as $value) {
            if ($value['CD_NM'] == $name) {
                $ret[$value['CD']] = $value['CD_VAL'];
            }
        }
        return $ret;
    }

    public function getCdValues($type = '*')
    {
        $cdList = $this->indexCode();
        $cdData = [];
        foreach ($cdList as $key => $value)
        {
            if($type != $value['CD_NM'])
            {
                continue;
            }
            $cdData[$key] = ['CD'=>$key,'CD_VAL'=>$value['CD_VAL']];


        }
        return $cdData;
    }

}