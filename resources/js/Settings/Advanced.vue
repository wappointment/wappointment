<template>
  <div v-if="dataLoaded">

    <BreadCrumbs v-if="crumbs.length>0" :crumbs="crumbs" @click="goTo"/>
    <component v-if="currentView !== false" :is="currentView" :key="subCompKey" 
    :subview="subview" v-bind="dynamicProps" @updateCrumb="updateCrumb"></component>
    
    <div class="reduced" v-else>
        <div class="card p-2 px-3">
          <div class="h5">Scheduling preferences</div>
          <hr/>
          <div>
            
            <div class="row my-2">
              <label for="approval-mode" class="col-sm-3">Approval mode</label>
              <div class="col-sm-4">
                <select class="form-control" id="approval-mode" @change="changedVD('approval_mode')" v-model="viewData.approval_mode">
                  <option value="1">Automatic</option>
                  <option value="2">Manual</option>
                </select>
              </div>
            </div>

            <div class="row mb-2">
                  <label for="date-format" class="col-sm-3 dateformatlabel"> Date format</label>

                  <div class="col-sm-9">
                    <div class="d-flex">
                      <div class="input-group input-group-sm">
                        <div class="input-group-prepend" :class="{show: toggled('date_format')}">
                          <button class="btn btn-secondary dropdown-toggle" type="button" @click="toggle('date_format')">Date</button>
                          <div class="dropdown-menu" :class="{show: toggled('date_format')}">
                            <span v-for="date in date_formats" class="dropdown-item" @click="changeDDP(date)">{{ date }} ({{ day_example(date) }})</span>
                          </div>
                        </div>
                        <input id="date-format" class="form-control" v-model="viewData.date_format" @change="changedReload('date_format')" size="5" type="text">
                      </div>

                      <div class="input-group  input-group-sm">
                        <input id="date-time-union" class="form-control" v-model="viewData.date_time_union" @change="changedReload('date_time_union')" size="3" type="text">
                      </div>

                      <div class="input-group  input-group-sm">
                        <div class="input-group-prepend" :class="{show: toggled('time_format')}">
                          <button class="btn btn-secondary dropdown-toggle" type="button" @click="toggle('time_format')">Time</button>
                          <div class="dropdown-menu" :class="{show: toggled('time_format')}">
                            <span v-for="timef in time_formats" class="dropdown-item" @click="changeDDP(timef, 'time_format')">{{ timef }} ({{ time_example(timef) }})</span>
                          </div>
                        </div>
                        <input id="time-format" class="form-control" v-model="viewData.time_format" @change="changedReload('time_format')" size="5" type="text">
                      </div>
                    </div>
                    <div class="date-preview"> <span class="font-weight-bold small">{{ viewData.today_formatted }}</span> </div>
                  </div>
            </div>
            <div class="d-flex mb-2">
            <div>
              <label for="week-starts-on" class="m-0">Week starts on</label>
              <div class="small text-muted">In Admin Calendar view</div>
            </div>
            <div class="ml-4">
                <weekDays id="week-starts-on" classN="form-control" :selected="viewData.week_starts_on" @changed="changedDayStart"></weekDays>
            </div>
          </div> 
            <div class="mb-2">
              <label class="form-check-label" for="hrs-before-allowed">
                <div class="d-flex align-items-center">Appointments can be booked up to 
                  <div class="input-group-sm mx-2">
                    <input id="hrs-before-allowed" v-model="viewData.hours_before_booking_allowed" 
                    @change="changedVD('hours_before_booking_allowed')" class="form-control min-field" size="2" type="text">
                  </div> hrs before it starts</div>
              </label>
            </div>
            <div class="mb-2">
              
              <label class="form-check-label" for="allow-cancel">
                <div class="d-flex align-items-center">
                  <input type="checkbox" v-model="viewData.allow_cancellation" id="allow-cancel" @change="changedVD('allow_cancellation')">
                    Allow clients to cancel <a v-if="viewData.allow_cancellation" href="javascript:;" class="ml-2" @click="EditTextPage">edit page's text</a>
                </div>
              </label>
              <div class="d-flex align-items-center small text-muted ml-2" v-if="viewData.allow_cancellation">Let client cancel up to 
                    <div class="input-group-sm mx-2">
                      <input v-model="viewData.hours_before_cancellation_allowed"
                      @change="changedVD('hours_before_cancellation_allowed')"  class="form-control min-field" size="2" type="text">
                    </div> hrs before appointment</div>
            </div>
            <div class="mb-2">
              
              <label class="form-check-label" for="allow-reschedule">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.allow_rescheduling" id="allow-reschedule" @change="changedVD('allow_rescheduling')">
                    Allow clients to reschedule <a v-if="viewData.allow_rescheduling" href="javascript:;" class="ml-2" @click="EditTextPage">edit page's text</a>
                  </div>
              </label>
              <div class="d-flex align-items-center small text-muted ml-2" v-if="viewData.allow_rescheduling">Let client reschedule up to 
                    <div class="input-group-sm mx-2">
                      <input v-model="viewData.hours_before_rescheduling_allowed" 
                      @change="changedVD('hours_before_rescheduling_allowed')" class="form-control min-field" size="2" type="text">
                </div> hrs before appointment</div>
            </div>

            <div class="d-flex mb-2">
                  
                  <label class="form-check-label w-100" for="buffer_time">
                  <div class="d-flex align-items-center">
                    <div class="min-label">Buffer time</div>
                    <FormFieldDuration v-model="viewData.buffer_time" model="buffer_time" @change="changed"/>
                  </div>
                  <div class="small text-muted">Time(in minutes) reserved to prepare your next appointment</div>
                  </label>

              </div>
              <div class="mt-3">
                <a v-if="viewData.front_page_type == 'page'" :href="'post.php?post='+viewData.front_page_id+'&action=edit'" target="_blank">
                  Edit Reschedule/Cancel page
                </a>
                <button v-else class="btn btn-secondary btn-sm" @click="updatePage" data-tt="Only if you don't like the default page template for cancellation and rescheduling">
                   Make Reschedule/Cancel page editable
                </button>
              </div>
          </div>
        </div>

        <div class="card p-2 px-3">
          <div class="h5">Notifications</div>
          <hr/>
          <div class="d-flex align-items-center">
                <InputValueCards ph="Email notifications are sent to" v-model="viewData.email_notifications" @changed="changedVD('email_notifications')" /> 
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
              Rescheduled Appointments 
              </label>
            </div>
            <div class="mt-4">
              <hr/>
              <NotificationEmail :status="viewData.mail_status" @configureEmail="goToMailConfig"/>
            </div>

            
          </div>
          
      <div class="mt-3">
          <button class="btn btn-danger btn-sm" @click="startResetConfirm">
            <span class="dashicons dashicons-image-rotate"></span> Uninstall
          </button>
        </div>
    </div>


  </div>
