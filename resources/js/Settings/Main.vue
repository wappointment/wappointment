<template>
  <div class="container-fluid">
    <h1 class="px-2">Settings</h1>
    
    <ul class="nav nav-tabs row px-4" id="myTab" role="tablist" v-if="isUserAdministrator">
        <li v-for="(tab, key) in tabs" class="nav-item">
            <span class="nav-link" :class="{'active' : isActive(key)}" @click="changeTab(key)">{{ tab.label }}</span>
        </li>
    </ul>

    <div class="tab-content p-2" id="myTabContent" :data-active-page="activeTab">
        <div class="tab-pane fade" :class="{'show active' : isActive('calendars')}" v-if="isActive('calendars')">
            <settingsCalendars @fullyLoaded="$emit('fullyLoaded')" :tablabel="tabs.calendars.label" />
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('services')}" v-if="isActive('services')">
            <settingsServices @fullyLoaded="$emit('fullyLoaded')" :tablabel="tabs.services.label" />
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('emailsnsms')}" v-if="isActive('emailsnsms')">
            <settingsEmailNSms @fullyLoaded="$emit('fullyLoaded')" :tablabel="tabs.emailsnsms.label" />
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('appearance')}" v-if="isActive('appearance')">
            <SettingsAppearance @fullyLoaded="$emit('fullyLoaded')" :tablabel="tabs.appearance.label" />
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('advanced')}" v-if="isActive('advanced')">
            <settingsAdvanced @fullyLoaded="$emit('fullyLoaded')" :tablabel="tabs.advanced.label" />
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('addonstab')}" v-if="isActive('addonstab')">
            <settingsAddons @fullyLoaded="$emit('fullyLoaded')" />
        </div>
    </div>
    
  </div>
</template>

<script>

import abstractView from '../Views/Abstract'
import settingsCalendars from './Calendars'
import settingsServices from './Services'
import settingsEmailNSms from './EmailNSms'
import SettingsAppearance from './Appearance'
import settingsAdvanced from './Advanced'
import settingsAddons from './Addons'
import hasPermissions from '../Mixins/hasPermissions'

export default {
  extends: abstractView,
  mixins: [hasPermissions],
    data: () => ({
        service: null,
        tabs:{
            calendars:{
                label: 'Calendars & Staff'
            },
            services:{
                label: 'Services'
            },
            emailsnsms:{
                label: 'Email & SMS'
            },
            appearance:{
                label: 'Appearance'
            },
            advanced:{
                label: 'Advanced'
            },
        },
    }),

    components: {
      settingsCalendars,
      settingsServices,
      settingsEmailNSms,
      settingsAdvanced,
      SettingsAppearance,
      settingsAddons
    },

    created() {
         if(window.wappointmentAdmin.addons !== undefined && this.addonsWithSettings().length > 0) {
            this.tabs['addonstab'] = { label: 'Addons'}
        }
    },
    computed: {
        convertedName(){
            return ['modalities', 'modalities_add', 'modalities_edit'].indexOf(this.$route.name) !== -1  ? 'services':this.$route.name
        },
        activeTab(){
            return this.convertedName.indexOf('_') === -1 ? this.convertedName : this.convertedName.split('_')[0]
        }
    },
    methods: {
         addonsWithSettings(){
            let addonsWithSettings = {}
            let addonscopy = window.wappointmentAdmin.addons
            for (const key in addonscopy) {
                if (addonscopy.hasOwnProperty(key)) {
                    const element = addonscopy[key]
                    if([undefined,false].indexOf(element.settings)=== -1){
                        addonsWithSettings[key] = element
                    }
                }
            }
            return Object.keys(addonsWithSettings)
        },

        isActive(key){
            return key == this.activeTab || this.activeTab.indexOf(key) === 0
        },
        
        changeTab(selectedTab){
            if(this.$route.name == selectedTab){
                //refresh content
            }else{
                this.$router.push({name: selectedTab})
            }
        },       
    }  
}
</script>
<style>
.max-200{
    max-width:200px
}
</style>