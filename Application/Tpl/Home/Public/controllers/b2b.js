/**===============================================================================
 * b2b 控制器
 * ==============================================================================*/

myApp.controller('b2b', ['$scope', '$Ajax', function ($, $Ajax) {
    // $.data = {
    //     "state": "1",
    //     "start_date": '2017/01/01',
    //     "end_date": '2017/07/07',
    //     "upd_date": '1',
    //     "page": '',
    //     "count": 0,
    //     "all_existing": 0,
    //     "all_sum": 0
    // };
    $.initData = {
        order_fh: 0,
        warehouse_state: 0,
        order_sk: 0,
        order_ts: 0,
        now_state: 0,
        PO_ID: '',
        CLIENT_NAME: '',
        SALES_TEAM: '',
        BILLING_CYCLE_STATE: '',
        goods_title_info: '',
        po_time_action: '',
        po_time_end: '',
        page: 10,
        count: 100,
    }
    $.data = [];
    $.search = function (data, p) {
        p = typeof p !== 'undefined' ? p : 1;
        var postdata = $.initData
        if (data == 1) postdata = null
        if (postdata) {
            var po_time_action = document.getElementById('po_time_action').value
            var po_time_end = document.getElementById('po_time_end').value
            if (po_time_action) postdata.po_time_action = this.formatDate(po_time_action)
            if (po_time_end) postdata.po_time_end = this.formatDate(po_time_end)
        }
        $Ajax.post("/index.php?m=b2b&a=show_list&p=" + p, postdata, function (result) {
            if (data == 1) {
                $.data = result.data
                if (!result.data.order) $.data.order = []
            } else {
                $.data.order = result.data.order
                $.data.count = result.data.count
            }
            $.initData.count = result.data.count
        });
    };
    $.reset = function () {
        $.initData = {
            order_fh: 0,
            warehouse_state: 0,
            order_sk: 0,
            order_ts: 0,
            now_state: 0,
            PO_ID: '',
            CLIENT_NAME: '',
            SALES_TEAM: '',
            BILLING_CYCLE_STATE: '',
            goods_title_info: '',
            po_time_action: '',
            po_time_end: '',
        }
        document.getElementById('po_time_action').value = null
        document.getElementById('po_time_end').value = null
    }

    // action button
    $.upd_date = function (e, i) {
        this.initData[e] = i
    }

    $.formatDate = function (date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }
    $.king = function (e) {
        if (e) {
            var k = e.toString().split('.')
            if (e.toString().indexOf('.') > 0) {
                var s = '.' + k[1]
            } else {
                var s = ''
            }
            return k[0].toString().replace(/\d{1,3}(?=(\d{3})+(\.\d*)?$)/g, '$&,') + s;
        } else {
            return e
        }
    }
    $.search(1)
}]);

/**===============================================================================
 * b2bAdd  新建订单控制器      $Ajax：封装的公共 $http方法
 * ==============================================================================*/

