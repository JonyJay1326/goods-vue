(function() {
    var d = document, w = window;
    
    function addEvent(el, type, fn){
    	if (w.addEventListener){
    		el.addEventListener(type, fn, false);
    	} else if (w.attachEvent){
    		var f = function(){
    		  fn.call(el, w.event);
    		};			
    		el.attachEvent('on' + type, f)
    	}
    }
    
    PayNode = function (obj, options) {
        this._obj = obj;
        this._settings = {
            name: '',
            data: ['periods', 'day', 'workday', 'percentage'],
            pnumber: 0,
        };
        this.init_data = '';
        this._startPeriods = 1;
        this._sdata;
        this._checkIsChange = {
            periods: false,
            percentage: false,
        };
        
        this._isCheckedPercentage = [];
        this._spercentage = []; // 保存已选中的分期数
        
        for (var i in options) {
            this._settings[i] = options [i];
        }
        this._pnu = this._settings.pnumber;
        this._createSelect();
        this._temporary = 0;
    };
    
    PayNode.prototype = {
        _createSelect: function () {
            self = this;
            if (!self._sdata) self._sdata = this._settings.data;
            var div = document.createElement('div');
            for (var j in self._sdata) {
                var select = document.createElement('select');
                select.setAttribute('class', j);
                select.setAttribute('name', this._settings.name);
                for (i in self._style()) {
                    select.style[i] = self._style()[i];
                }
                if (j == 'periods') {
                    var s = new Option('第' + self._startPeriods + '期', '');
                    select.options.add(s);
                }
                if (j == 'percentage') {
                    var s = new Option('分期百分比', '');
                    select.options.add(s);
                    for (var i in self._sdata[j][0]) {
                        var opt = new Option(self._sdata[j][0][i] + '%', i);
                        select.options.add(opt);
                    }
                }
                //if (j == 'workday') {
//                    var s = new Option();
//                    select.options.add(s);
//                }
                if (j == 'day') {
                    var s = new Option('付款天数', '');
                    select.options.add(s);
                }
                var st = self._settings.pnumber;
                var k = st;
                for (var i in self._sdata[j][0]) {
                    if (j == 'percentage') continue;
                    var opt = new Option(self._sdata[j][0][i], i);
                    select.options.add(opt);
                }
                div.append(select);
                if (j == 'periods') {
                    for (var i = self._settings.pnumber - 1; i > 0; i --) {
                        select.options[select.length - i].disabled = true;
                    }
                }
                if (j == 'percentage') {
                    if (self._settings.pnumber == 1) {
                        //select.options[select.length - 1].selected = true;
//                        console.log($(select.options[select.length - 1]));
//                        $(select.options[select.length - 1]).siblings().attr('disabled', 'true');
                    } else {
                        select.options[select.length - 1].disabled = true;
                    }
                    for (var i = this._settings.pnumber - 1; i > 0; i --) {
                        //select.options[select.length].disabled = true;
                    }
                }
                addEvent(select, 'change', function(e) {
                    var i = 1;
                    // 生成期数数据
                    if ($(this).hasClass('periods')) {
                        //$(this).next().next()[0].options[0].selected = true;
                        var periods_temp = '';
                        for (var i = this.selectedIndex + 1; i < this.length;  i++) {
                            periods_temp += '"' + this.options[i].value + '":"' + this.options[i].text + '",';
                        }
                        periods_temp = periods_temp.substring(0, periods_temp.length - 1);
                        periods_temp = "[{" + periods_temp + "}]";
                        self._sdata.periods = $.parseJSON(periods_temp);
                        if (this.value) self._checkIsChange.periods = true;
                        self._clean($(this));
                    }
                    // 生成分期百分比数据
                    if ($(this).hasClass('percentage')) {
                        var percentage_temp = '';
                        var _nvalue = this.value;
                        var temp_nvalue = 0;
                        
                        // 对于已被选中的分期数，进行入队
                        self._isCheckedPercentage.push(this.value);
                        $.unique(self._isCheckedPercentage);
                        
                        for (var i in self._settings.data.percentage[0]) {
                            var temp = 0;
                            var sum = 0;
                            for (var j = 0; j < self._isCheckedPercentage.length; j ++) {
                                sum += parseInt(self._isCheckedPercentage[j]);
                            }
                            if ((sum + parseInt(self._settings.data.percentage[0][i])) <= 100) {
                                percentage_temp += '"' + self._settings.data.percentage[0][i] + '":"' + self._settings.data.percentage[0][i] + '",';
                            }
                        }

                        if (this.value) self._checkIsChange.percentage = true;
                        //self._clean($(this));
                    }
                    
                    if (self._checkIsChange.periods == true || self._checkIsChange.percentage == true) {
                        if (self._settings.pnumber > 1) {
                            self._settings.pnumber --;
                            self._startPeriods ++;
                            self._createSelect(self._sdata, this.value);
                        }
                        self._initIsChange();
                    }
                });
            }
            this._obj.append(div);
        },
        // 生成单例的select
        _createSingleCase: function () {
            
        },
        // 某一个节点发生改变，需要动态去生成下一个节点
        _clean: function (obj) {
            self = this;
            self._settings.pnumber += obj.parent().nextAll().length;
            self._startPeriods -= obj.parent().nextAll().length;
            obj.parent().nextAll().remove();
        },
        
        _generateNewData: function () {
            self = this;
        },
        
        _initIsChange: function () {
            self = this;
            self._checkIsChange.periods = false;
            self._checkIsChange.percentage = false;
        },
        _style: function () {
            var styles = {
    			'width': '100px',
    			'height': '30px',
    			'fontSize': '14px',
                'margin-top': '5px',
                'margin-left': '5px'			
    		};
            return styles;
        }
    };
})();