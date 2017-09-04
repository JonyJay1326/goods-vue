<?php
// namespace app\components;


class ClsStr{


    /**
     * 字符串截取
     *
     * @author gesion<gesion@163.com>
     * @param string $str 原始字符串
     * @param int    $len 截取长度（中文/全角符号默认为 2 个单位，英文/数字为 1。
     *                    例如：长度 12 表示 6 个中文或全角字符或 12 个英文或数字）
     * @param bool   $dot 是否加点（若字符串超过 $len 长度，则后面加 "..."）
     * @return string
     */
    public static function g_substr($str, $len = 12, $dot = true) {
        $i = 0;
        $l = 0;
        $c = 0;
        $a = array();
        while ($l < $len) {
            $t = substr($str, $i, 1);
            if (ord($t) >= 224) {
                $c = 3;
                $t = substr($str, $i, $c);
                $l += 2;
            } elseif (ord($t) >= 192) {
                $c = 2;
                $t = substr($str, $i, $c);
                $l += 2;
            } else {
                $c = 1;
                $l++;
            }
            // $t = substr($str, $i, $c);
            $i += $c;
            if ($l > $len) break;
            $a[] = $t;
        }
        $re = implode('', $a);
        if (substr($str, $i, 1) !== false) {
            array_pop($a);
            ($c == 1) and array_pop($a);
            $re = implode('', $a);
            $dot and $re .= '...';
        }
        return $re;
    }

    /**
     * 截取UTF-8编码下字符串的函数
     *
     * @param   string      $str        被截取的字符串
     * @param   int         $length     截取的长度
     * @param   bool        $append     是否附加省略号
     *
     * @return  string
     */
    public static function sub_str($str, $length = 0, $append = true, $by_charset='utf-8')
    {
        $str = trim($str);
        $strlength = strlen($str);

        if ($length == 0 || $length >= $strlength)
        {
            return $str;
        }
        elseif ($length < 0)
        {
            $length = $strlength + $length;
            if ($length < 0)
            {
                $length = $strlength;
            }
        }

        if (function_exists('mb_substr'))
        {
            $newstr = mb_substr($str, 0, $length, $by_charset);
        }
        elseif (function_exists('iconv_substr'))
        {
            $newstr = iconv_substr($str, 0, $length, $by_charset);
        }
        else
        {
            //$newstr = trim_right(substr($str, 0, $length));
            $newstr = substr($str, 0, $length);
        }

        if ($append && $str != $newstr)
        {
            $newstr .= '...';
        }

        return $newstr;
    }

    /**
     * 计算字符串的长度（汉字按照两个字符计算）
     *
     * @param   string      $str        字符串
     *
     * @return  int
     */
    public static function str_len($str)
    {
        $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));

        if ($length)
        {
            return strlen($str) - $length + intval($length / 3) * 2;
        }
        else
        {
            return strlen($str);
        }
    }

    /**
     * UTF8字符串进行单字分割返回数组(包含中文)
     *
     * @params  string  $tempaddtext
     *
     * @return  array   $arr_cont
     */
    public static function addtext($tempaddtext=''){
        $cind = 0;
        $arr_cont = array();
        for ($i = 0; $i < strlen($tempaddtext); $i++) {
            if (strlen(substr($tempaddtext, $cind, 1)) > 0) {
                if (ord(substr($tempaddtext, $cind, 1)) < 192) {
                    if (substr($tempaddtext, $cind, 1) != " ") {
                    array_push($arr_cont, substr($tempaddtext, $cind, 1));
                    }
                    $cind++;
                } elseif(ord(substr($tempaddtext, $cind, 1)) < 224) {
                    array_push($arr_cont, substr($tempaddtext, $cind, 2));
                    $cind+=2;
                } else {
                    array_push($arr_cont, substr($tempaddtext, $cind, 3));
                    $cind+=3;
                }
            }
        }
        return $arr_cont;
    }

    public static function fmtFirstEndText($text, $mid_str='***'){
        $ret = '';
        $ret = $text;
        $tmp = self::addtext($text);
        if(count($tmp)>1){
            $first_str = array_shift($tmp);
            $end_str = array_pop($tmp);
            $ret = $first_str.$mid_str.$end_str;
        }

        return $ret;
    }

    /**
     * check(中文)
     *
     * @params  string  $str
     *
     * @return  intval  $is_chinese
     */
    public static function is_all_chinese($str){
        $is_chinese = 0;
        if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$str)) {
            $is_chinese = 1;
        } else {
        }
        return $is_chinese;
    }

    /**
     * 格式化中文4个字8长度
     *
     * @params  string  $str
     *
     * @return  string  $ret
     */
    public static function format_str_eight($str){
        $ret = $str;
        $is_chinese = self::is_all_chinese($str);
        if($is_chinese){
            $ch_arr = self::addtext($str);
            $len = count($ch_arr);
            if($len==2){
                $ret = $ch_arr[0].'&nbsp;&nbsp;&nbsp;&nbsp;'.$ch_arr[1];
            }
            elseif($len==2){
                $ret = $ch_arr[0].'&nbsp;'.$ch_arr[1].'&nbsp;'.$ch_arr[2];
            }
        }

        return $ret;
    }

    public static function word2num($str){
        $arr = array('零' => '0', '一' => '1', '二' => '2', '三' => '3', '四' => '4',
                     '五' => '5', '六' => '6', '七' => '7', '八' => '8', '九' => '9',
                     '十' => '10', 
                     );

        return strtr($str, $arr);
    }

    /**
     * 加密函数
     * @param   string  $str    加密前的字符串
     * @param   string  $key    密钥
     * @return  string  加密后的字符串
     */
    public static function encrypt($str, $key = 'AUTH_KEY')
    {
        $coded = '';
        $keylength = strlen($key);

        for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength)
        {
            $coded .= substr($str, $i, $keylength) ^ $key;
        }

        return str_replace('=', '', base64_encode($coded));
    }

    /**
     * 解密函数
     * @param   string  $str    加密后的字符串
     * @param   string  $key    密钥
     * @return  string  加密前的字符串
     */
    public static function decrypt($str, $key = 'AUTH_KEY')
    {
        $coded = '';
        $keylength = strlen($key);
        $str = base64_decode($str);

        for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength)
        {
            $coded .= substr($str, $i, $keylength) ^ $key;
        }

        return $coded;
    }

    /**
     * 处理json标准化
     * @param   string  $jsonstring     标准化前的字符串
     * @return  string  $jsonstring     标准化后的字符串
     */
    public static function chkJsonStr($jsonstring){
        $jsonstring=preg_replace('/([\{,]\s*)([a-z0-9_]+)(\s*:)/is','$1"$2"$3',$jsonstring);
        $jsonstring=preg_replace_callback('/([:]\s*)\'([^\']*)\'(\s*[,\}])/is',function($matches){
                $tmp = $matches[1].'"'.addslashes($matches[2]).'"'.$matches[3];
                return $tmp;
            },$jsonstring);
        return $jsonstring;
    }

    /**
    * transform ' hello, world !' to array('hello', 'world')
    */
    public static function strsToArray($strs) {
        $result = array();
        $array = array();
        $strs = str_replace('，', ',', $strs);
        $strs = str_replace("\r\n", ',', $strs);
        $strs = str_replace("\n", ',', $strs);
        $strs = str_replace(' ', ',', $strs);
        $array = explode(',', $strs);
        foreach ($array as $key => $value) {
            if ('' != ($value = trim($value))) {
                $result[] = $value;
            }
        }
        return $result;
    }

    public static function keep_str_number($str){
        return trim(preg_replace('/[^\.\d]/is','',$str));
    }

}
