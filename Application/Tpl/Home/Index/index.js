//打印界面功能
function Print() {
    var hrefStr = "",frameSrc,contentHtml;
    $("#min_title_list li").each(function (key,value) {
        var findActive = $(value).hasClass('active');
        if(findActive){
            hrefStr = $(value).find("span").data("href")
        }
    });

    $(document.getElementsByTagName("iframe")).each(function (key,value) {
        frameSrc  = $(value).attr('src');
        if(frameSrc === hrefStr){
            contentHtml = $(value.contentDocument).find("html").html()
        }
    });
    if(contentHtml){
        var printCont = window.open("print.htm","print");
        printCont.document.write(contentHtml);
        printCont.document.close();
        setTimeout(function(){
            printCont.print();
        },300);
    }
}
/*资讯-添加*/
function article_add(title,url){
    var index = layer.open({
        type: 2,
        title: title,
        content: url
    });
    layer.full(index);
}
/*图片-添加*/
function picture_add(title,url){
    var index = layer.open({
        type: 2,
        title: title,
        content: url
    });
    layer.full(index);
}
/*产品-添加*/
function product_add(title,url){
    var index = layer.open({
        type: 2,
        title: title,
        content: url
    });
    layer.full(index);
}
/*用户-添加*/
function member_add(title,url,w,h){
    layer_show(title,url,w,h);
}

/*管理员-编辑*/
function admin_edit(title,url,id,w,h){
    layer_show(title,url,w,h);
}

function displaynavbar(obj){
    if($(obj).hasClass("open")){
        $(obj).removeClass("open");
        $("body").removeClass("big-page");
        $(".slideBar").hide();
        $(".slideBar dl").remove();
    }else{
        $(obj).addClass("open");
        $("body").addClass("big-page");
        var  icon = $("aside dt i:first-child").text();
        var  iconClass = $("aside dt i:first-child");
        var iconHtml = "";
        for (var i=0,len=icon.length;i<len;i++){
            iconHtml +="<dl><dt><i class="+iconClass[i].className+">"+icon[i]+"</i></dt></dl>";
        }
        $(".slideBar").append(iconHtml).show(500).click(function () {
            $(obj).removeClass("open");
            $("body").removeClass("big-page");
            $(".slideBar").hide();
            $(".slideBar dl").remove();
        })
        $("section ul li").each(function(){
            if($(this).prop("class") === "active"){
                var active = $(this);
                $("aside dl a").each(function () {
                    var child = $(this);
                    if(active.text() === child.text()){
                        var showSlideIcon = child.parents("dd").prev().find("i:first-child").text();
                        $(".slideBar dt i").each(function () {
                            var slideBarIcon = $(this);
                            if(slideBarIcon.text() === showSlideIcon){
                                slideBarIcon.css("color","#3bb4f2")
                            }
                        });
                    }
                });
            }

        });
    }
}
$().ready(function(){
    $("aside dd ul li").click(function () {
        setTimeout(function () {
            $("section ul li").click(function () {
                var activeLi = $(this);
                $("aside dl a").each(function (){
                    if(activeLi.text() === $(this).text()){
                        var activeIcon = $(this).parents("dd").prev().find("i:first-child").text();
                        $(".slideBar dt i").each(function () {
                            var slideBarIcon = $(this);
                            if(slideBarIcon.text() === activeIcon){
                                slideBarIcon.css("color","#3bb4f2");
                                slideBarIcon.parents("dl").siblings().find("i").css("color","#E8E6E6");
                            }
                        });
                    }
                });
            })
        },500);
    });
   $(".Hui-aside .menu_dropdown").on("click","#menu-article dd ul li",function () {
       $(this).siblings().removeClass("active")
       $(this).addClass("active")
   })
    $(".navbar_userInfo").hover(function () {
        $(this).find("dl").show();
        $(this).find("span i").css({"transform":"rotateY(90deg)","transition":"2s"})
    },function () {
        $(this).find("dl").hide()
        $(this).find("span i").css({"transform":"rotateY(0deg)"})
    })
    var WindowHref=window.location.href;
    WindowHref=WindowHref.substring(WindowHref.length-5,WindowHref.length)
    WindowHref=WindowHref.replace(/(^\s+)|(\s+$)/g,"")
    if(WindowHref=="en-us"){
        $(".Hui-userbar .navbar_userInfo dl").css("left","-113%")
    }
    else if(WindowHref=="zh-cn"){
        $(".Hui-userbar .navbar_userInfo dl").css("left","-18px")
    }
});