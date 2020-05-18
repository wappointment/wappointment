<template>
    <div v-if="dataLoaded">
      <BreadCrumbs v-if="crumbs.length>0" :crumbs="crumbs" @click="goTo"/>
      <component v-if="currentView !== false" :is="currentView" :key="subCompKey" 
      :subview="subview" v-bind="dynamicProps" @updateCrumb="updateCrumb"></component>
      
      <div class="reduced" v-else>
          <div class="my-3">
            <div class="d-flex align-items-center">
                <LabelMaterial>
                  <input class="form-control" id="email-notifications" v-model="viewData.email_notifications" 
                  placeholder="Email notifications are sent to" type="email" @change="changed('email_notifications')">
                </LabelMaterial>
            </div>
            <div class="d-flex align-items-center mt-2">
              <Checkbox :value="viewData.weekly_summary"  @changed="changedCheck('weekly_summary')"></Checkbox>
              <label class="form-check-label">
              Weekly summary <span v-if="viewData.weekly_summary">every 
                <weekDays id="weekly-summary"  :selected="viewData.weekly_summary_day" @changed="changedDay"></weekDays> at 
                <dayTime :selected="viewData.weekly_summary_time" @changed="changedTime" :timeFormat="viewData.time_format"></dayTime> 
                <small class="text-muted">{{ viewData.timezone }}</small></span>
              </label>
            </div>
            <div class="d-flex align-items-center mt-2">
              <Checkbox :value="viewData.daily_summary"  @changed="changedCheck('daily_summary')"></Checkbox>
              <label class="form-check-label" >
              Daily summary <span v-if="viewData.daily_summary"> at 
                <dayTime :selected="viewData.daily_summary_time" :timeFormat="viewData.time_format" @changed="changedDayTime"></dayTime>
                <small class="text-muted">{{ viewData.timezone }}</small></span>
              </label>
            </div>
            <div class="d-flex align-items-center mt-2">
              <Checkbox :value="viewData.notify_new_appointments"  @changed="changedCheck('notify_new_appointments')"></Checkbox>
              <label class="form-check-label">
              New Appointments 
              </label>
            </div>
            <div class="d-flex align-items-center mt-2">
              <Checkbox :value="viewData.notify_canceled_appointments"  @changed="changedCheck('notify_canceled_appointments')"></Checkbox>
              <label class="form-check-label">
              Cancelled Appointments 
              </label>
            </div>
            <div class="d-flex align-items-center mt-2">
              <Checkbox :value="viewData.notify_rescheduled_appointments"  @changed="changedCheck('notify_rescheduled_appointments')"></Checkbox>
              <label class="form-check-label">
              Reschedulled Appointments 
              </label>
            </div>
          </div>
          <div class="mt-4">
            <hr/>
            <NotificationEmail :status="viewData.mail_status" @configureEmail="goToMailConfig"/>
          </div>
      </div>
      
    </div>
</template>

<script>
import abstractView from './Abstract'
import weekDays from "../Components/weekDays"
import dayTime from "../Components/dayTime"
import hasBreadcrumbs from '../Mixins/hasBreadcrumbs'
import Checkbox from '../Fields/Checkbox'
import LabelMaterial from '../Fields/LabelMaterial'
import MailConfig from '../Components/MailConfig'
import NotificationEmail from '../Notification/Email'
export default {
  extends: abstractView,
  components: {
      weekDays,
      dayTime,
      Checkbox,
      LabelMaterial,
      MailConfig,
      NotificationEmail
    },
  mixins: [ hasBreadcrumbs],
  data() {
    return {
      viewName: 'settingsnotifications',
    };
  },

  props:['tablabel'],
  created(){
    this.mainCrumbLabel = this.tablabel
  },

  methods: {

    goToMailConfig() {
      this.setCrumb('MailConfig', 'Mail Config', 'goToMailConfig')
    },
    mailConfigured(){
      this.viewData.mail_status = true
    },
    changedDay(value){
      this.changedDDPEXT(value, 'weekly_summary_day')
    },
    changedTime(value){
      this.changedDDPEXT(value, 'weekly_summary_time')
    },
    changedDayTime(value){
      this.changedDDPEXT(value, 'daily_summary_time')
    },

    changedDDPEXT(value, key){
      this.viewData[key] = value
      this.changed(key)
    },
    changedCheck(key){
      this.viewData[key] = !this.viewData[key]
       this.settingSave(key, this.viewData[key]);
    },
    changed(key) {
      this.settingSave(key, this.viewData[key]);
    },

  }
};
</script>
