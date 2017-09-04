<?php
/*
 * Created on 2017
 * Huaxin
 *
 */






/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep($value){
	if (empty($value))    {
		return $value;
	}else    {
		return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
	}
}


function stripslashes_deep($value){
	if (empty($value))    {
		return $value;
	}else    {
		return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	}
}


// function is_utf8($string) {
// 		// From http://w3.org/International/questions/qa-forms-utf-8.html
// 		return preg_match('%^(?:
// 		[\x09\x0A\x0D\x20-\x7E] # ASCII
// 		| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
// 		| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
// 		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
// 		| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
// 		| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
// 		| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
// 		| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
// 		)*$%xs', $string);
// }


function strlen_utf8($str) {
	$i = 0;
	$count = 0;
	$len = strlen ($str);
	while ($i < $len) {
		$chr = ord ($str[$i]);
		$count++;
		$i++;
		if($i >= $len) break;
		if($chr & 0x80) {
			$chr <<= 1;
			while ($chr & 0x80) {
				$i++;
				$chr <<= 1;
			}
		}
	}
	return $count;
}


/**
 * 计算字符串的长度（汉字按照两个字符计算）
 *
 * @param   string      $str        字符串
 *
 * @return  int
 */
function str_len($str)
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
 * 截取UTF-8编码下字符串的函数
 *
 * @param   string      $str        被截取的字符串
 * @param   int         $length     截取的长度
 * @param   bool        $append     是否附加省略号
 *
 * @return  string
 */
function sub_str($str, $length = 0, $append = true)
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
		$newstr = mb_substr($str, 0, $length, 'utf-8');
	}
	elseif (function_exists('iconv_substr'))
	{
		$newstr = iconv_substr($str, 0, $length, 'utf-8');
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


//check email
function isEmail($email){
	$email=trim($email);
	if(empty($email)){return false;}
	$a=preg_match("/^[a-z0-9][\w\.-]*@[\w-]+\.\w[\w-\.]*[a-z]$/i",$email,$m);
	if($a){
		return true;
	}else{
		return false;
	}
}


//获取随机数字
function simpleRand($num=1){
	if(intval($num)<1){
		$num=1;
	}
	$str='abcdefghijklmnopqrstuvwxyz1234567890';
	$r='';
	for($i=0;$i<$num;$i++){
		$tmp=rand(0,strlen($str)-1);
		$r.=$str{$tmp};
	}
	return $r;
}

/**
 * 获得用户的真实IP地址
 * Startar from ecshop
 * @access  public
 * @return  string
 */
function real_ip()
{
	static $realip = NULL;
	if ($realip !== NULL)
	{
		return $realip;
	}
	if (isset($_SERVER))
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			/* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
			foreach ($arr AS $ip)
			{
				$ip = trim($ip);
				if ($ip != 'unknown')
				{
					$realip = $ip;
					break;
				}
			}
		}
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$realip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else
		{
			if (isset($_SERVER['REMOTE_ADDR']))
			{
				$realip = $_SERVER['REMOTE_ADDR'];
			}
			else
			{
				$realip = '0.0.0.0';
			}
		}
	}
	else
	{
		if (getenv('HTTP_X_FORWARDED_FOR'))
		{
			$realip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_CLIENT_IP'))
		{
			$realip = getenv('HTTP_CLIENT_IP');
		}
		else
		{
			$realip = getenv('REMOTE_ADDR');
		}
	}
	preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
	$realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
	return $realip;
}


function show_permission_msg($msg,$type) {
	header('Content-Type: text/html; charset=utf-8');
	if ($type ==1) {
		$js_string ="<script type='text/javascript'>alert('".$msg."');window.history.go(-1);</script>";
	} else {
		$js_string ="<script type='text/javascript'>alert('".$msg."');window.history.go(-2);</script>";
	}
	echo $js_string;
	exit;
}


function show_msg($msg,$type,$style=false) {
	if ($type ==1) {
		//startar edit	返回先前一页,修正google浏览器返回后会重复选中(如checkbox)
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


/**
 *	自定义抓取网页的html,(可以保存cookie功能)
 *	@param		string	$url	网址
 *	@param		int		$needcookie	1，保存，2，使用
 *	@param		string	$usecookie	文件地址
 *	@return	string
 */
function get_website_html_cook($url=null,$needcookie=false,$usecookie=false,$useagent=0){
	if(empty($url)) return '';
	if($useagent==1){
		$SET_HTTP_USER_AGENT = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; Lumia 800)';
	}else{
		$SET_HTTP_USER_AGENT = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4 (.NET CLR 2.0.50727)';
	}
	$info=array();
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $SET_HTTP_USER_AGENT);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //跳过证书检查
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	if($needcookie==1){
		curl_setopt($ch, CURLOPT_COOKIEJAR, $usecookie);
	}elseif($needcookie==2){
		curl_setopt($ch, CURLOPT_COOKIEFILE, $usecookie);
	}

	$info['html']=curl_exec($ch);
	$info['info']=curl_getinfo($ch);
	curl_close($ch);

	if(isset($info['html']) and $info['info']['http_code']=='200'){
		return $info['html'];
	}elseif($needcookie==1 or $needcookie==2){
		return $info['html'];
	}else{
		if(substr($url,0,4)!='http'){
			$url='http://'.$url;
		}
		if(empty($info['html'])){
			return @file_get_contents($url);
		}else{
			return $info['html'];
		}
	}
}


