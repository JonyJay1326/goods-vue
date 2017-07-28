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
        path: 'goodsEdit',
        component: require('../pages/goodsEdit')
      },
      {
        path: 'goodsReview',
        component: require('../pages/goodsReview')
      }
    ]
  }, {
    path: '*',
    redirect: '/index'
  }]
})
