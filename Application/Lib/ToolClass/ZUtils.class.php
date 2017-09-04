<?php
// namespace app\components;
/**
 * 系统助手类
 * @edit          by huaxin 2015/01/20
 * @author        shuguang <5565907@qq.com>
 * @copyright     Copyright (c) 2007-2013 bagesoft. All rights reserved.
 * @link          http://www.bagecms.com
 * @package       BageCMS.Tools
 * @license       http://www.bagecms.com/license
 * @version       v3.1.0
 */

class ZUtils {

    /**
     * 友好显示var_dump
     */
    static public function dump( $var, $echo = true, $label = null, $strict = true ) {
        $label = ( $label === null ) ? '' : rtrim( $label ) . ' ';
        if ( ! $strict ) {
            if ( ini_get( 'html_errors' ) ) {
                $output = print_r( $var, true );
                $output = "<pre>" . $label . htmlspecialchars( $output, ENT_QUOTES ) . "</pre>";
            } else {
                $output = $label . print_r( $var, true );
            }
        } else {
            ob_start();
            var_dump( $var );
            $output = ob_get_clean();
            if ( ! extension_loaded( 'xdebug' ) ) {
                $output = preg_replace( "/\]\=\>\n(\s+)/m", "] => ", $output );
                $output = '<pre>' . $label . htmlspecialchars( $output, ENT_QUOTES ) . '</pre>';
            }
        }
        if ( $echo ) {
            echo $output;
            return null;
        } else
            return $output;
    }

