<?php
class ZZmscmncatModel extends CommonModel{

    protected $trueTableName = "tb_ms_cmn_cat";




    /**
     *  list by level
     *
     */
    public function gainListByLevel($level=1){
        $arr = array();
        $arr=$this->field ("*")
                ->where(array('CAT_LEVEL'=>array('eq',$level)))
                ->order(" CAT_CD ASC ")
                ->select();
        return $arr;
    }





}