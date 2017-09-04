/**
 * Created by b5m on 16/12/26.
 * for vue.js
 *
 */
var vm = new Vue({
    el: '#content',
    data: {
        json_list: ''

    },
    init:function () {
      this.json_list = document.getElementById('load_list').value;
    },
    methods: {
        beforeMount: function () {
            console.log('a')
        }
        ,
        load_this: function () {
            console.log('a')
        }
        ,
        edit_this: function (show_id, msg) {
            console.log(msg)
            show_window(show_id);
        }
        ,
        del_this: function (del_id, list) {

            var del_start = confirm('是否确认删除');
            if (del_start) {
                // upd

                this.json_list.$remove(list);
            }
        }
    }
})