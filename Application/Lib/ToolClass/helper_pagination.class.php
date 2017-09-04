<?php
/**
 * 类，自定义分页类
 * 
 * 
 * 
 */
class helper_pagination{
	var $total=0;		//总计数量
	var $perpage=10;	//分页数
	var $nowpage=1;		//显示页
	var $areapage=2;	//区域
	var $classArr=array('Prev'=>'PrevPage','Next'=>'NextPage','link'=>'pagelink','more'=>'morepage','current'=>'currentpage',);
	var $pagecount=0;	//分页总数
	var $page_name='page';	//分页的名称
	var $pageHtml='';
	var $url='';
	public $css_style = '<style>
.pagination-ctn .pc{
	margin-top:30px;
	margin-bottom:20px;
}

.pagination-ctn .pc,.pagination-ctn a,.pagination-ctn .form,.pagination-ctn .desc,.pagination-ctn .form span,.pagination-ctn .form input,.pagination-ctn .form .go,.bg-info .marker {
    display: inline-block;
    *display: inline;
    *zoom: 1;
    vertical-align: middle;
}

.pagination-ctn {
    padding: 1px;
    text-align: center;
}

.pagination-ctn .pn,.pagination-ctn .current {
    font-family: SumSin,微软雅黑,Microsoft YaHei;
}

.pagination-ctn .pn,.pagination-ctn .current {
    padding: 0 15px;
    background-color: #fff;
    color: #666;
    border: 1px solid #d3d3d3;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    -ms-border-radius: 3px;
    -o-border-radius: 3px;
    border-radius: 3px;
    margin: 0 2px;
}

.pagination-ctn .current {
    border-color: #f95172 !important;
    background: #f95172 !important;
    color: #fff !important;
    cursor: default;
}

.pagination-ctn .pn {
    width: auto;
    height: 38px;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
    line-height: 38px;
    text-align: center;
    background: #f5f5f5;
}

.pagination-ctn .pn:hover {
    background-color: #fff;
}

.pagination-ctn .pn:active {
    background-color: #f2f2f2;
    outline: thin dotted;
    outline-offset: -2px;
}

.pagination-ctn .pn:hover {
    background: #f95172;
    color: #fff;
}

.pagination-ctn .pn:active {
    background: #f59000;
    color: #fff;
}

.pagination-ctn a {
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    height: 38px;
    line-height: 38px;
}

.pagination-ctn .plain {
    border: 0;
    color: #666;
    cursor: default;
    padding: 0 8px;
}

.pagination-ctn .plain,.pagination-ctn .current {
    cursor: default;
}

.pagination-ctn .plain:focus,.pagination-ctn .current:focus {
    outline: 0;
}

.pagination-ctn .disabled {
    color: #ddd;
}

.pagination-ctn .disabled:hover {
    background: #fff;
    color: #ddd;
    cursor: default;
    cursor: not-allowed;
}

.pagination-ctn .form span,.pagination-ctn .form input,.pagination-ctn .form .go {
    height: 36px;
    line-height: 36px;
}

.pagination-ctn .form input {
    width: 45px;
    text-align: center;
    border: 1px solid #eb8226;
    border-right: 0;
    -webkit-border-radius: 3px 0 0 3px;
    -moz-border-radius: 3px 0 0 3px;
    -ms-border-radius: 3px 0 0 3px;
    -o-border-radius: 3px 0 0 3px;
    border-radius: 3px 0 0 3px;
}

.pagination-ctn .form .go {
    padding: 0 10px;
    margin: 0;
    margin-right: 2px;
    line-height: 36px;
    border: 1px solid #f95172;
    width: auto;
    height: auto;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
    line-height: auto;
    text-align: center;
    background: #f95172;
    -webkit-border-radius: 0 3px 3px 0;
    -moz-border-radius: 0 3px 3px 0;
    -ms-border-radius: 0 3px 3px 0;
    -o-border-radius: 0 3px 3px 0;
    border-radius: 0 3px 3px 0;
    background: #f95172;
    color: #fff;
}

.pagination-ctn .form .go:hover {
    background-color: #ffa729;
}

.pagination-ctn .form .go:active {
    background-color: #f59000;
    outline: thin dotted;
    outline-offset: -2px;
}

.pn_easy {
    width:25px;
    height: 38px;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
    line-height: 38px;
    text-align: center;
    background: #fff;
    border: 1px solid #d3d3d3;
    border-radius: 3px;
    color: #666;
    margin: 0 2px;
    padding: 0 5px;
}
</style>';


