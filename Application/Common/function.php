<?php
/**
 * Created by PhpStorm.
 * User: muzhitao
 * Date: 2016/4/7
 * Time: 21:17
 */
define('CURL_INFO');
function genCate($data, $pid = 0, $level = 0)
{
    $l = str_repeat("&nbsp;&nbsp;├", $level);
    $l = $l . '';
    static $arrcat = array();
    $arrcat = empty($level) ? array() : $arrcat;
    foreach ($data as $k => $row) {

        if ($row['PID'] == $pid) {
            if ($pid == 0) {
                $row['value'] = $row['TITLE'];
                $row['level'] = $level;
                $row['disable'] = 1;
                $arrcat[] = $row;
            } else {
                $row['value'] = $l . $row['TITLE'];
                $row['level'] = $level;
                $row['disable'] = 0;
                $arrcat[] = $row;
            }

            genCate($data, $row['ID'], $level + 1);
        }
    }
    return $arrcat;
}

function genCates($data, $pid = 1, $level = 0)
{
    if ($level < 2) {
        $l = str_repeat("&nbsp;&nbsp;├", $level);
    } else {
        $l = str_repeat("&nbsp;&nbsp;", $level);
    }

    $l = $l . '';
    static $arrcata = array();
    $arrcata = empty($level) ? array() : $arrcata;
    foreach ($data as $k => $row) {

        if ($row['PID'] == $pid) {
            if ($pid == 0) {
                $row['value'] = $row['NAME'];
                $row['level'] = $level;
                $row['disable'] = 1;
                $arrcata[] = $row;
            } else {
                $row['value'] = $l . $row['NAME'];
                $row['level'] = $level;
                $row['disable'] = 0;
                $arrcata[] = $row;
            }

            genCates($data, $row['ID'], $level + 1);
        }
    }
    return $arrcata;
}

function curl_get_json($url, $post)
{
    $ch = curl_init($url); //请求的URL地址
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);//$data JSON类型字符串
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post)));
    $data = curl_exec($ch);
    return $data;
}

// curl 访问内部接口
//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
function curl_request($url, $post = '', $cookie = '', $returnCookie = 0)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");

    if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    if ($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    define('CURL_INFO', curl_getinfo($curl, CURLINFO_HTTP_CODE));
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie'] = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}

function encode($string = '', $skey = 'cxphp')
{
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key] .= $value;
    return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
}

function decode($string = '', $skey = 'cxphp')
{
    $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key <= $strCount && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    return base64_decode(join('', $strArr));
}

if (!function_exists('array_column')) {
    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }
        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }
            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
}

if (!function_exists('get_client_browser')) {
    /**
     * 获取客户端浏览器类型
     * @param  string $glue 浏览器类型和版本号之间的连接符
     * @return string|array 传递连接符则连接浏览器类型和版本号返回字符串否则直接返回数组 false为未知浏览器类型
     */
    function get_client_browser($glue = null) {
        $browser = array();
        $agent = $_SERVER['HTTP_USER_AGENT']; //获取客户端信息

        /* 定义浏览器特性正则表达式 */
        $regex = array(
            'ie'      => '/(MSIE) (\d+\.\d)/',
            'chrome'  => '/(Chrome)\/(\d+\.\d+)/',
            'firefox' => '/(Firefox)\/(\d+\.\d+)/',
            'opera'   => '/(Opera)\/(\d+\.\d+)/',
            'safari'  => '/Version\/(\d+\.\d+\.\d) (Safari)/',
        );
        foreach($regex as $type => $reg) {
            preg_match($reg, $agent, $data);
            if(!empty($data) && is_array($data)){
                $browser = $type === 'safari' ? array($data[2], $data[1]) : array($data[1], $data[2]);
                break;
            }
        }
        return empty($browser) ? false : (is_null($glue) ? $browser : implode($glue, $browser));
    }
}

function format_for_currency($str , $len = 2)
{
    if (!$str) return '0.00';
    list($r, $x) = explode('.', $str);
    if (!$x) {
        for ($i = 0; $i < $len; $i ++) {
            $x .= '0';
        }
    } else {
        $x = substr($x, 0, $len);
    }
    $r = strrev($r);
    $nr = str_split($r, 3);

    $nrL = count($nr);
    $tmp = '';
    for ($i = $nrL -1; $i >= 0; $i --) {
        $tmp .= strrev($nr [$i]) . ',';
    }
    $tmp = rtrim($tmp, ',');
    return $tmp . '.' . $x;
}

function create_guid($namespace = '') {
    static $guid = '';
    $uid = uniqid("", true);
    $data = $namespace;
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['LOCAL_ADDR'];
    $data .= $_SERVER['LOCAL_PORT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid = substr($hash,  0,  8)
        .
        substr($hash,  8,  4)
        .
        substr($hash, 12,  4)
        .
        substr($hash, 16,  4)
        .
        substr($hash, 20, 12)
    ;
    return $guid;
}

function cutting_time($time)
{
    if (!$time) return;
    $unixTime = strtotime($time);
    return date('Y-m-d', $unixTime);
}

function exchangeRate($currency = '',$date = '') {
    if($currency == 'RMB') {
        return 1;
    }
    $date == '' ? $date = date('Y-m-d'):'';
    $url = "http://insight.gshopper.com/dashboard-backend/external/exchangeRate?date=$date&dst_currency=CNY&src_currency=$currency";
    $data = json_decode(file_get_contents($url),true);
    if($data['success'] == true && $data['data'][0]['rate']) {
        return $data['data'][0]['rate'];
    }else {
        return false;
    }
}

function cdVal($cd) {
    $model = TbMsCmnCdModel::getInstance();
    return $model->cache(true,60)->where(['CD'=>$cd])->getField('CD_VAL');
}

function valCd($val) {
    $model = TbMsCmnCdModel::getInstance();
    return $model->cache(true,60)->where(['CD_VAL'=>$val])->getField('CD');
}

function getHostInfo()
{
    $hostInfo = null;
    if ($hostInfo === null) {
        $secure = getIsSecureConnection();
        $http = $secure ? 'https' : 'http';
        if (isset($_SERVER['HTTP_HOST'])) {
            $hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
            $port = $secure ? getSecurePort() : $this->getPort();
            if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                $hostInfo .= ':' . $port;
            }
        }
    }

    return $hostInfo;
}

function getSecurePort()
{
    $secirePore = getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
    return $secirePore;
}

function getPort()
{
    $port = !getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
    return $port;
}

function getUrl()
{
    if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
        $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $requestUri = $_SERVER['REQUEST_URI'];
        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }
    } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
        $requestUri = $_SERVER['ORIG_PATH_INFO'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $requestUri .= '?' . $_SERVER['QUERY_STRING'];
        }
    } else {
        throw new InvalidConfigException('Unable to determine the request URI.');
    }

    return $requestUri;
}