//导出csv文件(utf-8编码)
function fileToCSV($filename,$data) {
	$data=trim($data);
	header("Content-type: text/csv; charset=gbk;");
	header("Content-type: text/csv; ");
	header("Content-Disposition: attachment; filename=" . $filename);
	if(substr($data,0,3)=='ID,'){
		$data=str_replace('ID,',' ID,',$data);
	}
	if(strpos($data,',')===false){
		echo iconv('utf-8','gbk//IGNORE',$data);
	}else{
		$data_new='';
		while( ($pos=strpos($data,','))!==false ){
			$data_new.=iconv('utf-8','gbk//IGNORE',substr($data,0,$pos));
			$data_new.=',';
			$data=substr($data,$pos+1);
		}
		if($data!=''){
			$data_new.=iconv('utf-8','gbk//IGNORE',$data);
		}
		echo $data_new;
	}
	exit();
}


function arr2csv($arr){
	$str='';
	if(is_array($arr)){
		foreach($arr as $k=>$v){
			if(!empty($v)){
				foreach($v as $key=>$value){
					$str.=replace_csvdata($value).",";
				}
				$str.="\r\n";
			}
		}
	}
	return $str;
}


//返回一个定义排序内容的数组
function order_filter(){
	$orderArr=array();
	$orderArr['order']	=(isset($_REQUEST['order']) and strtolower($_REQUEST['order'])=='desc')?'desc':'asc';
	$orderArr['next']	=($orderArr['order']=='asc')?'desc':'asc';
	$orderArr['type']	=isset($_REQUEST['type'])?$_REQUEST['type']:'';
	$orderArr['Desc']	='';
	$orderArr['DescSql']=' ';
	return $orderArr;
}


//分页：分页数、当前页数、分页sql
function page_filter($pagename='page',$pernum=1){
	$perpagename='per'.$pagename;
	$perpage	=isset($_GET[$perpagename])?abs(intval($_GET[$perpagename])):$pernum;
	$page		=isset($_GET[$pagename])?$_GET[$pagename]:1;
	$page		=(intval($page)>=1)?intval($page):1;
	$limit		=" LIMIT ".($page-1)*$perpage.",".$perpage." ";
	$arr=array('perpage'=>$perpage, 'page'=>$page, 'limit'=>$limit, );
	return $arr;
}


function date_join($y,$m,$d){
	$str='';
	$y=intval($y);
	$m=intval($m);
	$d=intval($d);
	$str=$y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
	return $str;
}


function get_request(){
	$get_r=array();
	$get_r['date_type']		= isset($_REQUEST['date_type'])?$_REQUEST['date_type']:'';
	$get_r['start_year']	= isset($_REQUEST['start_year'])?intval($_REQUEST['start_year']):'';
	$get_r['start_month']	= isset($_REQUEST['start_month'])?abs(intval($_REQUEST['start_month'])):'';
	$get_r['start_day']		= isset($_REQUEST['start_day'])?abs(intval($_REQUEST['start_day'])):'';
	$get_r['end_year']		= isset($_REQUEST['end_year'])?intval($_REQUEST['end_year']):'';
	$get_r['end_month']		= isset($_REQUEST['end_month'])?abs(intval($_REQUEST['end_month'])):'';
	$get_r['end_day']		= isset($_REQUEST['end_day'])?abs(intval($_REQUEST['end_day'])):'';
	$get_r['utm_source']	= isset($_REQUEST['utm_source'])?$_REQUEST['utm_source']:'';
	$get_r['utm_medium']	= isset($_REQUEST['utm_medium'])?$_REQUEST['utm_medium']:'';
	$get_r['utm_campaign']	= isset($_REQUEST['utm_campaign'])?$_REQUEST['utm_campaign']:'';

	return $get_r;
}


/**
 *	替换csv文字
 */
function replace_csvdata($str){
	/*$str=str_replace(
		array(','),
		array('，'),
		$str
	);
	$str=preg_replace('/\s/is',' ',$str);*/
	$str='"'.str_replace('"','""',$str).'"';
	return $str;
}


/**
 *	处理url参数
 */
function replace_querystring($url='',$name=''){
	$reurl='';

	if(empty($_SERVER['QUERY_STRING'])){
		$reurl='?a=1';
	}else{
		if(empty($url)){
			$url='?'.$_SERVER['QUERY_STRING'];
		}else{
		}
		if(strpos($url,'?'.$name.'=')!==false){
			$url=preg_replace(
				array("/[\?&]".$name."=[^&]*([&]?)(.*?)$/is",),
				array('?\\2'),
				$url
			);
			if(substr($url,-1)=='?'){
			}else{
			}
		}else{
			$url=preg_replace(
				array("/[\?&]".$name."=[^&]*([&]?)/is",),
				array('\\1'),
				$url
			);
		}
		$reurl=$url;
	}
	return $reurl;
}