	/*  初始化  */
	function __construct($dataArr=array() ,$styleArr=array()){
		$this->helper_pagination($dataArr ,$styleArr);
	}
	function helper_pagination($dataArr ,$styleArr){
		if(isset($styleArr['Prev'])){
			$this->classArr['Prev']=$styleArr['Prev'];
		}
		if(isset($styleArr['Next'])){
			$this->classArr['Next']=$styleArr['Next'];
		}
		if(isset($styleArr['link'])){
			$this->classArr['link']=$styleArr['link'];
		}
		if(isset($styleArr['more'])){
			$this->classArr['more']=$styleArr['more'];
		}
		if(isset($styleArr['current'])){
			$this->classArr['current']=$styleArr['current'];
		}

		if(isset($dataArr['total'])){
			$this->total=intval($dataArr['total']);
		}
		if(isset($dataArr['perpage'])){
			$this->perpage=intval($dataArr['perpage']);
		}
		if(isset($dataArr['nowpage'])){
			$this->nowpage=intval($dataArr['nowpage']);
		}elseif(isset($dataArr['page_name'])){
			$this->page_name=trim($dataArr['page_name']);
			$this->nowpage=intval(isset($_REQUEST[$this->page_name])?$_REQUEST[$this->page_name]:1);
		}
		if(isset($dataArr['areapage'])){
			$this->areapage=intval($dataArr['areapage']);
		}
		
		$this->pagecount=intval(($this->total-1)/$this->perpage)+1;
		$this->nowpage=($this->nowpage<1)?1:$this->nowpage;
		$this->nowpage=($this->nowpage>$this->pagecount)?$this->pagecount:$this->nowpage;

		//处理url
		if(empty($_SERVER['QUERY_STRING'])){
			$this->url=rtrim($_SERVER['REQUEST_URI'],'?')."?".$this->page_name."=";

		}else{
			$this->url=$_SERVER['REQUEST_URI'];
			if(strpos($this->url,'?'.$this->page_name.'=')!==false){
				$this->url=preg_replace(
						array("/[\?&]".$this->page_name."=[^&]*([&]?)(.*?)$/is",),
						array('?\\2'),
						$this->url
					);
				if(substr($this->url,-1)=='?'){
					$this->url.=''.$this->page_name.'=';
				}else{
					$this->url.='&'.$this->page_name.'=';
				}
			}else{
				$this->url=preg_replace(
						array("/[\?&]".$this->page_name."=[^&]*([&]?)/is",),
						array('\\1'),
						$this->url
					);
				if(strpos($this->url,'?')===false){
					$this->url.='?'.$this->page_name.'=';
				}else{
					$this->url.='&'.$this->page_name.'=';
				}
			}
		}

	}

	//整理分页
	function getPageHtml(){
		$html='';
		if($this->total==0 or $this->pagecount==0){
			$html='<a class="'.$this->classArr['link'].' '.$this->classArr['current'].'" title="0">0</a> ';
			return $html;
		}
		
		$current='';
		if($this->nowpage-$this->areapage<=2){
			for($i=1;$i<=$this->nowpage;$i++){
				if($i==$this->nowpage){
					$current=' '.$this->classArr['current'];
					$html.='<a class="'.$this->classArr['link'].''.$current.'" title="'.$i.'">'.$i.'</a> ';
				}else{
					$html.='<a href="'.$this->url.($i).'" class="'.$this->classArr['link'].''.$current.'" title="'.$i.'">'.$i.'</a> ';
				}
			}
		}else{
			$html.='<a href="'.$this->url.'1" class="'.$this->classArr['link'].''.$current.'" title="1">1</a> ';
			$html.='<span class="'.$this->classArr['more'].'" title="more">...</span> ';
			$i=$this->nowpage-$this->areapage;
			for($i;$i<=$this->nowpage;$i++){
				if($i==$this->nowpage){
					$current=' '.$this->classArr['current'];
					$html.='<a class="'.$this->classArr['link'].''.$current.'" title="'.$i.'">'.$i.'</a> ';
				}else{
					$html.='<a href="'.$this->url.($i).'" class="'.$this->classArr['link'].''.$current.'" title="'.$i.'">'.$i.'</a> ';
				}
			}
		}
		
		if($this->nowpage>=$this->pagecount){
			return $html;
		}
		
		$current='';
		if($this->nowpage+$this->areapage>=$this->pagecount-1){
			$i=$this->nowpage+1;
			for($i;$i<=$this->pagecount;$i++){
				$html.='<a href="'.$this->url.($i).'" class="'.$this->classArr['link'].''.$current.'" title="'.$i.'">'.$i.'</a> ';
			}
		}else{
			$i=$this->nowpage+1;
			$i_end=$this->nowpage+$this->areapage;
			for($i;$i<=$i_end;$i++){
				$html.='<a href="'.$this->url.($i).'" class="'.$this->classArr['link'].''.$current.'" title="'.$i.'">'.$i.'</a> ';
			}
			$html.='<span class="'.$this->classArr['more'].'" title="more">...</span> ';
			$html.='<a href="'.$this->url.($this->pagecount).'" class="'.$this->classArr['link'].''.$current.'" title="'.$this->pagecount.'">'.$this->pagecount.'</a> ';
		}

		return $html;
	}
	
