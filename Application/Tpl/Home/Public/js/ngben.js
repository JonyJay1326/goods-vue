var myApp = angular.module('myApp', ['ngAnimate','ui.bootstrap','ui.router']);
myApp.config(function ($stateProvider,$httpProvider) {
    $httpProvider.defaults.transformRequest = [function (data) {
        /**
         * 传输参数时的格式化
         * @param {Object} obj
         * @return {String}
         */
        var param = function (obj) {
            var query = '';
            var name, value, fullSubName, subName, subValue, innerObj, i;
            for (name in obj) {
                value = obj[name];
                if (value instanceof Array) {
                    for (i = 0; i < value.length; ++i) {
                        subValue = value[i];
                        fullSubName = name + '[]';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                } else if (value instanceof Object) {
                    for (subName in value) {
                        subValue = value[subName];
                        fullSubName = subName;
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                } else if (value !== undefined && value !== null) {
                    query += encodeURIComponent(name) + '='
                        + encodeURIComponent(value) + '&';
                }
            }
            return query.length ? query.substr(0, query.length - 1) : query;
        };
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];

    var dynamicpinState = {
        name: 'dynamicpin',
        url: '/dynamicpin',
        templateUrl: 'index.php?m=dynamic&a=ngpin',
        controller: "dynamicpin"
    }
    var b2bState = {
        name: 'b2b',
        url: '/b2b',
        templateUrl: 'index.php?m=b2b&a=ngorder_list',
        controller: "b2b"
    }
    var b2baddState = {
        name: 'b2badd',
        url: '/b2badd',
        templateUrl: 'index.php?m=b2b&a=ngorder_add',
        controller: "b2bAdd"
    }
    var b2bSendState = {
        name: 'b2bsend',
        url: '/b2bsend',
        templateUrl: 'index.php?m=b2b&a=ngsend_list',
        controller: "b2bSend"
    }

    $stateProvider.state(dynamicpinState)
        .state(b2bState)
        .state(b2baddState)
        .state(b2bSendState);

});


myApp.service('$Ajax', function ($http) {
        return {
            post: function (path, params, successFn, failureFn,flieType) {

                if (typeof failureFn != "function") {
                    failureFn = function () {
                    };
                }
                var responseType = flieType ? 'arraybuffer':null;
                $http({
                    url: path,
                    method: 'POST',
                    timeout: 60000,
                    // withCredentials: false,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=utf8'
                    },
                    responseType: responseType,
                    data: params
                }).success(function (result) {
                        successFn.call(this, result);
                }).error(failureFn);
            },
            get: function (path, param, successFn, errorFn) {
                $http({
                    method: 'GET',
                    url: path,
                    params: param
                }).success(function (data, status, header, config) {
                    if (successFn && typeof (successFn) == 'function') {
                        successFn(data, status, header, config);
                    }
                }).error(function (data, status, header, config) {
                    if (errorFn && typeof (errorfun) == 'function') {
                        errorFn(data, status, header, config);
                    }
                })
            }
        };
    }
);
