var vm = new Vue({
    el: "#showList",
    data: {
        labelPosition: 'left',
        labelModal: 'top',
        exportDialog: false,
        form: {
            seWorkNum: '',
            seDept: '',
            seWorkplace: '',
            seStatus: '',
            seScNm: '',
            seLeader: '',
            seComPh: '',
            seJobCd: '',
            seTrName: '',
            seEmail: '',
            seCellPh: '',
            seJobType: '',
            seName: '',
            seMonk: '',
            seKey: '',
            sePage: 1,
            pageSize: 20
        },
        editForm: {
            //修改选项
            editOption: 'perJobDate',
            //修改内容
            perJobDate: '',
            deptName: '',
            emplGroup: '',
            jobCd: '',
            JobEnCd: '',
            workPlace: '',
            directLeader: '',
            departHead: '',
            dockingHr: '',
            rank: '',
            sex: '',
            perIsSmoking: '',
            perIsMarried: '',
            perPolitical: '',
            hosehold: '',
            perNational: '',
            status: ''
        },
        edit: {
            status: false,
            checkNum: 0,
            checkedAll: false
        },
        data: [],
        selData: [],
        batch: [
            {
                label: "入职时间",
                value: 'perJobDate'
            }, {
                label: "部门",
                value: 'deptName'
            }, {
                label: "组别",
                value: 'emplGroup'
            }, {
                label: "中文职位",
                value: 'jobCd'
            }, {
                label: "工作地点",
                value: 'workPlace'
            }, {
                label: "直接领导",
                value: 'directLeader'
            }, {
                label: "部门总监",
                value: 'departHead'
            }, {
                label: "对接HR",
                value: 'dockingHr'
            }, {
                label: "职级",
                value: 'rank'
            }, {
                label: "性别",
                value: 'sex'
            }, {
                label: "是否吸烟",
                value: 'perIsSmoking'
            }, {
                label: "婚姻状况",
                value: 'perIsMarried'
            }, {
                label: "政治面貌",
                value: 'perPolitical'
            }, {
                label: "户口性质",
                value: 'hosehold'
            }, {
                label: "民族",
                value: 'perNational'
            }, {
                label: "状态",
                value: 'status'
            }
        ],
        //导出选中例子
        exportCheckList: [],
        //导出选项
        exportOption: [
            {
                label: "工号",
                value: 'workNum'
            }, {
                label: "花名",
                value: 'EmpScNm'
            }, {
                label: "部门",
                value: 'deptName'
            }, {
                label: "组别",
                value: 'deptGroup'
            }, {
                label: "司龄",
                value: 'companyAge'
            }, {
                label: "中文职位",
                value: 'jobCd'
            }, {
                label: "英文职位",
                value: 'JobEnCd'
            }, {
                label: "工作地点",
                value: 'workPlace'
            }, {
                label: "直接领导",
                value: 'directLeader'
            }, {
                label: "部门总监",
                value: 'departHead'
            }, {
                label: "对接HR",
                value: 'dockingHr'
            }, {
                label: "职级",
                value: 'rank'
            }, {
                label: "离职时间",
                value: 'depJobDate'
            }, {
                label: "离职编号",
                value: 'depJobNum'
            }, {
                label: "ERP账号",
                value: 'erpAct'
            }, {
                label: "ERP密码",
                value: 'erpPwd'
            }, {
                label: "状态",
                value: 'status'
            }, {
                label: "真名",
                value: 'empNm'
            }, {
                label: "联系方式",
                value: 'prePhone'
            }, {
                label: "分机号",
                value: 'offTel'
            }, {
                label: "职位类别",
                value: 'jobTypeCd'
            }, {
                label: "身份证号码",
                value: 'perCartId'
            }, {
                label: "性别",
                value: 'sex'
            }, {
                label: "是否吸烟",
                value: 'perIsSmoking'
            }, {
                label: "出生日期",
                value: 'perBirthDate'
            }, {
                label: "年龄",
                value: 'age'
            }, {
                label: "籍贯",
                value: 'perAddress'
            }, {
                label: "户籍",
                value: 'perResident'
            }, {
                label: "婚姻状态",
                value: 'perIsMarried'
            }, {
                label: "子女数",
                value: 'childNum'
            }, {
                label: "政治面貌",
                value: 'perPolitical'
            }, {
                label: "户口性质",
                value: 'hosehold'
            }, {
                label: "民族",
                value: 'perNational'
            }, {
                label: "公积金账号",
                value: 'fundAccount'
            }, {
                label: "花名邮箱",
                value: 'scEmail'
            }, {
                label: "私人邮箱",
                value: 'email'
            }, {
                label: "微信",
                value: 'weChat'
            }, {
                label: "私人邮箱",
                value: 'email'
            }, {
                label: "QQ",
                value: 'qqAccount'
            }, {
                label: "户籍地址",
                value: 'houAdderss'
            }, {
                label: "爱好及特长",
                value: 'hobbySpa'
            }]
    },
    created: function () {
        this.search();
        this.getSelData();
    },
    methods: {
        search: function () {
            axios.post('/index.php?m=Api&a=search', {params: JSON.stringify(this.form)})
                .then(function (res) {
                    if (res.data.code === 200) {
                        vm.data = res.data.data;
                        for (var i = vm.data.length; i--;) {
                            Vue.set(vm.data[i], 'checked');
                        }
                    } else {
                        vm.$message({
                            message: res.data.data,
                            type: 'warning'
                        });
                    }

                });
        },
        reset: function () {
            this.form = {
                seWorkNum: '',
                seDept: '',
                seWorkplace: '',
                seStatus: '',
                seScNm: '',
                seLeader: '',
                seComPh: '',
                seJobCd: '',
                seTrName: '',
                seEmail: '',
                seCellPh: '',
                seJobType: '',
                seName: '',
                seMonk: '',
                seKey: '',
                sePage: 1,
                pageSize: 20
            }
        },
        //获取下拉数据
        getSelData: function (param, option) {
            axios.post('/index.php?m=Api&a=choice', param)
                .then(function (res) {
                    vm.selData = res.data[0];
                    vm.selData.sex = [{id: 0, name: '男'}, {id: 1, name: '女'}];
                    if (option === 'jobEn') {
                        vm.editForm.JobEnCd = vm.selData.joben.ETC;
                    }
                });
        },
        handleSizeChange: function (size) {
            this.form.pageSize = size;
        },
        handleCurrentChange: function (currentPage) {
            this.search();
        },
        checkJob: function (value) {
            var param = {jobzh: value};
            this.getSelData(param, 'jobEn');
        },
        //批量操作
        batchEdit: function () {
            this.edit.checkNum = 0;
            var flag = true,
                i = this.data.length;
            for (i; i--;) {
                if (this.data[i].checked) {
                    flag = false;
                    this.edit.checkNum++;
                }
            }
            if (flag) {
                this.$message({
                    message: '请至少勾选一个人员',
                    type: 'warning'
                });
            } else {
                this.edit.status = true;
            }
        },
        checkAll: function () {
            var i = this.data.length;
            for (i; i--;) {
                this.data[i].checked = this.edit.checkedAll;
            }
        },
        saveBatch: function () {
            if (this.editForm[this.editForm.editOption]) {
                var i = this.data.length;
                for (i; i--;) {
                    if (this.data[i].checked) {
                        this.data[i][this.editForm.editOption] = this.editForm[this.editForm.editOption];
                        axios.post('/index.php?m=Api&a=batchChange', JSON.stringify(this.data[i]))
                            .then(function (res) {
                                if (res.data.code === 200) {
                                    vm.edit.status = false;
                                    vm.$message({
                                        message: '修改成功',
                                        type: 'warning'
                                    });
                                } else {
                                    vm.$message({
                                        message: res.data.data,
                                        type: 'error'
                                    });
                                }
                            })
                    }
                }
            } else {
                vm.$message({
                    message: '修改内容不能为空',
                    type: 'warning'
                });
            }
        },
        //导入模板
        importExcel: function (res, file, fileList) {
            if (res[0].code === 200) {
                vm.$message({
                    message: res[0].data,
                    type: 'success'
                });
                this.search();
            } else {
                vm.$message({
                    message: res[0].data,
                    type: 'error'
                });
            }
        },
        //导出模板
        exportEmp: function () {
            var url = '/index.php?m=Api&a=export_emp&EMPL_ID=',
                flag = true,
                i = this.data.length;
            for (i; i--;) {
                if (this.data[i].checked) {
                    flag = false;
                    url += this.data[i].EMPL_ID + ',';
                }
            }
            if (flag) {
                this.$message({
                    message: '请至少勾选一个人员',
                    type: 'warning'
                });
            } else {
                // this.exportDialog = true;
                var param = url.substring(0, url.length - 1);
                var form = document.createElement('form');
                form.setAttribute('style', 'display:none');
                form.setAttribute('target', '');
                form.setAttribute('method', 'post');
                form.setAttribute('action', param);
                document.body.appendChild(form);
                form.submit();
                form.remove();
            }
        },
        //  自定义导出
        /*  confirmExport:function () {
         console.log(this.exportCheckList)
         if(!this.exportCheckList.length){
         this.$confirm('确认删除此条信息？', '提示',{type:'warning'})
         .then(function () {

         })
         }
         /!*var param = url.substring(0,url.length - 1);
         var form = document.createElement('form');
         form.setAttribute('style','display:none');
         form.setAttribute('target','');
         form.setAttribute('method','post');
         form.setAttribute('action',param);
         document.body.appendChild(form);
         form.submit();
         form.remove();*!/
         },*/
        downloadTemp: function () {
            var aEle = document.createElement("a");
            aEle.setAttribute("style", "display: none");
            aEle.setAttribute("href", '/index.php?m=Api&a=download');
            aEle.click();
        },
        //查看人员详细信息
        seeDetail: function (id) {
            var url = "/index.php?m=Hr&a=addPerson&id=" + id;
            var aEle = document.createElement("a");
            aEle.setAttribute("style", "display: none");
            aEle.setAttribute("href", url);
            aEle.click();
        },
        //删除人员信息
        delInfo: function (id) {
            this.$confirm('确认删除此条信息？', '提示', {type: 'warning'})
                .then(function () {
                    axios.post('/index.php?m=Api&a=emplDelele', {emplId: id})
                        .then(function (res) {
                            if (res.data.code === 200) {
                                vm.$message({
                                    message: '删除成功',
                                    type: 'success'
                                });
                                vm.search();
                            } else {
                                vm.$message({
                                    message: '删除失败',
                                    type: 'warning'
                                });
                            }
                        })
                });
        }
    }
});