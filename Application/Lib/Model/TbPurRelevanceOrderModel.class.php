<?php

/**
 * User: yuanshixiao
 * Date: 2017/6/20
 * Time: 13:33
 */
class TbPurRelevanceOrderModel extends RelationModel
{

    protected $trueTableName    = 'tb_pur_relevance_order';
    protected $_link = [
        'TbPurOrderDetail' =>  [
            'mapping_type' => HAS_ONE,
            'foreign_key' => 'order_id',
            'mapping_key' => 'order_id',
            'mapping_name' => 'orders',
        ],
        'TbPurSellInformation' =>  [
            'mapping_type' => HAS_ONE,
            'foreign_key' => 'sell_id',
            'mapping_key' => 'sell_id',
            'mapping_name' => 'sell_information',
        ],
    ];

}