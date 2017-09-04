<?php

/**
 * 商品图片模块
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/7/28
 * Time: 14:16
 */
class GudsImgModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_guds_img';

    /**
     * 获取商品图片数据
     * @param array $where
     * @return mixed
     */
    public function getGudsImgData($where = array())
    {
        $field = '  tb_ms_guds_img.SLLR_ID,
                    tb_ms_guds_img.MAIN_GUDS_ID,
                    tb_ms_guds_img.GUDS_ID,
                    tb_ms_guds_img.GUDS_IMG_CD,
                    tb_ms_guds_img.GUDS_IMG_ORGT_FILE_NM,
                    tb_ms_guds_img.GUDS_IMG_SYS_FILE_NM,
                    tb_ms_guds_img.GUDS_IMG_CDN_ADDR,
                    tb_ms_guds_img.LANGUAGE';

        return $this->field($field)
            ->where($where)
            ->select();
    }

    /**
     * 通过$MAIN_GUDS_ID||/&& $LANGUAGE 获取商品图片详情
     * @param $MAIN_GUDS_ID  主商品
     * @param $LANGUAGE   语言
     * @return mixed
     */
    public function getGudsByMgudsIdAndLang($MAIN_GUDS_ID, $LANGUAGE)
    {
        $where = empty($MAIN_GUDS_ID) ? array() : array('MAIN_GUDS_ID' => array('eq', $MAIN_GUDS_ID));
        $where = empty($where) ? array() : (empty($LANGUAGE) ? $where : array_merge($where, array('LANGUAGE' => array('eq', $LANGUAGE))));
        return $this->getGudsImgData($where);
    }

    /**
     * 通过$GUDS_ID||/&& $SLLR_ID 获取商品图片详情
     * @param $GUDS_ID  商品id
     * @param $SLLR_ID  品牌id
     * @return mixed
     */
    public function getGudsImgByGudsIdAndSllrId($GUDS_ID, $SLLR_ID)
    {
        $where = empty($GUDS_ID) ? array() : array('GUDS_ID' => array('eq', $GUDS_ID));
        $where = empty($where) ? array() : (empty($SLLR_ID) ? $where : array_merge($where, array('SLLR_ID' => array('eq', $SLLR_ID))));
        return $this->getGudsImgData($where);
    }

    /**
     * 通过$MAIN_GUDS_ID||/&& $SLLR_ID 获取商品图片详情
     * @param $MAIN_GUDS_ID  商品主id
     * @param $SLLR_ID  品牌id
     * @return mixed
     */
    public function getGudsImgByMainIdAndSllrId($MAIN_GUDS_ID, $SLLR_ID)
    {
        $where = empty($MAIN_GUDS_ID) ? array() : array('MAIN_GUDS_ID' => array('eq', $MAIN_GUDS_ID));
        $where = empty($where) ? array() : (empty($SLLR_ID) ? $where : array_merge($where, array('SLLR_ID' => array('eq', $SLLR_ID))));
        return $this->getGudsImgData($where);
    }

    /**
     * add data
     * @param $params
     * @return mixed
     */
    public function saveData($params)
    {
        $data['SLLR_ID'] = $params['SLLR_ID'];
        $data['MAIN_GUDS_ID'] = $params['MAIN_GUDS_ID'];
        $data['GUDS_ID'] = $params['GUDS_ID'];
        $data['GUDS_IMG_CD'] = $params['GUDS_IMG_CD'];
        $data['GUDS_IMG_ORGT_FILE_NM'] = $params['GUDS_IMG_ORGT_FILE_NM'];
        $data['GUDS_IMG_SYS_FILE_NM'] = $params['GUDS_IMG_SYS_FILE_NM'];
        $data['GUDS_IMG_CDN_ADDR'] = $params['GUDS_IMG_CDN_ADDR'];
        $data['SYS_REGR_ID'] = $params['SLLR_ID'];
        $data['SYS_CHGR_ID'] = $params['SLLR_ID'];
        $data['SYS_REG_DTTM'] = date('Y-m-d H:i:s');
        $data['SYS_CHG_DTTM'] = date('Y-m-d H:i:s');
        $data['LANGUAGE'] = $params['LANGUAGE'];
        $data['updated_time'] = $data['SYS_REG_DTTM'];
        return $this->add($data);
    }

    /**
     * update data
     * @param $params
     * @return mixed
     */
    public function updateData($params)
    {
        $where['GUDS_ID'] = $params['GUDS_ID'];
        $where['SLLR_ID'] = $params['SLLR_ID'];
        !empty($params['GUDS_IMG_CD']) && $data['GUDS_IMG_CD'] = $params['GUDS_IMG_CD'];
        !empty($params['GUDS_IMG_ORGT_FILE_NM']) && $data['GUDS_IMG_ORGT_FILE_NM'] = $params['GUDS_IMG_ORGT_FILE_NM'];
        !empty($params['GUDS_IMG_SYS_FILE_NM']) && $data['GUDS_IMG_SYS_FILE_NM'] = $params['GUDS_IMG_SYS_FILE_NM'];
        !empty($params['GUDS_IMG_CDN_ADDR']) && $data['GUDS_IMG_CDN_ADDR'] = $params['GUDS_IMG_CDN_ADDR'];
        $data['SYS_CHGR_ID'] = $params['SLLR_ID'];
        $data['SYS_CHG_DTTM'] = date('Y-m-d H:i:s');
        $data['updated_time'] = date('Y-m-d H:i:s');
        return $this->where($where)->save($data);
    }

}