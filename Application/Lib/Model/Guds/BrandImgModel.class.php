<?php

/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/16
 * Time: 16:14
 */
class  BrandImgModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_brnd_str_img';
    public $field = '
    `BRND_STR_IMG_CD` AS imgCd,
    `BRND_STR_IMG_STAT_CD` AS imgStatCd,
    `BRND_STR_IMG_ORGT_FILE_NM` AS orgName,
    `BRND_STR_IMG_SYS_FILE_NM` AS newName,
    `BRND_STR_IMG_CDN_ADDR` AS cdnAddr
    ';

    public function addBrandImgData($data)
    {
        $data['updated_time'] = date("Y-m-d H:i:s");
        return $this->add($data);
    }

    /**
     * 获取品牌图片通过SLLR_ID
     * @param $SLLR_ID
     */
    public function getBrandImgBySllrId($SLLR_ID)
    {
        $where['SLLR_ID'] = $SLLR_ID;
        return $this->where($where)->getField($this->field);
    }

    /**
     * 保存图片修改数据
     * @param $params
     * @return mixed
     */
    public function updateBrandImgData($params)
    {
        $where['SLLR_ID'] = $params['SLLR_ID'];
        $where['BRND_STR_IMG_CD'] = $params['BRND_STR_IMG_CD'];
        $where['BRND_STR_IMG_STAT_CD'] = $params['BRND_STR_IMG_STAT_CD'];
        !empty($params['BRND_STR_IMG_ORGT_FILE_NM']) && $data['BRND_STR_IMG_ORGT_FILE_NM'] = $params['BRND_STR_IMG_ORGT_FILE_NM'];
        !empty($params['BRND_STR_IMG_SYS_FILE_NM']) && $data['BRND_STR_IMG_SYS_FILE_NM'] = $params['BRND_STR_IMG_SYS_FILE_NM'];
        !empty($params['BRND_STR_IMG_CDN_ADDR']) && $data['BRND_STR_IMG_CDN_ADDR'] = $params['BRND_STR_IMG_CDN_ADDR'];
        $data['updated_time'] = date('Y-m-d H:i:s');
        return $this->where($where)->save($data);
    }

}