<?php
/**
 * 价格监控后台入口
 * Date: 2016/7/28
 * Time: 14:18
 */
if (ini_get('magic_quotes_gpc')) {
	function stripslashesRecursive(array $array){
		foreach ($array as $k => $v) {
			if (is_string($v)){
				$array[$k] = stripslashes($v);
			} else if (is_array($v)){
				$array[$k] = stripslashesRecursive($v);
			}
		}
		return $array;
	}
	$_GET = stripslashesRecursive($_GET);
	$_POST = stripslashesRecursive($_POST);
    $_REQUEST = stripslashesRecursive($_REQUEST);
}
ini_set('date.timezone','Asia/Shanghai');
define ( 'APP_NAME', 'Application' );
define( 'APP_SITE', getcwd());
define ( 'APP_PATH', APP_SITE.'/Application/' );
define ( 'APP_DEBUG', true );
if (substr(php_sapi_name(), 0, 3) == 'cli') {
    $depr = '/';
    $path = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
    
    if(!empty($path)) {
        $params = explode($depr, trim($path,$depr));
    }
    //!empty($params)?$_GET['g']=array_shift($params):"";
    !empty($params)?$_GET['m'] = array_shift($params):"";
    !empty($params)?$_GET['a'] = array_shift($params):"";
    if(count($params)>1) {
        // 解析剩余参数 并采用GET方式获取
        @preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',',$params));
    }
} else {
    /**
     * 环境设置
     */
    if ('b5c.sms2.com'== $_SERVER ['HTTP_HOST'])
    {
        require APP_PATH.'Conf/stage.php';
    } elseif ('sms2.b5cai.com'== $_SERVER ['HTTP_HOST'] || 'erp.gshopper.com'== $_SERVER ['HTTP_HOST'] || 'erp.gshopper.prod.com'== $_SERVER ['HTTP_HOST'])
    {
        require APP_PATH.'Conf/online.php';
    } else
    {
        require APP_PATH.'Conf/stage.php';
    }
}

require './ThinkPHP/ThinkPHP.php';