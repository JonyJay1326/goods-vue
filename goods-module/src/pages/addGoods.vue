<template>
  <div class="erp-addgoods">
    <el-tabs v-model="activeName" @tab-click="handleClick">
      <el-tab-pane label="基础信息" name="first">
        <header>品牌信息</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-brand-info">
          <tr class="tr-1">
            <td class="info-title">品牌名</td>
            <td>
              <el-select v-model="value" placeholder="请选择">
                <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </td>
            <td class="info-title">品牌类目</td>
            <td>
              <el-select v-model="value" placeholder="请选择">
                <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
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
              <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="textarea" resize="none">
              </el-input>
            </td>
            <td class="info-title category">二级类目</td>
            <td>
              <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="textarea" resize="none">
              </el-input>
            </td>
            <td class="info-title category">三级类目</td>
            <td>
              <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="textarea" resize="none">
              </el-input>
            </td>
            <td class="info-title category">四级类目</td>
            <td>
              <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="textarea" resize="none">
              </el-input>
            </td>
          </tr>
        </table>
        <header>商品信息</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-goods-info">
          <tr class="tr-goods">
            <td class="info-title">语言</td>
            <td>
              <el-select v-model="value" placeholder="请选择">
                <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </td>
            <td class="info-title">品牌ID</td>
            <td>
              <p></p>
            </td>
            <td class="info-title">商品单位</td>
            <td>
              <el-select v-model="value" placeholder="请选择">
                <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </td>
            <td class="info-title">商品保质期限</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
          </tr>
          <tr class="tr-goods">
            <td class="info-title">标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
            <td class="info-title">英文标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
            <td class="info-title">韩文标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
            <td class="info-title">日文标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
          </tr>
          <tr class="tr-goods">
            <td class="info-title">副标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
            <td class="info-title">英文副标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
            <td class="info-title">韩文副标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
            <td class="info-title">日文副标题</td>
            <td>
              <el-input v-model="input" placeholder="请输入内容"></el-input>
            </td>
          </tr>
        </table>
        <header>商品主图</header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-goods-pic">
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
        </table>
        <div class="erp-addgoods-btns">
          <el-button type="primary">保存</el-button>
          <el-button>重置</el-button>
        </div>
      </el-tab-pane>
      <el-tab-pane label="SKU信息" name="second">
        <div class="sku-item">
          <span>币种</span>
          <el-select v-model="value" placeholder="请选择" id='currency_choice'>
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
          <span>产地</span>
          <el-select v-model="value" placeholder="请选择">
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
        </div>
        <header>添加SKU
          <el-button>应用</el-button>
        </header>
        <table border="0" cellspacing="0" cellpadding="0" class="erp-add-sku">
          <tr class="add-sku-title">
            <td>Option Name</td>
            <td>Option Value</td>
          </tr>
          <tr class="add-sku-option">
            <td>
              <ul>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
              </ul>
            </td>
            <td>
              <ul>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
                <li><el-input v-model="input" placeholder="请输入内容"></el-input></li>
              </ul>
            </td>
          </tr>
        </table>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>
<script>
export default {
  data() {
    return {
      activeName: 'first'
    }
  },
  components: {

  },
  methods: {
    handleClick(tab, event) {
      console.log(tab, event);
    }
  }
}

</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
.erp-mian-content{
  padding: 0px 30px;
  header{
    font-family: PingFangSC-Medium;
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
}
.el-tabs__header{
  border:none;
  .el-tabs__active-bar{
    left:14px;
    height:2px;
    margin-bottom: 0px;
  }
  .el-tabs__item{
    font-family: PingFangSC-Regular;
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
    padding:9px 20px 0px;
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
    font-family: PingFangSC-Regular;
    font-size: 13px;
    color: #546E7A;
    letter-spacing: 0;
    padding:0px;
    width:100px;
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
      font-family: PingFangSC-Regular;
      font-size: 14px;
      color: #546E7A;
      letter-spacing: 0;
      margin-top:23px;
    }
    button{
      background-image: linear-gradient(-180deg, #FFFFFF 0%, #EFEFEF 100%);
      border: 1px solid #C8D2D7;
      border-radius: 4px;
      font-family: PingFangSC-Regular;
      font-size: 14px;
      color: #263238;
      letter-spacing: 0;
      float:right;
      margin-top:20px;
    }
  }
}
.erp-addgoods-btns{
  margin: 60px 0px 100px;
  button{
    width:110px;
    height:44px;
    font-family: PingFangSC-Regular;
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
  span{
    padding-right:18px;
    font-family: PingFangSC-Regular;
    font-size: 13px;
    color: #263238;
    letter-spacing: 0;
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
      width:50%;
      border-right:1px solid #CADEE7;
      border-top:1px solid #ECEFF1;
    }
  }
  .add-sku-title{
    font-family: PingFangSC-Regular;
    font-size: 14px;
    color: #546E7A;
    letter-spacing: 0;
    height:40px;
    line-height:40px;
    td{
      padding-left:20px;
    }
  }
  .add-sku-option{
    height:95px;
    ul{
      li{
        float:left;
        padding:0px 6px;
        margin:5px 0px;
        .el-input{
          height:32px;
        }
      }
    }
  }
}
</style>
