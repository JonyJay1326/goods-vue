<?php

/**
 * User: yuanshixiao
 * Date: 2017/5/15
 * Time: 16:07
 */
class TbMsOrdPackageModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_ord_package';
    //修改发货信息,暂时只用于自营订单
    public function savePackage($ord_id,$company_cd,$tracking_number) {
        $data['ORD_ID']             = $ord_id;
        $data['EXPE_CODE']          = $company_cd;
        $data['EXPE_COMPANY']       = L($company_cd);
        $data['TRACKING_NUMBER']    = $tracking_number;
        $data['SUBSCRIBE_TIME']    = date('Y-m-d H:i:s');
        if($package = $this->where(['ORD_ID'=>$ord_id])->find()) {
            return $this->data($data)->where(['ORD_ID'=>$ord_id])->save();
        }else {
            $data['SYS_REG_DTTM'] = $data['SYS_CHG_DTTM'] = $data['updated_time'] =date('Y-m-d H:i:s');
            $fields = 'tb_ms_ord_guds_opt.GUDS_OPT_ID as b5cSkuId,tb_ms_ord_guds_opt.SLLR_ID as brndId,tb_ms_ord_guds_opt.GUDS_ID as goodsId, tb_ms_ord_guds_opt.ORD_GUDS_QTY as goodsNum,tb_ms_guds.GUDS_NM as gudsCnsNm';
            $guds   = M('ms_ord_guds_opt', 'tb_')
                ->join('left join tb_ms_guds on tb_ms_ord_guds_opt.GUDS_ID = tb_ms_guds.GUDS_ID')
                ->field($fields)
                ->where(['tb_ms_ord_guds_opt.ORD_ID'=>$ord_id])
                ->select();
            $data['GOODS_LIST'] = json_encode($guds);
            return $this->data($data)->add();
        }
    }
}