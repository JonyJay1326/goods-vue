<?php
/**
 * 字典模型类，查询各类字典码数据
 * User: afanti
 * Date: 2017/7/25
 * Time: 14:21
 */

class DirectoryModel extends Model
{
    /**
     * @var string 数据字典表
     */
    protected $trueTableName = "tb_ms_cmn_cd";

    const ORIGIN_NAME = '원산지코드'; //产地
    const ORIGIN_PREFIX = 'N00041';

    /**
     * var 币种
     */
    const CURRENCY_NAME = '기준환율종류코드';
    const CURRENCY_PREFIX = 'N00059';

    /**
     * var 仓库
     */
    const WAREHOUSE_NAME = 'DELIVERY_WAREHOUSE';
    const WAREHOUSE_PREFIX = 'N00068';

    /**
     * var 采购订单状态
     */
    const PURCHASE_ORDER_STATUS_NAME = '采购订单状态';
    const PURCHASE_ORDER_STATUS_PREFIX = 'N00132';

    /**
     *  var 商品单位
     */
    const GUDS_VALUATION_UNIT_NAME = 'VALUATION_UNIT';
    const GUDS_VALUATION_UNIT_PREFIX = 'N00069';


    /**
     *  var 销售渠道
     */
    const GUDS_SALE_CHANNEL_NAME = 'SALE CHANNEL';
    const GUDS_SALE_CHANNEL_PREFIX = 'N00083';

    /**
     *  var 品牌国家
     */
    const GUDS_BRAND_COUNTRY_NAME = '원산지코드';
    const GUDS_BRAND_COUNTRY_PREFIX = 'N00041';

    /**
     *  var 授权方式
     */
    const GUDS_AUTH_TYPE_NAME = '授权方式';
    const GUDS_AUTH_TYPE_PREFIX = 'N00074';

    /**
     *  var 银行账号
     */
    const GUDS_BANK_ACOUNT_NAME = '은행코드';
    const GUDS_BANK_ACOUNT_PREFIX = 'N00003';

    /**
     *  var 公司类型
     */
    const GUDS_COMPANY_TYPE_NAME = '사업자구분코드';
    const GUDS_COMPANY_TYPE_PREFIX = 'N00005';

    /**
     *  var 品牌状态
     */
    const GUDS_BRAND_STATUS_NAME = '브랜드스토어상태코드';
    const GUDS_BRAND_STATUS_PREFIX = 'N00004';

    public static $purchase_team_cd_pre             = 'N00129';
    public static $sell_team_cd_pre                 = 'N00128';
    public static $sell_mode_cd_pre                 = 'N00147';
    protected static $warehouse_difference_cd_pre   = 'N00146'; //出入库差异
    protected static $tax_rate_cd_pre               = 'N00134'; //采购税率
    protected static $currency_cd_pre               = 'N00059'; //采购税率



    /**
     * @param string $cd_pre 码表CD前六位
     * @return mixed
     * 根据数据字典CD前六位获取该类型数据字典
     */


    /**
     * 根据字典码前缀读取字典数据。
     *
     * @param $prefix
     * @return bool
     */
    public function getDirectory($prefix)
    {
        if (!empty($prefix) && strlen($prefix) == 6) {
            return $this->order('SORT_NO ASC')
                ->where(['CD' => ['like', $prefix . '%']])
                ->getField('CD,CD_NM,CD_VAL,ETC', true);
        } else {
            $this->error = '字典码前缀参数错误';
            return false;
        }
    }

    /**
     * 根据字典码名称查询字典数据
     * @param $name
     * @return bool
     */
    public function getDirectoryByName($name)
    {
        if (!empty($name)) {
            return $this->order('SORT_NO ASC')->where("CD_NM = '{$name}' AND USE_YN = 'Y'")
                ->getField('CD,CD_NM,CD_VAL,ETC', true);
        } else {
            $this->error = '字典码名称参数错误';
            return false;
        }
    }

    /**
     * 获取采购订单状态
     * @return mixed
     */
    public function getPurchaseOrderStatus()
    {
        return $this->getDirectoryByName(SELF::PURCHASE_ORDER_STATUS_NAME);
    }

    /**
     * 获取采购订单状态
     * @return mixed
     */
    public function getWarehouse()
    {
        return $this->getDirectoryByName(self::WAREHOUSE_NAME);
    }

    /**
     * 获取币种字典数据
     * @return bool
     */
    public function getCurrency()
    {
        return $this->getDirectoryByName(self::CURRENCY_NAME);
    }

    /**
     * 获取产地信息
     * @return bool
     */
    public function getOrigin()
    {
        return $this->getDirectoryByName(self::ORIGIN_NAME);
    }

    /**
     * 获取商品单位
     * @param $key 数组key
     * @param $val 数组val
     * @return mixed
     */
    public function getGudsUnitVals($key = 'CD', $val = '')
    {
        $result = $this->getDirectoryByName(self::GUDS_VALUATION_UNIT_NAME);
        return $this->dealWithDateToKeyVal($result, $key = 'CD', $val);
    }

    /**
     * 获取销售渠道
     * @param string $key
     * @param string $val
     * @return array
     */
    public function getSaleChannel($key='CD',$val='')
    {
        $result =  $this->getDirectoryByName(self::GUDS_SALE_CHANNEL_NAME);
        return $this->dealWithDateToKeyVal($result,$key='CD',$val);
    }

    /**
     * 获取品牌国家
     * @param string $key
     * @param string $val
     * @return array
     */
    public function getBrandCountry($key='CD',$val='')
    {
        $result =  $this->getDirectoryByName(self::GUDS_BRAND_COUNTRY_NAME);
        return $this->dealWithDateToKeyVal($result,$key='CD',$val);
    }

    /**
     * 获取授权类型
     * @param string $key
     * @param string $val
     * @return array
     */
    public function getAuthType($key='CD',$val='')
    {
        $result =  $this->getDirectoryByName(self::GUDS_AUTH_TYPE_NAME);
        return $this->dealWithDateToKeyVal($result,$key='CD',$val);
    }

    /**
     * 获取银行账号类型
     * @param string $key
     * @param string $val
     * @return array
     */
    public function getBankAcount($key='CD',$val='')
    {
        $result =  $this->getDirectoryByName(self::GUDS_BANK_ACOUNT_NAME);
        return $this->dealWithDateToKeyVal($result,$key='CD',$val);
    }

    /**
     * 获取公司类型
     * @param string $key
     * @param string $val
     * @return array
     */
    public function getCompanyType($key='CD',$val='')
    {
        $result =  $this->getDirectoryByName(self::GUDS_COMPANY_TYPE_NAME);
        return $this->dealWithDateToKeyVal($result,$key='CD',$val);
    }

    /**
     * 获取品牌状态类型
     * @param string $key
     * @param string $val
     * @return array
     */
    public function getBrandStatus($key='CD',$val='')
    {
        $result =  $this->getDirectoryByName(self::GUDS_BRAND_STATUS_NAME);
        return $this->dealWithDateToKeyVal($result,$key='CD',$val);
    }

    /**
     * 处理字典数据为key-value格式
     * @param $data
     */
    public function dealWithDateToKeyVal($data, $key = 'CD', $valKey = '')
    {
        if(empty($valKey)){return $data;}
        $arr = array();
        if (empty($data)) {
            return array();
        }
        foreach ((array)$data as $val) {
            $arr[$val[$key]] =  $val[$valKey];
        }

        return $arr;
    }

}