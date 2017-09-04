<?php
/**
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/8/16
 * Time: 16:15
 */
class BrandVideoModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_brnd_str_video';

    public function addBrandVideoData($data)
    {
        $data['updated_time'] = date("Y-m-d H:i:s");
        return $this->add($data);
    }
}