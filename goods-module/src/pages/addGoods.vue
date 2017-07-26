<template>
  <div class="erp-addgoods">
    <el-tabs v-model="activeName" @tab-click="handleClick">
      <el-tab-pane label="基础信息" name="first">
        <header>品牌信息</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-brand-info">
          <tbody>
            <tr class="tr-1">
              <td class="info-title">品牌名</td>
              <td>
                <el-select v-model="brandNameValue" placeholder="请选择">
                  <el-option v-for="item in brandName" :key="item.value" :label="item.label" :value="item.value">
                  </el-option>
                </el-select>
              </td>
              <td class="info-title">品牌类目</td>
              <td>
                <el-select v-model="brandCategoryValue" placeholder="请选择">
                  <el-option v-for="item in brandCategory" :key="item.value" :label="item.label" :value="item.value">
                  </el-option>
                </el-select>
              </td>
              <td class="info-title">类目code</td>
              <td>
                <p></p>
              </td>
              <td class="info-title">类目名字</td>
              <td>
                <p></p>
              </td>
            </tr>
            <tr class="tr-2">
              <td class="info-title category">一级类目</td>
              <td>
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel01" resize="none">
                </el-input>
              </td>
              <td class="info-title category">二级类目</td>
              <td>
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel02" resize="none">
                </el-input>
              </td>
              <td class="info-title category">三级类目</td>
              <td>
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel03" resize="none">
                </el-input>
              </td>
              <td class="info-title category">四级类目</td>
              <td>
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel04" resize="none">
                </el-input>
              </td>
            </tr>
          </tbody>
        </table>
        <header>商品信息</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-goods-info">
          <tbody>
            <tr class="tr-goods">
              <td class="info-title">语言</td>
              <td>
                <el-checkbox-group v-model="checkList" change="languageChange">
                  <el-checkbox label="中文"></el-checkbox>
                  <el-checkbox label="英文"></el-checkbox>
                  <el-checkbox label="韩文"></el-checkbox>
                  <el-checkbox label="日文"></el-checkbox>
                </el-checkbox-group>
              </td>
              <td class="info-title">品牌ID</td>
              <td>
                <p></p>
              </td>
              <td class="info-title">商品单位</td>
              <td>
                <el-select v-model="goodsUnitValue" placeholder="请选择">
                  <el-option v-for="item in goodsUnit" :key="item.value" :label="item.label" :value="item.value">
                  </el-option>
                </el-select>
              </td>
              <td class="info-title">商品保质期限</td>
              <td>
                <div style="width:100%;display:flex;">
                  <el-input v-model="goodsShelfLife" placeholder="请输入内容" style="width:100%">
                    <template slot="append">天</template>
                  </el-input>
                </div>
              </td>
            </tr>
            <tr class="tr-goods">
              <td class="info-title">标题</td>
              <td>
                <el-input v-model="chineseTitle" placeholder="请输入内容"></el-input>
              </td>
              <td class="info-title">英文标题</td>
              <td>
                <el-input v-model="englishTitle" placeholder="请输入内容"></el-input>
              </td>
              <td class="info-title">韩文标题</td>
              <td>
                <el-input v-model="koreaTitle" placeholder="请输入内容"></el-input>
              </td>
              <td class="info-title">日文标题</td>
              <td>
                <el-input v-model="japanTitle" placeholder="请输入内容"></el-input>
              </td>
            </tr>
            <tr class="tr-goods">
              <td class="info-title">副标题</td>
              <td>
                <el-input v-model="chineseSubtitle" placeholder="选填"></el-input>
              </td>
              <td class="info-title">英文副标题</td>
              <td>
                <el-input v-model="englishSubtitle" placeholder="选填"></el-input>
              </td>
              <td class="info-title">韩文副标题</td>
              <td>
                <el-input v-model="koreaSubtitle" placeholder="选填"></el-input>
              </td>
              <td class="info-title">日文副标题</td>
              <td>
                <el-input v-model="japanSubtitle" placeholder="选填"></el-input>
              </td>
            </tr>
          </tbody>
        </table>
        <header>商品主图</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-goods-pic">
          <tbody>
            <tr>
              <td>
                <div class='img-content'></div>
                <span>中文主图</span>
                <button>选择文件</button>
              </td>
              <td>
                <div class='img-content'></div>
                <span>英文主图</span>
                <button>选择文件</button>
              </td>
              <td>
                <div class='img-content'></div>
                <span>韩文主图</span>
                <button>选择文件</button>
              </td>
              <td>
                <div class='img-content'></div>
                <span>日文主图</span>
                <button>选择文件</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="erp-addgoods-btns">
          <el-button type="primary">保存</el-button>
          <el-button>重置</el-button>
        </div>
      </el-tab-pane>
      <el-tab-pane label="SKU信息" name="second">
        <div class="sku-item">
          <span>币种</span>
          <el-select v-model="currencyValue" placeholder="请选择" id='currency_choice'>
            <el-option v-for="item in currency" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
          <span>产地</span>
          <el-select v-model="originPlaceValue" placeholder="请选择">
            <el-option v-for="item in originPlace" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
        </div>
        <header>添加SKU
          <el-button type="button" class="apply_btn">应用</el-button>
        </header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-add-sku">
          <thead>
            <tr class="add-sku-title">
              <td>Option Name</td>
              <td>Option Value</td>
            </tr>
          </thead>
          <tbody>
            <tr class="add-sku-option">
              <td class="opiont_name">
                <el-row :gutter="10">
                  <el-col :span="6">
                    <el-input v-model="KoreanOptionName" placeholder="Korean"></el-input>
                  </el-col>
                  <el-col :span="6">
                    <el-input v-model="ChineseOptionName" placeholder="Chinese"></el-input>
                  </el-col>
                  <el-col :span="6">
                    <el-input v-model="EnglishOptionName" placeholder="English"></el-input>
                  </el-col>
                  <el-col :span="6">
                    <el-input v-model="JapaneseOptionName" placeholder="Japanese"></el-input>
                  </el-col>
                </el-row>
              </td>
              <td class="option_value">
                <el-row :gutter="10">
                  <el-col :span="5">
                    <el-input v-model="KoreanOptionValue" placeholder="Korean"></el-input>
                  </el-col>
                  <el-col :span="5">
                    <el-input v-model="ChineseOptionValue" placeholder="Chinese"></el-input>
                  </el-col>
                  <el-col :span="5">
                    <el-input v-model="EnglishOptionValue" placeholder="English"></el-input>
                  </el-col>
                  <el-col :span="5">
                    <el-input v-model="JapaneseOptionValue" placeholder="Japanese"></el-input>
                  </el-col>
                  <i class="el-icon-search" @click="openOptionSearch"></i>
                  <el-button class='add-sku-btn' icon="plus">添加</el-button>
                </el-row>
              </td>
            </tr>
          </tbody>
        </table>
        <header>SKU信息</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-sku-info">
          <thead>
            <tr>
              <th style="text-align:center">颜色</th>
              <th style="text-align:center">型号</th>
              <th>UPC</th>
              <th>CR code</th>
              <th>HS code</th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10">长(cm)</el-col>
                  <el-col :span="14">
                    <el-input v-model="skuLength" :length="skuLength" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10">宽(cm)</el-col>
                  <el-col :span="14">
                    <el-input v-model="skuWide" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10">高(cm)</el-col>
                  <el-col :span="14">
                    <el-input v-model="skuHeight" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10">重量(g)</el-col>
                  <el-col :span="14">
                    <el-input v-model="skuWeight" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>黑色</td>
              <td>型号</td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
            </tr>
            <tr>
              <td>黑色</td>
              <td>型号</td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
              <td>
                <el-input placeholder=""></el-input>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="erp-addsku-btns">
          <el-button type="primary">保存</el-button>
          <el-button>重置</el-button>
        </div>
      </el-tab-pane>
    </el-tabs>
    <!-- option搜索 -->
    <div class="option-search-box" style="display:none;">
      <h3><span>option 搜索</span><i class="el-icon-close" @click="closeSearchBox"></i></h3>
      <h4>option 名字</h4>
      <el-row :gutter="20" class="option-search">
        <el-col :span="14">
          <el-select v-model="value" placeholder="请选择">
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
        </el-col>
        <el-col :span="10">
          <el-input placeholder="" icon="search" v-model="input2" :on-icon-click="handleIconClick">
          </el-input>
        </el-col>
      </el-row>
      <h4>option 信息</h4>
      <el-row :gutter="10" class="option-info">
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
        <el-col :span="6">
          <div class="option-info-item">100/100/en/JPA</div>
        </el-col>
      </el-row>
      <h4>添加 option</h4>
      <el-row :gutter="10" class="option-add">
        <el-col :span="1">
          <div>Kr</div>
        </el-col>
        <el-col :span="4">
          <el-input placeholder=""></el-input>
        </el-col>
        <el-col :span="1">
          <span>Cn</span>
        </el-col>
        <el-col :span="4">
          <el-input placeholder=""></el-input>
        </el-col>
        <el-col :span="1">
          <span>En</span>
        </el-col>
        <el-col :span="4">
          <el-input placeholder=""></el-input>
        </el-col>
        <el-col :span="1">
          <span>Ja</span>
        </el-col>
        <el-col :span="4">
          <el-input placeholder=""></el-input>
        </el-col>
        <el-col :span="4">
          <el-button class='add-sku-btn' icon="plus">添加</el-button>
        </el-col>
      </el-row>
      <div class="erp-addoption-btns">
          <el-button type="primary">保存</el-button>
          <el-button>重置</el-button>
        </div>
    </div>
    <div id="mask">
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      activeName: 'first',
      // 品牌名
      brandName: [{
        value: '选项1',
        label: '黄金糕'
      }, {
        value: '选项2',
        label: '双皮奶'
      }, {
        value: '选项3',
        label: '蚵仔煎'
      }],
      brandNameValue: '小米',
      // 品牌类目
      brandCategory: [{
        value: '选项1',
        label: '黄金糕'
      }, {
        value: '选项2',
        label: '双皮奶'
      }, {
        value: '选项3',
        label: '蚵仔煎'
      }],
      brandCategoryValue: '母婴',
      // 商品单位
      goodsUnit: [{
        value: '选项1',
        label: '黄金糕'
      }, {
        value: '选项2',
        label: '双皮奶'
      }, {
        value: '选项3',
        label: '蚵仔煎'
      }],
      goodsUnitValue: '个',
      //币种
      currency: [{
        value: '选项1',
        label: '中国'
      }, {
        value: '选项2',
        label: '日本'
      }, {
        value: '选项3',
        label: '韩国'
      }],
      currencyValue: '',
      //产地
      originPlace: [{
        value: '选项1',
        label: '中国'
      }, {
        value: '选项2',
        label: '日本'
      }, {
        value: '选项3',
        label: '韩国'
      }],
      originPlaceValue: '',
      // 商品保质期限
      goodsShelfLife: '365',
      // 类目
      categoryLevel01: '1',
      categoryLevel02: '2',
      categoryLevel03: '3',
      categoryLevel04: '4',
      // 各语言标题
      chineseTitle: '',
      englishTitle: '',
      koreaTitle: '',
      japanTitle: '',
      // 各语言副标题
      chineseSubtitle: '',
      englishSubtitle: '',
      koreaSubtitle: '',
      japanSubtitle: '',
      // 语言多选框
      checkList: [],
      //option-name
      KoreanOptionName: '',
      ChineseOptionName: '',
      EnglishOptionName: '',
      JapaneseOptionName: '',
      //option-value
      KoreanOptionValue: '',
      ChineseOptionValue: '',
      EnglishOptionValue: '',
      JapaneseOptionValue: '',

      skuLength: '',
      skuWeight: '',
      skuHeight: '',
      skuWide: '',
    }
  },
  components: {

  },
  methods: {
    handleClick(tab, event) {
      console.log(tab, event);
    },
    languageChange(event) {
      console.log(event)
    },
    openOptionSearch(){
      this.$el.querySelector('.option-search-box').style.display="block"
    },
    closeSearchBox(){
      this.$el.querySelector('.option-search-box').style.display="none"
    }
  }
}

