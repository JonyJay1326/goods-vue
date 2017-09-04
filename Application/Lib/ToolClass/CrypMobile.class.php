<?php

class CrypMobile
{
    //加密服务器地址
    private static $_url = 'http://172.16.11.160/mobile/';
    private static $_req = 'POST';
    private static $_mobile = '';//处理后未加密前备份
    private static $_original = '';//最原始备份
    
    /**
     * 加密
     * 
     */
    public static function enCryp($mobile)
    {
        static::$_original = $mobile;
        $mobile = static::initMobile($mobile);
        $request_data = [
            'act'    => 'enc',
            'mobile' => $mobile,
            'userid' => 0
        ];
        $ret = curl_request(static::$_url, $request_data);
        return static::crypReturn($ret);
    }
    
    /**
     * 解密
     * 
     */
    public static function deCryp($mobile)
    {
        static::$_original = $mobile;
        //$mobile = static::initMobile($mobile);
        $request_data = [
            'act'    => 'dec',
            'mobile' => $mobile,
            'userid' => 0
        ];
        $ret = curl_request(static::$_url, $request_data);
        return static::crypReturn($ret);
    }
    
    /**
     * 统一返回结果值
     * 
     */
    public static function crypReturn($ret)
    {
        $msg = $data = $code = '';
        // 访问成功
        if (CURL_INFO == 200) {
            // 返回错误
            if ($ret and is_array($rs = json_decode($ret, true))) {  //
                $msg = $rs ['msg'];
                $code = $rs ['code'];
            } else {
                $code = CURL_INFO;
                $data = $ret;
                $msg  = 'success';
            }
        } else {
            $code = -1;
            $msg  = 'connection failed';
            $data = null;
        }
        return [
            'code' => $code,
            'data' => $data,
            'back_data' => static::$_mobile,
            'original' => static::$_original,
            'msg' => $msg
        ];
    }
    
    /**
     * 提取用户填入号码中的所有数字
     * 
     */
    public static function initMobile($mobile)
    {
        preg_match_all('/\d.*?/', trim($mobile), $matches);
        if ($matches[0]) {
            foreach ($matches[0] as $k => $v) {
                $str .= $v;
            }
        }
        if ($str and strlen($str) > 6) {
            static::$_mobile = $str;
            $mobile = $str;
        }
        return $mobile;
    }
    
    /**
     * 将加密后的数据转换为带*号数据
     * 例如023SLKJ4436可展示位023****4436
     */
    public static function transformation($mobile)
    {
        return preg_replace('/[A-Za-z]/', '*', $mobile);
    }
    
    /**
     * 解密
     * @param $enCrypData 加密过后的数据
     */
    public static function deCrypBtn($enCrypData)
    {
        $ret = static::deCryp($enCrypData);
        if ($ret ['code'] == 200) $ret = $ret ['data'];
        else $ret = $ret ['original'];    
$txt = <<<EOF
<button class='btn' onclick='$(this).parent().html("{$ret}")'>解密</button>   
EOF;
        return $txt;
    }
}