myApp.controller('b2bAdd', ['$scope', '$Ajax', function ($, $Ajax) {
    /**=========================================初始加载数据===============================================*/

    $.initData = {
        country: [],                     //国家
        city: [],                        //城市
        allWarehouse: [],                //所有仓库
        currency: [],                    //币种
        taxRebateRatio: [],              //退税比例
        salesTeam: [],                   //销售团队
        paymentNode: [],                 //付款节点
        paymentCycle: [],                //付款周期
        invoicePoint: [],                //发表和税点
        shipping: {                      //发货方式
            //TODO:暂无数据  data需要初始化值
            active: 0,
            data: []
        },
        invioce: [],
        tax_point: [],
        contracts: []
    };
    $.getData = {
        c2c_data:[],
        cd_company:[]
    }
    $.get_ht = function () {
        $Ajax.post("/index.php?m=b2b&a=get_ht", {sp_charter_no: $.poData.clientName}, function (result) {
            $.initData.contracts = result.data.contract;
            $.getData.c2c_data = result.data.c2c_data;
            $.getData.cd_company = result.data.cd_company;
        });
    }
    $.upd_company =function () {
        $.poData.ourCompany = $.getData.cd_company[$.getData.c2c_data[$.poData.contract]]
    }
    $.init = function () {
        $Ajax.post("/index.php?m=b2b&a=init", null, function (result) {
            $.initData.country = result.data.Country;
            $.initData.allWarehouse = result.data.all_warehouse;
            $.initData.currency = result.data.currency;
            $.initData.taxRebateRatio = result.data.tax_rebate_ratio;
            $.initData.salesTeam = result.data.sales_team;
            $.initData.paymentCycle = result.data.payment_cycle;
            $.initData.paymentNode = result.data.payment_node;
            $.initData.invoicePoint = result.data.invoice_point;
            $.initData.shipping.data = result.data.shipping;
            $.initData.invioce = result.data.invioce;
            $.initData.tax_point = result.data.tax_point;
            $.initData.number_th = result.data.number_th;
            $.initData.currency_bz = result.data.currency_bz;
            $.initData.allPurchasingArr = result.data.allPurchasingArr
            $.initData.allIntroduceArr = result.data.allIntroduceArr
            $.initData.wfgs = result.data.wfgs
            $.initData.user = result.data.user
        });

    };
    $.init();

    //目标城市联动
    $.changeCounty = function (poData, end) {
        $Ajax.post("/index.php?m=stock&a=getCity", {provinces: poData, end: end}, function (result) {
            if (end == 'end') {
                $.initData.city = result.data;
            } else {
                $.initData.province = result.data;
            }
        })
    };

    $.sync_sku = function (e) {
        $.skuData = [{
            skuId: '',
            gudsName: '',
            skuInfo: '',
            warehouse: '',
            gudsPrice: 0,
            demand: 0,
            subTotal: 0,
            drawback: '0',
            estimateDrawback: 0,
            purchasing_team: '',
            introduce_team: ''
        }];
        for (var i = 1; i < e.length; i++) {
            $.add();
        }
        for (var i = 0; i < e.length; i++) {
            $.skuData[i]['skuId'] = e[i]['search']
            $.skuData[i]['toskuid'] = e[i]['sku']
            $.skuData[i]['gudsName'] = e[i]['goods_name']
            $.skuData[i]['skuInfo'] = e[i]['val_str']
            $.skuData[i]['warehouse'] = e[i]['warehouse']
            $.skuData[i]['gudsPrice'] = e[i]['price']
            $.skuData[i]['demand'] = e[i]['number']
            $.skuData[i]['drawback'] = e[i]['drawback']
            $.skuData[i]['STD_XCHR_KIND_CD'] = e[i]['STD_XCHR_KIND_CD']
            $.skuData[i]['GUDS_OPT_ORG_PRC'] = e[i]['GUDS_OPT_ORG_PRC']
        }
        for (var i = 0; i < e.length; i++) {
            $.searchSku($.skuData[i])
            $.countSubTotal($.skuData[i])
            setTimeout($.countDrawback($.skuData[i]), 500)
            if ($.skuData[i]['estimateDrawback'] == 'NaN') $.skuData[i]['estimateDrawback'] = (0).toFixed(4)
        }
    }

    $.addgoods = function () {
        uploader = WebUploader.create({
            swf: '/Application/Tpl/Home/Public/lib/webuploader/0.1.5/Uploader.swf',
            server: '/index.php?m=b2b&a=importGoods',
            pick: {id: '#import-goods'},
            auto: true,
            duplicate: true,
        });
        uploader.on('uploadSuccess', function (file, res) {
            if (!$.poData.BZ) {
                utils.modal(true, {width: 380, title: "请先处理PO查询"}, false)
                return false
            }
            if (res.status && res.status == 1) {
                utils.modal(true, {width: 380, title: "导入成功", content: "导入成功", delay: 2000}, false);
                setTimeout(utils.modal(false), 3000);
                $.sync_sku(res.info)
            } else {
                utils.modal(true, {width: 380, title: "上传失败", content: res.info}, false)
            }
        });
        uploader.on('uploadStart', function (file) {
        })

    }
    setTimeout($.addgoods, 800);
    $.addpo = function () {
        $.switchNode(0)
        uploader = WebUploader.create({
            swf: '/Application/Tpl/Home/Public/lib/webuploader/0.1.5/Uploader.swf',
            server: '/index.php?m=b2b&a=importPo',
            fileSingleSizeLimit: 20*1024*1024,
            pick: {id: '#import-po'},
            auto: true,
            duplicate: true,
        });
        uploader.on('uploadSuccess', function (file, res) {
            if (res.status && res.status == 1) {
                utils.modal(true, {width: 380, title: "上传成功", content: "上传成功", delay: 2000}, false);
                setTimeout(utils.modal(false), 3000);
                $.poData.IMAGEFILENAME = res.info.file_name
                $.poData.po_erp_path = res.info.VOUCHER_ADDRESS
            } else {
                utils.modal(true, {width: 380, title: "上传失败", content: res.info}, false)
            }
        });
        uploader.on('uploadStart', function (file) {
        })
        uploader.on("error",function (type){
            if(type=="F_EXCEED_SIZE"){
                utils.modal(true, {width: 380, title: "上传失败", content: '文件大小不能超过20M'}, false)

            }
        });
    }
    setTimeout($.addpo, 800);
    /**=========================================po信息模块===============================================*/
    $.poData = {
        poNum: '',               //po编号
        clientName: '',          //客户名称
        busLice: '',             //客户营业执照号
        contract: '',            //适用合同
        ourCompany: '',          //我方公司
        poAmount: '',            //PO金额
        BZ: '',
        poScanner: '',           //PO扫描件
        poTime: '',              //PO时间
        targetCity: '',          //目标城市
        shipping: '',         //发货方式
        cycleNum: '',            //付款周期
        poPaymentNode: [],       //付款节点
        country: '',              //国家
        province: '',              //省市
        city: '',                 //城市
        street: '',               //街道
        detailAdd: '',            //详细地址
        saleTeam: '',             //销售团队
        Remarks: '',               //备注
        invioce: '',
        tax_point: '',
        backend_currency: '',
        backend_estimat: '',
        logistics_currency: '',
        logistics_estimat: '',

    };
    $.initall = {
        allEsBack: 0,
        allSubtotal: 0,
        allnum: 0,
        allPrice: 0
    }
    $.paymentNodeArray = [];      //付款节点数组

    $.searchPo = function () {
        var param = {CON_NO: $.poData.poNum};
        $Ajax.post("/index.php?m=b2b&a=get_po_data", param, function (result) {
            if (result.status == 1) {
                $.poData.clientName = result.data.YF;
                $.poData.busLice = result.data.CGBUSINESSLICENSE2;
                $.poData.contract = result.data.SUITABLE_CONTRACT;
                $.poData.ourCompany = result.data.GSMC;
                $.poData.poAmount = result.data.AMOUNT2;
                $.poData.poScanner = result.data.FJSC2;
                $.poData.poTime = result.data.SQRQ;
                $.poData.lastname = result.data.LASTNAME;
                $.poData.BZ = result.data.BZ;
                $.get_ht()
                $.switchNode(0)
            } else {
                alert(result.data)
            }
        });
    };

    //重组付款节点数组
    function regArray(arr, num, args) {
        var regArr = [];

        function sliceArr(obj, num, args, index) {
            var newObj = {};
            for (var key in arr) {
                newObj[key] = obj[key]
                if (key == args && 1 != 1) {
                    newObj[args] = obj[args].slice(index, arr[args].length - (num - index - 1))
                } else {
                    newObj[key] = obj[key]
                }
            }
            return newObj;
        }

        for (var i = 0; i < num; i++) {
            regArr[i] = sliceArr(arr, num, args, i);
        }
        return regArr;

    }


    //选择付款周期
    $.changeCycle = function () {
        $.paymentNodeArray = [];
        if ($.poData.cycleNum == 4) {
            $.paymentNodeArray = regArray($.initData.paymentNode, +1, 'node_type');
        } else if ($.poData.cycleNum) {
            $.paymentNodeArray = regArray($.initData.paymentNode, +$.poData.cycleNum, 'node_type');
        }
    };
    //切换发货方式
    $.switchNode = function (index) {
        $.initData.shipping.active = index;
        $.poData.shipping = $.initData.shipping.data[index].CD_VAL;
    };

    /**=========================================sku信息模块===============================================*/

    /**
     * 商品SKU信息
     * @type {[*]}  相对应的值
     */

    $.skuData = [{
        skuId: '',         //skuID
        gudsName: '',                //商品名称
        skuInfo: '',                 //sku信息
        warehouse: '',               //仓库
        selCurrency: '',             //币种
        gudsPrice: 0,                //商品售价
        demand: 0,                   //需求数量
        subTotal: 0,                 //小计
        drawback: '0',               //退税比例
        estimateDrawback: 0,          //预计退税金额
        purchasing_team: '',
        introduce_team: '',
        GUDS_OPT_ORG_PRC: '',
        STD_XCHR_KIND_CD: '',
    }];

    /**
     * 查询SKU信息
     */
    $.searchSku = function (item) {
        if (!item.toskuid) {
            utils.alert('info', '请输入正确的 SKUID');
            return false;
        }

        var countId = -1;
        var countIdsku = -1;
        angular.forEach($.skuData, function (data) {
            if (data.toskuid == item.toskuid) countId++;
        });

        if (countId) {
            utils.alert('info', '该SKU信息已经存在');
            return false;
        }

        var param = {GSKU: item.toskuid, warehouse_id: null};
        $Ajax.post('/index.php?m=stock&a=searchguds', param, function (result) {
            if (typeof result.info == "object" && result.info != null) {
                item.skuId = result.info['opt_val'][0]['GUDS_OPT_ID'];
                angular.forEach($.skuData, function (data) {
                    if (data.skuId == item.skuId) countIdsku++;
                });
                if (countIdsku) {
                    utils.alert('info', '该SKU信息已经存在');
                    return false;
                }
                item.gudsName = result.info[0]['Guds']['GUDS_NM'];
                item.skuInfo = result.info['opt_val'][0]['val'];
                item.GUDS_OPT_ORG_PRC = result.info[0]['GUDS_OPT_ORG_PRC'];
                item.STD_XCHR_KIND_CD = result.info[0]['Guds']['STD_XCHR_KIND_CD'];

                angular.forEach($.initData.allWarehouse, function (child) {
                    if (child.CD == result.info[0]['Guds']['DELIVERY_WAREHOUSE']) {
                        item.warehouse = child.warehouse;
                    }
                });

            } else {
                utils.alert("error", "请检查 SKUID 是否正确")
            }
        })


    };

    //计算小计金额
    $.countSubTotal = function (item) {
        if (typeof (item.gudsPrice) == 'string' && item.gudsPrice.indexOf('\.', item.gudsPrice.length - 1) > 0)return item
        item.gudsPrice = this.unking(item.gudsPrice)
        item.demand = this.unking(item.demand)
        item.subTotal = (parseFloat(item.gudsPrice * 1000000) * parseFloat(item.demand * 1000000)) / 1000000000000;
        this.countDrawback(item)
        item.gudsPrice = this.king((item.gudsPrice))
    };

    //计算预计退税金额
    $.countDrawback = function (item) {
        item.subTotal = this.unking(item.subTotal)
        item.drawback = this.unking(item.drawback)
        item.demand = this.unking(item.demand)
        item.estimateDrawback = this.get_ts(item);
        this.all()
        item.demand = this.king(parseFloat(item.demand))
        item.subTotal = this.king(parseFloat(item.subTotal))
        item.estimateDrawback = this.king(item.estimateDrawback)

    };
    $.upd_potime = function (e) {
        var date_val = document.getElementById('actual-receipt-date').value
        $.poData.poTime = date_val
    }
    //采用backend金额
    $.get_ts = function (item, bz) {
        let rate = 1
        if (this.poData.BZ) {
            var dst_currency = this.initData.currency_bz[this.poData.BZ].CD_VAL
            let dates = this.poData.poTime
            let currency = item.STD_XCHR_KIND_CD
            $Ajax.post("/index.php?m=b2b&a=get_currency_backend", {
                currency: currency,
                date: dates,
                dst_currency: dst_currency
            }, function (result) {
                if (result) rate = result
            })
        }
        return ((parseFloat(item.GUDS_OPT_ORG_PRC) * rate * parseFloat(item.demand) * parseFloat(item.drawback)) / 100).toFixed(4)
    }

    $.all = function () {
        $.initall = {
            allEsBack: 0,
            allSubtotal: 0,
            allnum: 0,
            allPrice: 0
        }
        for (s in $.skuData) {
            $.initall.allPrice += isNaN(this.unking($.skuData[s].gudsPrice)) ? 0 : parseFloat(this.unking($.skuData[s].gudsPrice))
            $.initall.allnum += isNaN(this.unking($.skuData[s].demand)) ? 0 : parseFloat(this.unking($.skuData[s].demand))
            $.initall.allSubtotal += isNaN(this.unking($.skuData[s].subTotal)) ? 0 : parseFloat(this.unking($.skuData[s].subTotal))
            $.initall.allEsBack += isNaN(this.unking($.skuData[s].estimateDrawback)) ? 0 : parseFloat(this.unking($.skuData[s].estimateDrawback))
        }
        for (a in  $.initall) {
            $.initall[a] = parseFloat($.initall[a]) ? this.king(parseFloat($.initall[a]).toFixed(2)) : parseFloat($.initall[a]);
        }
    }
    //添加新的一行sku信息
    $.add = function () {
        var addData = {
            skuId: '',
            gudsName: '',
            skuInfo: '',
            warehouse: '',
            gudsPrice: 0,
            demand: 0,
            subTotal: 0,
            drawback: '0',
            estimateDrawback: 0,
            purchasing_team: '',
            introduce_team: ''
        };
        $.skuData.push(addData)
    };

    //删除一行SKU信息
    $.del = function (item) {
        angular.forEach($.skuData, function (element, index) {
            if (element == item) {
                if ($.skuData.length > 1) {
                    $.skuData.splice(index, 1)
                } else {
                    utils.alert("error", "不可删除最后一条")
                }

            }
        });
        this.all()
    };

    //选择币种
    $.changeCurrency = function (item) {
        angular.forEach($.skuData, function (data) {
            data.selCurrency = item.selCurrency;
        })
    };

    // 切换团队
    $.changeAllTeam = function (key, val) {
        var sku_data = this.skuData
        for (v in sku_data) {
            this.skuData[v][key] = val
        }
    };
    //保存提交
    $.submit = function () {
        if (this.unking($.initall.allSubtotal) * 100 != this.unking($.poData.poAmount) * 100) {
            utils.alert("error", "PO金额不相同");
            return false;
        }
        $.poData.poPaymentNode = [];
        var nodeProp = 0;
        for (var i = 0; i < $.paymentNodeArray.length; i++) {
            // nodeProp += parseInt($.paymentNodeArray[i].node_prop[$.paymentNodeArray[i].nodeProp]);
            nodeProp += parseInt($.paymentNodeArray[i].nodeProp);
            paymentNode = {
                nodei: i,
                nodeType: $.paymentNodeArray[i].nodeType,
                nodeDate: $.paymentNodeArray[i].nodeDate,
                nodeWorkday: $.paymentNodeArray[i].nodeWorkday,
                nodeProp: $.paymentNodeArray[i].nodeProp
            };
            $.poData.poPaymentNode.push(paymentNode);
        }
        if ( nodeProp != 100) {
            utils.alert("error", "请检查付款节点比例");
            return false;
        }
        $.poData['tax_rebate_income'] = $.initall.allEsBack
        var param = {
            poData: $.poData,
            skuData: $.skuData
        };
        //  check data
        if (!this.check_data(param)) {
            return false
        }
        $Ajax.post('/index.php?m=b2b&a=save_order', JSON.stringify(param), function (result) {
            if (200 == result.status) {
                utils.alert("success", result.info)
                var id = result.data.order_id
                setTimeout(function () {
                    window.location.href = '/index.php?m=b2b&a=order_list&order_id=' + id + '#/b2bsend'
                }, 3000)
            } else {
                utils.alert("error", result.info)
            }
        })
    };
    $.check_data = function (e) {
         var info_check = ['poNum', 'cycleNum', 'invioce', 'tax_point', 'shipping', 'country', 'province', 'detailAdd', 'saleTeam',  'backend_estimat', 'logistics_estimat','backend_currency','logistics_currency','lastname','BZ','contract','clientName','ourCompany','poTime']
        // var info_check = ['backend_estimat', 'logistics_estimat', 'backend_currency', 'logistics_currency']
        var goods_check = ['skuId', 'gudsPrice', 'demand', 'drawback']
        var res_info = this.check_required(e.poData, info_check)
        if (res_info.info.length) {
            var msg = ''
            for (var i = 0; i < res_info.info.length; i++) {
                msg += res_info.info[i] + "<br/>"
            }
            utils.alert("error", msg);
            return false
        }
        var res_goods = this.check_required(e.skuData, goods_check, 'sku')
        if (res_goods.info.length) {
            var msg = ''
            for (var i = 0; i < res_goods.info.length; i++) {
                msg += res_goods.info[i] + "\n\r"
            }
            utils.alert("error", msg);
            return false
        }
        return true
    }
    $.tocn = {
        poNum: 'PO编号',
        cycleNum: '付款周期',
        invioce: '发票',
        tax_point: '税点',
        shipping: '发货方式',
        country: '目标城市',
        province: '省',
        detailAdd: '详细地址',
        saleTeam: '销售团队',
        remarks: '备注',
        IMAGEFILENAME: 'PO扫描件',
        backend_currency: '预估商品币种',
        logistics_currency: '预估物流币种',
        backend_estimat: '预估商品成本',
        logistics_estimat: '预估物流成本',
        skuId: 'SKUID',
        gudsPrice: '商品售价',
        demand: '需求数量',
        drawback: '退税比例',
        lastname:'销售同事',
        BZ:'PO金额币种',
        contract:'适用合同',
        clientName:'客户名称',
        ourCompany:'我方公司',
        poTime:'PO时间'
    }
    $.checkRate = function (input) {
        var re = /^[1-9]+[0-9]*]*$/
        return re.test(input)
    }
    $.check_required = function (data, check, type) {
        var err = []
        var err_info = []
        for (c in check) {
            if ('sku' == type) {
                for (var i = 0; i < data.length; i++) {
                    res = this.in_array(data[i], check[c])
                    if (err[i] == 'undefined' || !err[i]) err[i] = []
                    if (res) {
                        err_info.push('第' + (parseInt(i) + 1) + '行；' + $.tocn[check[c]] + '>为空')
                    }
                    if (check[c] == 'gudsPrice') {
                        if (!data[i][check[c]] || $.unking(data[i][check[c]]) <= 0) {
                            err_info.push('第' + (parseInt(i) + 1) + '行；' + $.tocn[check[c]] + '>格式错误[需为大于零的正数]')
                        }
                    }
                    if (check[c] == 'demand') {
                        var rate_data = $.checkRate($.unking(data[i][check[c]]))
                        if (!data[i][check[c]] || $.unking(data[i][check[c]]) < 0 || !rate_data) {
                            err_info.push('第' + (parseInt(i) + 1) + '行；' + $.tocn[check[c]] + '>格式错误[需为正整数]')
                        }
                    }
                }
                err.info = err_info
            } else {
                res = this.in_array(data, check[c])
                if (res) {
                    err.push(res)
                    err_info.push($.tocn[check[c]] + '>为空')
                }
                if (check[c] == 'backend_estimat' || check[c] == 'logistics_estimat') {
                    var rate_data = $.checkRate($.unking(data[check[c]]))
                    if (!data[check[c]] || data[check[c]] < 0 || !rate_data) {
                        err_info.push($.tocn[check[c]] + '>格式错误[需为大于零的正数]')
                    }
                }
                err.info = err_info
            }
        }
        return err
    }

    $.in_array = function (array, search) {
        var err = null
        if (!array[search]) err = search
        return err;
    }

    //重置表单
    $.reset = function () {
        $.skuData = [{
            skuId: '',
            gudsName: '',
            skuInfo: '',
            warehouse: '',
            gudsPrice: 0,
            demand: 0,
            subTotal: 0,
            drawback: '0',
            estimateDrawback: 0,
            purchasing_team: '',
            introduce_team: ''

        }];
        $.initall = {
            allEsBack: 0,
            allSubtotal: 0,
            allnum: 0,
            allPrice: 0
        }
    }
    $.king = function (e) {
        if (e) {
            var k = e.toString().split('.')
            if (e.toString().indexOf('.') > 0) {
                var s = '.' + k[1]
            } else {
                var s = ''
            }
            return k[0].toString().replace(/\d{1,3}(?=(\d{3})+(\.\d*)?$)/g, '$&,') + s;
        } else {
            return e
        }
    }

    $.unking = function (num) {
        if (isNaN(num) && typeof(num) == 'string') {
            var x = num.split(',');
            return parseFloat(x.join(""));
        } else {
            return num
        }
    }
    $.importError = function () {
        utils.modal(true,{
            title: '提示',
            content: "商品导入失败，你可以下载错误报告，查看具体原因",
            confirmText:'下载',
            width:480,
            contentClass:'text-center',
            confirmFn:function () {
                //TODO：处理下载的逻辑
            }
        })
    }
}]);

