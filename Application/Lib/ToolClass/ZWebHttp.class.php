<?php
// namespace app\components;


class ZWebHttp{
    public static $dh_g = "2";
    public static $dh_p = "106025087133488299239129351247929016326438167258746469805890028339770628303789813787064911279666129";
    public static $iv = '1234567812345678';

    public static $timeout = 6;
    public static $connecttimeout = 5;

    public static $multi_timeout = 3;
    public static $multi_connecttimeout = 2;

    public static $ip_resolve = '';

    static public function decryptedAes($key='',$data=''){
        $iv=self::$iv;
        $data = base64_decode($data);
        $ttt = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
        return $ttt;
    }

    static public function CallbackBegin($is_output=0){
        header('content-type:text/html; charset=utf-8;');
        $callback = isset($_REQUEST['callback'])?$_REQUEST['callback']:'';
        $jsonCallback = isset($_REQUEST['jsonCallback'])?$_REQUEST['jsonCallback']:'';
        $jsonpCallback = isset($_REQUEST['jsonpCallback'])?$_REQUEST['jsonpCallback']:'';
        if($callback or $jsonCallback or $jsonpCallback){
            header('Content-Type:application/json;charset=utf-8;');
            if($is_output==1){
                $ret = ($callback)?$callback:($jsonCallback?$jsonCallback:$jsonpCallback);
                $ret .= '(';
                return $ret;
            }
            echo ($callback)?$callback:($jsonCallback?$jsonCallback:$jsonpCallback);
            echo '(';
        }
    }

    static public function CallbackEnd($is_output=0){
        $callback = isset($_REQUEST['callback'])?$_REQUEST['callback']:'';
        $jsonCallback = isset($_REQUEST['jsonCallback'])?$_REQUEST['jsonCallback']:'';
        $jsonpCallback = isset($_REQUEST['jsonpCallback'])?$_REQUEST['jsonpCallback']:'';
        if($callback or $jsonCallback or $jsonpCallback){
            if($is_output==1){
                $ret = ')';
                return $ret;
            }
            echo ')';
        }
    }

    static public function get_version_activity($opt_memcache_arr, $name='_precae_sale_activity') {
        $mc = new Memcache();
        $mc->addServer($opt_memcache_arr[0], $opt_memcache_arr[1]);
        $new_version = $mc->get($name);
        if( $new_version ){
        }else{
            $new_version = self::set_version_activity($opt_memcache_arr, $name);
        }
        return $new_version;
    }

    static public function set_version_activity($opt_memcache_arr, $name='_precae_sale_activity') {
        $new_version = 'M'.date('Y').'D'.date('YmdHi').time();
        $mc = new Memcache();
        $mc->addServer($opt_memcache_arr[0], $opt_memcache_arr[1]);
        $mc -> set($name, $new_version);
        return $new_version;
    }

