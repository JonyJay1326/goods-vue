<?php

/**
 * 商品审核模块
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/7/26
 * Time: 17:55
 */
class GudsChkModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_guds_chk';
    static $_CHK_STATUS_VALS = array(
        'chkwait' => 'N000420100',      //草稿
        'chking' => 'N000420200',   //审核中
        'chksucc' => 'N000420400',  //审核成功
        'chkfail' => 'N000420300',  //审核失败
    );

    /**
     * add data
     * @param $params
     * @return mixed
     */
    public function saveData($params)
    {
        $data['MAIN_GUDS_ID'] = $params['MAIN_GUDS_ID'];
        $data['GUDS_ID'] = $params['GUDS_ID'];
        $data['CHK_STATUS'] = $params['CHK_STATUS'];
        $data['CHK_CONTENT'] = $params['CHK_CONTENT'];
        $data['ADD_TIME'] = date('Y-m-d H:i:s');
        $data['UPDATE_TIME'] = $data['ADD_TIME'];
        return $this->add($data);
    }

    /**
     * update data
     * @param $params
     * @return mixed
     */
    public function updateData($params)
    {
        $where['MAIN_GUDS_ID'] = $params['MAIN_GUDS_ID'];
        $where['GUDS_ID'] = $params['GUDS_ID'];
        !empty($params['CHK_STATUS']) && $data['CHK_STATUS'] = $params['CHK_STATUS'];
        if (!empty($params['CHK_CONTENT']) || $params['CHK_STATUS'] == self::$_CHK_STATUS_VALS['chksucc']) {
            $data['CHK_CONTENT'] = $params['CHK_CONTENT'];
        }

        $data['UPDATE_TIME'] = date('Y-m-d H:i:s');
        return $this->where($where)->save($data);
    }

    /**
     * 获取指定商品审核信息
     * @param $MAIN_GUDS_ID
     * @return mixed
     */
    public function getChkData($MAIN_GUDS_ID, $GUDS_ID, $field = 'GUDS_ID as guds,MAIN_GUDS_ID as mainId,CHK_STATUS as chkStatus, CHK_CONTENT as content')
    {
        $where['MAIN_GUDS_ID'] = $MAIN_GUDS_ID;
        $where['GUDS_ID'] = $GUDS_ID;
        return $this->field($field)->where($where)->find();
    }

    /**
     * 通过mainId获取商品评论内容
     * @param $MAIN_GUDS_ID
     * @return mixed
     */
    public function getChkContent($MAIN_GUDS_ID, $GUDS_ID)
    {
        return $this->getChkData($MAIN_GUDS_ID, $GUDS_ID, 'CHK_CONTENT as content');
    }
}