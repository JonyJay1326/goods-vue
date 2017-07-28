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
                <el-checkbox-group v-model="checkList" @change='toggleCheck'>
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
                <el-input v-model="chineseTitle" placeholder="请输入内容" :disabled="cnLanguage"></el-input>
              </td>
              <td class="info-title">英文标题</td>
              <td>
                <el-input v-model="englishTitle" placeholder="请输入内容" :disabled="enLanguage"></el-input>
              </td>
              <td class="info-title">韩文标题</td>
              <td>
                <el-input v-model="koreaTitle" placeholder="请输入内容" :disabled="krLanguage"></el-input>
              </td>
              <td class="info-title">日文标题</td>
              <td>
                <el-input v-model="japanTitle" placeholder="请输入内容" :disabled="jaLanguage"></el-input>
              </td>
            </tr>
            <tr class="tr-goods">
              <td class="info-title">副标题</td>
              <td>
                <el-input v-model="chineseSubtitle" placeholder="选填" :disabled="cnLanguage"></el-input>
              </td>
              <td class="info-title">英文副标题</td>
              <td>
                <el-input v-model="englishSubtitle" placeholder="选填" :disabled="enLanguage"></el-input>
              </td>
              <td class="info-title">韩文副标题</td>
              <td>
                <el-input v-model="koreaSubtitle" placeholder="选填" :disabled="krLanguage"></el-input>
              </td>
              <td class="info-title">日文副标题</td>
              <td>
                <el-input v-model="japanSubtitle" placeholder="选填" :disabled="jaLanguage"></el-input>
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
                  <i class="el-icon-search" @click="openSearchBox"></i>
                  <el-button type="button" class='add-sku-btn' icon="plus">添加</el-button>
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
                  <el-col :span="10"><span>价格</span></el-col>
                  <el-col :span="14">
                    <el-input v-model="skuLength" :length="skuLength" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10"><span>长(cm)</span></el-col>
                  <el-col :span="14">
                    <el-input v-model="skuLength" :length="skuLength" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10"><span>宽(cm)</span></el-col>
                  <el-col :span="14">
                    <el-input v-model="skuWide" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10"><span>高(cm)</span></el-col>
                  <el-col :span="14">
                    <el-input v-model="skuHeight" placeholder="批量输入"></el-input>
                  </el-col>
                </el-row>
              </th>
              <th>
                <el-row :gutter="10">
                  <el-col :span="10"><span>重量(g)</span></el-col>
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
    <div class="option-search-box" v-show="showSearchBox">
      <h3><span>option 搜索</span><i class="el-icon-close" @click="closeSearchBox"></i></h3>
      <h4>option 名字</h4>
      <el-row :gutter="20" class="option-search">
        <el-col :span="14">
          <el-select v-model="optionNameValue" placeholder="请选择">
            <el-option v-for="item in optionName" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
        </el-col>
        <el-col :span="10">
          <el-input placeholder="" icon="search" v-model="searchBarValue" :on-icon-click="searchOptionName">
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
          <el-button type="button" class='add-sku-btn' icon="plus">添加</el-button>
        </el-col>
      </el-row>
      <div class="erp-addoption-btns">
        <el-button type="primary">保存</el-button>
        <el-button>重置</el-button>
      </div>
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
    }
  },
  components: {

  },
  methods: {
    handleClick(tab, event) {
      console.log(tab, event);
    },
    languageChange(event) {

    },
    /**
     * 语言选择，input toggle
     */
    toggleCheck(event) {
      (event.indexOf("中文")!=-1)?this.cnLanguage=false:this.cnLanguage=true;
      (event.indexOf("英文")!=-1)?this.enLanguage=false:this.enLanguage=true;
      (event.indexOf("韩文")!=-1)?this.krLanguage=false:this.krLanguage=true;
      (event.indexOf("日文")!=-1)?this.jaLanguage=false:this.jaLanguage=true;
    },

    openSearchBox() {
      this.showSearchBox = true;
    },
    closeSearchBox() {
      this.showSearchBox = false;
    },
    searchOptionName() {

    }

  }
}

</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
@import "../assets/scss/addGoods.scss"
</style>
