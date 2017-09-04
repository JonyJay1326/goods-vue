<?php
class ZZmscustaddrModel extends CommonModel{

    protected $trueTableName = "tb_ms_cust_addr";




    /**
     *  Fetch user addr list
     *
     */
    public function gainCustList($cust_id,$max=100){
        $arr = array();
        $arr=$this->field ("*")
                ->where (array('CUST_ID'=>$cust_id))
                ->order(" ADDR_ID DESC ")
                ->limit(" 0,$max ")
                ->select();
        return $arr;
    }

}
