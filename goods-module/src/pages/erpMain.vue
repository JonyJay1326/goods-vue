<template>
  <div class="erp-main">
    <el-menu class="el-menu-demo" mode="horizontal" @select="handleSelect">
      <el-menu-item index="1">处理中心</el-menu-item>
      <el-submenu index="2">
        <template slot="title">我的工作台</template>
        <el-menu-item index="2-1">选项1</el-menu-item>
        <el-menu-item index="2-2">选项2</el-menu-item>
        <el-menu-item index="2-3">选项3</el-menu-item>
      </el-submenu>
      <el-menu-item index="3"><a href="https://www.ele.me" target="_blank">订单管理</a></el-menu-item>
    </el-menu>
    <el-tree node-key="id" :data="data" :default-expanded-keys="[0,1]" :props="defaultProps" accordion @node-click="handleNodeClick">
    </el-tree>
    <div class='erp-mian-content'>
      <keep-alive>
        <router-view></router-view>
      </keep-alive>
    </div>
  </div>
</template>
<script>
export default {
  name: 'erpMain',
  data() {
    return {
      data: [{
        id: 1,
        label: '商品管理',
        children: [{
          id: 4,
          label: '新增商品',
          class: "addgoods"
        }, {
          id: 5,
          label: '商品列表',
          class: "goodslist"

        }, {
          id: 6,
          label: '商品审核',
        }]
      }, {
        id: 2,
        label: '营销管理',
        children: [{
          label: '二级 2-1',
        }, {
          label: '二级 2-2',
        }]
      }, {
        id: 3,
        label: '合同管理',
        children: [{
          label: '二级 3-1',
        }, {
          label: '二级 3-2',
        }]
      }],
      defaultProps: {
        children: 'children',
        label: 'label',
        class: 'class'
      }
    }
  },
  methods: {
    handleSelect(key, keyPath) {
      console.log(key, keyPath);
    },
    handleNodeClick(data) {
      this.$router.push({
        path: '/index/' + data.class
      })
      console.log(data.class);
    }
  }
}

</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss" scoped>
.erp-mian-content{
  position: absolute;
  top: 60px;
  right: 0;
  bottom: 0;
  left: 220px;
  overflow: auto;
  z-index: 1;
  background-color: #fff;
}
.el-menu {
  height:60px;
}
.el-tree{
  position: absolute;
  top: 60px;
  bottom: 0;
  left: 0;
  padding-top: 10px;
  width: 220px;
  /* z-index: 10; */
  overflow: auto;
  border-right: 1px solid #3a3f51;
}
</style>
