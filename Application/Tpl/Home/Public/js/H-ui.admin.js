/*H-ui.admin.js v2.3.1 date:15:42 2015.08.19 by:guojunhui*/
/*获取顶部选项卡总长度*/
function tabNavallwidth(){
	var taballwidth=0,
		$tabNav = $(".acrossTab"),
		$tabNavWp = $(".Hui-tabNav-wp"),
		$tabNavitem = $(".acrossTab li"),
		$tabNavmore =$(".Hui-tabNav-more");
	if (!$tabNav[0]){return}
	$tabNavitem.each(function(index, element) {
        taballwidth+=Number(parseFloat($(this).width()+60))});
	$tabNav.width(taballwidth+25);
	var w = $tabNavWp.width();
	if(taballwidth+25>w){
		$tabNavmore.show()}
	else{
		$tabNavmore.hide();
		$tabNav.css({left:0})}
}

/*左侧菜单响应式*/
function Huiasidedisplay(){
	if($(window).width()>=768){
		$(".Hui-aside").show()
	} 
}
function getskincookie(){
	var v = getCookie("Huiskin");
	if(v==null||v==""){
		v="default";
	}
	$("#skin").attr("href","../Public/skin/"+v+"/skin.css");
}
$(function(){
	getskincookie();
	//layer.config({extend: 'extend/layer.ext.js'});
	Huiasidedisplay();
	var resizeID;
	$(window).resize(function(){
		clearTimeout(resizeID);
		resizeID = setTimeout(function(){
			Huiasidedisplay();
		},500);
	});
	
	$(".Hui-nav-toggle").click(function(){
		$(".Hui-aside").slideToggle();
	});
	$(".Hui-aside").on("click",".menu_dropdown dd li a",function(){
        if($(window).width()<768){
            $(".Hui-aside").slideToggle();
        }
	});
	/*左侧菜单*/
	$.Huifold(".menu_dropdown dl dt",".menu_dropdown dl dd","fast",1,"click");
	/*选项卡导航*/
	
	$(".Hui-aside").on("click",".menu_dropdown a",function(){
		if($(this).attr('_href')){
			var bStop=false;
			var bStopIndex=0;
			var _href=$(this).attr('_href');
			var _titleName=$(this).html();
			var topWindow=$(window.parent.document);
			var show_navLi=topWindow.find("#min_title_list li");
			show_navLi.each(function() {
				if($(this).find('span').attr("data-href")==_href){
					bStop=true;
					bStopIndex=show_navLi.index($(this));
					return false;
				}
			});
			if(!bStop){
				creatIframe(_href,_titleName);
				min_titleList();
			}
			else{
				show_navLi.removeClass("active").eq(bStopIndex).addClass("active");
				var iframe_box=topWindow.find("#iframe_box");
				iframe_box.find(".show_iframe").hide().eq(bStopIndex).show().find("iframe").attr("src",_href);
			}
		}
        $(document).click(function(e){
        	var closeAll = $("#closeAll");
            if(closeAll[0] !== e.target && !$.contains(closeAll[0],e.target)){
                closeAll.hide();
            }
        });
        var tab = $("#Hui-tabNav ul li");
        var closeAll = $("#closeAll");
        tab.bind("contextmenu",function () {
            return false;
        });
        tab.mousedown(function (e) {
        	var self = $(this);
            if(self[0] == tab[0]) return false;
			var left = 0,offsetLeft = $("aside.Hui-aside")[0].offsetLeft;
           	if(offsetLeft == 0){
                left = e.clientX - 200
			}else{
                left = e.clientX -50;
			};
			if(e.which == 3){
				closeAll.css({display:"block",left:left});
                var closeButton = $("#closeAll .but-div")[0];
                var liSrc = self.find("span").attr("data-href").split("&");
                var liSrcPar = liSrc[liSrc.length-1];
                //关闭当前
                $(closeButton.children[0]).unbind("click").bind("click",function() {
                    $("#iframe_box iframe").each(function (index,element) {
                        var iframSrc = element.src.split("&");
                        var iframSrcPar = iframSrc[iframSrc.length-1];
                        if(liSrcPar == iframSrcPar){
                        	//判断页面往前跳 还是往后条
                        	if(self.next().length == 0){
                                $(this).parent().prev().show();
                                //判断是否激活的页面
                                if(self.hasClass("active")){
                                	self.prev().addClass("active");
                                	self.remove();
                                	$(this).parent().remove();
								}else{
                                    self.remove();
                                    $(this).parent().remove();
								}
                            }else{
                                $(this).parent().next().show();
                                if(self.hasClass("active")){
									self.next().addClass("active");
                                    $(this).parent().remove();
									self.remove();
								}else{
                                    self.remove();
                                    $(this).parent().remove();
								}
                        	}
						}
                    });
                    $("#closeAll").hide();
                });
                //关闭其他
                $(closeButton.children[1]).unbind("click").bind("click",function () {
                    $("#iframe_box iframe").each(function (index,element) {
                        var iframSrc = element.src.split("&");
                        var iframSrcPar = iframSrc[iframSrc.length-1];
                        if(liSrcPar == iframSrcPar){
                    		self.siblings().not(":first").remove();
                            $(element).parent().show().siblings().not(":first").remove();
                            if(!self.hasClass("active")){
                            	self.addClass("active");
							}
                        }
                    });
                    $("#closeAll").hide();
                });
                //关闭全部
                $(closeButton.children[2]).unbind("click").bind("click",function () {
                    self.parent().children("li:first-child").addClass("active").siblings().remove();
                    $("#iframe_box").children("div:first-child").show().siblings().remove();
                    $("#closeAll").hide();
                });
			}
        });
        $("#closeAll .cancel").click(function () {
			closeAll.hide();
		});

	});
	
	function min_titleList(){
		var topWindow=$(window.parent.document);
		var show_nav=topWindow.find("#min_title_list");
		var aLi=show_nav.find("li");
	};
	function creatIframe(href,titleName){
		var topWindow=$(window.parent.document);
		var show_nav=topWindow.find('#min_title_list');
		show_nav.find('li').removeClass("active");
		var iframe_box=topWindow.find('#iframe_box');
		show_nav.append('<li class="active"><span data-href="'+href+'">'+titleName+'</span><i></i><em></em></li>');
		tabNavallwidth();
		var iframeBox=iframe_box.find('.show_iframe');
		iframeBox.hide();
		iframe_box.append('<div class="show_iframe"><div class="loading"></div><iframe frameborder="0" src='+href+'></iframe></div>');
		var showBox=iframe_box.find('.show_iframe:visible');
		showBox.find('iframe').attr("src",href).load(function(){
			showBox.find('.loading').hide();
		});
	}

	var num=0;
	var oUl=$("#min_title_list");
	var hide_nav=$("#Hui-tabNav");
	$(document).on("click","#min_title_list li",function(){
		var bStopIndex=$(this).index();
		var iframe_box=$("#iframe_box");
		$("#min_title_list li").removeClass("active").eq(bStopIndex).addClass("active");
		iframe_box.find(".show_iframe").hide().eq(bStopIndex).show();
	});
	$(document).on("click","#min_title_list li i",function(){
		var aCloseIndex=$(this).parents("li").index();
		$(this).parent().remove();
		$('#iframe_box').find('.show_iframe').eq(aCloseIndex).remove();	
		num==0?num=0:num--;
		tabNavallwidth();
	});
	$(document).on("dblclick","#min_title_list li",function(){
		var aCloseIndex=$(this).index();
		var iframe_box=$("#iframe_box");
		if(aCloseIndex>0){
			$(this).remove();
			$('#iframe_box').find('.show_iframe').eq(aCloseIndex).remove();	
			num==0?num=0:num--;
			$("#min_title_list li").removeClass("active").eq(aCloseIndex-1).addClass("active");
			iframe_box.find(".show_iframe").hide().eq(aCloseIndex-1).show();
			tabNavallwidth();
		}else{
			return false;
		}
	});
	tabNavallwidth();
	
	$('#js-tabNav-next').click(function(){
		num==oUl.find('li').length-1?num=oUl.find('li').length-1:num++;
		toNavPos();
	});
	$('#js-tabNav-prev').click(function(){
		num==0?num=0:num--;
		toNavPos();
	});
	
	function toNavPos(){
		oUl.stop().animate({'left':-num*100},100);
	}
	
	/*换肤*/
	$("#Hui-skin .dropDown-menu a").click(function(){
		var v = $(this).attr("data-val");
		setCookie("Huiskin", v);
		$("#skin").attr("href","../Public/skin/"+v+"/skin.css");
	});
}); 
/*弹出层*/
/*
	参数解释：
	title	标题
	url		请求的url
	id		需要操作的数据id
	w		弹出层宽度（缺省调默认值）
	h		弹出层高度（缺省调默认值）
*/
function layer_show(title,url,w,h){
	if (title == null || title == '') {
		title=false;
	};
	if (url == null || url == '') {
		url="404.html";
	};
	if (w == null || w == '') {
		w=800;
	};
	if (h == null || h == '') {
		h=($(window).height() - 50);
	};
	layer.open({
		type: 2,
		area: [w+'px', h +'px'],
		fix: false, //不固定
		maxmin: true,
		shade:0.4,
		title: title,
		content: url
	});
}
/*关闭弹出框口*/
function layer_close(){
	var index = parent.layer.getFrameIndex(window.name);
	parent.layer.close(index);
}
function opennewtab(o,title){
	if($(o).attr('_href')){
		var bStop=false;
		var bStopIndex=0;
		var _href=$(o).attr('_href');
		var _titleName=$(o).html();
		var topWindow=$(window.parent.document);
		var show_navLi=topWindow.find("#min_title_list li");
		show_navLi.each(function() {
			if($(this).find('span').attr("data-href")==_href){
				bStop=true;
				bStopIndex=show_navLi.index($(o));
				return false;
			}
		});
		if(!bStop){
			var topWindow=$(window.parent.document);
			var show_nav=topWindow.find('#min_title_list');
			show_nav.find('li').removeClass("active");
			var iframe_box=topWindow.find('#iframe_box');
			show_nav.append('<li class="active"><span data-href="'+_href+'">'+title+'</span><i></i><em></em></li>');

			var topWindow=$(window.parent.document);
			var taballwidth=0,
					$tabNav = topWindow.find('.acrossTab'),
					$tabNavWp = topWindow.find(".Hui-tabNav-wp"),
					$tabNavitem = topWindow.find(".acrossTab li"),
					$tabNavmore =topWindow.find(".Hui-tabNav-more");
			if (!$tabNav[0]){return}
			$tabNavitem.each(function(index, element) {
				taballwidth+=Number(parseFloat($(this).width()+60))});
			$tabNav.width(taballwidth+25);
			var w = $tabNavWp.width();
			if(taballwidth+25>w){
				$tabNavmore.show()}
			else{
				$tabNavmore.hide();
				$tabNav.css({left:0})}
			var iframeBox=iframe_box.find('.show_iframe');
			iframeBox.hide();
			iframe_box.append('<div class="show_iframe"><div class="loading"></div><iframe frameborder="0" src='+_href+'></iframe></div>');
			var showBox=iframe_box.find('.show_iframe:visible');
			showBox.find('iframe').attr("src",_href).load(function(){
				showBox.find('.loading').hide();
			});

		}
		else{
			show_navLi.removeClass("active").eq(bStopIndex).addClass("active");
			var iframe_box=topWindow.find("#iframe_box");
			iframe_box.find(".show_iframe").hide().eq(bStopIndex).show().find("iframe").attr("src",_href);
		}
	}
}