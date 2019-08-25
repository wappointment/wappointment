__webpack_public_path__ = apiWappointment.resourcesUrl;
import Vue from './appVue'
import VueRouter from 'vue-router'
import App from './App'
import VueWapModal from './Plugins/vue-wap-modal'
import StickyBar from './Components/StickyBar'
import RingLoader from './Components/Loaders/Ring'
import WLoader from './Components/Loaders/BigCalendar'
import VueService from './Plugins/vue-service'

Vue.use(VueWapModal)
Vue.use(VueService, {base:apiWappointment.root})

Vue.component('v-style', {
  render: function (createElement) {
      return createElement('style', this.$slots.default)
  }
});
Vue.component('StickyBar', StickyBar)
Vue.component('RingLoader', RingLoader)
Vue.component('WLoader', WLoader)
Vue.use(VueRouter)

const CalendarPage = () => import(/* webpackChunkName: "group-calendar" */ './Views/Calendar')
const SettingsPage = () => import(/* webpackChunkName: "group-settings" */ './Views/Settings')
const AddonsPage = () => import(/* webpackChunkName: "group-addons" */ './Views/Addons')
const HelpPage = () => import(/* webpackChunkName: "group-help" */ './Views/Help')

const WizardPage = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard')
const Wizard1Page = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard1')
const Wizard2Page = () => import(/* webpackChunkName: "group-wizard" */ './Views/Subpages/Wizard2')
const Wizard3Page = () => import(/* webpackChunkName: "group-wizard2" */ './Views/Subpages/Wizard3')
const Wizard4Page = () => import(/* webpackChunkName: "group-wizard2" */ './Views/Subpages/Wizard4')

const RegavPage = () => import(/* webpackChunkName: "group-settingspages" */ './Views/Subpages/Regav')
const SyncPage = () => import(/* webpackChunkName: "group-settingspages" */ './Views/Subpages/Sync')
const ServicePage = () => import(/* webpackChunkName: "group-settingspages" */ './Views/Subpages/Service')
const ReminderAdd = () => import(/* webpackChunkName: "group-settingspages" */ './Views/Subpages/ReminderAdd')

const router = window.wappointmentrouter = new VueRouter({
  mode: 'history',
  base: '/wp-admin/admin.php?page=wappointment_',
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
                path: 'sync',
                name: 'sync',
                component: SyncPage
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
                path: 'addreminder',
                name: 'addreminder',
                component: ReminderAdd
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

window.changeWpMenuActiveLink = function (pagename) {

  jQuery(function($){
    $('#toplevel_page_wappointment_calendar li.current, #toplevel_page_wappointment_calendar li a.current').removeClass('current')
    let testpagename = pagename
    if(['general','reminders', 'notifications', 'advanced', 'addonstab'].indexOf(testpagename) !== -1){
      testpagename = 'wappointment_settings'
    }
    let menuIndex = ['wappointment_calendar','wappointment_settings', 'wappointment_addons', 'wappointment_help' ].indexOf(testpagename) + 2
      $('#toplevel_page_wappointment_calendar ul.wp-submenu li:nth-child('+menuIndex+') , #toplevel_page_wappointment_calendar ul.wp-submenu li:nth-child('+menuIndex+') a')
      .addClass('current')
  });
  
};

router.beforeEach((to, from, next) => {
  if(to.query.page!== undefined && to.query.page.indexOf('wappointment_')!==-1){
    if(['wappointment_calendar', 'wappointment_settings'].indexOf(to.query.page) !== -1 && to.hash.indexOf('#/') !== -1){
            next({ name: to.hash.replace('#/','')})
        }else{
          if(to.path == '/wp-admin/admin.php' && to.query.page=='wappointment_calendar' && to.query.start !== undefined){
            //we save the query parameters for later use start, end , timezone
            window.savedQueries = to.query
          }
          next({ name: to.query.page})
            //
        }
    } else{
        changeWpMenuActiveLink(to.name)
        next()
    }

  })

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
            let result = wap_parse_query_string(window.location.search.replace('?',''))

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
  components: { App },
  render: h => h(App)
})

function wap_parse_query_string(query) {
    var vars = query.split("&");
    var query_string = {};
    for (var i = 0; i < vars.length; i++) {
      var pair = vars[i].split("=");
      var key = decodeURIComponent(pair[0]);
      var value = decodeURIComponent(pair[1]);
      // If first entry with this name
      if (typeof query_string[key] === "undefined") {
        query_string[key] = decodeURIComponent(value);
        // If second entry with this name
      } else if (typeof query_string[key] === "string") {
        var arr = [query_string[key], decodeURIComponent(value)];
        query_string[key] = arr;
        // If third or later entry with this name
      } else {
        query_string[key].push(decodeURIComponent(value));
      }
    }
    return query_string;
  }
