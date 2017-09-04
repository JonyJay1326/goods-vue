<?php 
/**
* 名片表模型
*/
class TbHrCardModel extends BaseModel
{
	protected $trueTableName = 'tb_hr_card';
	protected $_link = [
        'empl' => [
            'mapping_type' => HAS_ONE,
            'class_name' => 'TbHrEmplModel',
            'foreign_key' => 'ID',
            'relation_foreign_key' => 'EMPL_ID',
            'mapping_name' => 'empl',
        ]
     
    ];
	   protected $_auto = [
	        ['CREATE_TIME', 'getTime', Model:: MODEL_INSERT, 'callback'],
	        ['UPDATE_TIME', 'getTime', Model::MODEL_BOTH, 'callback'],
	        ['CREATE_USER_ID', 'getName', Model::MODEL_INSERT, 'callback'],
	        ['UPDATE_USER_ID', 'getName', Model::MODEL_BOTH, 'callback']
    ];

   protected $_validate = [
        /*['EMP_NM','require','请输入真名称'],//默认情况下用正则进行验证
        ['PER_PHONE','require','请输入联系方式'],//默认情况下用正则进行验证
        ['OFF_TEL','require','请输入分机号'],//默认情况下用正则进行验证
        ['JOB_TYPE_CD','require','请输入职位类别'],//默认情况下用正则进行验证
        ['PER_CART_ID','require','身份证号'],//默认情况下用正则进行验证
        ['SEX','require','请输入性别'],//默认情况下用正则进行验证
        ['PER_IS_SMOKING','require','请选择是否吸烟'],//默认情况下用正则进行验证
        ['PER_BIRTH_DATE','require','请填写出生日期'],//默认情况下用正则进行验证
        ['AGE','require','请输入年龄'],//默认情况下用正则进行验证
        ['PER_ADDRESS','require','请输入籍贯'],//默认情况下用正则进行验证
        ['PER_RESIDENT','require','请输入户籍'],//默认情况下用正则进行验证
        ['PER_IS_MARRIED','require','请输入婚姻状况'],//默认情况下用正则进行验证
        ['CHILD_NUM','require','请输入子女数'],//默认情况下用正则进行验证
        ['PER_POLITICAL','require','请选择政治面貌'],//默认情况下用正则进行验证
        ['HOUSEHOLD','require','请输入户口性质'],//默认情况下用正则进行验证
        ['PER_NATIONAL','require','请输入民族'],//默认情况下用正则进行验证
        ['FUND_ACCOUNT','require','请输入公积金账号'],//默认情况下用正则进行验证
        ['SC_EMAIL','require','请输入花名邮箱'],//默认情况下用正则进行验证
        ['EMAIL','require','请输入私人邮箱'],//默认情况下用正则进行验证
        ['WE_CHAT','require','请输入微信'],//默认情况下用正则进行验证
        ['QQ_ACCOUNT','require','请输入QQ账号'],//默认情况下用正则进行验证
        ['DETAIL','require','请输入户籍地址'],//默认情况下用正则进行验证
        ['DETAIL_LIVING','require','请输入现居住地址'],//默认情况下用正则进行验证
        ['FIRST_LAN','require','请输入语言能力'],//默认情况下用正则进行验证
        ['FIRST_LAN_LEVEL','require','请输入语言能力程度'],//默认情况下用正则进行验证
        ['SECOND_LAN','require','请输入第二语言能力'],//默认情况下用正则进行验证
        ['SECOND_LAN_LEVEL','require','请输入第二语言能力程度'],//默认情况下用正则进行验证
        ['HOBBY_SPA','require','请输入爱好及特长'],//默认情况下用正则进行验证

        ['PER_CARD_PIC','require','请上传身份证正反面照片'],//默认情况下用正则进行验证
        ['RESUME','require','请上传简历'],*///默认情况下用正则进行验证


    ];

}



 ?>