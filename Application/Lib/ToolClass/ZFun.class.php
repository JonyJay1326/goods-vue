<?php
// namespace app\components;


class ZFun{
    public static $cache_file_dir = '';
    public static $cache_file_path = '';


    public static function test(){
        return 'gggg';
    }

    /**
     * 格式化价格
     *
     * @params  string  $num_str
     *
     * @return  string   $num_str
     */
    public static function formatPriceNum($num_str){
        $num_str = sprintf("%f", $num_str);
        $num_str = number_format($num_str , 2, '.', '');
        return $num_str;
    }

    public static function keepPriceNum($num_str, $num=1){
        $num_str = sprintf("%f", $num_str);
        $num_str = number_format($num_str , $num, '.', '');
        return $num_str;
    }

    public static function keepNumber($str){
        return trim(preg_replace('/[^\.\d]/is','',$str));
    }

    public static function roundZero($str){
        self::formatPriceNum($str);
        return round($str,0);
    }

    public static function roundPriceNum($num_str, $num=2){
        return round($num_str,$num);
    }

    public static function urlStrParse($url){
        $retArr=array();
        $urlinfo = parse_url($url);
        if(isset($urlinfo['query'])){
            parse_str($urlinfo['query'], $urldata);
            $retArr=$urldata;
        }
        return $retArr;
    }

