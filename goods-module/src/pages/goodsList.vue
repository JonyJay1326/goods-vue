<template>
  <div class="erp-goods-list">
    <el-row :gutter="10" class="gl-row01">
      <el-col :span="1">
        <div class=" first-title">品牌名</div>
      </el-col>
      <el-col :span="4">
        <el-select v-model="brandNameValue" placeholder="请选择">
          <el-option v-for="item in brandName" :key="item.value" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </el-col>
      <el-col :span="2">
        <div class="other-title">搜索条件</div>
      </el-col>
      <el-col :span="6">
        <el-input placeholder="请输入内容" v-model="searchCondition">
          <el-select v-model="searchConditionSelect" slot="prepend" placeholder="" class='search-condition-select' style="color:#263238;">
            <el-option label="餐厅名" value="1"></el-option>
            <el-option label="订单号" value="2"></el-option>
            <el-option label="用户电话" value="3"></el-option>
          </el-select>
        </el-input>
      </el-col>
      <el-col :span="2">
        <div class="other-title">业务类型</div>
      </el-col>
      <el-col :span="7" class='business-type'>
        <el-select v-model="Type01Value" placeholder="请选择">
          <el-option v-for="item in Type01" :key="item.value" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
        <el-select v-model="Type02Value" placeholder="请选择">
          <el-option v-for="item in Type02" :key="item.value" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
        <el-select v-model="Type03Value" placeholder="请选择">
          <el-option v-for="item in Type03" :key="item.value" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </el-col>
    </el-row>
    <el-row :gutter="0" class="gl-row02">
      <el-col :span="1">
        <div class=" first-title">语言</div>
      </el-col>
      <el-col :span="10">
        <el-checkbox-group v-model="checkLangList">
          <el-checkbox label="全部" :checked="checkedAll" @change="handleCheckAll"></el-checkbox>
          <el-checkbox :value="item.id" :label="item.value" :checked="item.checked" v-for="item in checkLists" @change="handleCheck"></el-checkbox>
        </el-checkbox-group>
      </el-col>
    </el-row>
    <el-row :gutter="0" class="gl-row03">
      <el-col :span="1">
        <div class=" first-title">状态</div>
      </el-col>
      <el-col :span="10">
        <el-checkbox-group v-model="checkStatusList">
          <el-checkbox label="全部" :checked="checkedAllStatus" @change="handleCheckAllStatus"></el-checkbox>
          <el-checkbox :value="item.id" :label="item.value" :checked="item.checked" v-for="item in checkListsStatus" @change="handleCheckStatus"></el-checkbox>
        </el-checkbox-group>
      </el-col>
    </el-row>
    <el-row :gutter="10" class="gl-row04">
      <el-col :span="1">
        <div class="first-title">操作日期</div>
      </el-col>
      <el-col :span="2">
        <el-select v-model="actionDateValue" placeholder="请选择">
          <el-option v-for="item in actionDate" :key="item.value" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </el-col>
      <el-col :span="4">
        <div class="block">
          <el-date-picker v-model="value7" type="daterange" align="right" placeholder="选择日期范围" :picker-options="pickerOptions2">
          </el-date-picker>
        </div>
      </el-col>
    </el-row>
    <div class='query-btns'>
      <el-button type="primary" class="query-btn">查询</el-button>
      <el-button type="primary" class="reset-btn">重置</el-button>
    </div>
    <div class="parting-line"> </div>
    <div class="query-result-list">
      <h3>搜索结果:共<span> 394</span>条记录</h3>
      <el-table :data="tableData" stripe style="width: 100%">
        <el-table-column prop="brandName" label="品牌名">
        </el-table-column>
        <el-table-column prop="spuId" label="SPU ID">
        </el-table-column>
        <el-table-column prop="brandType" label="品牌类型">
        </el-table-column>
        <el-table-column prop="goodName" label="商品名称">
        </el-table-column>
        <el-table-column prop="costPrice" label="成本价">
        </el-table-column>
        <el-table-column prop="unit" label="单位">
        </el-table-column>
        <el-table-column prop="language" label="语言">
        </el-table-column>
        <el-table-column prop="reviewStatus" label="审核状态">
        </el-table-column>
        <el-table-column prop="reviewContain" label="审核内容">
        </el-table-column>
        <el-table-column prop="action" label="操作">
          <template scope="scope">
            <el-button type="primary" class="see-detail" @click="seeDetail">查看</el-button>
            <el-button type="primary" class="do-review" @click="doReview">审核</el-button>
            </el-row>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      // 语言
      checkedAll: true,
      checkLangList: [],
      checkLists: [{
        id: "1",
        checked: false,
        value: "中文"
      }, {
        id: "2",
        checked: false,
        value: "韩文"
      }, {
        id: "3",
        checked: false,
        value: "英文"
      }, {
        id: "4",
        checked: false,
        value: "日文"
      }],

      // 状态
      checkedAllStatus: true,
      checkStatusList: [],
      checkListsStatus: [{
          id: "1",
          checked: false,
          value: "草稿"
        },
        {
          id: "2",
          checked: false,
          value: "已提交审核"
        },
        {
          id: "3",
          checked: false,
          value: "审核成功"
        },
        {
          id: "4",
          checked: false,
          value: "审核失败"

        }
      ],
      brandNameValue: '中国',
      brandName: [{
        value: '选项1',
        label: '中国'
      }, {
        value: '选项2',
        label: '日本'
      }, {
        value: '选项3',
        label: '韩国'
      }],

      searchCondition: "条件",
      searchConditionSelect: '商品名',
      Type01: [{
        value: '选项1',
        label: '中国'
      }, {
        value: '选项2',
        label: '日本'
      }, {
        value: '选项3',
        label: '韩国'
      }],
      Type02: [{
        value: '选项1',
        label: '中国'
      }, {
        value: '选项2',
        label: '日本'
      }, {
        value: '选项3',
        label: '韩国'
      }],
      Type03: [{
        value: '选项1',
        label: '中国'
      }, {
        value: '选项2',
        label: '日本'
      }, {
        value: '选项3',
        label: '韩国'
      }],
      Type01Value: '',
      Type02Value: '',
      Type03Value: '',

      actionDateValue: '创建日期',
      actionDate: [{
        value: '选项1',
        label: '创建日期'
      }, {
        value: '选项2',
        label: '日本'
      }, {
        value: '选项3',
        label: '韩国'
      }],
      pickerOptions2: {
        shortcuts: [{
          text: '今天',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: '最近一周',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: '最近一个月',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: '最近一年',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 365);
            picker.$emit('pick', [start, end]);
          }
        }]
      },
      tableData: [{
        brandName: '小米',
        spuId: '80000512',
        brandType: '电子产品',
        goodName: '小米滑板车',
        costPrice: '2000',
        unit: '个',
        language: '中文',
        reviewStatus: '审核成功',
        reviewContain: '哈哈哈',

      }, {
        brandName: '小米',
        spuId: '80000512',
        brandType: '电子产品',
        goodName: '小米滑板车',
        costPrice: '2000',
        unit: '个',
        language: '中文',
        reviewStatus: '审核成功',
        reviewContain: '哈哈哈',

      }, {
        brandName: '小米',
        spuId: '80000512',
        brandType: '电子产品',
        goodName: '小米滑板车',
        costPrice: '2000',
        unit: '个',
        language: '中文',
        reviewStatus: '审核成功',
        reviewContain: '哈哈哈',

      }, {
        brandName: '小米',
        spuId: '80000512',
        brandType: '电子产品',
        goodName: '小米滑板车',
        costPrice: '2000',
        unit: '个',
        language: '中文',
        reviewStatus: '审核成功',
        reviewContain: '哈哈哈',

      }],
      value6: '',
      value7: '',
    }
  },
  methods: {
    seeDetail() {

    },
    doReview() {

    },
    /**
     * checkbox 逻辑
     */
    handleCheck(event) {
      console.log(event);
      if (this.checkedAll) {
        this.checkedAll = !this.checkedAll;
        this.checkLangList = [];
        console.log(event);
        this.checkLangList.push(event.srcElement.defaultValue)
      } else if (this.checkLangList.length == 0) {
        this.checkedAll = !this.checkedAll;
        this.checkLangList = ["全部"];
      }
    },
    handleCheckAll() {
      this.checkedAll = !this.checkedAll;
      if (this.checkedAll) {
        this.checkLangList = ["全部"];
      } else if (this.checkLangList.length == 0) {
        this.checkedAll = !this.checkedAll;
        this.checkLangList = ["全部"];
      }
    },
    handleCheckStatus(event) {
      if (this.checkedAllStatus) {
        this.checkedAllStatus = !this.checkedAllStatus;
        this.checkStatusList = [];
        console.log(event);
        this.checkStatusList.push(event.srcElement.defaultValue)
      } else if (this.checkStatusList.length == 0) {
        this.checkedAllStatus = !this.checkedAllStatus;
        this.checkStatusList = ["全部"];
      }
    },
    handleCheckAllStatus() {
      this.checkedAllStatus = !this.checkedAllStatus;
      if (this.checkedAllStatus) {
        this.checkStatusList = ["全部"];
      } else if (this.checkStatusList.length == 0) {
        this.checkedAllStatus = !this.checkedAllStatus;
        this.checkStatusList = ["全部"];
      }

    },
  }
}

</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
@import "../assets/scss/goodsList.scss"
</style>
