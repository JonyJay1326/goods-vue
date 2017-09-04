<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/2
 * Time: 13:35
 */

class OptModel extends RelationModel {
    protected $trueTableName = "tb_ms_guds_opt";
    protected $_link = array(
        'Guds'=>array(
            'mapping_type'      => BELONGS_TO,
            'class_name'        => 'Guds',
            'foreign_key'       =>'GUDS_ID',
        ),
        'Img'=>array(
            'mapping_type'      => HAS_ONE,
            'class_name'        => 'Img',
            'foreign_key'       =>'GUDS_ID',
        ),
    );
}