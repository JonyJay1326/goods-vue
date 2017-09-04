<?php

/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/16
 * Time: 16:05
 */
class  BrandSllrModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_sllr';
    public $field = '
    `SLLR_ID` AS brandId,
    `BZOP_CONM` AS companyName,
    `CRN` AS crn,
    `BZOP_REPR_NM` AS bzNm,
    `COMM_RTL_ANC_NO` AS commRtlNo,
    `BZOP_REP_TEL_NO` AS bzopTelNo,
    `CALP_DPST_BANK_CD` AS calpDpstBankCd,
    `CALP_DPSR_NM` AS calpDpsrNm,
    `CALP_ACNT_NO` AS calpAcntNo,
    `BZOP_DIV_CD` AS bzopDivCd,
    `CRP_REG_NO` AS cRegNo,
    `BZTP_NM` AS bztNm,
    `ITEM_NM` AS itNm';

    /**
     * 判断SLLR_ID 是否存在
     * @param $SLLR_ID
     * @return mixed
     */
    public function getSllrIdIsExist($SLLR_ID)
    {
        $where['SLLR_ID'] = $SLLR_ID;
        return $this->where($where)->count();
    }

    /**
     * 判断SLLR_ID对应的地址是否存在是否存在
     * @param $SLLR_ID
     * @return mixed
     */
    public function getCompanyNameIsExist($COMPANY_NAME)
    {
        $where['BZOP_CONM'] = $COMPANY_NAME;
        return $this->where($where)->count();
    }

    /**
     * 添加品牌管理员
     * @param $data
     * @return mixed
     */
    public function addSllrData($data)
    {
        $data['SYS_REGR_ID'] = $data['SLLR_ID'];
        $data['SYS_REG_DTTM'] = date("Y-m-d H:i:s");
        $data['SYS_CHGR_ID'] = $data['SLLR_ID'];
        $data['SYS_CHG_DTTM'] = date("Y-m-d H:i:s");
        $data['updated_time'] = date("Y-m-d H:i:s");
        return $this->add($data);
    }

    /**
     * 获取品牌管理者信息
     * @param $sllrId
     * @return mixed
     */
    public function getSllrIdDataBySllrId($sllrId)
    {
        $where['SLLR_ID'] = $sllrId;
        return $this->field($this->field)->where($where)->find();
    }

    /**
     * 保存修改数据
     * @param $params
     * @return mixed
     */
    public function updataSllrData($params)
    {
        $where['SLLR_ID'] = $params['SLLR_ID'];
        !empty($params['BZOP_CONM']) && $data['BZOP_CONM'] = $params['BZOP_CONM'];
        !empty($params['CRN']) && $data['CRN'] = $params['CRN'];
        !empty($params['BZOP_REPR_NM']) && $data['BZOP_REPR_NM'] = $params['BZOP_REPR_NM'];
        !empty($params['COMM_RTL_YN']) && $data['COMM_RTL_YN'] = $params['COMM_RTL_YN'];
        !empty($params['COMM_RTL_ANC_NO']) && $data['COMM_RTL_ANC_NO'] = $params['COMM_RTL_ANC_NO'];
        !empty($params['BZOP_REP_TEL_NO']) && $data['BZOP_REP_TEL_NO'] = $params['BZOP_REP_TEL_NO'];
        !empty($params['DLV_MODE_CD']) && $data['DLV_MODE_CD'] = $params['DLV_MODE_CD'];
        !empty($params['CALP_DPST_BANK_CD']) && $data['CALP_DPST_BANK_CD'] = $params['CALP_DPST_BANK_CD'];
        !empty($params['CALP_DPSR_NM']) && $data['CALP_DPSR_NM'] = $params['CALP_DPSR_NM'];
        !empty($params['CALP_ACNT_NO']) && $data['CALP_ACNT_NO'] = $params['CALP_ACNT_NO'];
        !empty($params['BZOP_DIV_CD']) && $data['BZOP_DIV_CD'] = $params['BZOP_DIV_CD'];
        !empty($params['CRP_REG_NO']) && $data['CRP_REG_NO'] = $params['CRP_REG_NO'];
        !empty($params['BZTP_NM']) && $data['BZTP_NM'] = $params['BZTP_NM'];
        !empty($params['ITEM_NM']) && $data['ITEM_NM'] = $params['ITEM_NM'];
        $data['SYS_CHG_DTTM'] = date('Y-m-d H:i:s');
        $data['updated_time'] = date('Y-m-d H:i:s');
        return $this->where($where)->save($data);
    }

}