</template>

<script>
import FormFieldDuration from '../Form/FormFieldDuration'
import abstractView from '../Views/Abstract'
import momenttz from '../appMoment'
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import hasBreadcrumbs from '../Mixins/hasBreadcrumbs'
import EditCancelPage from '../Views/Subpages/EditCancelPage'
import weekDays from "../Components/weekDays"
import dayTime from "../Components/dayTime"
import Checkbox from '../Fields/Checkbox'
import LabelMaterial from '../Fields/LabelMaterial'
import MailConfig from '../Components/MailConfig'
import NotificationEmail from '../Notification/Email'
import InputValueCards from '../Fields/InputValueCards'


export default {
  extends: abstractView,
  props:['tablabel'],

  components: {FormFieldDuration, EditCancelPage, weekDays,dayTime,
  Checkbox,
      LabelMaterial,
      MailConfig,
      NotificationEmail,
      InputValueCards},
  mixins: [ hasBreadcrumbs],
  data() {
    return {
      viewName: 'settingsadvanced',
      isToggled: {
        date_format : false,
        time_format : false,
      },
      date_formats : [
        'F j, Y',
        'Y-m-d',
        'm/d/Y',
        'd/m/Y'
      ],
      time_formats : [
        'g:i a',
        'g:i A',
        'H:i',
        'H\\hi',
      ],
    };
  },
  created(){
    this.mainCrumbLabel = this.tablabel
  },
  methods: {
    EditTextPage(){
      this.setCrumb('EditCancelPage', 'Edit Cancel/reschedule page', 'EditTextPage')
    },
    day_example(dformat){
      return momenttz().tz(this.viewData.timezone).format( convertDateFormatPHPtoMoment(dformat) )
    },

    time_example(tformat){
      return momenttz().tz(this.viewData.timezone).format( convertDateFormatPHPtoMoment(tformat) )
    },

    changeDDP(date, key = 'date_format'){
      this.viewData[key] = date
      this.toggle(key)
      //this.changedVD(key)
      this.changedReload(key)
    },

    changedReload(key){
      this.changedVD(key)
      this.refreshInitValue()
    },

    changedDayStart(value){
      this.viewData['week_starts_on'] = value
      this.changedVD('week_starts_on')
    },
    
    toggled(element){
      return this.isToggled[element]
    },

    toggle(element){
      this.isToggled[element] = !this.isToggled[element]
    },
    async resetInstallation() {
        return await this.service.call('freshinstall')
    },
    updatePage(){
      this.$WapModal().confirm({
          title: 'Are you sure you need this?',
          content: 'This is useful only to 1% of the users<br/>Basically instead of using a <strong>CPT</strong>(Custom Post Type) for Reschedule, Cancel and View page of your appointment you will use a standard Page'
        }).then((result) => {
          if(result === true){
              this.request(this.updatePageRequest,  undefined, undefined,false, this.updateViewData)
          } 
        })
      
    },
    updateViewData(response){
      this.viewData = response.data
    },
    async updatePageRequest() {
        return await this.service.call('updatepage')
    },

    startResetConfirm() {
        this.$WapModal().confirm({
          title: 'Do you really want to uninstall Wappointment?',
          content: 'All your data(appointments, settings, etc...) will be lost'
        }).then((result) => {
          if(result === true){
              this.request(this.resetInstallation,  undefined, undefined,false, this.redirectReset)
          } 
        })
    },

    disconnectCalendar(calendar_id){
      this.request(this.disconnectCalendarRequest, {calendar_id: calendar_id}, null ,this.disconnectCalendarSuccess, this.disconnectCalendarSuccess,this.disconnectCalendarSuccess)
    },
    async disconnectCalendarRequest(params) {
        return await this.serviceSettingStaff.call('disconnectCal', params) 
    },

    disconnectCalendarSuccess(response){
        this.viewData.calendar_url = response.data.calendar_url
        this.viewData.calendar_logs = response.data.calendar_logs
        this.savedSync()
    },

    savedSync(){
      this.hideModal()
      this.refreshInitValue()
    },
    changedVD(key){
      this.changed(this.viewData[key], key)
    },
    changed(value, key) {
      this.settingSave(key, value)
    },
    redirectReset(){
         this.$WapModal()
            .request(this.sleep(4000))
          window.location = window.apiWappointment.base_admin + '?page=wappointment_calendar'
    },

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
       this.settingSave(key, this.viewData[key])
    },

  }
};
</script>
<style>
.min-label{
  min-width : 20%;
}
.btn-danger{
  color: #fff;
}
[data-active-page='advanced'] .card{
  max-width: none;
}

</style>