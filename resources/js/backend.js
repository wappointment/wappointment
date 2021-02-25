__webpack_public_path__ = apiWappointment.resourcesUrl;
import Vue from './appVue'
import ClickCopy from './Fields/ClickCopy'
import InputPh from './Fields/InputLabelMaterial'
import VideoIframe from './Ne/VideoIframe'
const FontAwesomeIcon = () => import(/* webpackChunkName: "appFawesome" */ './appFawesome')
const WapImage = () => import(/* webpackChunkName: "WapImage" */ './Components/WapImage')
import DurationCell from './BookingForm/DurationCell'
import PhoneInput from './BookingForm/PhoneInput'
import AbstractListing from './Views/AbstractListing'
import momenttz from './appMoment'
import RequestMaker from './Modules/RequestMaker'

window.wappointmentExtends.store('commons', {RequestMaker,AbstractListing, PhoneInput, InputPh, ClickCopy, VideoIframe, FontAwesomeIcon, DurationCell, momenttz})

import VueRouter from 'vue-router'
import Backend from './Backend'
import VueWapModal from './Plugins/vue-wap-modal'
import FieldsOptional from './FormOptional/Fields.js'
import FormGenerator from './Form/FormGenerator'
import StickyBar from './Components/StickyBar'
import RingLoader from './Components/Loaders/Ring'
import WLoader from './Components/Loaders/BigCalendar'
import VueService from './Plugins/vue-service'
import rewriteWPMenu from './Standalone/rewriteWPMenu'
import routerSetupRedirect from './Standalone/routerSetupRedirect'
import routerQueryRedirect from './Standalone/routerQueryRedirect'
import getRoutePush from './Standalone/getRoutePush'
import ServicesDelivery from './Settings/ServicesDelivery'
import ClientsPage from './Views/Clients'

Vue.use(VueWapModal)
Vue.use(VueService, {base:apiWappointment.root})

Vue.component('WapImage', WapImage)
Vue.component('v-style', {
  render: function (createElement) {
      return createElement('style', this.$slots.default)
  }
});
Vue.component('WAPFormGenerator', FormGenerator)
Vue.component('StickyBar', StickyBar)
Vue.component('InputPh', InputPh)
Vue.component('RingLoader', RingLoader)
Vue.component('WLoader', WLoader)
Vue.component('DurationCell', DurationCell)
Vue.use(VueRouter)

const CalendarPage = () => import(/* webpackChunkName: "group-calendar" */ './CalendarAdmin/Main')
//const ClientsPage = () => import(/* webpackChunkName: "group-clients" */ './Views/Clients')
const SettingsPage = () => import(/* webpackChunkName: "group-settings" */ './Settings/Main')
const AddonsPage = () => import(/* webpackChunkName: "group-addons" */ './Views/Addons')
const HelpPage = () => import(/* webpackChunkName: "group-help" */ './Ne/Help')

const WrapperPage = () => import(/* webpackChunkName: "group-wizardinit" */ './Views/Subpages/Wrapper')
const Wizard1Page = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard1')
const Wizard2Page = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard2')
const Wizard3Page = () => import(/* webpackChunkName: "group-wizard2" */ './Views/Subpages/Wizard3')
const Wizard4Page = () => import(/* webpackChunkName: "group-wizard2" */ './Views/Subpages/Wizard4')

const ServicePage = () => import(/* webpackChunkName: "group-service" */ './Views/Subpages/Service')
const ServicesManage = () => import(/* webpackChunkName: "group-service-manage" */ './Settings/ServicesManage')
let ClientsPageExtended = () => new Promise((resolutionFunc) => resolutionFunc(window.wappointmentExtends.filter('ClientsPage', ClientsPage)))
let ServicesDeliveryExtended = () => new Promise((resolutionFunc) => resolutionFunc(window.wappointmentExtends.filter('ServicesDelivery', ServicesDelivery)))
const WappointmentErrorFileNotLoading = () => import(/* webpackChunkName: "wappo-error" */ './Views/WappointmentErrorFileNotLoading')