function backurl_goback(){
	$isgoback=isset($_REQUEST['isgoback'])?intval($_REQUEST['isgoback']):'';
	$backurl =isset($_REQUEST['backurl'])?trim($_REQUEST['backurl']):'';
	if($isgoback==1 and $backurl!='' ){
		echo "<script type='text/javascript'>location.href='".$backurl."';</script>";
		exit();
	}
}


/**
 *	从数组日期中，获取时间戳
 */
function datetotime($arr ,$isend=''){
	if($isend==''){
		$str=' 0:0:0';
	}elseif($isend=='end'){
		$str=' 23:59:59';
	}else{
		$str=$isend;
	}

	return strtotime($arr[0].'-'.$arr[1].'-'.$arr[2].$str);
}


/**
 *	验证实际的日期
 */
function get_real_day($year,$month,$day){
	$year	=intval($year);
	$month	=intval($month);
	$day	=abs(intval($day));
	switch( $month ){
		case '2':
			if( ($year%4==0) && (($year%400==0) || ($year%100!=0)) ){
				if($day>29){
					$day=29;
				}
			}else{
				if($day>28){
					$day=28;
				}
			}
			break;
		case '4':
		case '6':
		case '9':
		case '11':
			if($day>30){
				$day=30;
			}
			break;
		default:
			if($day>31){
				$day=31;
			}
			break;
	}
	return $day;
}


function date_month_diff_num($d1,$d2) {
	$m1 = date("n",($d1));
	$m2 = date("n",($d2));
	$y1 = date("Y",($d1));
	$y2 = date("Y",($d2));
	$a = ($y2-$y1)*12+($m2-$m1)+1;
	return $a;
}


function date_day_diff_num($d1,$d2) {
	$day=floor( ($d2-$d1)/3600/24+1 );
	return $day;
}


/**
 * 格式化商品价格
 *
 * @access  public
 * @param   float   $price  商品价格
 * @return  string
 */
function price_format($price, $point_num=1) {
	$price = number_format($price, $point_num, '.', '');
	if($price==0){
		$price=0;
	}
	return $price;
}


function price_sql_format($type ,$pre='tl.' ){
	$sql_format='';
	if( '1'==$type ){
		$sql_format=" round(trim(".$pre."price),2)<=50 ";
	}elseif( '2'==$type ){
		$sql_format=" round(trim(".$pre."price),2)>50 AND round(trim(".$pre."price),2)<=100 ";
	}elseif( '3'==$type ){
		$sql_format=" round(trim(".$pre."price),2)>100 AND round(trim(".$pre."price),2)<=150 ";
	}elseif( '4'==$type ){
		$sql_format=" round(trim(".$pre."price),2)>150 ";
	}
	return $sql_format;
}


function price_export_format($type ){
	$format='';
	if( '1'==$type ){
		$format="<=50";
	}elseif( '2'==$type ){
		$format="50-100 (inc. 100)";
	}elseif( '3'==$type ){
		$format="100-150 (inc. 150)";
	}elseif( '4'==$type ){
		$format=">150";
	}
	return $format;
}


function discount_sql_format($type ,$pre='tl.' ){
	$sql_format='';
	$n1=30;
	$n2=50;
	$n3=70;
	if(defined('DISCOUNT_1') AND defined('DISCOUNT_2') AND defined('DISCOUNT_3')){
		$n1=DISCOUNT_1;
		$n2=DISCOUNT_2;
		$n3=DISCOUNT_3;
	}
	if( '1'==$type ){
		$sql_format=" round(trim(".$pre."discount_percent),2)<=".$n1." ";
	}elseif( '2'==$type ){
		$sql_format=" round(trim(".$pre."discount_percent),2)>".$n1." AND round(trim(".$pre."discount_percent),2)<=".$n2." ";
	}elseif( '3'==$type ){
		$sql_format=" round(trim(".$pre."discount_percent),2)>".$n2." AND round(trim(".$pre."discount_percent),2)<=".$n3." ";
	}elseif( '4'==$type ){
		$sql_format=" round(trim(".$pre."discount_percent),2)>".$n3." ";
	}
	return $sql_format;
}


function discount_export_format($type ){
	$format='';
	if( '1'==$type ){
		$format="<30%";
	}elseif( '2'==$type ){
		$format="30-50%（inc.50%)";
	}elseif( '3'==$type ){
		$format="50-70% (inc.70%)";
	}elseif( '4'==$type ){
		$format=">70%";
	}
	return $format;
}
//格式化打印数据
function printr($array){
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

function quick_encode($s){
	return base64_encode(serialize($s));
}


function quick_decode($s){
	$s=@unserialize(base64_decode($s));
	return $s;
}


function js_redirect($url=''){
	echo '<script type="text/javascript">location.href="'.$url.'";</script>';
	die();
}


