<?php

/**
 * Created by PhpStorm.
 * User: b5m
 * Date: 17/1/16
 * Time: 14:39
 */
class StreamModel extends RelationModel
{
    protected $trueTableName = "tb_wms_stream";
    protected $_link = array(
        'Goods' => array(
            'mapping_type' => BELONGS_TO ,
            'class_name' => 'Goods',

        ),
        'Warehouses' => array(
            'mapping_type' => BELONGS_TO ,
            'class_name' => 'Warehouse',
        )
    );
}