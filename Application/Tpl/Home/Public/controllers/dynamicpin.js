myApp.controller('dynamicpin', ['$scope', '$http', function ($, $http) {
    $.data = {
        "state": "1",
        "start_date": '2017/01/01',
        "end_date": '2017/07/07',
        "upd_date": '1',
        "firstRow": 0,
        "count": 0,
        "all_existing": 0,
        "all_sum": 0,
    }
    $.list = []
    $.res_key = $.date_diff = ''
    $.datelist = ['3Day', 'One Week', 'Two Week', 'One Month', 'Two Month', 'Three Month', 'Six Month']
    $.gostate = $.data.state
    $.$watch('data', function (newValue, oldValue) {
        if (newValue != oldValue) {
            // console.log(newValue)
        }
    }, true)
    $.sub = function () {
        var data = $.data
        $http.post('/index.php?m=Dynamic&a=getdata', JSON.stringify(data)).then(function (response) {

            if (response.data != 'null') {
                $.list = response.data.list
                $.data.count = response.data.count
                $.data.all_existing = response.data.all_existing
                $.data.all_sum = response.data.all_sum
                $.res_key = response.data.res_key
                $.date_diff = response.data.date_diff
                $.show = response.data.show
            } else {
                $.list = []
            }
            $.gostate = response.data.state
        })
    }
    $.upd_date = function (e, event) {
        $.data.end_date = new Date().toJSON().slice(0, 10);
        $.data.upd_date = e
        switch (e) {
            case 0:
                $.data.start_date = $.GetDateStr(-3)
                break;
            case 1:
                $.data.start_date = $.GetDateStr(-7)
                break;
            case 2:
                $.data.start_date = $.GetDateStr(-14)
                break;
            case 3:
                $.data.start_date = $.GetDateStr(-2, 'm')
                break;
            case 4:
                $.data.start_date = $.GetDateStr(-3, 'm')
                break;
            case 5:
                $.data.start_date = $.GetDateStr(-4, 'm')
                break;
            case 6:
                $.data.start_date = $.GetDateStr(-7, 'm')
                break;

        }
    }
    $.GetDateStr = function (d, m) {
        var dd = new Date();
        if (m == 'm') {
            dd.setMonth(dd.getMonth() + 1 + d);
        } else {
            dd.setDate(dd.getDate() + d);
        }
        var y = dd.getYear() + 1900;
        var m = (dd.getMonth() + 1) < 10 ? "0" + (dd.getMonth() + 1) : (dd.getMonth() + 1);
        var d = dd.getDate() < 10 ? "0" + dd.getDate() : dd.getDate();
        return y + "-" + m + "-" + d;
    }
    $.king = function(e){
        if(e){
            var k = e.toString().split('.')
            if(e.toString().indexOf('.') > 0){
                var s = '.'+k[1]
            }else{
                var s = ''
            }
            return k[0].toString().replace(/\d{1,3}(?=(\d{3})+(\.\d*)?$)/g, '$&,')+s;
        }else {
            return e
        }

    },
    $.upd_date(0)
}])

