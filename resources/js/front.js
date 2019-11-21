__webpack_public_path__ = apiWappointment.resourcesUrl;

import Vue from './appVue'
import Front from './Front'
import VueWapModal from './Plugins/vue-wap-modal'
import VueService from './Plugins/vue-service'
import WLoader from './Components/Loaders/BigCalendar'

Vue.use(VueWapModal)
Vue.use(VueService, {base:apiWappointment.root})
Vue.component('WLoader', WLoader)
Vue.component('v-style', {
    render: function (createElement) {
        return createElement('style', this.$slots.default)
    }
});

const vuesInstances = [];
const vues = document.querySelectorAll(".wappointment_page, .wappointment_widget");

for (let index = 0; index < vues.length; index++) {
    const el = vues[index]
    vuesInstances[index] = new Vue({
        el, 
        components: { Front },
        render: h => h(Front, {
            props: {
                'classEl' : el.getAttribute('class'),
                'buttonTitle' : el.getAttribute('data-button-title') ? el.getAttribute('data-button-title'):'Book an appointment',
                'brFixed' : [undefined,null].indexOf(el.getAttribute('data-brfixed')) === -1 ? true:false
            }
        }),
    }) 
    
}