	//显示分页
	function show($use_perpage=false){
		$tmp='';
		$tmp=$this->getPageHtml();
		if($this->nowpage>1){
			$tmp='<a href="'.$this->url.($this->nowpage-1).'" class="'.$this->classArr['Prev'].'" title="">&nbsp;&lt;&nbsp;</a> '.$tmp;
		}
		if($this->nowpage<$this->pagecount){
			$tmp.='<a href="'.$this->url.($this->nowpage+1).'" class="'.$this->classArr['Next'].'" title="">&nbsp;&gt;&nbsp;</a> ';
		}
		
		if($use_perpage==true){
			$this->url=preg_replace('/&perpage=[^&]+/is','',$this->url);
			$this->url=preg_replace('/\?perpage=[^&]*[&]?/is','?',$this->url);
			$perpage=isset($_REQUEST['perpage'])?intval($_REQUEST['perpage']):'';
			if($perpage==''){
				$perpage=$this->perpage;
			}
			$pageHtmlTow='<select name="perpage2" onchange="javascript:perpage_onchange(this.options[this.selectedIndex].value);" class="'.$this->classArr['Next'].'">
				<option value="10" '.($perpage==10?'selected="selected"':'').'>10</option>
				<option value="20" '.($perpage==20?'selected="selected"':'').'>20</option>
				<option value="50" '.($perpage==50?'selected="selected"':'').'>50</option>
				<option value="100" '.($perpage==100?'selected="selected"':'').'>100</option>
				<option value="200" '.($perpage==200?'selected="selected"':'').'>200</option>
				<option value="500" '.($perpage==500?'selected="selected"':'').'>500</option>
			</select>
			<script type="text/javascript">
				function perpage_onchange(num){
					location.href="'.$this->url.'&perpage="+num;
				}
			</script>
			';
			$tmp.=$pageHtmlTow;
		}
		return $tmp;
	}

	//显示分页
	function show_html($use_perpage=1){
		$tmp='';
		$tmp=$this->getPageHtml();
		if($this->nowpage>1){
			$tmp='<a href="'.$this->url.($this->nowpage-1).'" class="'.$this->classArr['link'].' '.$this->classArr['Prev'].'" title="">&nbsp;上一页&nbsp;</a> '.$tmp;
		}
		if($this->nowpage<$this->pagecount){
			$tmp.='<a href="'.$this->url.($this->nowpage+1).'" class="'.$this->classArr['link'].' '.$this->classArr['Next'].'" title="">&nbsp;下一页&nbsp;</a> ';
		}

		$htm_num = '';
		if($use_perpage==1){
			$htm_num = ' <span>共 '.$this->pagecount.'页 到第<input type="text" value="" class="pn_easy" name="page" id="web_pageNumber" style="">页 </span>'.
				'<a id="web_gotoBtn" class="go pn" href="javascript:;">确定</a>';
            $desc_html = '<input type="hidden" id="web_maxCount" name="" value="'.$this->pagecount.'" >';
            $js_html =<<<JJJ
<script type="text/javascript">
var total_size = 0;
try {
setTimeout(function () {
    document.getElementById('web_gotoBtn').onclick = function(){
        var number = parseInt(document.getElementById('web_pageNumber').value);
        var max = parseInt(document.getElementById('web_maxCount').value);
        if(number>=1 && number<=max){
        }else{
            if(number>0)  number=1;
        }
        if(number>=1 && number<=max){
            var url = document.URL;
            var newurl = "";
            var arr1 = new Array();
            arr1 = url.split('?');
            if(arr1[1]){
                var arr2 = new Array();
                arr2 = arr1[1].split('&');
                if(!arr2[1]){
                    newurl = url+'&pppppage='+number;
                	if(!arr2[0].indexOf('pppppage=')){
                		newurl = arr1[0] + '?' + 'pppppage='+number;
                	}
                }else{
                    var hasPage = false;
                    for(var i=0;i<arr2.length;i++){
                        if(!arr2[i].indexOf('pppppage=')){
                           arr2[i] = 'pppppage='+number;
                           hasPage = true;
                        }
                    }
                    var newparams = arr2.join('&');
                    if(!hasPage){
                        newparams = newparams + '&pppppage='+number;
                    }
                    newurl = arr1[0] + '?' + newparams; 
                }
            }else{
                newurl = arr1[0]+"?pppppage="+number;
            }
            location.href = newurl;       
        }else{
            return false;            
        }
    };
}, 2e3);
} catch (e) {}
</script>
JJJ;
            $js_html = str_replace('pppppage',$this->page_name,$js_html);
            $htm_num .= $desc_html.$js_html;
		}

		$tmp = $this->css_style.$tmp;
		$tmp = '<div class="pagination2 pagination-ctn"><div class="pc">'.$tmp.$htm_num.'</div></div>';
		return $tmp;
	}


