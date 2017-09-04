<?php

/**
 * User: yangsu
 * Date: 17/5/18
 * Time: 10:40
 */
class StockModel extends Model
{
    public static $location = 0;
    public static $inventory_status = [
        '0' =>  '全部',
        '1' =>  '紧缺',
        '2' =>  '正常',
        '3' =>  '充足',
        '4' =>  '滞销',
        '5' =>  '滞销(待)',
    ];

    public static function get_show_warehouse()
    {
        $Warehouse = M('warehouse', 'tb_wms_');
        $where['is_show'] = 1;
        self::$location == 1 ? $where['location_switch'] = 1 : ''; //货位
        return $Warehouse->where($where)->getField('CD,company_id,warehouse');
    }

    public static function get_all_warehouse($cache = false)
    {
        $Warehouse = M('warehouse', 'tb_wms_');
        return $Warehouse->cache($cache)->getField('CD,company_id,warehouse');
    }
//  渠道
    public static function get_all_channel()
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = SALE_CHANNEL;
        return $Cmn_cd->cache(300)->where($where)->getField('CD,CD_VAL,ETc');
    }

}