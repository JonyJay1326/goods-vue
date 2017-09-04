/**
 * Created by b5m on 2017/5/15.
 */
(function (window, $) {
    if (typeof $ != "function") {
        this.alert("info", "未发现Jquery，有些功能会受影响");
    }

    utils = {

        /**
         * 合并对象
         * @param traget    目标对象
         * @param newObj    合并对象
         * @returns {*}
         */
        assignObj: function (traget, newObj) {
            var result = {};
            if (typeof Object.assign == "function") {
                return result = Object.assign(traget, newObj);
            } else {
                for (var i in traget) {
                    for (var j in newObj) {
                        if (i == j) {
                            result[i] = newObj[j];
                        } else {
                            result[j] = newObj[j];
                            result[i] = traget[i];
                        }
                    }
                }
            }
            return result;
        },
        /**
         * 获取对象的长度
         * @param obj
         * @returns {number}
         */
        objLen: function (obj) {
            var count = 0;
            for (var item in obj) {
                count++
            }
            return count;
        },
        /**
         * 截取对象的方法
         * @param obj       数据
         * @param start     起始位置
         * @param end       结束位置
         * @returns {{}}
         */
        sliceObj: function (obj, start, end) {
            var arr = [];
            for (item in obj) {
                var childArr = [item, obj[item]]
                arr.push(childArr);
            }
            var resultArr = arr.slice(start, end);
            var resultObj = {};
            for (var i = 0; i < resultArr.length; i++) {
                resultObj[resultArr[i][0]] = resultArr[i][1];
            }
            return resultObj;
        },

        /**================================================自定义弹框============================================*/

        /**
         * 自定义确认框
         * @param state     控制显示隐藏
         * @param param     传入各个参数
         */
        modal: function (state, param, file) {
            var defaultOption = {
                    id: "smsModal",
                    modalWidth: 500,
                    width: 360,
                    title: "提示",
                    content: "请确认",
                    confirmText: '确认',
                    cancelText: '取消',
                    contentClass: '',
                    btnClass: 'btn-danger',
                    confirmFn: null,
                    cancelFn: null
                },
                options = this.assignObj(defaultOption, param),
                status = state ? "show" : "hide";


            if (options.width > options.modalWidth) {
                options.modalWidth = options.width
            }

            options.confirmDismiss = typeof options.confirmFn === 'function' ? '' : ("data-dismiss='modal'");
            options.cancelDismiss = typeof options.cancelFn === 'function' ? '' : ("data-dismiss='modal'");
            options.confirmId = options.id === "smsModal" ? "smsModalConfirm" : options.id + "Confirm";
            options.cancelId = options.id === "smsModal" ? "smsModalCancel" : options.id + "Cancel";
            // options.cancelFnName = options.cancelFnName ? ("onclick=" + options.cancelFnName + "()") : ("data-dismiss='modal'");
            var prompt = "<div class='" + options.contentClass + "'>" + options.content + "</div>",
                fileHtml = '<style>.custom-file-control, .custom-file-control::after,.custom-file-control::before,' +
                    '.custom-file-input { cursor: pointer; height: 36px;font-size: 13px }' +
                    '.custom-file-control::after { content: "文件名称..."; }' +
                    '.custom-file-control::before { content: "浏览文件"; } </style >' +
                    '<label class="custom-file">' +
                    '<input type="file" class="custom-file-input" multiple>' +
                    '<span class="custom-file-control"></span></label>',
                htmlVariable = file ? fileHtml : prompt,
                modalStyle = file ? 'style =" margin:0 auto;"' : '';

            var html = "<div class='modal fade' id='" + options.id + "'>" +
                "<div class='modal-dialog' role='document' style='max-width:" + options.modalWidth + "px'>" +
                "<div class='modal-content' style='width:" + options.width + "px;'>" +
                "<div class='modal-header'>" +
                "<h5 class='modal-title'>" + options.title + "</h5>" +
                "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>" +
                "<span class='sr-only'>Close</span>" +
                "<span aria-hidden='true'>&times;</span>" +
                "</button></div>" +
                "<div class='modal-body'" + modalStyle + " >" + htmlVariable + "</div>" +
                "<div class='modal-footer'>" +
                "<button type='button' id='" + options.confirmId + "' class='btn-sm-cus btn  " + options.btnClass + "'" + options.confirmDismiss + ">" + options.confirmText + "</button>" +
                "<button type='button' id='" + options.cancelId + "' style='margin-left: 20px' class='btn btn-secondary btn-sm-cus'" + options.cancelDismiss + ">" + options.cancelText + "</button>" +
                // "<button type='button' style='margin-left: 20px' class='btn btn-secondary btn-sm-cus'" + options.cancelFnName + ">取消</button>" +
                "</div></div></div></div>";
            var queryId = "#" + options.id,
                queryIdLen = $(queryId).length;
            if (!queryIdLen) {
                var wrap = document.createElement("div");
                wrap.innerHTML = html;
                document.querySelector("body").appendChild(wrap);
            } else {
                document.getElementById(options.id).parentNode.innerHTML = html;
            }

            function closeModal() {
                var elModal = $("#" + options.id);
                elModal.modal("hide");
            }

            if (typeof options.confirmFn === 'function') {
                document.getElementById(options.confirmId).addEventListener("click", function () {
                    options.confirmFn();
                    closeModal();
                });
            }
            if (typeof options.cancelFn === 'function') {
                document.getElementById(options.cancelId).addEventListener("click", function () {
                    options.cancelFn();
                    closeModal();
                });
            }
            $('.custom-file-input').change(function () {
                var files = $(this).prop("files");
                var style = '', size = 0, i = 0;
                if (files.length > 1) {
                    while (i < files.length) {
                        size += files[i].size;
                        i++;
                    }
                    style = "<style>.custom-file-control::after { content:'已选择 " + files.length + " 个文件...'!important; }</style>"
                } else {
                    var names = files[0].name.split('.'),
                        name = '';
                    if (files[0].name.length > 20) {
                        name = names[0].substr(0, 8) + "..." + names[0].substr(-3) + "." + names[1];
                    } else {
                        name = names.join('.');
                    }
                    style = "<style>.custom-file-control::after { content: '" + name + "'!important; }</style>"
                }

                $(this).before(style);
            });

            var element = $("#" + options.id);
            if (typeof  element.modal === "function") {
                element.modal(status);
                if (options.delay > 1000) {
                    setTimeout(function () {
                        element.modal("hide");
                    }, options.delay)
                }
            } else {
                utils.alert("info", "请引入bootstrap.min.js")
            }
        },

        /**
         * 自定义提示小弹窗
         * @param type          提示类型
         * @param content       自定义内容
         * @returns {boolean}
         */

        alert: function (type, content) {
            var alertCount = $('.custom-alert').length;
            if (alertCount > 4) {
                return false;
            }
            var alertClass = 'alert-info',
                icon = 'fa-info-circle';
            switch (type) {
                case 'error':
                    alertClass = 'alert-danger';
                    icon = 'fa-remove ';
                    break;
                case 'success':
                    alertClass = 'alert-success';
                    icon = ' fa-check';
                    break;
                default:
                    alertClass = 'alert-info';
                    icon = 'fa-info-circle';
                    break;
            }
            content = content || '警告！';
            var template = "<div class='alert " + alertClass + "'>" +
                    "<a href='#'  class='close' style='line-height: 22px'> &times; </a>" +
                    "<i class='fa  fa-lg " + icon + "' style='font-size: 20px; padding: 5px;display: inline-block'></i> " +
                    "<div style='display: inline-block; vertical-align: text-top;'>" + content + "</div></div>",
                alertTemp = document.createElement("div");
            alertTemp.innerHTML = template;
            alertTemp.className = 'custom-alert';
            alertTemp.style.minWidth = '220px';
            alertTemp.style.maxWidth = '450px';
            alertTemp.style.position = 'absolute';
            alertTemp.style.bottom = alertCount == 0 ? '10px' : (alertCount * 50) + ((alertCount + 1) * 10) + 'px';
            alertTemp.style.right = '20px';
            var alertTempWrap = document.querySelector("body").appendChild(alertTemp);

            $('.close').click(function () {
                $(this).parent().parent().remove();
            });

            setTimeout(function () {
                $(alertTempWrap).hide(2500, function () {
                    $(this).remove();
                });
            }, 900)


        },

        /**
         * 下载文件
         * @param url  接口
         */
        download: function (url) {
            var aEle = document.createElement("a");
            aEle.setAttribute("style", "display: none");
            aEle.setAttribute("href", url); //传中文值使用encodeURIComponent()
            aEle.click();
        },

        /**
         * 判定是否为空
         * 返回true
         */
        isEmpty: function (obj) {
            return obj == null || obj === "" || obj === 'undefined' || obj === undefined || obj.length === 0;
        },

        /**
         * 增加千分位符
         */
        numberFormat: function (value) {
            if (this.isEmpty(value)) return value;

            var intValue, decValue, symbol = '', result = '',
                strValue = value.toString();
            if (strValue.indexOf('.') > -1) {
                symbol = strValue.indexOf('-') > -1 ? '-' : '';
                intValue = strValue.substring(strValue.indexOf('-') > -1 ? 1 : 0, strValue.indexOf('.'));
                decValue = strValue.substring(strValue.indexOf('.'))
            } else {
                intValue = value + '';
                decValue = '';
            }
            while (intValue.length > 3) {
                result = ',' + intValue.slice(-3) + result;
                intValue = intValue.slice(0, intValue.length - 3);
            }

            result = symbol + intValue + result + decValue;

            return result;
        },

        /**
         * 千分位转换为数字
         * @param param (string || number)
         * @returns {Number}
         */
        convertNum: function (param) {
            var num = param.indexOf(',');
            if (num > -1) {
                return parseFloat(param.replace(/,/g, ''));
            } else {
                return parseFloat(param);
            }
        },
        /**
         * 保留小数点数字
         * @param param       需要操作的数字(string || number)
         * @param num         保留几位数字
         * @returns {number}  返回Number
         */
        fixedNum: function (param, num) {
            num = num || 0;
            var floatNum = parseFloat(param);
            if (!isNaN(floatNum)) {
                return +floatNum.toFixed(num);
            } else {
                this.alert('error', '不是正确的数字');
            }
        },
        /**================================================日期操作============================================*/

        /**
         * 判定日期字符串
         */
        isDateString: function (str) {
            var date = /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/,
                month = /^[0-9]{4}-[0-9]{2}$/,
                year = /^[0-9]{4}$/;
            return date.test(str) || month.test(str) || year.test(str);
        },

        /**
         *  日期格式化
         */
        dateFormat: function (date, format) {
            if (date instanceof Date) {
                var o = {
                    "M+": date.getMonth() + 1,                 //月份
                    "d+": date.getDate(),                    //日
                    "h+": date.getHours(),                   //小时
                    "m+": date.getMinutes(),                 //分
                    "s+": date.getSeconds(),                 //秒
                    "q+": Math.floor((date.getMonth() + 3) / 3), //季度
                    "S": date.getMilliseconds()             //毫秒
                };
                if (/(y+)/.test(format))
                    format = format.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
                for (var k in o) {
                    if (new RegExp("(" + k + ")").test(format)) {
                        format = format.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
                    }
                }
                return format;
            } else {
                return date;
            }
        },
        /**
         *  日期的加减操作
         */
        addDate: function (date, interval, value) {
            var returnDate = new Date(date.getTime());
            if (value) {
                switch (interval.toLowerCase()) {
                    case 'second':
                        returnDate.setTime(date.getTime() + value * 1000);
                        break;
                    case 'minute':
                        returnDate.setTime(date.getTime() + value * 60 * 1000);
                        break;
                    case 'hour':
                        returnDate.setTime(date.getTime() + value * 60 * 60 * 1000);
                        break;
                    case 'day':
                        returnDate.setDate(date.getDate() + value);
                        break;
                    case 'month':
                        returnDate.setMonth(date.getMonth() + value);
                        break;
                    case 'year':
                        returnDate.setFullYear(date.getFullYear() + value);
                        break;
                }
            }

            return returnDate;
        },

        /**
         *  获取日期的季度
         */
        getQuarter: function (date) {
            var month = date.getMonth() + 1;
            if (month <= 3) return 1;
            else if (month <= 6) return 2;
            else if (month <= 9) return 3;
            else return 4
        },

        /**
         * 将字符型转成Date型
         */
        strToDate: function (dateStr, format) {
            var dateParseStr;
            if (format == 'yyyy-MM-dd') {
                dateParseStr = dateStr.replace(/-/g, "/");
            } else if (format == 'yyyyMMdd') {
                dateParseStr = dateStr.substr(0, 4) + '/' + dateStr.substr(4, 2) + '/' + dateStr.substr(6, 2);
            }
            return new Date(Date.parse(dateParseStr));
        },

        /**================================================数组操作============================================*/

        /**
         *  合并数组
         */
        mergeArray: function (array1, array2, etc) {
            var array = []

            for (var i = 0; i < arguments.length; i++) {
                array = array.concat(arguments[i]);
            }

            return array;
        },

        /**
         * 从数组中删除
         */
        removeFromArray: function (array, target, key) {
            if (angular.isObject(target)) {
                for (var i = 0; i < array.length; i++) {
                    if (array[i][key] === target[key]) {
                        array.splice(i, 1);
                    }
                }
            } else {
                for (var i = 0; i < array.length; i++) {
                    if (array[i] === target) {
                        array.splice(i, 1);
                    }
                }
            }
        },

        /**
         * 数组转化成字符串
         */
        arrayToString: function (array, separate) {
            var result = "";
            if (!this.isEmpty(array)) {

                if (this.isEmpty(separate)) {
                    separate = ",";
                }

                for (var i = 0; i < array.length; i++) {
                    result += array[i] + separate;
                }
                return result.substring(0, result.length - 1);
            }

            return result;
        },

        /**
         * 数组中是否包含
         */
        isArrayContains: function (array, obj) {
            if (this.isEmpty(array)) {
                return false;
            }

            for (var i = 0; i < array.length; i++) {
                if (array[i] == obj) {
                    return true;
                }
            }

            return false;
        },
        loading: function (status) {
            var element = $("#utilsLoading");
            if (!$(element).length) {
                var loadHtml =
                    "<div class='modal fade' id='utilsLoading' data-backdrop='static' style='background: rgba(153, 153, 153, 0.1)'>" +
                    "<div class='modal-dialog' role='document' style='position: absolute;top:40%; left: 50%;'>" +
                    "<div><img src='/Application/Tpl/Public/images/ajax-loader.gif' alt='' width='40'></div>" +
                    "</div></div>";
                var loadWrap = document.createElement("div");
                loadWrap.innerHTML = loadHtml;
                document.querySelector("body").appendChild(loadWrap);
            }
            var type = status ? "show" : "hide";

            if (typeof  element.modal === "function") {
                $("#utilsLoading").modal(type);
            }
        }

    };

})(window, jQuery);
