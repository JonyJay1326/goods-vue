    box = {
        Alert:function (title,val) {
            generTeHtml("alert",title,val);
            confirm();
        },
        Confirm:function (title,val,callback) {
            generTeHtml("Confirm",title,val);
            confirm(callback);
            cancel();
        }
    };
    var generTeHtml = function (type,title,val) {
        var _html = '';
        _html += '<div id="box-bg"><div class="box-wrap" style="z-index: 999">';
        _html += '<div class="title">'+title+'</div>';
        _html += '<div class="context">'+val+'</div>';
        if(type==='alert'){
            _html += '<div class="btn-wrap"><button class="btn-confirm">确认</button></div>';
        } else {
            _html += '<div class="btn-wrap"><button class="btn-confirm">确认</button><button class="btn-cancel">取消</button></div>';
        }
        _html += '</div></div>';
        $("body").append(_html);
        generateCss();
    };
    var generateCss = function () {
        $("#box-bg").css({position: 'absolute',background: 'rgba(34, 34, 34, 0.2)', top: '0', left: '0',width: '100%', height: '100%'});
        $("#box-bg .box-wrap").css({
            boxSizing: 'border-box', minHeight: '230px', minWidth: '380px', background: 'white',
            margin: '0 auto', position: 'absolute', top :'50%',left:'38%',borderRadius: '8px',
            fontFamily: '"微软雅黑 Regular", "微软雅黑"', fontSize: '15px',
        });
        $("#box-bg .title").css({
            boxSizing: 'border-box', padding: '10px 0', width: '100%', textAlign: 'center', background: '#ED2D65',
            borderRadius: '8px 8px 0 0', color: 'white', fontSize: '16px', fontWeight: '500'
        });
        $("#box-bg .context").css({boxSizing: 'border-box', width: '100%', padding: '20px 10px', textAlign: 'center'});
        $("#box-bg .btn-wrap").css({width:'100%', position: 'absolute', bottom: '20px', textAlign: 'center'});
        $("#box-bg .btn-confirm").css({
            display: 'inline-block', padding: '3px 25px', color: 'white', border: '1px solid #ED2D65',
            background: '#ED2D65', borderRadius: '3px', margin: '0 20px', cursor: 'pointer', fontSize: '15px', fontFamily: '"微软雅黑 Regular", "微软雅黑"'
        });
        $("#box-bg .btn-cancel").css({
            display: 'inline-block', padding: '3px 25px', color: '#ED2D65', border: '1px solid #ED2D65',
            background: 'white', borderRadius: '3px', margin: '0 20px', cursor: 'pointer', fontSize: '15px', fontFamily: '"微软雅黑 Regular", "微软雅黑"'
        });
    };
    var confirm = function(callback){
        $("#box-bg .btn-confirm").click(function () {
            $("#box-bg").remove();
             setTimeout(function(){if (typeof (callback) == 'function') {
                 callback();}},10);
        });
    };
    var cancel = function(){
        $("#box-bg .btn-cancel").click(function () {
            $("#box-bg").remove();
        });
    };