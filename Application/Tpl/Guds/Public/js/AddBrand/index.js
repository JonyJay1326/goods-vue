webpackJsonp([9],{149:function(t,a){},154:function(t,a){t.exports={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"add-brand-content"},[e("header",[t._v("公司信息")]),t._v(" "),e("div",{staticClass:"company-info"},[e("el-row",{staticClass:"row-line row01",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title necessary"},[t._v("品牌ID")])]),t._v(" "),e("el-col",{attrs:{span:7}},[e("el-input",{attrs:{placeholder:""},model:{value:t.brandID,callback:function(a){t.brandID=a},expression:"brandID"}})],1),t._v(" "),e("el-col",{attrs:{span:3}},[e("el-button",{staticClass:"repeat-check",on:{click:t.checkBrandRepeat}},[t._v("重复查询")])],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title necessary"},[t._v("公司名字")])]),t._v(" "),e("el-col",{attrs:{span:7}},[e("el-input",{attrs:{placeholder:""},model:{value:t.companyName,callback:function(a){t.companyName=a},expression:"companyName"}})],1),t._v(" "),e("el-col",{attrs:{span:3}},[e("el-button",{staticClass:"repeat-check",on:{click:t.checkCompanyRepeat}},[t._v("重复查询")])],1)],1),t._v(" "),e("el-row",{staticClass:"row-line row02",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("公司地址")])]),t._v(" "),e("el-col",{staticClass:"address",attrs:{span:4}},[e("el-input",{attrs:{placeholder:""},model:{value:t.address,callback:function(a){t.address=a},expression:"address"}})],1),t._v(" "),e("el-col",{attrs:{span:6}},[e("el-input",{staticClass:"detail-address",attrs:{placeholder:""},model:{value:t.detailAddress,callback:function(a){t.detailAddress=a},expression:"detailAddress"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("法人")])]),t._v(" "),e("el-col",{attrs:{span:3}},[e("el-input",{attrs:{placeholder:""},model:{value:t.artificialPerson,callback:function(a){t.artificialPerson=a},expression:"artificialPerson"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("公司电话")])]),t._v(" "),e("el-col",{staticClass:"company-tel",attrs:{span:1}},[e("el-input",{attrs:{placeholder:"区号"},model:{value:t.tel01,callback:function(a){t.tel01=a},expression:"tel01"}})],1),t._v(" "),e("el-col",{staticClass:"company-tel",attrs:{span:3}},[e("el-input",{attrs:{placeholder:"座机号"},model:{value:t.tel02,callback:function(a){t.tel02=a},expression:"tel02"}})],1),t._v(" "),e("el-col",{attrs:{span:1}},[e("el-input",{attrs:{placeholder:"分机号"},model:{value:t.tel03,callback:function(a){t.tel03=a},expression:"tel03"}})],1)],1),t._v(" "),e("el-row",{staticClass:"row-line row03",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("企业组织机构代码")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-input",{staticClass:"organization-code",attrs:{placeholder:""},model:{value:t.organizationCode01,callback:function(a){t.organizationCode01=a},expression:"organizationCode01"}}),t._v(" "),e("el-input",{staticClass:"organization-code",attrs:{placeholder:""},model:{value:t.organizationCode02,callback:function(a){t.organizationCode02=a},expression:"organizationCode02"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title necessary"},[t._v("业务类型")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-input",{attrs:{placeholder:""},model:{value:t.businessType,callback:function(a){t.businessType=a},expression:"businessType"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("公司业务")])]),t._v(" "),e("el-col",{attrs:{span:3}},[e("el-input",{attrs:{placeholder:""},model:{value:t.companyBusiness,callback:function(a){t.companyBusiness=a},expression:"companyBusiness"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("商业登记号码")])]),t._v(" "),e("el-col",{attrs:{span:5}},[e("el-input",{attrs:{placeholder:""},model:{value:t.regNumber,callback:function(a){t.regNumber=a},expression:"regNumber"}})],1)],1)],1),t._v(" "),e("header",[t._v("品牌信息")]),t._v(" "),e("div",{staticClass:"brand-info"},[e("el-row",{staticClass:"row-line row01",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title necessary"},[t._v("品牌名字(CN)")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-input",{attrs:{placeholder:""},model:{value:t.cnBrandName,callback:function(a){t.cnBrandName=a},expression:"cnBrandName"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title necessary"},[t._v("品牌名字(EN)")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-input",{attrs:{placeholder:""},model:{value:t.enBrandName,callback:function(a){t.enBrandName=a},expression:"enBrandName"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title necessary"},[t._v("品牌名字(KR)")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-input",{attrs:{placeholder:""},model:{value:t.krBrandName,callback:function(a){t.krBrandName=a},expression:"krBrandName"}})],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title necessary"},[t._v("品牌名字(JPA)")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-input",{attrs:{placeholder:""},model:{value:t.jpBrandName,callback:function(a){t.jpBrandName=a},expression:"jpBrandName"}})],1)],1),t._v(" "),e("el-row",{staticClass:"row-line row02",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title "},[t._v("销售渠道")])]),t._v(" "),e("el-col",{attrs:{span:22}},[e("el-checkbox-group",{on:{change:function(a){t.handleCheckedCitiesChange(a)}},model:{value:t.checkedChannel,callback:function(a){t.checkedChannel=a},expression:"checkedChannel"}},t._l(t.distributionChannel,function(a,n){return e("el-checkbox",{key:n,attrs:{label:a,"data-code":n}},[t._v(t._s(a))])}))],1)],1),t._v(" "),e("el-row",{staticClass:"row-line row03",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title "},[t._v("Banner(MOBILE)")])]),t._v(" "),e("el-col",{attrs:{span:10}},[e("form",{staticClass:"updatePicForm",attrs:{action:""}},[e("a",{staticClass:"file",attrs:{href:"javascript:;"}},[t._v("选择文件"),e("input",{staticClass:"picMobile",attrs:{type:"file","data-content":"mobileContent"},on:{change:function(a){t.updatePic(a)}}})]),t._v(" "),e("span",{staticClass:"picName"})])]),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title "},[t._v("Banner(PC)")])]),t._v(" "),e("el-col",{attrs:{span:10}},[e("form",{staticClass:"updatePicForm",attrs:{action:""}},[e("a",{staticClass:"file",attrs:{href:"javascript:;"}},[t._v("选择文件"),e("input",{staticClass:"picPC",attrs:{type:"file","data-content":"pcContent"},on:{change:function(a){t.updatePic(a)}}})]),t._v(" "),e("span",{staticClass:"picName"})])])],1),t._v(" "),e("el-row",{staticClass:"row-line row04",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("LOGO")])]),t._v(" "),e("el-col",{attrs:{span:10}},[e("form",{staticClass:"updatePicForm",attrs:{action:""}},[e("a",{staticClass:"file",attrs:{href:"javascript:;"}},[t._v("选择文件"),e("input",{staticClass:"picLogo",attrs:{type:"file","data-content":"logoContent"},on:{change:function(a){t.updatePic(a)}}})]),t._v(" "),e("span",{staticClass:"picName"})])]),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title "},[t._v("详情图")])]),t._v(" "),e("el-col",{attrs:{span:10}},[e("form",{staticClass:"updatePicForm",attrs:{action:""}},[e("a",{staticClass:"file",attrs:{href:"javascript:;"}},[t._v("选择文件"),e("input",{staticClass:"picDetail",attrs:{type:"file","data-content":"detailContent"},on:{change:function(a){t.updatePic(a)}}})]),t._v(" "),e("span",{staticClass:"picName"})])])],1),t._v(" "),e("el-row",{staticClass:"row-line row05",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("品牌信息")])]),t._v(" "),e("el-col",{attrs:{span:22}},[e("el-input",{staticClass:"brand-info-content",staticStyle:{height:"160px"},attrs:{type:"textarea",placeholder:"请输入内容",resize:"none"},model:{value:t.brandDetailInfo,callback:function(a){t.brandDetailInfo=a},expression:"brandDetailInfo"}})],1)],1),t._v(" "),e("el-row",{staticClass:"row-line row06",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("授权状态")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-select",{staticClass:"choose-country",attrs:{placeholder:""},on:{change:function(a){t.selectAccredit(a)}},model:{value:t.accreditStatusValue,callback:function(a){t.accreditStatusValue=a},expression:"accreditStatusValue"}},t._l(t.accreditStatusList,function(t,a){return e("el-option",{key:a,attrs:{label:t,value:t,"data-code":a}})}))],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("品牌国家")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-select",{staticClass:"choose-country",attrs:{placeholder:""},on:{change:function(a){t.selectBrandCountry(a)}},model:{value:t.brandCountryValue,callback:function(a){t.brandCountryValue=a},expression:"brandCountryValue"}},t._l(t.brandCountryList,function(t,a){return e("el-option",{key:a,attrs:{label:t,value:t,"data-code":a}})}))],1)],1),t._v(" "),e("el-row",{staticClass:"row-line row07",attrs:{type:"flex"}},[e("el-col",{attrs:{span:2}},[e("div",{staticClass:"first-title"},[t._v("所属前端类目")])]),t._v(" "),e("el-col",{attrs:{span:4}},[e("el-select",{staticClass:"choose-country",attrs:{placeholder:""},on:{change:function(a){t.selcetCateData(a)}},model:{value:t.cateDataValue,callback:function(a){t.cateDataValue=a},expression:"cateDataValue"}},t._l(t.cateDataList,function(t){return e("el-option",{key:t.catId,attrs:{label:t.catNamePath,value:t.catNamePath,"data-code":t.catId}})}))],1),t._v(" "),e("el-col",{attrs:{span:2}},[e("i",{staticClass:"el-icon-plus",on:{click:t.addedCateDate}})]),t._v(" "),t._l(t.cateListContent,function(a){return e("el-col",{attrs:{span:2}},[e("span",{staticStyle:{"text-align":"center","font-size":"14px",border:"1px solid #bfcbd9",padding:"6px 10px"}},[t._v(t._s(a))])])})],2)],1),t._v(" "),e("div",{staticClass:"erp-addbrand-btns"},[e("el-button",{staticClass:"addbrand-save-btn",attrs:{type:"primary"},on:{click:t.createBrand}},[t._v("保存")])],1)])},staticRenderFns:[]}},2:function(t,a){},3:function(t,a){},4:function(t,a,e){"use strict";var n="";n="localhost:8801"==window.location.host?"http://erp.stage.com/index.php":"//"+window.location.host+"/index.php",a.a={getBrand:function(){return n+"?g=guds&m=guds&a=addPage"},getBrandInfo:function(t){return n+"?g=guds&m=brand&a=showBrandCateList&brandId="+t+"&isAjax=1"},getOptionList:function(t,a){return n+"?g=guds&m=gudsOptions&a=getOptionList&gudsId="+t+"&sellerId="+a},getBasicOptions:function(){return n+"?g=guds&m=gudsOptions&a=getBasicOptions"},getOptionValues:function(t){return n+"?g=guds&m=guds_options&a=getOptionValues&selectedOptId="+t},getOptionGroup:function(){return n+"?g=guds&m=gudsOptions&a=getOptionGroup"},searchOptionValue:function(t,a){return n+"?g=guds&m=gudsOptions&a=searchOptionValue&optNameCode="+t+"&keyword="+a},createSku:function(){return n+"?g=guds&m=guds_options&a=create"},updatePic:function(){return n+"?g=guds&m=guds&a=uploadGudsImage"},addNewOptionValue:function(){return n+"?g=guds&m=gudsOptions&a=addNewOptionValue"},createGoodsBasic:function(){return n+"?g=guds&m=guds&a=doAdd"},createGoods:function(){return n+"?g=guds&m=gudsOptions&a=create"},showBrandList:function(){return n+"?g=guds&m=brand&a=showBrandList&isAjax=1"},showGoodsList:function(){return n+"?g=guds&m=guds&a=showList"},getType:function(t,a){return n+"?g=guds&m=B5cai&a=getB5caiListByLevel&pId="+t+"&levId="+a},showGudsBasic:function(t,a,e){return n+"?g=guds&m=guds&a=showGuds&mainId="+t+"&sllrId="+a+"&gudsId="+e},showGudsSKU:function(t,a,e){return n+"?g=guds&m=gudsOptions&a=getOptionList&mainGudsId="+t+"&sellerId="+a+"&gudsId="+e},updateGudsData:function(){return n+"?g=guds&m=guds&a=updateGudsData"},modifySKU:function(){return n+"?g=guds&m=gudsOptions&a=modify"},doCheckGuds:function(){return n+"?g=guds&m=guds&a=doChkGuds"},getBrandList:function(){return n+"?g=guds&m=brand&a=showList"},isCompanyNameExit:function(t){return n+"?g=guds&m=brandSllr&a=isCompanyNameExit&companyName="+t},isSllrIdExist:function(t){return n+"?g=guds&m=brandSllr&a=isSllrIdExist&sllrId="+t},showBrandDetail:function(t){return n+"?g=guds&m=brand&a=showBrandData&brandId="+t},getAddBrandInfo:function(){return n+"?g=guds&m=brand&a=addPage"},doAddBrand:function(){return n+"?g=guds&m=brand&a=doAdd"},downloadData:function(){return n+"?g=guds&m=brand&a=downLoadBrandData"}}},62:function(t,a,e){e(149);var n=e(8)(e(98),e(154),null,null);t.exports=n.exports},89:function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=e(1),s=e(62),i=e.n(s),o=e(0),l=(e.n(o),e(3)),r=(e.n(l),e(6)),c=e.n(r),d=e(2);e.n(d);n.default.use(c.a),new n.default({components:{App:i.a}}).$mount("app")},98:function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),function(t){var n=e(9),s=e.n(n),i=e(4);a.default={data:function(){return{brandID:"",companyName:"",address:"",detailAddress:"",artificialPerson:"",tel01:"",tel02:"",tel03:"",organizationCode01:"",organizationCode02:"",businessType:"",companyBusiness:"",regNumber:"",cnBrandName:"",enBrandName:"",krBrandName:"",jpBrandName:"",distributionChannel:[],checkedChannel:[],checkedChannelCode:[],accreditStatusValue:"",accreditStatusList:[],accreditStatusId:"",cateDataValue:"",cateDataList:[],cateDataId:"",cateListContent:[],cateListCodeContent:[],brandCountryValue:"",brandCountryList:[],brandCountryId:"",mobileContent:[],pcContent:[],logoContent:[],detailContent:[],brandDetailInfo:""}},created:function(){this.getAddBrandInfo()},methods:{getAddBrandInfo:function(){var a=this;t.getJSON(i.a.getAddBrandInfo(),function(t,e){2e3==t.code&&(a.brandCountryList=t.data.brandCountryData,a.distributionChannel=t.data.saleChannelData,a.accreditStatusList=t.data.authTypeData,a.cateDataList=t.data.cateData)})},checkBrandRepeat:function(){var a=this;t.getJSON(i.a.isSllrIdExist(a.brandID),function(t,e){2e3==t.code?a.$alert("可以使用",{confirmButtonText:"确定"}):a.$alert("已经存在的品牌",{confirmButtonText:"确定"})})},checkCompanyRepeat:function(){var a=this;t.getJSON(i.a.isCompanyNameExit(a.companyName),function(t,e){2e3==t.code?a.$alert("可以使用",{confirmButtonText:"确定"}):a.$alert("已经存在的公司名字",{confirmButtonText:"确定"})})},handleCheckedCitiesChange:function(){var a=this;a.checkedChannelCode=[],setTimeout(function(){t(".el-checkbox-group input").each(function(t,e){e.checked&&a.checkedChannelCode.push(e.parentNode.parentNode.getAttribute("data-code"))})},50),console.log(a.checkedChannelCode),console.log(a.checkedChannel)},updatePic:function(){var a=this,e=(event.currentTarget.className,event.currentTarget.getAttribute("data-content"));console.log(e);var n=new FormData;t(".updatePicForm").each(function(a,e){t.each(t(this).find("input")[0].files,function(a,s){t(e).find("span").text(s.name),n.append("file",s)})}),t.ajax({url:i.a.updatePic(),type:"POST",dataType:"JSON",contentType:!1,processData:!1,data:n,cache:!1}).success(function(t){2e3==t.code?a[e]=t.data:a.$alert("接口错误",{confirmButtonText:"确定"})}).error(function(){console.log("error")}).complete(function(){})},selectAccredit:function(){this.accreditStatusId=event.currentTarget.getAttribute("data-code")},selectBrandCountry:function(){this.brandCountryId=event.currentTarget.getAttribute("data-code")},selcetCateData:function(){this.cateDataId=event.currentTarget.getAttribute("data-code")},addedCateDate:function(){-1==this.cateListContent.indexOf(this.cateDataValue)?this.cateListContent.push(this.cateDataValue):this.$alert("已选择的类目",{confirmButtonText:"确定"}),this.cateListCodeContent.indexOf(this.cateDataId)&&this.cateListCodeContent.push(this.cateDataId),console.log(this.cateListContent),console.log(this.cateListCodeContent)},createBrand:function(){var a={companyName:this.companyName,sllrId:this.brandID,sllrAddr:this.address,sllrDtlAddr:this.detailAddress,bzNm:this.artificialPerson,bzopTelNo:this.tel01+"-"+this.tel02+"-"+this.tel03,cRegNo:this.organizationCode01+"-"+this.organizationCode02,bztNm:this.businessType,itNm:this.companyBusiness,commRtlNo:this.regNumber,brandName:this.cnBrandName,brandKrName:this.krBrandName,brandJpName:this.jpBrandName,brandEnName:this.enBrandName,saleChannel:this.checkedChannelCode,brandContent:this.brandDetailInfo,vestWay:this.accreditStatusId,brandOrgCd:this.brandCountryId,catList:this.cateListCodeContent,imgList:[{brandImgCd:"N000350200",brandImgStatCd:"N000360100",imgContent:this.mobileContent},{brandImgCd:"N000350300",brandImgStatCd:"N000360100",imgContent:this.logoContent},{brandImgCd:"N000350600",brandImgStatCd:"N000360100",imgContent:this.pcContent},{brandImgCd:"N000350400",brandImgStatCd:"N000360100",imgContent:this.detailContent}]};t.ajax({url:i.a.doAddBrand(),type:"POST",dataType:"json",data:s()(a)}).done(function(t){console.log(t)}).fail(function(){console.log("error")}).always(function(){console.log("complete")})}}}}.call(a,e(0))}},[89]);