<?php

/**
 * b5cai类目模块
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/7/27
 * Time: 15:56
 */
class B5caiCateModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_cmn_cat';

    /**
     * 获取帮5采类目
     * @param array $where
     * @param string $filed
     * @return mixed
     */
    public function getB5caiCateById($where = array(), $filed = 'CAT_CD as catId,CAT_NM as catName,CAT_CNS_NM as catCnName,CAT_LEVEL as catLevel,CAT_SORT as catSort,PAR_CAT_CD as pId,CAT_NM_PATH as catPath')
    {
        $where['DISABLE_YN'] = 'Y';
        $result = $this->where($where)->getField($filed);
        return $result;
    }

    /**
     * 通过level获取b5cai公共分类
     * @param $pId
     * @return mixed
     */
    public function getB5caiCateList($levelId, $pId, $field = 'CAT_CD as catId,CAT_NM as catName,CAT_NM_PATH as catNamePath,CAT_LEVEL as catLevel')
    {
        $where['CAT_LEVEL'] = $levelId;
        if (!empty($pId)) {
            $where['PAR_CAT_CD'] = $pId;
        }
        $where['DISABLE_YN'] = 'Y';
        return $this->getB5caiCateById($where, $field);
    }
}