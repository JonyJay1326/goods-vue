<?php
/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/22
 * Time: 17:35
 */
class  BrandStrRepCatModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_brnd_str_rep_cat';

    public function addBrandStrRepCatData($data)
    {
        $data['updated_time'] = date("Y-m-d H:i:s");
        return $this->add($data);
    }

    public function getBrandStrRepCatBySllrId($SLLR_ID)
    {
        $where['SLLR_ID']  = $SLLR_ID;
        return $this->field('SLLR_ID AS brandId,CAT_CD AS cateId')->where($where)->select();
    }
    public function delBrandStrRepCatBySllrId($SLLR_ID)
    {
        $where['SLLR_ID']  = $SLLR_ID;
        return $this->where($where)->delete();
    }
}