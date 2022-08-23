import store from './_store';
import Vue from 'vue';
import vClickOutside from 'v-click-outside';
import Postbox from './index.vue';

Vue.use(vClickOutside);

export default function() {
    new Vue({
        store,
        render: h => h(Postbox)
    }).$mount('.joms-postbox'); 
}