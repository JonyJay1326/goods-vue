<?php

/**
 * User: yuanshixiao
 * Date: 2017/7/10
 * Time: 14:23
 */
class TbPurShipGoodsModel extends RelationModel
{
    protected $trueTableName = 'tb_pur_ship_goods';
    protected $_link = [
        'TbPurGoodsInformation' => [
            'mapping_type'  => BELONGS_TO,
            'foreign_key'   => 'information_id',
            'mapping_name'  => 'information',
        ],
        'warehouse' => [
            'class_name'    => 'TbMsCmnCd',
            'mapping_type'  => BELONGS_TO,
            'foreign_key'   => 'warehouse',
            'mapping_name'  => 'warehouse',
        ],
        'differenceReason' => [
            'class_name'    => 'TbMsCmnCd',
            'mapping_type'  => BELONGS_TO,
            'foreign_key'   => 'difference_reason',
            'mapping_name'  => 'difference_reason',
        ],
    ];
}