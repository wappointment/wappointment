<template>
  <div class="container-fluid">
    <h1>Settings</h1>
    
    <ul class="nav nav-tabs row" id="myTab" role="tablist">
        <li v-for="(tab, key) in tabs" class="nav-item">
            <span class="nav-link" :class="{'active' : isActive(key)}" @click="changeTab(key)">{{ tab.label }}</span>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent" :data-active-page="activeTab">
        <div class="tab-pane fade" :class="{'show active' : isActive('general')}" v-if="isActive('general')">
            <settingsGeneral :tablabel="tabs.general.label"></settingsGeneral>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('notifications')}" v-if="isActive('notifications')">
            <settingsNotifications :tablabel="tabs.notifications.label"></settingsNotifications>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('reminders')}" v-if="isActive('reminders')">
            <settingsReminders :tablabel="tabs.reminders.label"></settingsReminders>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('sync')}" v-if="isActive('sync')">
            <settingsSync :tablabel="tabs.advanced.label"></settingsSync>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('advanced')}" v-if="isActive('advanced')">
            <settingsAdvanced :tablabel="tabs.advanced.label"></settingsAdvanced>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('addonstab')}" v-if="isActive('addonstab')">
            <settingsAddons ></settingsAddons>
        </div>
    </div>
    
  </div>
</template>

<script>

import abstractView from './Abstract'
import settingsGeneral from './SettingsGeneral'
import settingsNotifications from './SettingsNotifications'
import settingsReminders from './SettingsReminders'
import SettingsSync from './SettingsSync'
import settingsAdvanced from './SettingsAdvanced'
import settingsAddons from './SettingsAddons'

export default {
  extends: abstractView,
    data: () => ({
        service: null,
        activeTab: 'general',
        tabs:{
            general:{
                label: 'General'
            },
            reminders:{
                label: 'Confirmations & Reminders'
            },
            notifications:{
                label: 'Admin notifications'
            },
            sync:{
                label: 'Sync'
            },
            advanced:{
                label: 'Advanced'
            },
        },
    }),

    components: {
      settingsGeneral,
      settingsNotifications,
      settingsReminders,
      settingsAdvanced,
      SettingsSync,
      settingsAddons
    },


    created() {
         if(window.wappointmentAdmin.addons !== undefined && this.addonsWithSettings().length > 0) {
            this.tabs['addonstab'] = { label: 'Addons'}
        }
        this.activeTab = this.$route.name
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
            this.activeTab = selectedTab
            this.$router.push({name: selectedTab})
        },       
    }  
}
</script>