<?php

class TbMsGudsOptModel extends BaseModel
{
    protected $trueTableName = 'tb_ms_guds_opt';
    
    protected $_link = [
        'guds' => [
            'mapping_type' => BELONGS_TO,
            'class_name'   => 'Guds',
            'foreign_key'  => 'GUDS_ID',
        ]
    ];
}