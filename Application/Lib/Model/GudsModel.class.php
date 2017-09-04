<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/8/2
 * Time: 13:35
 */
//namespace Application\Model;
//use Think\Model\RelationModel;
class GudsModel extends RelationModel {
    protected $trueTableName = "tb_ms_guds";
    protected $_link = array(
        'Opt'=>array(
            'mapping_type'      => HAS_MANY,
            'class_name'        => 'Opt',
        ),
        'Img'=>array(
            'mapping_type'      => HAS_ONE,
            'class_name'        => 'Img',
            'condition'         => 'GUDS_IMG_CD = "N000080200"',
        ),
    );
}