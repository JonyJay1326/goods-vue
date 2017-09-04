<?php
class ZZmsmerchantapplicationsModel extends CommonModel{
    protected $trueTableName = "tb_ms_merchant_applications";

    protected $_validate = array(
        array('name','require','{%NAME_MUST}',1),
        array('name','','{%ACCOUNT_EXISTS}',0,'unique'), // 在新增的时候验证 name 字段是否唯一
        array('name','2,30','{%NAME_LEN}',0,'length'),
        array('tel','require','{%TEL_MUST}',1),
        array('tel','/.{3,30}/','{%TEL_LEN}',0,'regex'),
    );

    protected $_auto = array (
        array('add_time','time',1,'function'),
    );

    public function searchModel($params)
    {
        $conditions = array();
        if(!empty($params['keywords'])){
            $keywords = $params['keywords'];
            $keywords = array('like','%'.trim($keywords).'%');
            $where['name'] = $keywords;
            $where['tel'] = $keywords;
            $where['_logic'] = 'or';
            $conditions['_complex'] = $where;
        }
        return $conditions;
    }

    //状态(0申请1已处理2其他)
    const Apply_ing = 0;
    const Apply_done = 1;
    const Apply_invalid = 2;
    public static function getStatusForApply($key = null)
    {
        $items = [
            self::Apply_ing => '申请', 
            self::Apply_done => '已处理',
            self::Apply_invalid => '无效',
        ];
        return DataMain::getItems($items, $key);
    }

    public static function htmStatusForApply($key = null){
        $ret = '';
        $htm_begin = '<span class="label" style="--style--">';
        $htm_end = '</span>';
        $htm_style = '';
        $tmp_val = self::getStatusForApply($key);
        if(is_string($tmp_val)){
            if($key==self::Apply_ing){
                $htm_style = 'color:#fff;background-color:#546e7a;';
            }
            elseif($key==self::Apply_done){
                $htm_style = 'color:#fff;background-color:#2ba384;';
            }if($key==self::Apply_invalid){
                $htm_style = 'color:#fff;background-color:gray;';
            }
            $ret = str_replace('--style--',$htm_style,$htm_begin).$tmp_val.$htm_end;
        }
        return $ret;
    }
}