let WappoBackroutes = [
  {
      path: 'calendar',
      name: 'wappointment_calendar',
      component: CalendarPage
  },
  {
      path: 'settings',
      name: 'wappointment_settings',
      redirect: { name: 'calendars'}
  },
  {
    path: 'clients#',
    component: WrapperPage,
    children: [
      {
        path:'',
        component: ClientsPageExtended,
        name: 'wappointment_clients',
      },
    ]
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
      component: WrapperPage,
      children: [
        {
          path: 'error',
          name: 'wappointment_error',
          component: WappointmentErrorFileNotLoading
        },
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
      component: WrapperPage,
      children: [
        {
            path: 'service',
            name: 'servicepage',
            component: ServicePage
        },
        {
            path: 'calendars',
            name: 'calendars',
            component: SettingsPage
        },
        {
            path: 'general_zoom_account',
            name: 'general_zoom_account',
            component: SettingsPage
        },
        {
            path: 'general_regav',
            name: 'general_regav',
            component: SettingsPage
        },
        {
            path: 'services',
            component: SettingsPage,
            children: [
              {
                name: 'services',
                  path: '',
                  component: ServicesManage
              }
            ]
        },
        {
          path: 'modalities',
          component: SettingsPage,
          children: [
            {
                path: '',
                name: 'modalities',
                component: ServicesDeliveryExtended
            },
            {
                path: 'edit/:id',
                name: 'modalities_edit',
                component: ServicesDeliveryExtended
            }
          ]
        },
        {
            path: 'emailsnsms',
            name: 'emailsnsms',
            component: SettingsPage
        },
        {
            path: 'appearance',
            name: 'appearance',
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

]


  function runWappo() {
    WappoBackroutes = window.wappointmentExtends.filter('WappoBackroutes', WappoBackroutes)
    const router = window.wappointmentrouter = new VueRouter({
      mode: 'history',
      base: window.apiWappointment.base_admin + '?page=wappointment_',
      routes: WappoBackroutes,
      linkActiveClass: 'active',
    })
    
    router.beforeEach((to, from, next) => {
      if(to.query.page!== undefined && to.query.page.indexOf('wappointment_')!==-1){
        if(['wappointment_calendar', 'wappointment_settings', 'wappointment_clients'].indexOf(to.query.page) !== -1 && to.hash.indexOf('#/') !== -1){
          next(getRoutePush(to.hash, to))
          }else{
            if(to.path == window.apiWappointment.base_admin && to.query.page=='wappointment_calendar' && to.query.start !== undefined){
              //we save the query parameters for later use start, end , timezone
              window.savedQueries = to.query
            }
            next({ name: to.query.page})
          }
      } else{
          rewriteWPMenu(to.name)
          next()
      }
    })
    router.onError((error) => {
      window.backWappoError = error
      router.push({ name: 'wappointment_error'})
    })
    
    window.jQuery(function($){ // scan certain link to apply a routing
      $('.wappointmentLink').click(function(e){
        let pagename =  e.currentTarget.getAttribute('data-pagename')
        router.push({ name: pagename })
        rewriteWPMenu(pagename)
        if (e.stopPropagation) e.stopPropagation()
        if (e.preventDefault) e.preventDefault()
        return false
      })
      
    })
      
    const app = new Vue({
      router,
      el: '#wappointment_app',
      created: function () {
        routerSetupRedirect(router)
        // if(routerSetupRedirect(router) === false){
        //   routerQueryRedirect(router)
        // }
      },
      components: { Backend },
      render: h => h(Backend)
    })
  }

  //run  with delay when addons are present to load all the injectors
  if(Object.keys(window.wappointmentAdmin.addons.length > 0 )){
    //allow addons to add more routes
    window.addEventListener('load', runWappo)
  }else{
    runWappo()
  }
  





