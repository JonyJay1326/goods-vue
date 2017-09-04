<?php
/**
 * Created by PhpStorm.
 * User: afanti
 * Date: 2017/8/3
 * Time: 11:18
 */
class OptionValueModel extends RelationModel{
    protected $trueTableName = "tb_ms_opt_val";


    public function getValueById(){

    }

    /**
     * 按照指定的SKU属性名的ID，读取关联的所有属性值列表
     * @param $condition
     */
    public function getValuesByNameId($condition){

    }

    /**
     * 创建多种语言版本的SKU属性值，目前支持 韩语和中文
     * @param string $optNameId SKU属性名对应的CODE编码，或者叫做ID
     * @param array $values 多语言的SKU属性值，数组，每种语言一个元素。
     * @return array | bool
     */
    public function createOptionValues($optNameId, $values){
        if (empty($optNameId) || empty($values)){
            return false;
        }

        $data = "";
        $lastValues = array();
        $optMaxId = $this->getMaxValueId($optNameId);
        foreach ($values as $key => $item){
            $optValId = $optMaxId + $key + 1;
            $data .= "({$optNameId},{$optValId},'{$item['KR']}','{$item['CN']}','Y','{$item['EN']}','{$item['JP']}'),";
            $lastValues[$optValId] = $item;//构建新的数据，替换原来的数字索引为 valueId。
        }

        $data = trim($data, ',');
        $sql = "INSERT INTO tb_ms_opt_val 
                (OPT_ID,OPT_VAL_ID,OPT_VAL_NM,OPT_VAL_CNS_NM,OPT_VAL_USE_YN,OPT_VAL_ENG_NM,OPT_VAL_JPA_NM)
                VALUES {$data};";

        $res = $this->execute($sql);
        return array($res, $lastValues);
    }

    public function deleteValuesByNameId(){

    }

    public function deleteValueById($options = array())
    {
        
    }



    /**
     * 找出指定属性名所包含的所有属性值的 最大ID，生成新的ID是使用。
     * @param $optNameCode
     * @return mixed
     */
    public function getMaxValueId($optNameCode){
        return $this->where(" OPT_ID = '{$optNameCode}' ")->getField("MAX(OPT_VAL_ID)");
    }

    /**
     * 生成新的值ID编码。
     * @param $maxId
     * @return mixed
     */
    public function getNewValueId($maxId)
    {
        return $maxId + 1;
    }
    
}