<?php



class Mainfunc{


    /**
     *  clean url params
     *
     */
    public static function replace_url_by_arr($url='',$arr=array()){
        foreach($arr as $v){
            $url = ZFun::replace_querystring($url,$v);
        }
        return $url;
    }

    static public function show_msg($msg,$type,$style=false) {
            if ($type ==1) {
                if(isset($_SERVER['HTTP_REFERER'])){
                    $js_string ="<script type='text/javascript'>location.href='".$_SERVER['HTTP_REFERER']."';</script>";
                }else{
                    $js_string ="<script type='text/javascript'>window.history.go(-1);</script>";
                }
            } else {
                $js_string ="<script language='javascript'>window.history.go(-2);</script>";
            }
            if($style==true){
                $js_string ="<script type='text/javascript'>alert('".$msg."');</script>".$js_string;
            }
            echo $js_string;
            exit;
    }

    static public function backurl_goback(){
        $isgoback=isset($_REQUEST['isgoback'])?intval($_REQUEST['isgoback']):'';
        $backurl =isset($_REQUEST['backurl'])?trim($_REQUEST['backurl']):'';
        if($isgoback==1 and $backurl!='' ){
            echo "<script type='text/javascript'>location.href='".$backurl."';</script>";
            exit();
        }
    }

    /**
     *  del array datas by value
     *
     */
    static public function arrRmByVal($arr,$value=''){
        $arr = is_array($arr)?$arr:array();
        foreach ($arr as $k=>$v)
        {
            if ($v == $value)
                unset($arr[$k]);
        }
        return $arr;
    }

    /**
     * Input > GET > POST.
     * 
     */
    public static function chooseParam($name,$defaultValue=null)
    {
        $ret = null;
        $res = file_get_contents('php://input');
        $res = @json_decode($res, TRUE);
        $req_param = isset($_REQUEST[$name])?$_REQUEST[$name]:$defaultValue;
        $req_param = isset($res[$name])?$res[$name]:$req_param;
        return $req_param;
    }

    public static function cleanHex($input){ 
        $clean = preg_replace("![\\][xX]([A-Fa-f0-9]{1,3})!", "",$input); 
        return $clean; 
    }

    //php防注入和XSS攻击通用过滤. 
    //by qq:
    // $_GET     && SafeFilter($_GET);
    // $_POST    && SafeFilter($_POST);
    // $_COOKIE  && SafeFilter($_COOKIE);
    public static function SafeFilter (&$arr) 
    {

       $ra=Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','/script/','/javascript/','/vbscript/','/expression/','/applet/','/meta/','/xml/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/','/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/','/onunload/');

       if (is_array($arr))
       {
         foreach ($arr as $key => $value) 
         {
            if (!is_array($value))
            {
              if (!get_magic_quotes_gpc())             //不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
              {
                 $value  = addslashes($value);           //给单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符）加上反斜线转义
              }
              $value       = preg_replace($ra,'',$value);     //删除非打印字符，粗暴式过滤xss可疑字符串
              $arr[$key]     = htmlentities(($value)); //去除 HTML 和 PHP 标记并转换为 HTML 实体
            }
            else
            {
              self::SafeFilter($arr[$key]);
            }
         }
       }
    }


}

