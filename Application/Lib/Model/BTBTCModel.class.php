<?php

class BTBTCModel extends CommonModel
{
    protected $trueTableName = 'tb_ms_receiver_cust';
    
    public $count;
    
    public static function state()
    {
        return [
            '' => 'ALL',
            'N000550100' => '待确认',
            'N000550200' => '确认中',
            'N000550300' => '待付款',
            'N000550400' => '待发货',
            'N000550500' => '待收货',
            'N000550600' => '已收货',
            'N000550700' => '已付尾款',
            'N000550800' => '交易成功',
            'N000550900' => '交易关闭',
            'N000551000' => '交易取消',
        ];
    }
    
    public static function plat()
    {
        return [
            '-1' => 'ALL',
            'N000830100' => 'B5C',
            'N000830200' => 'BHB',
            '-2' => 'YT',
            'N000830400' => 'Qoo10-krs',
            'N000830300' => 'Qoo10-sg',
            'N000830500' => 'Qoo10-jp',
            'N000830600' => 'Wish',
            'N000830700' => 'Lazada-MY',
            'N000830800' => 'Lazada-ID',
            'N000830900' => 'Lazada-TH',
            'N000831000' => 'Lazada-PH',
            'N000831100' => 'Lazada-SG',
            'N000831200' => 'Ebay'
        ];
    }


    
    /**
     * 获取参数
     * @return tel_num
     */
    public function sendRequest($tel_nums)
    {
        $tel_num = ['mobile'=>$tel_nums,'act'=>'enc'];   //接口要什么格式数据,参数就以什么格式传递
        $back_tel_num = curl_request("http://172.16.11.160/mobile/",$tel_num);
        return $back_tel_num;
    }
    
  

    public function searchModel($params)
    {
        if ($params ['CREATE_TIME_starttime'] and $params ['CREATE_TIME_endtime']) {
            $conditions ['CREATE_TIME'] = [['gt', $params ['CREATE_TIME_starttime'] . ' 00:00:00'], ['lt', $params ['CREATE_TIME_endtime']. ' 23:59:59']];
        }
        if ($params ['UPDATE_TIME_starttime'] and $params ['UPDATE_TIME_endtime']) {
            $conditions ['UPDATE_TIME'] = [['gt', $params ['UPDATE_TIME_starttime'] . ' 00:00:00'], ['lt', $params ['UPDATE_TIME_endtime']. ' 23:59:59']];
        }
        if ($params ['CREATE_TIME_starttime'] and !$params ['CREATE_TIME_endtime']) {
            $conditions ['CREATE_TIME'] = [['gt', $params ['CREATE_TIME_starttime'] . ' 00:00:00'], ['lt', $params ['CREATE_TIME_starttime'] . ' 23:59:59']];
        }
        if ($params ['CREATE_TIME_endtime']  and !$params ['CREATE_TIME_starttime']) {
            $conditions ['CREATE_TIME'] = [['gt', $params ['CREATE_TIME_endtime'] . ' 00:00:00'], ['lt', $params['CREATE_TIME_endtime']. ' 23:59:59']];
        }
        if ($params ['UPDATE_TIME_starttime'] and !$params ['UPDATE_TIME_endtime']) {
            $conditions ['UPDATE_TIME'] = [['gt', $params ['UPDATE_TIME_starttime'] . ' 00:00:00'], ['lt', $params ['UPDATE_TIME_starttime']. ' 23:59:59']];
        }
        if ($params ['UPDATE_TIME_endtime'] and !$params ['UPDATE_TIME_starttime']) {
            $conditions ['UPDATE_TIME'] = [['gt', $params ['UPDATE_TIME_endtime'] . ' 00:00:00'], ['lt', $params ['UPDATE_TIME_endtime'] . ' 23:59:59']];
        }
        if ($params ['time_type'] == '1' and $params ['keywords']) {
            //var_dump($params ['keywords']);
            if (strlen($this->sendRequest($params ['keywords']))==11) {
                 $params ['keywords'] = $this->sendRequest($params ['keywords']);
                 //var_dump($params);
            }
            //echo $params ['keywords'];
            $conditions ['REL_TEL_NUM'] = ['like', '%' . $params ['keywords'] . '%'];
        } else if($params ['time_type'] == '2' and $params ['keywords']) {
            $conditions ['RES_EMAIL'] = $params ['keywords'];
        } else if($params ['time_type'] == '3' and $params ['keywords']) {
            $conditions ['RES_NAME'] = $params ['keywords'];
        }
        if ($params ['CREATE_PLAT_CD'] and $params ['CREATE_PLAT_CD'] != '-1' and $params ['CREATE_PLAT_CD'] != '-2') {
            $plat_cds = explode(',', $params ['CREATE_PLAT_CD']);
            $conditions ['CREATE_PLAT_CD'] = ['in', $plat_cds];
        }
     
        
        return $conditions;
    }
}