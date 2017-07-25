import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
  routes: [{
    path: '/index',
    name: 'erpMain',
    component: require('../pages/erpMain'),
    children: [{
        path: 'addgoods',
        component: require('../pages/addGoods')
      },
      {
        path: 'goodslist',
        component: require('../pages/goodsList')
      },
      {
        path: 'goodsdetails',
        component: require('../pages/goodsDetails')
      },
      {
        path: 'goodsdetailsedit',
        component: require('../pages/goodsDetailsEdit')
      }
    ]
  }, {
    path: '*',
    redirect: '/index'
  }]
})