    public static function open($uri, $type,$post_data= '') {

        $user_agent = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; CIBA)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connecttimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);

        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        switch ($type) {
            case "GET":
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                // json格式
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=utf-8"));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                break;
            case "GETCookie":
                curl_setopt($ch, CURLOPT_HEADER, true);
                break;
            default:
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
        }

        //默认ipv4
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function getCurlHtml($url=null,$urlData=array()){
        if(empty($url)) return '';
        $useragent='Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4 (.NET CLR 2.0.50727)';
        if(isset($_SERVER['HTTP_USER_AGENT']))	$useragent=$_SERVER['HTTP_USER_AGENT'];
        if(isset($urlData['useragent']))	$useragent=$urlData['useragent'];
        $needcookie=$usecookie=false;
        if(isset($urlData['needcookie']))	$needcookie=$urlData['needcookie'];
        if(isset($urlData['usecookie']))	$usecookie=$urlData['usecookie'];

        $info=array();
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connecttimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        $cookie_str = isset($urlData['cookie_str'])?$urlData['cookie_str']:'';
        if($cookie_str){
            curl_setopt($ch,CURLOPT_COOKIE,$cookie_str);
        }
        if($needcookie==1){
            curl_setopt($ch, CURLOPT_COOKIEJAR, $usecookie);
        }elseif($needcookie==2){
            curl_setopt($ch, CURLOPT_COOKIEFILE, $usecookie);
        }
        if(isset($urlData['header']) and is_array($urlData['header'])){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $urlData['header']);
        }
        if(isset($urlData['is_post']) and isset($urlData['post_str']) and $urlData['is_post']==1){
            curl_setopt($ch, CURLOPT_POST,       1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $urlData['post_str']);
        }
        if(isset($urlData['is_post']) and isset($urlData['post_str']) and $urlData['is_post']==2){
            curl_setopt($ch, CURLOPT_POST,       1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=utf-8"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $urlData['post_str']);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if(isset($urlData['useproxy']) and $urlData['useproxy']==1){
            $proxytype=isset($urlData['proxytype'])?$urlData['proxytype']:'';
            if(strtoupper($proxytype)!='SOCKS'){
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            }else{
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
            curl_setopt($ch, CURLOPT_PROXY, $urlData['proxy']);
            $proxyauth=isset($urlData['proxyauth'])?$urlData['proxyauth']:'';
            if($proxyauth!=''){
                if(strtoupper($proxyauth)!='NTLM'){
                    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                }else{
                    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
                }
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $urlData['proxyuserpwd']);
            }
        }

        if(self::$ip_resolve!=''){
            if(self::$ip_resolve=='IPv4'){
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            }
            elseif(self::$ip_resolve=='IPv6'){
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6 );
            }
        }

        $info['html']=curl_exec($ch);
        $info['info']=curl_getinfo($ch);
        curl_close($ch);
        return $info;
    }

    public static function outputArr(){
        $output = array();
        $output['code'] ='-1';
        $output['msg']  ='';
        $output['val']  ='';
        return $output;
    }

    public static function rmCallbackReq($reqAll){
        if(isset($reqAll['_'])) {
            unset($reqAll['_']);
        }
        if(isset($reqAll['callback'])) {
            unset($reqAll['callback']);
        }
        if(isset($reqAll['jsonCallback'])) {
            unset($reqAll['jsonCallback']);
        }
        if(isset($reqAll['jsonpCallback'])) {
            unset($reqAll['jsonpCallback']);
        }
        return $reqAll;
    }

    /**
     *  生成get方式的url
     *
     */
    public static function makeGetUrl($url='', $getdata=array()){

        $url_bk = $url;
        if(strpos($url,'?')===false){
            $url.='?';
        }
        $httpdata   = http_build_query($getdata);
        $url        = $url.$httpdata;

        return $url;
    }

    /**
     *  并发curl
     *
     *
     */
    public static function multiRequest($data, $options = array()) {
        $useragent='Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4 (.NET CLR 2.0.50727)';
        if(isset($_SERVER['HTTP_USER_AGENT']))  $useragent=$_SERVER['HTTP_USER_AGENT'];

        // array of curl handles
        $curly = array();
        // data to be returned
        $result = array();

        if(empty($data)){
            return $result;
        }

        // multi handle
        $mh = curl_multi_init();

        // loop through $data and create curl handles
        // then add them to the multi-handle
        foreach ($data as $id => $d) {

            $curly[$id] = curl_init();

            $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
            curl_setopt($curly[$id], CURLOPT_URL,            $url);
            curl_setopt($curly[$id], CURLOPT_HEADER,         0);
            curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curly[$id], CURLOPT_CONNECTTIMEOUT, self::$multi_connecttimeout);
            curl_setopt($curly[$id], CURLOPT_TIMEOUT, self::$multi_timeout);

            // post?
            if (is_array($d)) {
                if (!empty($d['post'])) {
                    curl_setopt($curly[$id], CURLOPT_POST,       1);
                    curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
                }
            }

            // extra options?
            if (!empty($options)) {
                // curl_setopt_array($curly[$id], $options);
                if(isset($options[$id])){
                    $urlData = $options[$id];
                    if(isset($urlData['is_post']) and isset($urlData['post_str']) and $urlData['is_post']==2){
                        curl_setopt($curly[$id], CURLOPT_POST,       1);
                        curl_setopt($curly[$id], CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=utf-8"));
                        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $urlData['post_str']);
                    }
                }
            }

            curl_setopt($curly[$id], CURLOPT_USERAGENT, $useragent);
            curl_setopt($curly[$id], CURLOPT_FOLLOWLOCATION, true);

            curl_multi_add_handle($mh, $curly[$id]);
        }

        // execute the handles
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);

        // get content and remove handles
        foreach($curly as $id => $c) {
            $result[$id] = curl_multi_getcontent($c);
            curl_multi_remove_handle($mh, $c);
        }

        // all done
        curl_multi_close($mh);

        return $result;
    }


}
