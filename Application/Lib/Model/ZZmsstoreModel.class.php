<?php
class ZZmsstoreModel extends CommonModel{

    protected $trueTableName = "tb_ms_store";




    /**
     *  Fetch last store list
     *
     */
    public function findLastStore($num=100){
        $arr = array();
        $arr=$this->field ("*")
                ->where ("")
                ->order(" ID DESC ")
                ->limit(" 0,$num ")
                ->select();
        return $arr;
    }

    /**
     *  Fetch ont store data
     *
     */
    public function findOneStore($id){
        $row = $this->where(array("ID"=>$id))->find();
        return $row;
    }

    /**
     *  Fetch ont store name
     *
     */
    public function gainStoreName($id){
        $row = $this->findOneStore($id);
        $name = isset($row['STORE_NAME'])?$row['STORE_NAME']:null;
        return $name;
    }


}
