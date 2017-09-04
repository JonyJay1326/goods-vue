var Vm = new Vue({
    el: '#hrAdd',
    data: {
        //标题文字方向
        title: '',
        labelPosition: 'left',
        //控制展示页面
        control: {
            admin: '',
            staff: '',
            id: ''
        },
        //编辑参数
        edit: {
            show: false,
        },
        //查看时的url
        url: {
            Pic: '',
            perCardPic: '',
            resume: '',
            graCert: '',
            degCert: '',
            learnProve: ''
        },
        //切换菜单选项
        menu: {
            personInfo: true
        },
        //第一页定义字段
        form: {
            //hr 模块
            Pic: '',
            workNum: '',
            EmpScNm: '',
            perJobDate: '',
            deptName: '',
            deptGroup: '',
            companyAge: '',
            jobCd: '',
            JobEnCd: '',
            workPlace: '',
            directLeader: '',
            departHead: '',
            dockingHr: '',
            rank:'',
            status: '',
            depJobDate: '',
            depJobNum: '',
            erpAct: '',
            erpPwd: '',
            //个人信息模块
            empNm: '',
            prePhone: '',
            offTel: '',
            jobTypeCd: '',
            perCartId: '',
            sex: '',
            perIsSmoking: '',
            perBirthDate: '',
            houAdderss: {
                proh: '',
                cityH: '',
                areaH: '',
                detailH: ''
            },
            livingAddress: {
                provL: '',
                cityL: '',
                areaL: '',
                detailL: ''
            },
            age: '',
            perAddress: '',
            perResident: '',
            perIsMarried: '',
            childNum: '',
            childBoyNum: '',
            childGirlNum: '',
            perPolitical: '',
            hosehold: '',
            fundAccount: '',
            perNational:'',
            scEmail: '',
            email: '',
            weChat: '',
            qqAccount: '',
            firstLan: '',
            firstLanLevel: '',
            secondLan: '',
            secondLanLevel: '',
            hobbySpa: '',
            //紧急联系人
            concatRel: '',
            concatName: '',
            concatWay: '',
            //教育经历
            eduExp: [
                {
                    eduStartTime: '',
                    eduEndTime: '',
                    schoolName: '',
                    eduMajors: '',
                    eduDegNat: '',
                    isDegree: '',
                    certiNo: '',
                    validateRes: ''
                }
            ],
            //工作经历
            workExp: [
                {
                    wordStartTime: '',
                    wordEndTime: '',
                    companyName: '',
                    posi: '',
                    depReason: ''
                }
            ],
            //家庭情况
            home: [
                {
                    homeRes: '',
                    homeName: '',
                    homeAge: '',
                    occupa: '',
                    workUnits: ''
                }
            ],
            //培训经验
            training: [
                {
                    trainingName: '',
                    trainingStartTime: '',
                    trainingEndTime: '',
                    trainingIns: '',
                    trainingDes: ''
                }
            ],
            //资格证书
            certificate: [
                {
                    certiName: '',
                    certifiTime: '',
                    certifiunit: ''
                }
            ],
            //银行卡信息
            bankCard: [
                {
                    bankAct: '',
                    bankName: '',
                    swiftCood: '',
                    bankDeposit: '',
                    BankEndeposit: ''
                }
            ]
        },
        // 第二页字段定义
        form2: {
            //合同信息
            contract: [
                {
                    conCompany: '',
                    natEmploy: '',
                    trialEndTime: '',
                    conStartTime: '',
                    conEndTime: ''
                }
            ],
            reward: [
                {
                    rewardName: '',
                    rewardContent: ''
                }
            ],
            promo: [
                {
                    promoType: '',
                    promoTime: '',
                    promoContent: ''
                }
            ],
            paperMiss: [
                {
                    paperMissTime: '',
                    paperMissCon: ''
                }
            ],
            interArr: [
                {
                    interType: '',
                    interTime: '',
                    interObj: '',
                    interPerson: '',
                    interContent: '',
                    afterCase: ''
                }
            ]
        },
        //下拉选项数据
        selData: {},
        //个人信息数据
        infoData: {},
        address: {
            houProvinceData: [],
            houCityData: [],
            houAreaData: [],
            livingProvinceData: [],
            livingCityData: [],
            livingAreaData: []
        },
        //验证是否为空prop
        rules:{},
        rulesHr: {
            workNum: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            EmpScNm: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perJobDate: [
                {type: 'date', required: true, message: ' ', trigger: 'blur'}
            ],
            deptName: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            deptGroup: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            companyAge: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            jobCd: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            JobEnCd: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            workPlace: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            directLeader: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            departHead: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            dockingHr: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            rank: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            erpAct: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            erpPwd: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            status: [
                {required: true, message: ' ', trigger: 'blur'}
            ]
        },
        rulesPer: {
            workNum: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            EmpScNm: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perJobDate: [
                {type: 'date', required: true, message: ' ', trigger: 'blur'}
            ],
            deptName: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            deptGroup: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            companyAge: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            jobCd: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            JobEnCd: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            workPlace: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            directLeader: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            departHead: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            dockingHr: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            rank: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            erpAct: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            erpPwd: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            status: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            empNm: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            prePhone: [
                {required: true, message: ' ', trigger: 'blur'}
            ],

            jobTypeCd: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perCartId: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            sex: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perIsSmoking: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perBirthDate: [
                {type: 'date', required: true, message: ' ', trigger: 'change'}
            ],
            age: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perAddress: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perResident: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perIsMarried: [
                {required: true, message: ' ', trigger: 'change'}
            ],
            perPolitical: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            hosehold: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            perNational: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            fundAccount: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            scEmail: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            email: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            weChat: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            qqAccount: [
                {required: true, message: ' ', trigger: 'blur'}
            ],
            hobbySpa: [
                {required: true, message: ' ', trigger: 'blur'}
            ]
        }
    },
    created: function () {
        var param = $("#ctrl").val(),
            staff = param.indexOf('card') > -1,
            admin = param.indexOf('addPerson') > -1,
            getId = param.split("=").reverse()[0],
            id = isNaN(Number(getId)) ? null : Number(getId);
        this.getSelData();
        //获取个人信息
        if (staff || id) {
            this.card(id);
        }

        if(admin && !id){
            this.rules = this.rulesHr;
        }

        this.control.admin = admin;
        this.control.staff = staff;
        this.control.id = id;

        if (staff) {
            this.title = "我的名片";
        } else if (admin && id) {
            this.title = "员工名片";
        } else if (admin) {
            this.title = "新建人员";
        }

        //取消省市联动
        /*  axios.post('/index.php?m=Api&a=address')
             .then(function (res) {
                 var address = JSON.parse(res.data[0]);
                 Vm.address.houProvinceData = address.data;
                 Vm.address.livingProvinceData = address.data;
             })*/
    },
    methods: {
        //获取个人信息
        card: function (id) {
            axios.post('/index.php?m=Api&a=card', {emplID: id})
                .then(function (res) {
                    if (res.data.code === 200) {
                        //个人信息
                        Vm.form = res.data.data.cardInfo;
                        //教育经历
                        Vue.set(Vm.form, 'eduExp', res.data.data.eduInfo);

                        //工作经历
                        Vue.set(Vm.form, 'workExp', res.data.data.workExp);

                        //家庭情况
                        Vue.set(Vm.form, 'home', res.data.data.home);

                        //培训经验
                        Vue.set(Vm.form, 'training', res.data.data.training);

                        //资格证书
                        Vue.set(Vm.form, 'certificate', res.data.data.certificate);

                        //银行卡信息
                        Vue.set(Vm.form, 'bankCard', res.data.data.bankCard);

                        //紧急联系人
                        for (var key in res.data.data.friInfo) {
                            Vue.set(Vm.form, key, res.data.data.friInfo[key])
                        }

                        //跟踪信息
                        if (res.data.data.conInfo.length) {
                            Vm.form2.contract = res.data.data.conInfo;
                        }
                        if (res.data.data.reward.length) {
                            Vm.form2.reward = res.data.data.reward;
                        }
                        if (res.data.data.promo.length) {
                            Vm.form2.promo = res.data.data.promo;
                        }
                        if (res.data.data.paper.length) {
                            Vm.form2.paperMiss = res.data.data.paper;
                        }
                        if (res.data.data.inter.length) {
                            Vm.form2.interArr = res.data.data.inter;
                        }
                        if(Vm.control.staff && !Vm.form.empNm){
                            Vm.rules = Vm.rulesPer
                        }
                    }
                })
        },
        //编辑按钮
        editContent: function () {
            this.edit.show = !this.edit.show;
            /*this.edit.text = this.edit.show ? '取消' : '编辑';
            if (this.control.staff || this.control.id) {
                this.card(this.control.id);
            }*/
        },
        //获取下拉数据
        getSelData: function (param, option) {
            axios.post('/index.php?m=Api&a=choice', param)
                .then(function (res) {
                    Vm.selData = res.data[0];
                    if (option === 'jobEn') {
                        Vm.form.JobEnCd = Vm.selData.joben.ETC;
                    }
                });
        },
        //保存第一页
        saveForm: function () {
            var interfaceName = this.form.emplid ? 'editCard' : 'AddPersonnel';
            axios.post('/index.php?m=Api&a=' + interfaceName, {params: JSON.stringify(this.form)})
                .then(function (res) {
                    if (res.data.code === 200) {
                        Vm.$message({
                            message: Vm.form.emplid ? res.data.data : res.data.data.res,
                            type: 'success'
                        });
                        //保存生成的ID 带到第二页
                        if (interfaceName === 'editCard') {
                            Vm.card(Vm.form.emplid);
                            Vm.edit.show = false;
                            Vm.edit.text = '编辑';

                        } else {
                           /* Vm.menu.personInfo = false;
                            Vm.form2.lastInsertId = res.data.data.lastInsertId;*/
                            var route = document.createElement("a");
                            route.setAttribute("style", "display: none");
                            route.setAttribute("onclick", "opennewtab(this,'人员列表')");
                            route.setAttribute("_href", '/index.php?m=hr&a=showList');
                            route.click();
                        }
                    } else {
                        Vm.$message({
                            message: res.data.data,
                            type: 'error'
                        });
                    }
                })
        },
        //取消保存
        cancel: function () {
            Vm.edit.show = false;
            console.log(this.form.emplid);
            if(this.form.emplid){
                Vm.card(this.form.emplid)
            }else{
                var form = document.getElementsByTagName("form");
                for(var i = form.length;i--;){
                    form[i].reset();
                }
            }
        },
        //保存第二页
        saveForm2: function () {
            var interfaceName1 = this.form.emplid ? 'changeTrack' : 'addTrack';
            this.form2.emplid = this.form.emplid;
            axios.post('/index.php?m=Api&a=' + interfaceName1, {params: JSON.stringify(this.form2)})
                .then(function (res) {
                    if (res.data.code === 200) {
                        Vm.$message({
                            message: res.data.data,
                            type: 'success'
                        });
                        if (interfaceName1 === 'addTrack') {
                            var route = document.createElement("a");
                            route.setAttribute("style", "display: none");
                            route.setAttribute("onclick", "opennewtab(this,'人员列表')");
                            route.setAttribute("_href", '/index.php?m=hr&a=showList');
                            route.click();
                        } else {
                            Vm.edit.show = false;
                            Vm.edit.text = '编辑';
                            Vm.card(Vm.form.emplid);
                        }
                    } else {
                        Vm.$message({
                            message: res.data.data,
                            type: 'error'
                        });
                    }
                });
        },
        //上传文件成功
        uploadFileFun: function (res, file, fileList) {
            //只留一个文件
            if (fileList.length > 1) {
                fileList.splice(0, 1);
            }
            if (res.code === 200) {
                Vm.$message({
                    message: '上传成功',
                    type: 'success'
                });
                //url 查看生成的url,savename 保存时发送的名称
                this.url[res.data.name] = URL.createObjectURL(file.raw);
                this.form[res.data.name] = res.data.savename;
            } else {
                //清空文件列表
                this.$refs[res.data.name].clearFiles();
                Vm.$message({
                    message: res.data.res,
                    type: 'error'
                });
            }
        },
        //查看预览功能
        preview: function (param) {
            if (this.url[param]) {
                window.open(this.url[param], '查看文件')
            } else {
                Vm.$message({
                    title: '警告',
                    message: '未上传附件',
                    type: 'warning',
                    duration: 1500
                });
            }
        },
        //查看附件
        previewAnnex: function (key, data) {
            if (data[key]) {
                var url = "/index.php?m=Api&a=checkAttach&" + key + "=" + data[key];
                var aEle = document.createElement("a");
                aEle.setAttribute("style", "display: none");
                aEle.setAttribute("href", url);
                aEle.click();
            } else {
                Vm.$message({
                    title: '警告',
                    message: '未找到附件',
                    type: 'warning',
                    duration: 1500
                });
            }
        },
        //添加条目信息
        addEntry: function (key) {
            var item = {};
            switch (key) {
                case 'eduExp':
                    item = {
                        eduStartTime: '',
                        eduEndTime: '',
                        schoolName: '',
                        eduMajors: '',
                        eduDegNat: '',
                        isDegree: '',
                        certiNo: ''
                    };
                    break;
                case 'workExp':
                    item = {
                        wordStartTime: '',
                        wordEndTime: '',
                        companyName: '',
                        posi: '',
                        depReason: ''
                    };
                    break;
                case 'home':
                    item = {
                        homeRes: '',
                        homeName: '',
                        homeAge: '',
                        occupa: '',
                        workUnits: ''
                    };
                    break;
                case 'training':
                    item = {
                        trainingName: '',
                        trainingStartTime: '',
                        trainingEndTime: '',
                        trainingIns: '',
                        trainingDes: ''
                    };
                    break;
                case 'certificate':
                    item = {
                        certiName: '',
                        certifiTime: '',
                        certifiunit: ''
                    };
                    break;
                case 'bankCard':
                    item = {
                        bankAct: '',
                        bankName: '',
                        swiftCood: '',
                        bankDeposit: '',
                        BankEndeposit: ''
                    };
                    break;
                default:
                    Vm.$message({
                        title: '警告',
                        message: '无法增加条目',
                        type: 'warning',
                        duration: 1500
                    });
                    break;
            }
            this.form[key].push(item);
        },
        //删减条目信息
        delEntry: function (key, item) {
            var expArr = this.form[key],
                i = expArr.length;
            for (i; i--;) {
                if (expArr[i] === item) {
                    if (expArr.length > 1) {
                        expArr.splice(i, 1)
                    }
                }
            }
        },
        //添加员工交谈
        addChat: function () {
            var item = {
                interType: '',
                interTime: '',
                interObj: '',
                interPerson: '',
                interContent: '',
                afterCase: ''
            };
            this.form2.interArr.push(item);
        },
        //添加合同
        addContract: function () {
            var item = {
                conCompany: '',
                natEmploy: '',
                trialEndTime: '',
                conStartTime: '',
                conEndTime: ''
            };
            this.form2.contract.push(item);
        },
        //删除合同
        delContract: function (item) {
            var contract = this.form2.contract,
                i = contract.length;
            for (i; i--;) {
                if (contract[i] === item) {
                    if (contract.length > 1) {
                        contract.splice(i, 1)
                    } else {
                        this.$message({
                            title: '警告',
                            message: '不能删除最后一条',
                            type: 'warning',
                            duration: 1500
                        });
                    }
                }
            }
        },
        //添加奖惩信息
        addReward: function () {
            var item = {
                rewardName: '',
                rewardContent: ''
            };
            this.form2.reward.push(item);
        },
        //添加晋升记录
        addPromo: function () {
            var item = {
                promoType: '',
                promoTime: '',
                promoContent: '',
            };
            this.form2.promo.push(item);
        },
        //添加日报缺失
        addPaperMiss: function () {
            var item = {
                paperMissTime: '',
                paperMissCon: ''
            };
            this.form2.paperMiss.push(item);
        },
        //省市联动 选取省市
        getAdd: function (areaNo, addressData) {
            axios.post('/index.php?m=Api&a=address', {areaNo: areaNo})
                .then(function (res) {
                    var addressObj = JSON.parse(res.data[0]);
                    Vue.set(Vm.address, addressData, addressObj.data);
                })
        },
        checkJob: function (value) {
            var param = {jobzh: value};
            this.getSelData(param, 'jobEn');
        },
        readCardId: function () {
            var cardId = this.form.perCartId;
            if (cardId.length < 18) {
                this.$message({
                    title: '警告',
                    message: '请输入18位号码',
                    type: 'warning',
                    duration: 1500
                });
            } else {
                var nowDate = new Date();
                this.form.perBirthDate = new Date(cardId.substring(6, 10), cardId.substring(10, 12) - 1, cardId.substring(12, 14));
                this.form.age = nowDate.getFullYear() - cardId.substring(6, 10);
                this.form.sex = cardId.substring(16, 17) % 2 === 0 ? '1' : '0';
            }
        },
        //计算司龄
        countComAge: function (value) {
            var nowDate = new Date(),
                entryDate = new Date(value),
                year = (nowDate.getFullYear() - entryDate.getFullYear()) * 12,
                month = (nowDate.getMonth() - entryDate.getMonth());
            this.form.companyAge = (year + month) ? (year + month) + "月" : "未满一个月";
        }
    }
});

