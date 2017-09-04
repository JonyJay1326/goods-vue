/**
 * Created by b5m on 2016/7/28.
 */
$(function(){
    $("#login").click(function(){
        var username = $("#username").val();
        var password = $("#password").val();

        var nusername = username.replace(/\s+/g,"");
        var npassword = password.replace(/\s+/g,"");

        if (nusername == '') {
            layer.msg('Please fill in the username');
            $('#username').focus();
            return false;
        }

        if (npassword == '') {
            layer.msg('Please fill in the password');
            $('#password').focus();
            return false;
        }

        var is_remember = document.getElementsByName('is_remember')[0].checked;
        is_remember = is_remember?1:0;

        $.ajax({
            url:requestUrl,
            type:'post',
            data:{username:nusername,password:npassword,is_remember:is_remember},
            dataType:'json',
            success:function(r) {
                if (r.status == 1) {
                    layer.msg(r.info);
                    setTimeout(function(){
                        window.location.href = backUrl;
                    },1000);

                } else {
                    layer.msg(r.info);
                    return false;
                }
            }
        })
    })
    $(".remember #is_remember").click(function () {
        if($(this).prop("checked")){
            $(this).prev().addClass("remember_checkbox_active");
        }else{
            $(this).prev().removeClass("remember_checkbox_active");
        }
    })
})
