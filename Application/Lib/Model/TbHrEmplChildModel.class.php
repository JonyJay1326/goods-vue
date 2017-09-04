<?php 

class TbHrEmplChildModel extends BaseModel
{
	protected $trueTableName = 'tb_hr_empl_child';
    
	protected $_link = [
        'parent' => [
            'mapping_type' => HAS_ONE,
            'class_name' => 'TbHrEmplModel',
            'foreign_key' => 'ID',
            'relation_foreign_key' => 'EMPL_ID',
            'mapping_name' => 'empl_parent',
        ]
    ];

	/*protected $_validate = [
        ['V_STR1','require','可扩展字段1'],//默认情况下用正则进行验证
        ['V_STR2','require','可扩展字段2']//默认情况下用正则进行验证
    ];*/
   /* protected $_auto = [
        ['CREATE_TIME', 'getTime', Model:: MODEL_INSERT, 'callback'],
        ['UPDATE_TIME', 'getTime', Model::MODEL_BOTH, 'callback'],
        ['CREATE_USER_ID', 'getName', Model::MODEL_INSERT, 'callback'],
        ['UPDATE_USER_ID', 'getName', Model::MODEL_BOTH, 'callback']
    ];*/
}
?>