$(document).ready(function() {
    $(".ck-wrap tbody tr>td:last-child .edit-btn:first-child").click(function () {
        var self = $(this);
        console.log($(this)[0]);
        if(self.text() === "修改"){
            self.text("保存").css("background","#33CCFF").siblings('span').text("取消").css("background","grey");

            self.parent().siblings('td').find('span').text('');
            self.parents("tr").find("input[type='text']").show();
        }else{
            self.text("修改").css("background","#EC2D64").siblings('span').text("封存").css("background","black");

            self.parent().siblings('td').find('span').text('1');
            self.parents("tr").find("input[type='text']").hide();
        }
    });
    $(".ck-wrap tbody tr>td:last-child .edit-btn:last-child").click(function () {
        var self = $(this);
        if(self.text() === "封存"){
            alert("封存阿西吧");
        }else{
            self.text("封存").css("background","black").siblings('span').text("修改").css("background","#EC2D64");
            self.parents("tr").find("input[type='text']").hide();
        }
    });
});
