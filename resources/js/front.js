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

        triggerWEvent(eventName, eventData){
            let event = null
            if(typeof(Event) === 'function') {
                event = new Event(eventName)
            }else{
                event = document.createEvent('Event')
                event.initEvent(eventName, true, true)
            }
            
            event.wdata = eventData
            document.dispatchEvent(event)
        },
        cleanString: function (string) {
            let doc = new DOMParser().parseFromString(string, 'text/html')
            return doc.body.textContent || ''
        },
    }
});


const vuesInstances = [];
const wappoInstances = document.querySelectorAll(".wappointment_page, .wappointment_widget");
const footerContainer = getFooterContainer();

for (const el of wappoInstances) {

    if([undefined,null].indexOf(el.getAttribute('data-brc-floats')) === -1){
        console.log('test there is one fixed')
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
