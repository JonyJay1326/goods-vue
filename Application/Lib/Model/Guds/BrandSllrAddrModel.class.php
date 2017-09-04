<?php

/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/16
 * Time: 16:13
 */
class  BrandSllrAddrModel extends RelationModel
{

    protected $trueTableName = 'tb_ms_sllr_addr';
    public $field = '
    `SLLR_ID` AS sllrId,
    `SLLR_ZPNO` AS sllrZpNo,
    `SLLR_ADDR` AS sllrAddr,
    `SLLR_DTL_ADDR` AS sllrDtlAddr';

    public function addSllrAddrData($data)
    {
        $data['SYS_REGR_ID'] = $data['SLLR_ID'];
        $data['SYS_REG_DTTM'] = date("Y-m-d H:i:s");
        $data['SYS_CHGR_ID'] = $data['SLLR_ID'];
        $data['SYS_CHG_DTTM'] = date("Y-m-d H:i:s");
        $data['updated_time'] = date("Y-m-d H:i:s");
        return $this->add($data);
    }

    /**获取品牌公司地址信息
     * @param $sllrId
     * @return mixed
     */
    public function getSllrAddrDataBySllrId($sllrId)
    {
        $where['SLLR_ID'] = $sllrId;
        return $this->field($this->field)->where($where)->find();
    }

    /**
     * 保存修改数据
     * @param $params
     * @return mixed
     */
    public function updateSllrAddrData($params)
    {
        $where['SLLR_ID'] = $params['SLLR_ID'];
        $where['SLLR_ADDR_DIV_CD'] = $params['SLLR_ADDR_DIV_CD'];
        !empty($params['SLLR_ZPNO']) && $data['SLLR_ZPNO'] = $params['SLLR_ZPNO'];
        !empty($params['SLLR_ADDR']) && $data['SLLR_ADDR'] = $params['SLLR_ADDR'];
        !empty($params['SLLR_DTL_ADDR']) && $data['SLLR_DTL_ADDR'] = $params['SLLR_DTL_ADDR'];
        $data['SYS_CHG_DTTM'] = date('Y-m-d H:i:s');
        $data['updated_time'] = date('Y-m-d H:i:s');
        return $this->where($where)->save($data);
    }
}