    public static function create_guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
        .substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12)
        .chr(125);// "}"
        $uuid=strtolower(str_replace(array(chr(45),chr(123),chr(125)),array('','',''),$uuid));
        $uuid=substr($uuid,0,12).'4'.substr($uuid,13);
        return $uuid;
    }

    public static function lowerKeyArr($arr){
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                $k_lower = strtolower($k);
                if($k_lower!=$k){
                    $arr[$k_lower] = $v;
                    unset($arr[$k]);
                }
            }
        }
        return $arr;
    }

    /**
     * 递归方式的对变量中的key进行转小写
     *
     * @access  public
     * @param   mix     $value
     *
     * @return  mix
     */
    public static function lowerKeyDeep($value)
    {
        if (empty($value))
        {
            return $value;
        }
        else
        {
            return is_array($value) ? self::lowerKeyArr(array_map('self::lowerKeyArr', $value)) : ($value);
        }
    }

    /**
     * 字符串lcfirst自定义
     *
     */
    public static function strlcfirst( $str )
    {
        return (string)(strtolower(substr($str,0,1)).substr($str,1));
    }

    public static function lcfirstKeyArr($arr){
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                $k_lower = self::strlcfirst($k);
                if($k_lower!=$k){
                    $arr[$k_lower] = $v;
                    unset($arr[$k]);
                }
            }
        }
        return $arr;
    }

    /**
     * 递归方式的对变量中的key进行转lcfirst
     *
     * @access  public
     * @param   mix     $value
     *
     * @return  mix
     */
    public static function lcfirstKeyDeep($value)
    {
        if (empty($value))
        {
            return $value;
        }
        else
        {
            return is_array($value) ? self::lcfirstKeyArr(array_map('self::lcfirstKeyArr', $value)) : ($value);
        }
    }

    public static function zeroMinLow($price){
        if($price<0){
            $price=0;
        }
        return $price;
    }

    public static function stripWhitespaces($str){
        /* remove leading and trailing whitespaces of each line */
        $str = preg_replace('![\t ]*[\r\n]+[\t ]*!', '', $str);
        return $str;
    }

    /**
     * 多个间隔转单空格
     *
     */
    public static function stripSpacesToBlank($str){
        $str = preg_replace('![\s]+!', ' ', $str);
        return $str;
    }

    public static function arrNoEmpty($arr){
        $ret = array();
        foreach($arr as $k=>$v){
            $value = trim($v);
            if($value){
                $ret[$k]=$v;
            }
        }
        return $ret;
    }

    public static function arrNoEmptyMix($arr){
        $ret = array();
        if(!is_array($arr))  $arr=array();
        foreach($arr as $k=>$v){
            if(!empty($v)){
                $ret[$k]=$v;
            }
        }
        return $ret;
    }

    /**
     * 数组按照默认顺序保留唯一
     *
     */
    public static function arrNormalUnique($arr){
        $ret = array();
        if(is_array($arr)){
            $tmp = array();
            foreach($arr as $key=>$val){
                if(!isset($tmp[$val])){
                    $tmp[$val]=true;
                    $ret[]=$val;
                }
                unset($arr[$key]);
            }
        }
        return $ret;
    }

    /**
     * 一维数组格式化为一维id数据值的数组
     *
     * @params  array       $arr
     *
     * @return  array       $ret
     */
    public static function arrToIdsArr($arr){
        $ret = array();
        foreach($arr as $k=>$v){
            $tmp = ClsStr::strsToArray($v);
            $ret = array_merge($ret,$tmp);
        }
        return $ret;
    }

    //返回一个定义排序内容的数组
    public static function order_filter(){
        $orderArr=array();
        $orderArr['order']  =(isset($_REQUEST['order']) and strtolower($_REQUEST['order'])=='asc')?'asc':'desc';
        $orderArr['next']   =($orderArr['order']=='asc')?'desc':'asc';
        $orderArr['type']   =isset($_REQUEST['type'])?$_REQUEST['type']:'';
        $orderArr['Desc']   ='';
        $orderArr['DescSql']='';
        return $orderArr;
    }


    //分页：分页数、当前页数、分页sql
    public static function page_filter($pagename='page',$pernum=10){
        $perpagename='per'.$pagename;
        $perpage    =isset($_GET[$perpagename])?abs(intval($_GET[$perpagename])):$pernum;
        $page       =isset($_GET[$pagename])?$_GET[$pagename]:1;
        $page       =(intval($page)>=1)?intval($page):1;
        $limit      =" LIMIT ".($page-1)*$perpage.",".$perpage." ";
        $arr=array('perpage'=>$perpage, 'page'=>$page, 'limit'=>$limit, );
        return $arr;
    }

    /**
     * 二维数组提取指定键值的字符值拼接
     *
     */
    public static function arrKeyValueToIds($arr,$key_n='id'){
        $ret = '';
        if(is_array($arr)){
            $ret = array();
            foreach($arr as $k=>$v){
                if(isset($v[$key_n])){
                    $value = isset($v[$key_n])?$v[$key_n]:null;
                    $value = strval($value);
                    $ret[] = $value;
                }
            }
            $ret = implode(',',$ret);
        }
        return $ret;
    }

    /**
     *	处理url参数,替换name
     */
    public static function replace_querystring($url='',$name=''){
        $reurl='';

        if(empty($url)){
            $reurl='?';
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

    /**
     * 转换url，排序等替换
     *
     * @params  string  $url
     * @params  string  $str    e.g. sort=default
     *
     * @return  string  $ret
     */
    public static function addQueryStrParam($url,$str=''){
        $ret = '';
        $arr = explode('=',$str);
        $name   = isset($arr[0])?$arr[0]:'';
        $value  = isset($arr[1])?$arr[1]:'';
        $ret = self::replace_querystring($url,$name);
        if($value){
            if(substr($ret,-1)=='?'){
                $ret .= $str;
            }else{
                $ret .= '&'.$str;
            }
        }

        //存在mps的话，放最后
        $parse_url = parse_url($ret);
        $query = isset($parse_url['query'])?$parse_url['query']:'';
        parse_str($query, $output);
        if(isset($output['mps'])){
            $ret = self::replace_querystring($ret,'mps');
            if(substr($ret,-1)=='?'){
                $ret .= 'mps='.$output['mps'];
            }else{
                $ret .= '&'.'mps='.$output['mps'];
            }
        }

        if(substr($ret,-1)=='?'){
            $ret = substr($ret,0,-1);
        }
        return $ret;
    }

    public static function js_back($msg='',$type=1) {
        $type=intval($type);
        $js_string='';
        if($msg!=''){
            $js_string = "<script type='text/javascript'>alert('".$msg."');</script>";
        }
        $js_string.="<script language='javascript'>window.history.go(-".$type.");</script>";
        echo $js_string;
        die();
    }

    public static function t2date($t){
        return date('Y-m-d H:i:s',$t);
    }

    //web图片转为普通
    public static function webpImgToNormalDiv($str){
        return str_replace('_.webp"','"',$str);
    }

    //从后取出索引位1个元素
    public static function arrRightOneByIdx($arr, $idx=0){
        $data = null;
        if(is_array($arr)){
            $num = count($arr);
            //顺序相反
            $arr = array_reverse($arr);
            if(isset($arr[$idx])){
                $data = $arr[$idx];
            }
        }
        return $data;
    }

    //直接方式从后取出索引位1个元素
    public static function arrRightOneByNum($arr, $idx=0){
        $data = null;
        if(is_array($arr)){
            $num = count($arr);
            //顺序相反
            $arr = array_reverse($arr);
            $i=0;
            foreach($arr as $key=>$val){
                if($i==$idx){
                    $data = $val;
                    break(1);
                }
                ++$i;
            }
        }
        return $data;
    }

    //提取数组取出第一个
    public static function arrFirst($arr){
        $data = '';
        if(is_array($arr) and (!empty($arr))){
            $data = array_shift($arr);
        }
        return $data;
    }

    public static function explodeFirst($str){
        $data = '';
        $tmp = explode(',',$str);
        if(is_array($tmp) and count($tmp)>0){
            $data = array_shift($tmp);
        }
        return $data;
    }

    //分割字符并且取出第一个
    public static function explodeShift($delimiter=',',$str){
        $data = '';
        $tmp = explode($delimiter,$str);
        if(is_array($tmp) and count($tmp)>0){
            $data = array_shift($tmp);
        }
        return $data;
    }

    public static function explodePop($delimiter=',',$str){
        $data = '';
        $tmp = explode($delimiter,$str);
        if(is_array($tmp) and count($tmp)>0){
            $data = array_pop($tmp);
        }
        return $data;
    }

    //压缩处理-压缩
    public static function makeCompress($str){
        return gzcompress($str, 9);
    }

    //压缩处理-解压
    public static function makeUnCompress($compressed){
        return gzuncompress($compressed);
    }

    public static function array_sort_rows($arr,$row_key='',$sort_type='asc'){
        if(!is_array($arr)){ $arr=array(); }
        if( empty($row_key) ){ return $arr; }
        $sort = array();
        foreach ($arr as $key => $row) {
            if(isset($row[$row_key])){
                $sort[$key]         =$row[$row_key];
            }
        }
        if(!empty($sort)){
            if(strtolower($sort_type)=='desc'){
                array_multisort($sort, SORT_DESC, $arr);
            }else{
                array_multisort($sort, SORT_ASC, $arr);
            }
        }
        return $arr;
    }

    public static function array_sort_two($arr,$row_key='',$sort_type='asc',$row_key2='',$sort_type2='asc'){
        if(!is_array($arr)){ $arr=array(); }
        if( empty($row_key) ){ return $arr; }
        $sort = array();
        $sort2 = array();
        foreach ($arr as $key => $row) {
            $sort[$key]         =$row[$row_key];
            $sort2[$key]            =$row[$row_key2];
        }
        array_multisort($sort, ($sort_type=='desc'?SORT_DESC:SORT_ASC), $sort2, ($sort_type2=='desc'?SORT_DESC:SORT_ASC), $arr);
        return $arr;
    }

    public static function getArrByKey($list_arr, $key_name='', $key_value=0, $need_all=0){

        $ret = array();
        if(is_array($list_arr)){
            foreach($list_arr as $k=>$v){

                if($need_all){

                    continue(1);
                }

                if(array_key_exists($key_name, $v)){
                    if($key_value == $v[$key_name]){
                        $ret[] = $v;
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * 分页分割数组数据
     *
     */
    public static function array_perpage($listsArr,$perpage,$page){
        $tmpARR=array_chunk($listsArr,$perpage);
        if(isset($tmpARR[$page-1])){
            $listsArr=$tmpARR[$page-1];
        }else{
            $count=count($tmpARR);
            if(isset($tmpARR[$count-1])){
                $listsArr=$tmpARR[$count-1];
            }else{
                $listsArr=array();
            }
        }
        unset($tmpARR);
        return $listsArr;
    }

}