</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
.option-search-box{
  z-index:9999;
  width:860px;
  height: 640px;
  background: #FFFFFF;
  box-shadow: 0 0 10px 0 rgba(0,0,0,0.20);
  border-radius: 4px;
  position: fixed;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%);
  padding: 0px 30px;
  .option-info-item{
    font-size: 14px;
    color: #263238;
    letter-spacing: 0;
    background: #FFFFFF;
    border: 1px solid #C8D2D7;
    width:175px;
    height:40px;
    line-height:40px;
    margin:20px auto 0px;
  }
  h3{
    font-size: 18px;
    color: #263238;
    letter-spacing: 0;
    font-weight:600;
    line-height:64px;
  }
  h4{
    font-size: 14px;
    color: #263238;
    letter-spacing: 0;
    font-weight:600;
    text-align:left;
    line-height:32px;
  }
  .el-icon-close{
    float:right;
    color:#546E7A;
    font-size:14px;
    margin-top: 25px;
  }
  .el-row{
    background-color: #F6F9FA;
    padding-bottom:20px;
  }
  .option-add{
      padding:20px 20px 20px 10px;
      line-height:32px; 
      font-size: 14px;
      color: #263238;
  }
  .el-select{
    width:100%;
  }
  .el-icon-search{
    font-size:16px;
  }
  .option-search{
    padding:20px 30px 20px 0px;
    background:#ffffff;
  }
  .erp-addoption-btns{
    text-align:right;
    padding-top: 20px;
    button{
      width:80px;
      height:32px;
      padding:0px;
    }
    .el-button--primary{
      background: #1E7EB4;
      border-radius: 4px;
      font-size: 14px;
      color: #FFFFFF;
      letter-spacing: 0;
    }
    .el-button--default{
      background: #FFFFFF;
      border: 1px solid #C8D2D7;
      border-radius: 4px;
      font-size: 14px;
      color: #546E7A;
      letter-spacing: 0;
    }
  }
}
.erp-mian-content{
  padding: 0px 30px;
  header{
    font-family: "Arial","Microsoft YaHei","黑体","宋体",sans-serif;
    font-size: 16px;
    height:40px;
    line-height:40px!important;
    text-align:left;
    padding-left: 20px;
    color: #FFFFFF;
    letter-spacing: 0;
    line-height: 20px;
    background: #546E7A;
    margin-top:20px;
  }
  .el-select,.el-input,.el-input__inner{
    font-family: "Arial","Microsoft YaHei","黑体","宋体",sans-serif !important;
    font-size: 13px;
    color: #263238;
    letter-spacing: 0;
    height:32px;
  }
  textarea,{
    font-family: "Arial","Microsoft YaHei","黑体","宋体",sans-serif !important;
    font-size: 13px;
    color: #263238;
    letter-spacing: 0;
  }
}
.el-tabs__header{
  border:none;
  .el-tabs__active-bar{
    left:14px;
    height:2px;
    margin-bottom: 0px;
  }
  .el-tabs__item{
    font-family: "Arial","Microsoft YaHei","黑体","宋体",sans-serif;
    font-size: 18px;
    color: #546E7A;
    letter-spacing: 0;
    padding-left:0px;
    padding-right:16px;
    margin-top:20px;
  }
  .el-tabs__item.is-active{
    color: #263238;
    font-weight:600;
  }
  .el-tabs__active-bar{
    background: #FF5224;
    width:40px !important;
  }
}
.erp-brand-info,.erp-goods-info{
  width:100%;
  border: 1px solid #CADEE7;
  .tr-1{
    height:50px;
  }
  .tr-2{
    height:92px;
  }
  .tr-goods{
    height:51px;
  }
  td{
    padding:9px 20px 9px 20px;
    border-right:1px solid #CADEE7;
    border-top:1px solid #ECEFF1;
    width:19%;
    .el-select{
         width:100%;
      }
    .el-textarea{
        width:100%;
    }
    .el-input__inner{
      height:32px;
      width:100%;
    }

  }
  .info-title{
    background: #F7F9FB;
    font-size: 13px;
    color: #546E7A;
    letter-spacing: 0;
    padding:0px;
    width:100px;
    span{
      font-size: 13px;
      color: #263238;
      letter-spacing: 0;
    }
  }
  .category.info-title{
    vertical-align:middle;
  }
}
.erp-goods-pic{
  width:100%;
  border: 1px solid #CADEE7;
  td{
    padding: 28px 52px 35px;
    .img-content{
      width:300px;
      height:300px;
      border:1px solid #C8D2D7;
    }
    span{
      float:left;
      font-size: 14px;
      color: #546E7A;
      letter-spacing: 0;
      margin-top:23px;
    }
    button{
      background-image: linear-gradient(-180deg, #FFFFFF 0%, #EFEFEF 100%);
      border: 1px solid #C8D2D7;
      border-radius: 4px;
      font-size: 14px;
      color: #263238;
      letter-spacing: 0;
      float:right;
      margin-top:20px;
      width:80px;
      height:26px;
      cursor: pointer;
    }
  }
}
.erp-addgoods-btns,.erp-addsku-btns{
  margin: 60px 0px 100px;
  button{
    width:110px;
    height:44px;
    font-size: 14px;
    border-radius: 4px;  
    letter-spacing: 0;
    margin-right: 30px;
  }
  button.el-button--default{
    background: #FFFFFF;
    border: 1px solid #C8D2D7;    
    color: #546E7A;
  }
  button.el-button--primary{
    background: #1E7EB4;
    color: #FFFFFF;
  }
}
.sku-item{
  text-align:left;
  margin-top: 20px;
  height:40px;
  line-height:40px;
  span{
    padding-right:18px;
    font-size: 13px;
    color: #263238;
    letter-spacing: 0;
    height:40px;
    display:inline-block;
  }
  .el-input__inner{
    height:32px;
    width:286px;
  }
  .el-select{
    margin-right:40px;
  }
}

.erp-add-sku{
  width:100%;
  border: 1px solid #CADEE7;
  tr{
    width:100%;
    text-align:left;

    td{
      border-right:1px solid #CADEE7;
      border-top:1px solid #ECEFF1;
    }
    .opiont_name{
      padding:0px 20px;
      width:40%;
      li{
        width:23%;
        padding:0px 5px; 
      }
    }
    .option_value{
      line-height:36px;
      padding:0px 20px;
      width:60%;
      .el-icon-search{
        padding:0px 20px;
        cursor:pointer;
      }
    }
  }
  .add-sku-title{
    font-size: 14px;
    color: #546E7A;
    letter-spacing: 0;
    height:40px;
    line-height:40px;
    background: #F7F9FB;
    td{
      padding-left:20px;
      border-bottom:1px solid #CADEE7;
    }
  }
  .add-sku-option{
    height:95px;
    ul{
      li{
        float:left;
        margin:5px 0px;
        .el-input{
          height:32px;
        }
      }
    }
    
  }
}
.add-sku-btn{
      font-size: 14px;
      color: #263238;
      letter-spacing: 0;
      background-image: linear-gradient(-180deg, #FFFFFF 0%, #EFEFEF 100%);
      border: 1px solid #C8D2D7;
      border-radius: 4px;
      height:26px;
      width:66px;
      padding:0px;
      i{
        color:#90A4AE;
        font-size:14px;

      }
       span{
     color:#263238;

  }
}

.apply_btn{
    float:right;
    margin-top:6px;
    margin-right:12px;
    font-size: 14px;
    color: #FFFFFF;
    letter-spacing: 0;
    border:none;
    background-image: linear-gradient(-180deg, #FFFFFF 0%, #EFEFEF 100%);
    background: #FF9100;
    border-radius: 4px;
    height: 26px;
    width: 66px;
    padding: 0px;
    span{
      color: #FFFFFF;
    }
}
.apply_btn:hover,.apply_btn:visited,.apply_btn:active,.apply_btn:link{
  color: #FFFFFF;
}
.el-row {
    margin-bottom: 20px;
    &:last-child {
      margin-bottom: 0;
    }
  }
  .row-bg {
    padding: 10px 0;
    background-color: #f9fafc;
  }

.erp-sku-info{
  width:100%;
  border: 1px solid #CADEE7;
  thead{
    background: #F7F9FB;
    th{
      border-right: 1px solid #CADEE7;
      border-bottom:1px solid #CADEE7;
      width:10%;
      height:50px;
      font-size: 13px;
      color: #546E7A;
      letter-spacing: 0;
      text-align:left;
      padding:0px 10px;
      .el-row{
        line-height:32px;
      }
    }
  }
  td{
      border-right: 1px solid #CADEE7;
      height:50px;
      padding:0px 10px;
      font-size: 13px;
      color: #263238;
      letter-spacing: 0;
  }
}
</style>
