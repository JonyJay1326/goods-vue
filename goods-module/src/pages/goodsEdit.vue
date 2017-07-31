<template>
  <div class="goodsEdit">
    <div class="status-btn">
      <el-button type="primary" class='edit-btn' v-show="showStatus" @click="toggleStatus">编辑</el-button>
      <el-button type="primary" class='save-btn' v-show="editStatus" @click="toggleStatus">保存</el-button>
      <el-button type="primary" class='back-to-edit' v-show="reviewStatus" @click="openBackText">退回编辑</el-button>
      <el-button type="primary" class='submit-review-btn' v-show="showStatus||editStatus">提交审核申请</el-button>
      <el-button type="primary" class='review-pass' v-show="reviewStatus">审核通过</el-button>
    </div>
    <el-tabs v-model="activeName" @tab-click="handleClick">
      <el-tab-pane label="基础信息" name="first">
        <header>品牌信息</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-brand-info">
          <tbody>
            <tr class="tr-1">
              <td class="info-title">品牌名</td>
              <td>
                <el-select v-model="brandNameValue" placeholder="请选择" v-show="editStatus">
                  <el-option v-for="item in brandName" :key="item.value" :label="item.label" :value="item.value">
                  </el-option>
                </el-select>
                <span v-show="showStatus">{{brandNameValue}}</span>
              </td>
              <td class="info-title">品牌类目</td>
              <td>
                <el-select v-model="brandCategoryValue" placeholder="请选择" v-show="editStatus">
                  <el-option v-for="item in brandCategory" :key="item.value" :label="item.label" :value="item.value">
                  </el-option>
                </el-select>
                <span v-show="showStatus">{{brandCategoryValue}}</span>
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
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel01" resize="none" v-show="editStatus">
                </el-input>
                <span v-show="showStatus">{{categoryLevel01}}</span>
              </td>
              <td class="info-title category">二级类目</td>
              <td>
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel02" resize="none" v-show="editStatus">
                </el-input>
                <span v-show="showStatus">{{categoryLevel02}}</span>
              </td>
              <td class="info-title category">三级类目</td>
              <td>
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel03" resize="none" v-show="editStatus">
                </el-input>
                <span v-show="showStatus">{{categoryLevel03}}</span>
              </td>
              <td class="info-title category">四级类目</td>
              <td>
                <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="categoryLevel04" resize="none" v-show="editStatus">
                </el-input>
                <span v-show="showStatus">{{categoryLevel04}}</span>
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
                <el-checkbox-group v-model="checkList" @change='toggleCheck' v-show="editStatus">
                  <el-row :gutter="0">
                    <el-col :span="6">
                      <el-checkbox label="中文"></el-checkbox>
                    </el-col>
                    <el-col :span="6">
                      <el-checkbox label="英文"> </el-checkbox>
                    </el-col>
                    <el-col :span="6">
                      <el-checkbox label="韩文"></el-checkbox>
                    </el-col>
                    <el-col :span="6">
                      <el-checkbox label="日文"></el-checkbox>
                    </el-col>
                  </el-row>
                </el-checkbox-group>
                <span v-show="showStatus">{{checkList.join(";")}}</span>
              </td>
              <td class="info-title">品牌ID</td>
              <td>
                <p></p>
              </td>
              <td class="info-title">商品单位</td>
              <td>
                <el-select v-model="goodsUnitValue" placeholder="请选择" v-show="editStatus">
                  <el-option v-for="item in goodsUnit" :key="item.value" :label="item.label" :value="item.value">
                  </el-option>
                </el-select>
                <span v-show="showStatus">{{goodsUnitValue}}</span>
              </td>
              <td class="info-title">商品保质期限</td>
              <td>
                <div style="width:100%;display:flex;">
                  <el-input v-model="goodsShelfLife" placeholder="请输入内容" style="width:100%" v-show="editStatus">
                    <template slot="append">天</template>
                  </el-input>
                  <span v-show="showStatus">{{goodsShelfLife}} 天</span>
                </div>
              </td>
            </tr>
            <tr class="tr-goods">
              <td class="info-title">标题</td>
              <td>
                <el-input v-model="chineseTitle" placeholder="请输入内容" :disabled="cnLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{chineseTitle}}</span>
              </td>
              <td class="info-title">英文标题</td>
              <td>
                <el-input v-model="englishTitle" placeholder="请输入内容" :disabled="enLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{englishTitle}}</span>
              </td>
              <td class="info-title">韩文标题</td>
              <td>
                <el-input v-model="koreaTitle" placeholder="请输入内容" :disabled="krLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{koreaTitle}}</span>
              </td>
              <td class="info-title">日文标题</td>
              <td>
                <el-input v-model="japanTitle" placeholder="请输入内容" :disabled="jaLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{japanTitle}}</span>
              </td>
            </tr>
            <tr class="tr-goods">
              <td class="info-title">副标题</td>
              <td>
                <el-input v-model="chineseSubtitle" placeholder="选填" :disabled="cnLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{chineseSubtitle}}</span>
              </td>
              <td class="info-title">英文副标题</td>
              <td>
                <el-input v-model="englishSubtitle" placeholder="选填" :disabled="enLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{englishSubtitle}}</span>
              </td>
              <td class="info-title">韩文副标题</td>
              <td>
                <el-input v-model="koreaSubtitle" placeholder="选填" :disabled="krLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{koreaSubtitle}}</span>
              </td>
              <td class="info-title">日文副标题</td>
              <td>
                <el-input v-model="japanSubtitle" placeholder="选填" :disabled="jaLanguage" v-show="editStatus"></el-input>
                <span v-show="showStatus">{{japanSubtitle}}</span>
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
                <button v-show="editStatus">选择文件</button>
              </td>
              <td>
                <div class='img-content'></div>
                <span>英文主图</span>
                <button v-show="editStatus">选择文件</button>
              </td>
              <td>
                <div class='img-content'></div>
                <span>韩文主图</span>
                <button v-show="editStatus">选择文件</button>
              </td>
              <td>
                <div class='img-content'></div>
                <span>日文主图</span>
                <button v-show="editStatus">选择文件</button>
              </td>
            </tr>
          </tbody>
        </table>
      </el-tab-pane>
      <el-tab-pane label="SKU信息" name="second">
        <div class="sku-item">
          <span>币种</span>
          <el-select v-model="currencyValue" placeholder="请选择" id='currency_choice' v-show="editStatus">
            <el-option v-for="item in currency" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
          <span v-show="showStatus" style="width:200px;font-size:16px;font-weight:600">{{currencyValue}}</span>
          <span>产地</span>
          <el-select v-model="originPlaceValue" placeholder="请选择" v-show="editStatus">
            <el-option v-for="item in originPlace" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
          <span v-show="showStatus" style="width:200px;font-size:16px;font-weight:600">{{originPlaceValue}}</span>
        </div>
        <header v-show="editStatus">添加SKU
          <el-button type="button" class="apply_btn">应用</el-button>
        </header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-add-sku" v-show="editStatus">
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
                  <i class="el-icon-search" @click="openSearchBox"></i>
                  <el-button type="pr" class='add-sku-btn' icon="plus">添加</el-button>
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
                  <el-col :span="24"><span>价格</span></el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="24"><span>长(cm)</span></el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="24"><span>宽(cm)</span></el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="24"><span>高(cm)</span></el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="24"><span>重量(g)</span></el-col>
                </el-row>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>黑色</td>
              <td>型号</td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="upc01"></el-input>
                <span v-show="showStatus">{{upc01}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="crCode01"></el-input>
                <span v-show="showStatus">{{crCode01}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="hsCode01"></el-input>
                <span v-show="showStatus">{{hsCode01}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="price01"></el-input>
                <span v-show="showStatus">{{price01}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="length01"></el-input>
                <span v-show="showStatus">{{length01}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="width01"></el-input>
                <span v-show="showStatus">{{width01}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="height01"></el-input>
                <span v-show="showStatus">{{height01}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="weight01"></el-input>
                <span v-show="showStatus">{{weight01}}</span>
              </td>
            </tr>
            <tr>
              <td>黑色</td>
              <td>型号</td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="upc02"></el-input>
                <span v-show="showStatus">{{upc02}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="crCode02"></el-input>
                <span v-show="showStatus">{{crCode02}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="hsCode02"></el-input>
                <span v-show="showStatus">{{hsCode02}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="price02"></el-input>
                <span v-show="showStatus">{{price02}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="length02"></el-input>
                <span v-show="showStatus">{{length02}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="width02"></el-input>
                <span v-show="showStatus">{{width02}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="height02"></el-input>
                <span v-show="showStatus">{{height02}}</span>
              </td>
              <td>
                <el-input placeholder="" v-show="editStatus" v-model="weight01"></el-input>
                <span v-show="showStatus">{{weight01}}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </el-tab-pane>
    </el-tabs>
    <div class='open-bac-text' v-show="showBackText">
      <el-input type="textarea" :rows="4" placeholder="请输入退回理由" v-model="backText" resize="none">
      </el-input>
      <div class="confirm-btn">
        <el-button type="primary" class="confirm-to-back">确认退回</el-button>
        <el-button type="primary" class="cancle-btn" @click="cancelBackText">取消</el-button>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      showStatus: true,
      editStatus: false,
      reviewStatus: false,
      activeName: 'first',
      // 品牌名
      brandName: [{
        value: '黄金糕',
        label: '黄金糕'
      }, {
        value: '双皮奶',
        label: '双皮奶'
      }, {
        value: '蚵仔煎',
        label: '蚵仔煎'
      }],
      brandNameValue: '小米',
      // 品牌类目
      brandCategory: [{
        value: '黄金糕',
        label: '黄金糕'
      }, {
        value: '双皮奶',
        label: '双皮奶'
      }, {
        value: '蚵仔煎',
        label: '蚵仔煎'
      }],
      brandCategoryValue: '母婴',
      // 商品单位
      goodsUnit: [{
        value: '黄金糕',
        label: '黄金糕'
      }, {
        value: '双皮奶',
        label: '双皮奶'
      }, {
        value: '蚵仔煎',
        label: '蚵仔煎'
      }],
      goodsUnitValue: '个',
      //币种
      currency: [{
        value: '中国',
        label: '中国'
      }, {
        value: '日本',
        label: '日本'
      }, {
        value: '韩国',
        label: '韩国'
      }],
      currencyValue: '人民币',
      //产地
      originPlace: [{
        value: '中国',
        label: '中国'
      }, {
        value: '日本',
        label: '日本'
      }, {
        value: '韩国',
        label: '韩国'
      }],
      originPlaceValue: '中国',
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
      cnLanguage: true,
      enLanguage: true,
      krLanguage: true,
      jaLanguage: true,
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

      searchBarValue: '',
      optionNameValue: '',
      optionName: [{
        value: '选项1',
        label: '颜色'
      }, {
        value: '选项2',
        label: '尺寸'
      }],
      showSearchBox: false,

      showBackText: false,
      backText: ''
    }
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      if (to.path.match('goodsEdit')) {
        vm.editStatus = false;
        vm.reviewStatus = false;
        vm.showStatus = true;
      } else if (to.path.match('goodsReview')) {
        vm.editStatus = false;
        vm.reviewStatus = true;
        vm.showStatus = false;
      }
    })
  },
  beforeRouteLeave(to, from, next) {
    next();
  },
  methods: {
    toggleStatus() {
      this.showStatus = !this.showStatus;
      this.editStatus = !this.editStatus;
    },
    handleClick(tab, event) {
      console.log(tab, event);
    },
    languageChange(event) {

    },
    /**
     * 语言选择，input toggle
     */
    toggleCheck(event) {
      (event.indexOf("中文") != -1) ? this.cnLanguage = false: this.cnLanguage = true;
      (event.indexOf("英文") != -1) ? this.enLanguage = false: this.enLanguage = true;
      (event.indexOf("韩文") != -1) ? this.krLanguage = false: this.krLanguage = true;
      (event.indexOf("日文") != -1) ? this.jaLanguage = false: this.jaLanguage = true;
    },

    openSearchBox() {
      this.showSearchBox = true;
    },
    closeSearchBox() {
      this.showSearchBox = false;
    },
    openBackText() {
      this.showBackText = true
    },
    cancelBackText(){
      this.showBackText = false
    },
    searchOptionName() {

    }

  }
}

</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
@import "../assets/scss/addGoods.scss";
@import "../assets/scss/goodsEdit.scss";
</style>