/**===============================================================================
 * b2bSend 控制器
 * ==============================================================================*/
myApp.filter('to_trusted', ['$sce', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
}]);
myApp.controller('b2bSend', ['$scope', '$Ajax', '$location', function ($, $Ajax, $location) {
    $.initData = {}
    $.data = [];
    $.goods_sum = {
        neednum: 0,
        shipnum: 0,
        warehousing: 0,
        allnum: 0.00
    }
    $.datas = []
    $.init_data = []
    $.data['order_id'] = $location.$$absUrl.split('order_id=')[1].split('#')[0]
    $.init = function () {
        $Ajax.post('/index.php?m=b2b&a=order_content', $.data, function (result) {
            if (200 == result.status) {
                $.initData = result.data['info'][0]
                $.data['sales_team'] = result.data['sales_team']
                $.data['goods'] = result.data['goods']
                $.datas['ship'] = result.data['ship']
                $.datas['receipt'] = result.data['receipt']
                $.datas['profit'] = result.data['profit']
                $.sum_compute($.data['goods']);
                $.init_data['area'] = result.data['area']
                $.init_data['number_th'] = result.data['number_th']
                $.init_data['node_is_workday'] = result.data['node_is_workday']
                $.init_data['node_type'] = result.data['node_type']
                $.init_data['node_date'] = result.data['node_date']
                $.init_data['invioce'] = result.data['invioce']
                $.init_data['tax_point'] = result.data['tax_point']
                $.init_data['period'] = result.data['period']
                $.init_data['or_invoice_arr'] = result.data['or_invoice_arr']
                $.init_data['warehousing_state'] = result.data['warehousing_state']
                $.init_data['currency_bz'] = result.data['currency_bz']
            } else {
                console.log(result)

            }
        })
    }
    $.getdatestr = function (times) {
        if (times == '' || times == null || times == 'undefined')return times
        var dd = new Date(times);
        dd.setDate(dd.getDate());
        var y = dd.getFullYear();
        var m = dd.getMonth() + 1;
        var d = dd.getDate();
        return y + "-" + m + "-" + d;
    }
    $.sum_compute = function ($e) {
        for (s in $e) {
            $.goods_sum.neednum += parseInt($e[s].required_quantity)
            $.goods_sum.shipnum += parseInt($e[s].SHIPPED_NUM)
            $.goods_sum.warehousing += parseInt($e[s].is_inwarehouse_num)

            $.goods_sum.allnum += (parseFloat($e[s].price_goods) * parseFloat($e[s].required_quantity))
        }
        $.goods_sum.allnum = $.goods_sum.allnum.toFixed(2)
    }


    $.init();
    $.king = function (e) {
        if (e) {
            var k = e.toString().split('.')
            if (e.toString().indexOf('.') > 0) {
                var s = '.' + k[1]
            } else {
                var s = ''
            }
            return k[0].toString().replace(/\d{1,3}(?=(\d{3})+(\.\d*)?$)/g, '$&,') + s;
        } else {
            return e
        }
    }
    $.join_ares = function (e) {
        if (e != null) {
            e_data = JSON.parse(e)
            var initdata = this.init_data
            var area = ''
            if (e_data.country) area = initdata.area[e_data.country]
            if (e_data.stareet) area += '-' + initdata.area[e_data.stareet]
            if (e_data.city) area += '-' + initdata.area[e_data.city]
            if (e_data.targetCity) area += '-' + e_data.targetCity
            return area
        }
        return e
    },
        $.show_node_arr = function (e) {
            var arr = JSON.parse(e)
            var run = ''
            if (!arr[0].nodeType)return null
            for (var a in arr) {
                if (arr[a]) run += (this.show_node(JSON.stringify(arr[a])) + '; ')
            }
            return run

        },
        $.show_node = function (e) {
            var d = JSON.parse(e)
            var init_data = this.init_data
            if (!d)return '-(退税)'
            if (!d.nodeType)return null
            var run_e = init_data.number_th[d.nodei] + ':' + init_data.node_type[d.nodeType].CD_VAL + init_data.node_date[d.nodeDate].CD_VAL + init_data.node_is_workday[d.nodeWorkday].CD_VAL + '-' + d.nodeProp + '%'
            return run_e
        },
        $.gather_date = function (k) {
            var gather_key = k
            if (!gather_key)return null
            var d = JSON.parse(gather_key.receiving_code)
            if (!d)return null
            var times = null
            switch (parseInt(d.nodeType)) {
                case 0:
//                        合同
                    times = gather_key.po_time
                    break;
                case 1:
//                        发货
                    times = gather_key.DELIVERY_TIME
                    break;
                case 2:
//                      入港
                    times = gather_key.Estimated_arrival_DATE
                    break;
                case 3:
//                        入库
                    times = gather_key.WAREING_DATE
                    break;

                default:
            }
            if (!times)return null
            var gather_date_string = this.GetDateStr(times, this.init_data.node_date[d.nodeDate].CD_VAL)
            return gather_date_string
        },
        $.GetDateStr = function (times, AddDayCount) {
            var dd = new Date(times);
            dd.setDate(dd.getDate() + AddDayCount);
            var y = dd.getFullYear();
            var m = dd.getMonth() + 1;
            var d = dd.getDate();
            return y + "-" + m + "-" + d;
        },
        $.overdue = function (e, t_date) {
            if (!e || e.transaction_type || this.gather_date(e) == null)return null
            var overdue_msg = '未逾期'
            var overdue_text = 0
            var gather_date_t = new Date(this.gather_date(e))
            var today_time = Math.round(new Date().getTime() / 1000)
            gather_date_t = Math.round(Date.parse(gather_date_t) / 1000)
            if (t_date) today_time = Math.round(Date.parse(new Date(t_date)) / 1000)
            if (gather_date_t < today_time) overdue_text = Math.floor(Math.abs(gather_date_t - today_time) / 60 / 60 / 24)
            if (overdue_text) overdue_msg = '<span style="color: #f00">逾期' + overdue_text + 'Day</span>'
            return overdue_msg
        },
        $.run_invioce = function (e) {
            if (e)return this.init_data.invioce[e].CD_VAL
        },
        $.run_tax_point = function (e) {
            if (e)return this.init_data.tax_point[e].CD_VAL
        },
        $.run_period = function (e) {
            if (e)return this.init_data.period[e - 1].CD_VAL
        }

}])


