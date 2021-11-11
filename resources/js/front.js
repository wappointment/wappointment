__webpack_public_path__ = apiWappointment.resourcesUrl;

import Vue from './appVue'
import Front from './Front'
import VueService from './Plugins/vue-service'
import WLoader from './Components/Loaders/BigCalendar'
import VueWapModal from './Plugins/vue-wap-modal'
import __get from 'lodash/get'
import UrlParam from './Modules/UrlParam'
import WTrigger from './Mixins/WTrigger'


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

Vue.mixin(WTrigger)
Vue.mixin(UrlParam)


const vuesInstances = [];
const wappoInstances = document.querySelectorAll(".wappointment_page, .wappointment_widget");
const footerContainer = getFooterContainer();

for (const el of wappoInstances) {

    if([undefined,null].indexOf(el.getAttribute('data-brc-floats')) === -1){
        footerContainer.appendChild(el)
    }
}


for (const el of wappoInstances) {
    vuesInstances.push(
        new Vue({
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
    )
}

function getFooterContainer(){
    let id = 'wap-footer-container'
    let modalContainer = document.getElementById(id)

    if(modalContainer === null){
      modalContainer = document.createElement('div')
      modalContainer.setAttribute('id', id)
      modalContainer.setAttribute('style', 'position:absolute;z-index:9999999999999999999999;')
      modalContainer.setAttribute('class', 'wappointment-wrap')
      modalContainer = document.body.appendChild(modalContainer)
    }
    return modalContainer
}
