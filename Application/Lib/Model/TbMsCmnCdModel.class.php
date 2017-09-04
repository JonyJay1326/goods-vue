<?php

/**
 * User: yuanshixiao
 * Date: 2017/6/20
 * Time: 14:18
 */
class TbMsCmnCdModel extends Model
{
    private static $_instance;
    protected $trueTableName                        = 'tb_ms_cmn_cd';
    public static $purchase_order_status_cd_pre     = 'N00132';
    public static $warehouse_cd_pre                 = 'N00068';
    public static $purchase_team_cd_pre             = 'N00129';
    public static $sell_team_cd_pre                 = 'N00128';
    public static $sell_mode_cd_pre                 = 'N00147';
    public static $warehouse_difference_cd_pre      = 'N00146'; //出入库差异
    public static $tax_rate_cd_pre                  = 'N00134'; //采购税率
    public static $currency_cd_pre                  = 'N00059'; //币种
    public static $ship_credential_type_cd_pre      = 'N00148'; //发货凭证类型
    public static $payment_days_cd_pre              = 'N00142'; //付款天数
    public static $payment_percent_cd_pre           = 'N00141'; //付款比例
    public static $payment_node_cd_pre              = 'N00139'; //付款节点
    public static $payment_dif_reason_cd_pre        = 'N00154'; //应付差额原因
    public static $our_company_cd_pre               = 'N00124'; //我方公司

    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    /**
     * @param string $cd_pre 码表CD前六位
     * @return mixed
     * 根据数据字典CD前六位获取该类型数据字典
     */
    public function getCd($cd_pre = '') {
        if($cd_pre && strlen($cd_pre) == 6) {
            return $this->field('CD,CD_VAL,ETC')->order('SORT_NO ASC')->where(['CD'=>['like',$cd_pre.'%']])->select();
        }else {
            $this->error = '参数错误';
            return false;
        }
    }

    /**
     * @param string $cd_pre 码表CD前六位
     * @return mixed
     * 根据数据字典CD前六位获取该类型数据字典
     */
    public function getCdY($cd_pre = '') {
        if($cd_pre && strlen($cd_pre) == 6) {
            return $this->field('CD,CD_VAL,ETC')->order('SORT_NO ASC')->where(['CD'=>['like',$cd_pre.'%'],['USE_YN'=>'Y']])->select();
        }else {
            $this->error = '参数错误';
            return false;
        }
    }

    /**
     * @param string $cd_pre
     * @return bool
     * 根据数据字典CD前六位获取该类型数据字典，并以CD字段作为键名,只能两个字段，不然返回数据格式会变
     */
    public function getCdKey($cd_pre = '') {
        if($cd_pre && strlen($cd_pre) == 6) {
            return $this->order('SORT_NO ASC')->order('SORT_NO ASC')->where(['CD'=>['like',$cd_pre.'%']])->getField('CD,CD_VAL',true);
        }else {
            $this->error = '参数错误';
            return false;
        }
    }

    /**
     * @return mixed
     * 获取采购订单状态
     */
    public function getPurchaseOrderStatus() {
        return $this->getCd(self::$purchase_order_status_cd_pre);
    }

    /**
     * @return mixed
     * 获取采购订单状态,CD作为键名
     */
    public function getPurchaseOrderStatusKey() {
        return $this->getCdKey(self::$purchase_order_status_cd_pre);
    }

    /**
     * @return mixed
     * 获取采购订单状态
     */
    public function warehouse() {
        return $this->getCd(self::$warehouse_cd_pre);
    }

    /**
     * @return mixed
     * 获取采购订单状态,CD作为键名
     */
    public function warehouseKey() {
        return $this->getCdKey(self::$warehouse_cd_pre);
    }

    /**
     * @return mixed
     * 获取采购团队
     */
    public function purchaseTeams() {
        return $this->getCd(self::$purchase_team_cd_pre);
    }

    /**
     * @return mixed
     * 获取采购团队,CD作为键名
     */
    public function purchaseTeamsKey() {
        return $this->getCdKey(self::$purchase_team_cd_pre);
    }

    public function warehouseDifference() {
        return $this->getCd(self::$warehouse_difference_cd_pre);
    }

    public function taxRate() {
        return $this->getCd(self::$tax_rate_cd_pre);
    }

    public function currency() {
        return $this->getCd(self::$currency_cd_pre);
    }
}