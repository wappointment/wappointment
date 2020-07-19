__webpack_public_path__ = apiWappointment.resourcesUrl;
import Vue from './appVue'
import ClickCopy from './Fields/ClickCopy'
import InputPh from './Fields/InputLabelMaterial'
import VideoIframe from './Ne/VideoIframe'
window.wappointmentExtends.store('commons', {InputPh, ClickCopy, VideoIframe})

import VueRouter from 'vue-router'
import Backend from './Backend'
import VueWapModal from './Plugins/vue-wap-modal'
import FormGenerator from './Form/FormGenerator'
import StickyBar from './Components/StickyBar'
import RingLoader from './Components/Loaders/Ring'
import WLoader from './Components/Loaders/BigCalendar'
import DurationCell from './BookingForm/DurationCell'
import VueService from './Plugins/vue-service'
import changeWPmenu from './Standalone/changeWPmenu'
import parseQuery from './Standalone/parseQuery'



window.wappoChangeWPmenu = changeWPmenu

Vue.use(VueWapModal)
Vue.use(VueService, {base:apiWappointment.root})

Vue.component('v-style', {
  render: function (createElement) {
      return createElement('style', this.$slots.default)
  }
});
Vue.component('WAPFormGenerator', FormGenerator)
Vue.component('StickyBar', StickyBar)
Vue.component('RingLoader', RingLoader)
Vue.component('WLoader', WLoader)
Vue.component('DurationCell', DurationCell)
Vue.use(VueRouter)


const CalendarPage = () => import(/* webpackChunkName: "group-calendar" */ './CalendarAdmin/Main')
const SettingsPage = () => import(/* webpackChunkName: "group-settings" */ './Views/Settings')
const AddonsPage = () => import(/* webpackChunkName: "group-addons" */ './Views/Addons')
const HelpPage = () => import(/* webpackChunkName: "group-help" */ './Ne/Help')

const WizardPage = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard')
const Wizard1Page = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard1')
const Wizard2Page = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard2')
const Wizard3Page = () => import(/* webpackChunkName: "group-wizard2" */ './Views/Subpages/Wizard3')
const Wizard4Page = () => import(/* webpackChunkName: "group-wizard2" */ './Views/Subpages/Wizard4')

const RegavPage = () => import(/* webpackChunkName: "group-settingspages" */ './Views/Subpages/Regav')
const ServicePage = () => import(/* webpackChunkName: "group-settingspages" */ './Views/Subpages/Service')

const router = window.wappointmentrouter = new VueRouter({
  mode: 'history',
  base: window.apiWappointment.base_admin + '?page=wappointment_',
  routes: [
      {
          path: 'calendar',
          name: 'wappointment_calendar',
          component: CalendarPage
      },
      {
          path: 'settings',
          name: 'wappointment_settings',
          redirect: { name: 'general'}
      },
      {
          path: 'help',
          name: 'wappointment_help',
          component: HelpPage
      },
      {
          path: 'addons',
          name: 'wappointment_addons',
          component: AddonsPage
      },
      
      {
          path: 'calendar#',
          component: WizardPage,
          children: [
            {
              path: 'wizard1',
              name: 'wizard1',
              component: Wizard1Page
            },
            {
              path: 'wizard2',
              name: 'wizard2',
              component: Wizard2Page
            },
            {
              path: 'wizard3',
              name: 'wizard3',
              component: Wizard3Page
            },
            {
              path: 'wizard4',
              name: 'wizard4',
              component: Wizard4Page
            },            
          ]
      },
      {
          path: 'settings#',
          component: WizardPage,
          children: [
            {
              path: 'regav',
              name: 'regav',
              component: RegavPage
            },
            {
                path: 'service',
                name: 'servicepage',
                component: ServicePage
            },
            {
                path: 'general',
                name: 'general',
                component: SettingsPage
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: SettingsPage
            },
            {
                path: 'reminders',
                name: 'reminders',
                component: SettingsPage
            },
            {
                path: 'sync',
                name: 'sync',
                component: SettingsPage
            },
            {
                path: 'advanced',
                name: 'advanced',
                component: SettingsPage
            },
            {
                path: 'addonstab',
                name: 'addonstab',
                component: SettingsPage
            }
            
          ]
      },

  ],
  linkActiveClass: 'active',
})



router.beforeEach((to, from, next) => {
  if(to.query.page!== undefined && to.query.page.indexOf('wappointment_')!==-1){
    if(['wappointment_calendar', 'wappointment_settings'].indexOf(to.query.page) !== -1 && to.hash.indexOf('#/') !== -1){
          next({ name: to.hash.replace('#/','')})
        }else{
          if(to.path == window.apiWappointment.base_admin && to.query.page=='wappointment_calendar' && to.query.start !== undefined){
            //we save the query parameters for later use start, end , timezone
            window.savedQueries = to.query
          }
          next({ name: to.query.page})

        }
    } else{
        window.wappoChangeWPmenu(to.name)
        next()
    }

  })

  window.jQuery(function($){
    $('.wappointmentLink').click(function(e){
      let pagename =  e.currentTarget.getAttribute('data-pagename')
      router.push({ name: pagename })
      window.wappoChangeWPmenu(pagename)
      if (e.stopPropagation) e.stopPropagation()
      if (e.preventDefault) e.preventDefault()
      return false
    })
    
  });

  
const app = new Vue({
  router,
  el: '#wappointment_app',
  created: function () {

    var wizardInt = parseInt(wappointmentAdmin.wizardStep);
    if(wizardInt > -1){
        switch (wizardInt) {
            case 0:
            case 1:
            case 2:
            case 3:
                router.push({ name: 'wizard'+(wizardInt+1)})
                break;
            default:
                break;
        }
        
    }else {
        if(parseInt(wappointmentAdmin.updatePage) === 1){
            router.push({ name: 'wappointment_update'})
        }else{
            let result = parseQuery(window.location.search.replace('?',''))
            if(result.page !== undefined && result.page.startsWith('wappointment_')) {
                if(result.page == 'wappointment_settings') {
                    if(result.page == 'wappointment_settings' && window.location.hash.indexOf('#/') !== -1){
                        router.push({ name: window.location.hash.replace('#/','')})
                    }else{
                        router.push({ name: 'wappointment_settings'})
                    }
                }else{
                    router.push({ name: result.page})
                }
                
            }else{
                router.push({ name: 'wappointment_calendar'})
            }
            
        }
    }
    
  },
  components: { Backend },
  render: h => h(Backend)
})


