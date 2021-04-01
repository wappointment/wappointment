__webpack_public_path__ = apiWappointment.resourcesUrl;

import Vue from './appVue'
import Front from './Front'
import VueService from './Plugins/vue-service'
import WLoader from './Components/Loaders/BigCalendar'
import VueWapModal from './Plugins/vue-wap-modal'
import __get from 'lodash/get'

const WapImage = () => import(/* webpackChunkName: "WapImage" */ './Components/WapImage')

Vue.use(VueWapModal)
Vue.use(VueService, {base:apiWappointment.root})

Vue.component('WapImage', WapImage)
Vue.component('WLoader', WLoader)
Vue.component('v-style', {
    render: function (createElement) {
        return createElement('style', this.$slots.default)
    }
});
Vue.mixin({
    methods: {

        triggerWEvent(eventName){
            const event = document.createEvent('Event')
            event.initEvent(eventName, true, true)
            document.dispatchEvent(event)
        }
    }
});


const vuesInstances = [];
const vues = document.querySelectorAll(".wappointment_page, .wappointment_widget");

for (let index = 0; index < vues.length; index++) {
    const el = vues[index]
    console.log('el.dataset',Object.assign({},el.dataset))
    vuesInstances[index] = new Vue({
        el, 
        components: { Front },
        render: h => h(Front, {
            props: {
                'classEl' : el.getAttribute('class'),
                'attributesEl':  Object.assign({},el.dataset),
                'buttonTitle' : el.getAttribute('data-button-title') ? el.getAttribute('data-button-title'):'Book an appointment',
                'brFixed' : [undefined,null].indexOf(el.getAttribute('data-brfixed')) === -1 ? true:false
            }
        }),
    }) 
    
}