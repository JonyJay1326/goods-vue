webpackJsonp([7],{101:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});a(4);t.default={data:function(){return{brandAccredit:[],brandSearch:[],brandStatusValue:"",brandStatusCode:[],categoryLevelList:[{id:1,checked:!1,value:"Level 1"},{id:2,checked:!1,value:"Level 2"},{id:3,checked:!1,value:"Level 3"}],currentPage:5,totalNum:100,pageNum:10}},methods:{handleCheckAll:function(){this.checkedAll=!this.checkedAll,this.checkedAll?(this.brandStatusValue=["全部"],this.brandStatusCode=[]):0==this.brandStatusValue.length&&(this.checkedAll=!this.checkedAll,this.brandStatusValue=["全部"],this.brandStatusCode=[]),console.log(this.brandStatusCode)},handleCheck:function(){},handleSizeChange:function(e){console.log("每页 "+e+" 条")},handleCurrentChange:function(e){console.log("当前页: "+e)}}}},152:function(e,t){},157:function(e,t){e.exports={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"list-common FE-category-content"},[a("el-row",{staticClass:"bl-row01"},[a("el-col",{attrs:{span:2}},[a("div",{staticClass:"first-title"},[e._v("类目等级")])]),e._v(" "),a("el-checkbox-group",{staticClass:"langGroup",model:{value:e.categoryLevel,callback:function(t){e.categoryLevel=t},expression:"categoryLevel"}},[a("el-checkbox",{attrs:{label:"全部",checked:e.checkedAll},on:{change:e.handleCheckAll}}),e._v(" "),e._l(e.categoryLevelList,function(t){return a("el-checkbox",{attrs:{value:t.value,label:t.value,checked:t.checked},on:{change:function(t){e.handleCheck(t)}}})})],2)],1),e._v(" "),a("el-row",{staticClass:"bl-row02"},[a("el-col",{attrs:{span:2}},[a("div",{staticClass:"first-title"},[e._v("类目")])]),e._v(" "),a("el-col",{staticClass:"business-type",attrs:{span:22}},[a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(t){e.selectType01(t)}},model:{value:e.Type01Value,callback:function(t){e.Type01Value=t},expression:"Type01Value"}},e._l(e.Type01,function(e){return a("el-option",{key:e.catNamePath,attrs:{label:e.catNamePath,value:e.catNamePath,"data-id":e.catId}})})),e._v(" "),a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(t){e.selectType02(t)}},model:{value:e.Type02Value,callback:function(t){e.Type02Value=t},expression:"Type02Value"}},e._l(e.Type02,function(e){return a("el-option",{key:e.catNamePath,attrs:{label:e.catNamePath.split(">")[1],value:e.catNamePath,"data-id":e.catId}})})),e._v(" "),a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(t){e.selectType03(t)}},model:{value:e.Type03Value,callback:function(t){e.Type03Value=t},expression:"Type03Value"}},e._l(e.Type03,function(e){return a("el-option",{key:e.catNamePath,attrs:{label:e.catNamePath.split(">")[2],value:e.catNamePath,"data-id":e.catId}})}))],1)],1),e._v(" "),a("div",{staticClass:"query-btns"},[a("el-button",{staticClass:"query-btn",attrs:{type:"primary"},on:{click:e.queryGoods}},[e._v("查询")])],1),e._v(" "),a("div",{staticClass:"parting-line"}),e._v(" "),a("div",{staticClass:"query-result-list"},[a("h3",[e._v("搜索结果:共"),a("span",[e._v(e._s(e.totalNum))]),e._v("条记录")]),e._v(" "),a("el-table",{staticStyle:{width:"100%","min-width":"1000px"},attrs:{data:e.tableData,stripe:""}},[a("el-table-column",{attrs:{prop:"",label:"操作"}}),e._v(" "),a("el-table-column",{attrs:{prop:"",label:"类目Code"}}),e._v(" "),a("el-table-column",{attrs:{prop:"",label:"类目名称(拼音)"}}),e._v(" "),a("el-table-column",{attrs:{prop:"",label:"类目名称(中文)"}}),e._v(" "),a("el-table-column",{attrs:{prop:"",label:"类目等级"}}),e._v(" "),a("el-table-column",{attrs:{prop:"",label:"前台可见"}})],1)],1),e._v(" "),a("div",{staticClass:"pagination-block"},[a("el-pagination",{attrs:{"current-page":e.currentPage,"page-size":e.pageNum,layout:"prev, pager, next, jumper",total:e.totalNum},on:{"size-change":e.handleSizeChange,"current-change":e.handleCurrentChange,"update:currentPage":function(t){e.currentPage=t}}})],1)],1)},staticRenderFns:[]}},2:function(e,t){},3:function(e,t){},4:function(e,t,a){"use strict";var n="";n="localhost:8801"==window.location.host?"http://erp.stage.com/index.php":"//"+window.location.host+"/index.php",t.a={getBrand:function(){return n+"?g=guds&m=guds&a=addPage"},getBrandInfo:function(e){return n+"?g=guds&m=brand&a=showBrandCateList&brandId="+e+"&isAjax=1"},getOptionList:function(e,t){return n+"?g=guds&m=gudsOptions&a=getOptionList&gudsId="+e+"&sellerId="+t},getBasicOptions:function(){return n+"?g=guds&m=gudsOptions&a=getBasicOptions"},getOptionValues:function(e){return n+"?g=guds&m=guds_options&a=getOptionValues&selectedOptId="+e},getOptionGroup:function(){return n+"?g=guds&m=gudsOptions&a=getOptionGroup"},searchOptionValue:function(e,t){return n+"?g=guds&m=gudsOptions&a=searchOptionValue&optNameCode="+e+"&keyword="+t},createSku:function(){return n+"?g=guds&m=guds_options&a=create"},updatePic:function(){return n+"?g=guds&m=guds&a=uploadGudsImage"},addNewOptionValue:function(){return n+"?g=guds&m=gudsOptions&a=addNewOptionValue"},createGoodsBasic:function(){return n+"?g=guds&m=guds&a=doAdd"},createGoods:function(){return n+"?g=guds&m=gudsOptions&a=create"},showBrandList:function(){return n+"?g=guds&m=brand&a=showBrandList&isAjax=1"},showGoodsList:function(){return n+"?g=guds&m=guds&a=showList"},getType:function(e,t){return n+"?g=guds&m=B5cai&a=getB5caiListByLevel&pId="+e+"&levId="+t},showGudsBasic:function(e,t,a){return n+"?g=guds&m=guds&a=showGuds&mainId="+e+"&sllrId="+t+"&gudsId="+a},showGudsSKU:function(e,t,a){return n+"?g=guds&m=gudsOptions&a=getOptionList&mainGudsId="+e+"&sellerId="+t+"&gudsId="+a},updateGudsData:function(){return n+"?g=guds&m=guds&a=updateGudsData"},modifySKU:function(){return n+"?g=guds&m=gudsOptions&a=modify"},doCheckGuds:function(){return n+"?g=guds&m=guds&a=doChkGuds"},getBrandList:function(){return n+"?g=guds&m=brand&a=showList"},isCompanyNameExit:function(e){return n+"?g=guds&m=brandSllr&a=isCompanyNameExit&companyName="+e},isSllrIdExist:function(e){return n+"?g=guds&m=brandSllr&a=isSllrIdExist&sllrId="+e},showBrandDetail:function(e){return n+"?g=guds&m=brand&a=showBrandData&brandId="+e},getAddBrandInfo:function(){return n+"?g=guds&m=brand&a=addPage"},doAddBrand:function(){return n+"?g=guds&m=brand&a=doAdd"},downloadData:function(){return n+"?g=guds&m=brand&a=downLoadBrandData"}}},65:function(e,t,a){a(152);var n=a(8)(a(101),a(157),null,null);e.exports=n.exports},94:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=a(1),s=a(65),u=a.n(s),l=a(0),o=(a.n(l),a(3)),d=(a.n(o),a(6)),r=a.n(d),c=a(2);a.n(c);n.default.use(r.a),new n.default({components:{App:u.a}}).$mount("app")}},[94]);