	/*************************************************************************
	php easy :: pagination scripts set - Version Two
	==========================================================================
	Author:      php easy code, www.phpeasycode.com
	Web Site:    http://www.phpeasycode.com
	Contact:     webmaster@phpeasycode.com
	*************************************************************************/
	function paginate_two($reload, $page, $tpages, $adjacents) {
		
		$firstlabel = "&laquo;&nbsp;";
		$prevlabel  = "&lsaquo;&nbsp;";
		$nextlabel  = "&nbsp;&rsaquo;";
		$lastlabel  = "&nbsp;&raquo;";
		
		$out = "<div class=\"pagin\">\n";
		
		// first
		if($page>($adjacents+1)) {
			$out.= "<a href=\"" . $reload . "\">" . $firstlabel . "</a>\n";
		}
		else {
			$out.= "<span>" . $firstlabel . "</span>\n";
		}
		
		// previous
		if($page==1) {
			$out.= "<span>" . $prevlabel . "</span>\n";
		}
		elseif($page==2) {
			$out.= "<a href=\"" . $reload . "\">" . $prevlabel . "</a>\n";
		}
		else {
			$out.= "<a href=\"" . $reload . "&amp;page=" . ($page-1) . "\">" . $prevlabel . "</a>\n";
		}
		
		// 1 2 3 4 etc
		$pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
		$pmax = ($page<($tpages-$adjacents)) ? ($page+$adjacents) : $tpages;
		for($i=$pmin; $i<=$pmax; $i++) {
			if($i==$page) {
				$out.= "<span class=\"current\">" . $i . "</span>\n";
			}
			elseif($i==1) {
				$out.= "<a href=\"" . $reload . "\">" . $i . "</a>\n";
			}
			else {
				$out.= "<a href=\"" . $reload . "&amp;page=" . $i . "\">" . $i . "</a>\n";
			}
		}
		
		// next
		if($page<$tpages) {
			$out.= "<a href=\"" . $reload . "&amp;page=" .($page+1) . "\">" . $nextlabel . "</a>\n";
		}
		else {
			$out.= "<span>" . $nextlabel . "</span>\n";
		}
		
		// last
		if($page<($tpages-$adjacents)) {
			$out.= "<a href=\"" . $reload . "&amp;page=" . $tpages . "\">" . $lastlabel . "</a>\n";
		}
		else {
			$out.= "<span>" . $lastlabel . "</span>\n";
		}
		
		$out.= "</div>";
		
		return $out;
	}

    //
    function show_perpage_block($perpage_name='page_num', $perpage=10){
        $tmp='';
        $tmp = '
        <style>
        .block_p_s{
            background-color: #ddd;
            border: 0px solid #ddd;
            color: #337ab7;
            float: left;
            line-height: 1.42857;
            margin-left: -1px;
            padding: 6px 12px;
            position: relative;
            text-decoration: none;
            margin: 6px 6px;
        }
        .block_p_s_active{
            background-color: #337ab7;
            border-color: #337ab7;
            color: #fff;
            cursor: default;
        }
        .block_p_s_active a{
            color: #fff;
        }
        </style>
        ';
        if($perpage_name){
            $this->url=preg_replace('/\&'.$perpage_name.'=[^&]+/is','',$this->url);
            $this->url=preg_replace('/\?'.$perpage_name.'=[^&]*[&]?/is','?',$this->url);

            if($perpage==''){
                $perpage=$this->perpage;
            }

            $target_url = $this->url.'&'.$perpage_name.'=';

            $tmp .= '<span class="block_p_s '.($perpage==10?' block_p_s_active ':'').'" style=""><a href="'.$target_url.'10" class="">10</a></span>';
            $tmp .= '<span class="block_p_s '.($perpage==20?' block_p_s_active ':'').'" style=""><a href="'.$target_url.'20" class="">20</a></span>';
            $tmp .= '<span class="block_p_s '.($perpage==50?' block_p_s_active ':'').'" style=""><a href="'.$target_url.'50" class="">50</a></span>';
            $tmp .= '<span class="block_p_s '.($perpage==100?' block_p_s_active ':'').'" style=""><a href="'.$target_url.'100" class="">100</a></span>';

        }
        return $tmp;
    }









}

