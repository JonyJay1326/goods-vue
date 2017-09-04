<?php

/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/23
 * Time: 13:50
 */
class MultiCodeModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_multi_code';

    /**
     * 品牌相关分类
     */
    const BRAND_CATE_TYPE_VAL = 'N000960400';
    public $field = 'id,SLLR_ID,PAR_CODE,CODE,VAL,ALL_VAL,TYPE,LANGUAGE';

    public function getBrandCateLangData($where, $field)
    {
        $where['TYPE'] = self::BRAND_CATE_TYPE_VAL;
        $where['USED_YN'] = 'Y';
        if (empty($field)) {
            $field = $this->field;
        }
       return $this->field($field)->where($where)->select();
    }

    /**
     * 通过sllrId 和 code 获取商品分类不同语言数据
     * @param $sllrId
     * @param $Code
     */
    public function getBrandCateDataBySllrIdAndCode($sllrId, $Code, $field = '')
    {
        $where['SLLR_ID'] = $sllrId;
        $where['CODE'] = $Code;
        return $this->getBrandCateLangData($where,$field);
    }

}