    /**
     * 获取客户端IP地址
     */
    static public function getClientIP() {
        static $ip = NULL;
        if ( $ip !== NULL )
            return $ip;
        if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $arr = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $pos = array_search( 'unknown', $arr );
            if ( false !== $pos )
                unset( $arr[$pos] );
            $ip = trim( $arr[0] );
        } elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $ip = ( false !== ip2long( $ip ) ) ? $ip : '0.0.0.0';
        return $ip;
    }

    /**
     * 循环创建目录
     */
    static public function mkdir( $dir, $mode = 0777 ) {
        if ( is_dir( $dir ) || @mkdir( $dir, $mode ) )
            return true;
        if ( ! mk_dir( dirname( $dir ), $mode ) )
            return false;
        return @mkdir( $dir, $mode );
    }

    /**
     * 格式化单位
     */
    static public function byteFormat( $size, $dec = 2 ) {
        $a = array ( "B" , "KB" , "MB" , "GB" , "TB" , "PB" );
        $pos = 0;
        while ( $size >= 1024 ) {
            $size /= 1024;
            $pos ++;
        }
        return round( $size, $dec ) . " " . $a[$pos];
    }

    /**
     * 下拉框，单选按钮 自动选择
     *
     * @param $string 输入字符
     * @param $param  条件
     * @param $type   类型
     *            selected checked
     * @return string
     */
    static public function selected( $string, $param = 1, $type = 'select' ) {

        if ( is_array( $param ) ) {
            $true = in_array( $string, $param );
        }elseif ( $string == $param ) {
            $true = true;
        }
        if ( $true )
            $return = $type == 'select' ? 'selected="selected"' : 'checked="checked"';

        echo $return;
    }

    /**
     * 获得来源类型 post get
     *
     * @return unknown
     */
    static public function method() {
        return strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET' );
    }

    /**
     * 提示信息
     */
    static public function message( $action = 'success', $content = '', $redirect = '', $timeout = 3 ) {

        $url = $redirect;
        switch ( $action ) {
        case 'success': 
            $vars = array('titler'=>'操作完成', 'class'=>'success','status'=>'✔'); 
            break;
        case 'error': 
            $vars = array('titler'=>'操作未完成', 'class'=>'error','status'=>'✘'); 
            break;
        case 'errorBack': 
            $vars = array('titler'=>'操作未完成', 'class'=>'error','status'=>'✘'); 
            break;
        case 'redirect':  header( "Location:$url" ); break;
        case 'script':
            exit( '<script language="javascript">alert("' . $content . '");window.location=" ' . $url . '"</script>' );
            break;
        }
        if($action !='errorBack')
            $script = '<div class="go">系统自动跳转在 <span id="time">'.$timeout.'</span> 秒钟后，如果不想等待 > <a href="'.$redirect.'">点击这里跳转</a><script>function redirect(url) {window.location.href = url;}setTimeout("redirect(\''.$redirect.'\');",'.$timeout * 1000 .');</script>';
        else
            $script = '<a href="'.$url.'" >[点这里返回上一页]</a>';
        $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><title>'.$vars['titler'].'</title><style type="text/css">body { font-size: 15px; font-family: "Tahoma", "Microsoft Yahei" }.wrap { background: #F7FBFE; border: 1px solid #DEEDF6; width: 650px; padding: 50px; margin: 50px auto 0; border-radius: 5px }h1 { font-size: 25px }div { padding: 6px 0 }div:after { visibility: hidden; display: block; font-size: 0; content: " "; clear: both; height: 0; }a { text-decoration: none; }#status, #content { float: left; }#status { height: auto; line-height: 50px; margin-right: 30px; font-size: 25pt }#content { float: left; width: 550px; }.message { color: #333; line-height: 25px }#time { color: #F00 }.error { color: #F00 }.success{color:#060}.go { font-size: 12px; color: #666 }</style></head><body><div class="wrap"><div id="status" class="'.$vars['class'].'">'.$vars['status'].'</div><div id="content"><div class="message '.$vars['class'].'">'.$content.'</div>'.$script.'</p></div></div></div></body></html>';
        exit ( $body  );
    }

    static public function js_redirect($url=''){
        echo '<script type="text/javascript">location.href="'.$url.'";</script>';
        die();
    }

    static public function js_redirect_friendly($url=''){
        echo '<script type="text/javascript">location.href="'.$url.'";</script>';
    }

    /**
     * 查询字符生成
     */
    static public function buildCondition( array $getArray, array $keys = array() ) {
        if ( $getArray ) {
            foreach ( $getArray as $key => $value ) {
                if ( in_array( $key, $keys ) && $value ) {
                    $arr[$key] = CHtml::encode(strip_tags($value));
                }
            }
            return $arr;
        }
    }

    /**
     * base64_encode
     */
    static function b64encode( $string ) {
        $data = base64_encode( $string );
        $data = str_replace( array ( '+' , '/' , '=' ), array ( '-' , '_' , '' ), $data );
        return $data;
    }

    /**
     * base64_decode
     */
    static function b64decode( $string ) {
        $data = str_replace( array ( '-' , '_' ), array ( '+' , '/' ), $string );
        $mod4 = strlen( $data ) % 4;
        if ( $mod4 ) {
            $data .= substr( '====', $mod4 );
        }
        return base64_decode( $data );
    }

    /**
     * 验证邮箱
     */
    public static function email( $str ) {
        if ( empty( $str ) )
            return true;
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if ( strpos( $str, '@' ) !== false && strpos( $str, '.' ) !== false ) {
            if ( preg_match( $chars, $str ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * 验证手机号码
     */
    public static function mobile( $str ) {
        if ( empty( $str ) ) {
            return false;
        }

        return preg_match( '#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$|^1[0-9][0-9]\d{8}$#', $str );
    }
    
    /**
     * 验证固定电话
     */
    public static function tel( $str ) {
        if ( empty( $str ) ) {
            return true;
        }
        return preg_match( '/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/', trim( $str ) );

    }
    
    /**
     * 验证qq号码
     */
    public static function qq( $str ) {
        if ( empty( $str ) ) {
            return true;
        }

        return preg_match( '/^[1-9]\d{4,12}$/', trim( $str ) );
    }

    /**
     * 验证邮政编码
     */
    public static function zipCode( $str ) {
        if ( empty( $str ) ) {
            return true;
        }

        return preg_match( '/^[1-9]\d{5}$/', trim( $str ) );
    }
    
    /**
     * 验证ip
     */
    public static function ip( $str ) {
        if ( empty( $str ) )
            return true;

        if ( ! preg_match( '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str ) ) {
            return false;
        }

        $ip_array = explode( '.', $str );

        //真实的ip地址每个数字不能大于255（0-255）
        return ( $ip_array[0] <= 255 && $ip_array[1] <= 255 && $ip_array[2] <= 255 && $ip_array[3] <= 255 ) ? true : false;
    }

    /**
     * 验证身份证(中国)
     */
    public static function idCard( $str ) {
        $str = trim( $str );
        if ( empty( $str ) )
            return true;

        if ( preg_match( "/^([0-9]{15}|[0-9]{17}[0-9a-z])$/i", $str ) )
            return true;
        else
            return false;
    }

    /**
     * 验证网址
     */
    public static function url( $str ) {
        if ( empty( $str ) )
            return true;

        return preg_match( '#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str ) ? true : false;
    }

    /**
     * 根据ip获取地理位置
     * @param $ip
     * return :ip,beginip,endip,country,area
     */
    public static function getlocation( $ip = '' ) {
        $ip = new XIp();
        $ipArr = $ip->getlocation( $ip );
        return $ipArr;
    }

    /**
     * 中文转换为拼音
     */
    public static function pinyin( $str ) {
        $ip = new XPinyin();
        return $ip->output( $str );
    }

    /**
     * 拆分sql
     *
     * @param $sql
     */
    public static function splitsql( $sql ) {
         $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . Yii::app()->db->charset, $sql);
        $sql = str_replace("\r", "\n", $sql);
        $ret = array ();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $ret[$num] .= $query;
            }
            $num ++;
        }
        return ($ret);
    }

    /**
     * 字符截取
     *
     * @param $string
     * @param $length
     * @param $dot
     */
    public static function cutstr( $string, $length, $dot = '...', $charset = 'utf-8' ) {
        if ( strlen( $string ) <= $length )
            return $string;

        $pre = chr( 1 );
        $end = chr( 1 );
        $string = str_replace( array ( '&amp;' , '&quot;' , '&lt;' , '&gt;' ), array ( $pre . '&' . $end , $pre . '"' . $end , $pre . '<' . $end , $pre . '>' . $end ), $string );

        $strcut = '';
        if ( strtolower( $charset ) == 'utf-8' ) {

            $n = $tn = $noc = 0;
            while ( $n < strlen( $string ) ) {

                $t = ord( $string[$n] );
                if ( $t == 9 || $t == 10 || ( 32 <= $t && $t <= 126 ) ) {
                    $tn = 1;
                    $n ++;
                    $noc ++;
                } elseif ( 194 <= $t && $t <= 223 ) {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                } elseif ( 224 <= $t && $t <= 239 ) {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                } elseif ( 240 <= $t && $t <= 247 ) {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                } elseif ( 248 <= $t && $t <= 251 ) {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                } elseif ( $t == 252 || $t == 253 ) {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                } else {
                    $n ++;
                }

                if ( $noc >= $length ) {
                    break;
                }

            }
            if ( $noc > $length ) {
                $n -= $tn;
            }

            $strcut = substr( $string, 0, $n );

        } else {
            for ( $i = 0; $i < $length; $i ++ ) {
                $strcut .= ord( $string[$i] ) > 127 ? $string[$i] . $string[++ $i] : $string[$i];
            }
        }

        $strcut = str_replace( array ( $pre . '&' . $end , $pre . '"' . $end , $pre . '<' . $end , $pre . '>' . $end ), array ( '&amp;' , '&quot;' , '&lt;' , '&gt;' ), $strcut );

        $pos = strrpos( $strcut, chr( 1 ) );
        if ( $pos !== false ) {
            $strcut = substr( $strcut, 0, $pos );
        }
        return $strcut . $dot;
    }

    /**
     * 描述格式化
     * @param  $subject
     */
    public static function clearCutstr ($subject, $length = 0, $dot = '...', $charset = 'utf-8')
    {
        if ($length) {
            return XUtils::cutstr(strip_tags(str_replace(array ("\r\n" ), '', $subject)), $length, $dot, $charset);
        } else {
            return strip_tags(str_replace(array ("\r\n" ), '', $subject));
        }
    }

    /**
     * 检测是否为英文或英文数字的组合
     *
     * @return unknown
     */
    public static function isEnglist( $param ) {
        if ( ! eregi( "^[A-Z0-9]{1,26}$", $param ) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 将自动判断网址是否加http://
     *
     * @param $http
     * @return  string
     */
    public static function convertHttp( $url ) {
        if ( $url == 'http://' || $url == '' )
            return '';

        if ( substr( $url, 0, 7 ) != 'http://' && substr( $url, 0, 8 ) != 'https://' )
            $str = 'http://' . $url;
        else
            $str = $url;
        return $str;

    }

    /*
        标题样式格式化
    */
    public static function titleStyle( $style ) {
        $text = '';
        if ( $style['bold'] == 'Y' ) {
            $text .='font-weight:bold;';
            $serialize['bold'] = 'Y';
        }

        if ( $style['underline'] == 'Y' ) {
            $text .='text-decoration:underline;';
            $serialize['underline'] = 'Y';
        }

        if ( !empty( $style['color'] ) ) {
            $text .='color:#'.$style['color'].';';
            $serialize['color'] = $style['color'];
        }

        return array( 'text' => $text, 'serialize'=>empty( $serialize )? '': serialize( $serialize ) );

    }

     // 自动转换字符集 支持数组转换
    static public function autoCharset ($string, $from = 'gbk', $to = 'utf-8')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($string) || (is_scalar($string) && ! is_string($string))) {
            //如果编码相同或者非字符串标量则不转换
            return $string;
        }
        if (is_string($string)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($string, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $string);
            } else {
                return $string;
            }
        } elseif (is_array($string)) {
            foreach ($string as $key => $val) {
                $_key = self::autoCharset($key, $from, $to);
                $string[$_key] = self::autoCharset($val, $from, $to);
                if ($key != $_key)
                    unset($string[$key]);
            }
            return $string;
        } else {
            return $string;
        }
    }

    /*
        标题样式恢复
    */
    public static function titleStyleRestore( $serialize, $scope = 'bold' ) {
        $unserialize = unserialize( $serialize );
        if ( $unserialize['bold'] =='Y' && $scope == 'bold' )
            return 'Y';
        if ( $unserialize['underline'] =='Y' && $scope == 'underline' )
            return 'Y';
        if ( $unserialize['color'] && $scope == 'color' )
            return $unserialize['color'];

    }

    /**
     * 列出文件夹列表
     *
     * @param $dirname
     * @return unknown
     */
    public static function getDir( $dirname ) {
        $files = array();
        if ( is_dir( $dirname ) ) {
            $fileHander = opendir( $dirname );
            while ( ( $file = readdir( $fileHander ) ) !== false ) {
                $filepath = $dirname . '/' . $file;
                if ( strcmp( $file, '.' ) == 0 || strcmp( $file, '..' ) == 0 || is_file( $filepath ) ) {
                    continue;
                }
                $files[] =  self::autoCharset( $file, 'GBK', 'UTF8' );
            }
            closedir( $fileHander );
        }
        else {
            $files = false;
        }
        return $files;
    }

    /**
     * 列出文件列表
     *
     * @param $dirname
     * @return unknown
     */
    public static function getFile( $dirname ) {
        $files = array();
        if ( is_dir( $dirname ) ) {
            $fileHander = opendir( $dirname );
            while ( ( $file = readdir( $fileHander ) ) !== false ) {
                $filepath = $dirname . '/' . $file;

                if ( strcmp( $file, '.' ) == 0 || strcmp( $file, '..' ) == 0 || is_dir( $filepath ) ) {
                    continue;
                }
                $files[] = self::autoCharset( $file, 'GBK', 'UTF8' );;
            }
            closedir( $fileHander );
        }
        else {
            $files = false;
        }
        return $files;
    }


    /**
     * [格式化图片列表数据]
     *
     * @return [type] [description]
     */
    public static function imageListSerialize( $data ) {

        foreach ( (array)$data['file'] as $key => $row ) {
            if ( $row ) {
                $var[$key]['fileId'] = $data['fileId'][$key];
                $var[$key]['file'] = $row;
            }

        }
        return array( 'data'=>$var, 'dataSerialize'=>empty( $var )? '': serialize( $var ) );

    }

    /**
     * 反引用一个引用字符串
     * @param  $string
     * @return string
     */
    static function stripslashes($string) {
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = self::stripslashes($val);
            }
        } else {
            $string = stripslashes($string);
        }
        return $string;
    }
    
    /**
     * 引用字符串
     * @param  $string
     * @param  $force
     * @return string
     */
   static function addslashes($string, $force = 1) {
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = self::addslashes($val, $force);
            }
        } else {
            $string = addslashes($string);
        }
        return $string;
    }

    public static function addslashes_deep($value)
    {
        if (empty($value))
        {
            return $value;
        }
        else
        {
            return is_array($value) ? array_map('self::addslashes_deep', $value) : addslashes($value);
        }
    }

    /**
     * 格式化内容
     */
    static function formatHtml($content, $options = ''){
        $purifier = new CHtmlPurifier();
        if($options != false)
            $purifier->options = $options;
        return $purifier->purify($content);
    }


    public static function unicode_encode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2)
        {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0)
            {   //两个字节的文字
                $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            }
            else
            {
                $str .= $c2;
            }
        }
        return $str;
    }
    //将UNICODE编码后的内容进行解码
    public static function unicode_decode($name)
    {
        //转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches))
        {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++)
            {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0)
                {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code).chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                }
                else
                {
                    $name .= $str;
                }
            }
        }
        return $name;
    }

    public static function utf8_to_unicode( $str ) {
     
        $unicode = array();       
        $values = array();
        $lookingFor = 1;
       
        for ($i = 0; $i < strlen( $str ); $i++ ) {
            $thisValue = ord( $str[ $i ] );
        if ( $thisValue < ord('A') ) {
            // exclude 0-9
            if ($thisValue >= ord('0') && $thisValue <= ord('9')) {
                 // number
                 $unicode[] = chr($thisValue);
            }
            else {
                 $unicode[] = '%'.dechex($thisValue);
            }
        } else {
              if ( $thisValue < 128)
            $unicode[] = $str[ $i ];
              else {
                    if ( count( $values ) == 0 ) $lookingFor = ( $thisValue < 224 ) ? 2 : 3;               
                    $values[] = $thisValue;               
                    if ( count( $values ) == $lookingFor ) {
                        $number = ( $lookingFor == 3 ) ?
                            ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
                            ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
                $number = dechex($number);
                $unicode[] = (strlen($number)==3)?"%u0".$number:"%u".$number;
                        $values = array();
                        $lookingFor = 1;
              } // if
            } // if
        }
        } // for
        return implode("",$unicode);

    } // utf8_to_unicode

    //精准时间戳
    public static function microtime_float(){ 
        list($usec, $sec) = explode(" ", microtime()); 
        return ((float)$usec + (float)$sec); 
    } 

    //单独使用反斜线引用单引号
    public static function addENTQUOTES($str){ 
        return str_replace("'","\\'",$str);
    }

    public static function addAllQUOTES($str){ 
        // return str_replace(array("'",'"'),array("\\'",'\\"'),$str);
        return self::addslashes_deep($str);
    }

    /**
      * 取得根域名
      * @param type $domain 域名
      * @return string 返回根域名
      */
    public static function GetUrlToDomain($domain) {
         $re_domain = '';
         if($domain==''){
            return $re_domain;
         }
         $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn");
         $array_domain = explode(".", $domain);
         $array_num = count($array_domain) - 1;
         if ($array_domain[$array_num] == 'cn') {
             if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
                 $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
             } else {
                 $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
             }
         } else {
             $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
         }
         return $re_domain;
    }

    /**
     *  获取站点host
     *
     *  @param  string  $url
     *
     *  @return string  $host
     *
     */
    public static function hostInUrl($url){
        $host = '';
        $uUrls = parse_url($url);
        $host = isset($uUrls['host'])?$uUrls['host']:'';
        return $host;
    }

    /**
     *  是否是某站点
     *
     *  @param  string  $url
     *  @param  string  $name
     *
     *  @return int     $isInUrl
     *
     */
    public static function isInUrl($url, $name){
        $isInUrl = 0;
        $uUrls = parse_url($url);
        $u_url = (isset($uUrls['scheme'])?$uUrls['scheme']:'').'://'.(isset($uUrls['host'])?$uUrls['host']:'').(isset($uUrls['port'])?':'.$uUrls['port']:'');
        if(strpos($u_url,$name) !== false) {
            $isInUrl = 1;
        }
        return $isInUrl;
    }

    /**
     *  是否存在某个url参数数据
     *
     *  @param  string  $url
     *  @param  string  $by_name
     *  @param  string  $by_val
     *  @return int     $ret
     *
     */    
    public static function isInUrlByParam($url, $by_name, $by_val){
        $ret = 0;
        if($by_val!=''){
            $url_info = parse_url($url);
            $url_info_query = isset($url_info['query'])?$url_info['query']:'';
            parse_str($url_info_query,$url_info_query_arr);
            $chk_data = isset($url_info_query_arr[$by_name])?$url_info_query_arr[$by_name]:'';
            if($chk_data==$by_val){
                $ret = 1;
            }
        }
        return $ret;
    }

    /**
     *  是否存在某个url参数
     *
     *  @param  string  $url
     *  @param  string  $by_name
     *
     *  @return int     $ret
     *
     */
    public static function isKeyInUrlByParam($url, $by_name){
        $ret = 0;

        $url_info = parse_url($url);
        $url_info_query = isset($url_info['query'])?$url_info['query']:'';
        parse_str($url_info_query,$url_info_query_arr);
        $chk_data = isset($url_info_query_arr[$by_name])?$url_info_query_arr[$by_name]:null;
        if($chk_data!==null){
            $ret = 1;
        }

        return $ret;
    }

    public static function requestFromUrl($url){
        $url_info = parse_url($url);
        $url_info_query = isset($url_info['query'])?$url_info['query']:'';
        parse_str($url_info_query,$url_info_query_arr);
        return $url_info_query_arr;
    }

    /**
     *  获取某个参数或者其来源参数
     *
     *  @param  string  $name
     *  @param  mix     $default_n
     *
     *  @return mix     $tmp
     */
    public static function getReqWithRefer($name,$default_n=null){
        $tmp = isset($_REQUEST[$name])?$_REQUEST[$name]:$default_n;
        if(empty($tmp)){
            $urlinfo = parse_url( isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'' );
            $urlinfo_query = isset($urlinfo['query'])?$urlinfo['query']:'';
            parse_str($urlinfo_query,$urlinfo_query_arr);
            $tmp = isset($urlinfo_query_arr[$name])?$urlinfo_query_arr[$name]:$default_n;
        }
        return $tmp;
    }

    //从一个标准 url 里取出文件的扩展名
    public static function getExt($url){
        $ret_url = '';
        $arr = parse_url($url);
        if(isset($arr['path'])){
            $file = basename($arr['path']);
            $ret_url = substr(strrchr($file, '.'), 1);
        }
        return $ret_url;
    }

    //php获取扩展名的方法
    public static function get_extension($file){
        return substr(strrchr($file, '.'), 1);
    }

    /**
     *  来源
     *
     *  @param  string  $url
     *  @param  int  $is_simple
     *
     *  @return int     $ret
     *
     */
    public static function judgePlatformSource($url, $is_simple=0){
        $u_url = $url;
        if($is_simple==1){
        }else{
            $uUrls = parse_url($url);
            $u_url = (isset($uUrls['scheme'])?$uUrls['scheme']:'').'://'.(isset($uUrls['host'])?$uUrls['host']:'').(isset($uUrls['port'])?':'.$uUrls['port']:'');
        }
        $ret = '';

        if(strpos($u_url,'taobao') !== false) {
            $ret = '淘宝';
        }
        elseif(strpos($u_url,'tmall') !== false) {
            $ret = '天猫';
        }
        elseif(strpos($u_url,'jd.com') !== false) {
            $ret = '京东';
        }
        elseif(strpos($u_url,'stylenanda.com') !== false) {
            $ret = 'STYLENANDA';
        }
        elseif(strpos($u_url,'suning.com') !== false) {
            $ret = '苏宁易购';
        }
        
        return $ret;
    }

    //做ascii替换(SOH)
    public static function removenonprintable($strin){
        $strout = preg_replace('/[\x00-\x01]/', '', $strin);
        return $strout;
    }

    /**
     * 时间差计算
     *
     * @param Timestamp $time
     * @return String Time Elapsed
     * @author Shelley Shyan
     * @copyright http://phparch.cn (Professional PHP Architecture)
     */
    public static function time2Units ($time)
    {
       $year   = floor($time / 60 / 60 / 24 / 365);
       $time  -= $year * 60 * 60 * 24 * 365;
       $month  = floor($time / 60 / 60 / 24 / 30);
       $time  -= $month * 60 * 60 * 24 * 30;
       $week   = floor($time / 60 / 60 / 24 / 7);
       $time  -= $week * 60 * 60 * 24 * 7;
       $day    = floor($time / 60 / 60 / 24);
       $time  -= $day * 60 * 60 * 24;
       $hour   = floor($time / 60 / 60);
       $time  -= $hour * 60 * 60;
       $minute = floor($time / 60);
       $time  -= $minute * 60;
       $second = $time;
       $elapse = '';

       $unitArr = array('年'  =>'year', '个月'=>'month',  '周'=>'week', '天'=>'day',
                        '小时'=>'hour', '分钟'=>'minute', '秒'=>'second'
                        );

       foreach ( $unitArr as $cn => $u )
       {
           if ( $$u > 0 )
           {
               $elapse = $$u . $cn;
               break;
           }
       }

       return $elapse;
    }

    /**
      * 时间间距
      * @param $type 1 return string 天时
      *              2 return string 天时分
      *              3 return string 天时分秒
      * @author Jozh liu
      */
     public static function left_time($big, $small, $type=1){
         // if ( strlen($big) != 10 || !is_int($big) ) return false;
         // if ( strlen($small) != 10 || !is_int($small) ) return false;
         if ($big < $small) return '';

         $return = $re = abs($big-$small);

         $return = '';
         if ($d = floor($re/3600/24)) $return .= $d.'天';
         if ($h = floor(($re-3600*24*$d)/3600)) $return .= $h.'时';
         if ( $type == 2 ) {
             $i = floor(($re-3600*24*$d-3600*$h)/60);
             $return .= $i.'分';
         }
         if ( $type == 3 ) {
             $i = floor(($re-3600*24*$d-3600*$h)/60);
             $return .= $i.'分';
             $s = floor($re-3600*24*$d-3600*$h-60*$i);
             $return .= $s.'秒';
         }
         if ( $type == 4 ) {
             $return = '';
             if ($d = floor($re/3600/24)) $return .= $d.'天 ';
             if ($h = floor(($re-3600*24*$d)/3600)) $return .= $h.'：';
             $i = floor(($re-3600*24*$d-3600*$h)/60);
             $return .= $i.'：';
             $s = floor($re-3600*24*$d-3600*$h-60*$i);
             $return .= $s.'';
         }

         return $return;
     }

    /**
     * 相差的月份
     *
     */
    public static function getMonthNum( $date1, $date2, $tags='-' ){
        $date1 = explode($tags,$date1);
        $date2 = explode($tags,$date2);

        if($date1[0]>$date2[0]){
            list($date1, $date2) = array($date2, $date1);
        }

        $ret = abs($date1[0] - $date2[0]) * 12 + ($date2[1] - $date1[1]);
        $chk = $date2[2] - $date1[2];
        if($chk<0){
            $ret = $ret-1;
        }
        $ret = ($ret<0)?0:$ret;
        return $ret;
    }

    /**
     * 格式化时间戳作为日期
     *
     */
    public static function fmtTime($time, $format = 'Y-m-d')
    {
        return date($format, $time);
    }

    public static function divFromTextarea($html){
        //处理textarea
        if(preg_match('/<textarea[^>]*>(.*?)<\/textarea>/is',$html,$m)){
            $html = preg_replace_callback(
                '/<textarea[^>]*>(.*?)<\/textarea>/is',
                function ($matches) {
                    return htmlspecialchars_decode($matches[1]);
                },
                $html
            );
        }
        return $html;
    }

    public static function stripCleanDiv($html){

        $search = array ("'<script[^>]*?>.*?</script>'si",
            "/<a[^>]*>/",
            "/<\/a>/",
        );
        $replace = array ("",
            "",
            "",
        );
        $html = preg_replace ($search, $replace, $html);
        $html = self::RemoveXSS($html);

        return $html;
    }

    /**
    * @去除XSS（跨站脚本攻击）的函数
    * @par $val 字符串参数，可能包含恶意的脚本代码如<script language="javascript">alert("hello world");</script>
    * @return  处理后的字符串
    * @Recoded By Androidyue
    **/
    public static function RemoveXSS($val) {  
       // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed  
       // this prevents some character re-spacing such as <java\0script>  
       // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs  
       $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);  
     
       // straight replacements, the user should never need these since they're normal characters  
       // this prevents like <IMG SRC=@avascript:alert('XSS')>  
       $search = 'abcdefghijklmnopqrstuvwxyz'; 
       $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
       $search .= '1234567890!@#$%^&*()'; 
       $search .= '~`";:?+/={}[]-_|\'\\'; 
       for ($i = 0; $i < strlen($search); $i++) { 
          // ;? matches the ;, which is optional 
          // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
     
          // @ @ search for the hex values 
          $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ; 
          // @ @ 0{0,7} matches '0' zero to seven times  
          $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ; 
       } 
     
       // now the only remaining whitespace attacks are \t, \n, and \r 
       $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 
        // 'style', 'embed',
        'script', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 
        // 'title', 
        'base'); 
       $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
       $ra = array_merge($ra1, $ra2); 
     
       $found = true; // keep replacing as long as the previous round replaced something 
       while ($found == true) { 
          $val_before = $val; 
          for ($i = 0; $i < sizeof($ra); $i++) { 
             $pattern = '/'; 
             for ($j = 0; $j < strlen($ra[$i]); $j++) { 
                if ($j > 0) { 
                   $pattern .= '(';  
                   $pattern .= '(&#[xX]0{0,8}([9ad]);)'; 
                   $pattern .= '|';  
                   $pattern .= '|(&#0{0,8}([9|10|13]);)'; 
                   $pattern .= ')*'; 
                } 
                $pattern .= $ra[$i][$j]; 
             } 
             $pattern .= '/i';  
             $replacement = substr($ra[$i], 0, 2).'xxx'.substr($ra[$i], 2); // add in <> to nerf the tag  
             $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags  
             if ($val_before == $val) {  
                // no replacements were made, so exit the loop  
                $found = false;  
             }  
          }  
       }  
       return $val;  
    }

    /**
     * 自动闭合html标签
     */
    public static function closetags($html) {
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        if(!$result)  return $html;
        $openedtags = $result[1];

        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        if(!$result)  return $html;
        $closedtags = $result[1];

        $self_closing_tags = array('img'=>1, 'br'=>1, 'input'=>1, 'meta'=>1, 'link'=>1, 'hr'=>1, 'base'=>1, 'embed'=>1, 'spacer'=>1);
        $openedtags_chk = array();
        foreach($openedtags as $k=>$v){
            if(!isset($self_closing_tags[$v])){
                $openedtags_chk[]=$v;
            }
        }
        $openedtags = $openedtags_chk;
        unset($openedtags_chk);
        $closedtags_chk = array();
        foreach($closedtags as $k=>$v){
            if(!isset($self_closing_tags[$v])){
                $closedtags_chk[]=$v;
            }
        }
        $closedtags = $closedtags_chk;
        unset($closedtags_chk);

        $len_opened = count($openedtags);

        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);

        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)){
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    }

    /**
     * 安全格式字符数据
     *
     */
    public static function safeSprintf(){
        $tmp = '';
        $tmp = @call_user_func_array("sprintf", func_get_args());
        return $tmp;
    }

}


