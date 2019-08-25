<template>
  <div class="container-fluid">
    <h1>Settings</h1>
    
    <ul class="nav nav-tabs row" id="myTab" role="tablist">
        <li v-for="(tab, key) in tabs" class="nav-item">
            <span class="nav-link" :class="{'active' : isActive(key)}" @click="changeTab(key)">{{ tab.label }}</span>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade" :class="{'show active' : isActive('general')}" v-if="isActive('general')">
            <settingsGeneral></settingsGeneral>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('notifications')}" v-if="isActive('notifications')">
            <settingsNotifications></settingsNotifications>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('reminders')}" v-if="isActive('reminders')">
            <settingsReminders></settingsReminders>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('advanced')}" v-if="isActive('advanced')">
            <settingsAdvanced></settingsAdvanced>
        </div>
        <div class="tab-pane fade" :class="{'show active' : isActive('addonstab')}" v-if="isActive('addonstab')">
            <settingsAddons></settingsAddons>
        </div>
    </div>
    
  </div>
</template>

<script>

import abstractView from './Abstract'
import settingsGeneral from './SettingsGeneral'
import settingsNotifications from './SettingsNotifications'
import settingsReminders from './SettingsReminders'
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
      settingsAddons
    },

    created() {
        if(this.tabs[this.$router.currentRoute.name] !== undefined) {
            this.activeTab = this.$router.currentRoute.name
        }
        if(window.addonsWappointment !== undefined && Object.keys(window.addonsWappointment).length > 0) {
            this.tabs['addonstab'] = { label: 'Addons'}
        }
    },

    methods: {
        isActive(key){
            return (key==this.activeTab)
        },
        changeTab(selectedTab){
            this.activeTab = selectedTab
            this.$router.push({name: selectedTab})
        },       
    }  
}
</script>
<style>
#myTab .nav-link {
    cursor:pointer;
}

.updated.error ol{
  margin-top: 1rem;
}
.card{
  max-width: none;

}
.card.cardb:hover  {
  background-color:#f8f8f8;
  cursor:pointer;
}
.card .hidden{
  display: none;
}
.card:hover .hidden{
  display: block;
}
</style>

