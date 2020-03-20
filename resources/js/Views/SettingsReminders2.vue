<template>
  <div v-if="reminders!== undefined && reminders.length > 0">
    
      <BreadCrumbs v-if="crumbs.length>0" :crumbs="crumbs" @click="goTo"/>
      <component v-if="currentView !== false" :is="currentView" :key="subCompKey" 
      :subview="subview" v-bind="dynamicProps" :passedViewData="viewData" @updateCrumb="updateCrumb"></component>
      
      <div class="reduced" v-else>
          <div v-if="remindersAreLoaded" class="mt-2">
              <div v-for="reminder in reminders" class="p-2 lrow" :class="{'unpublished' : !isPublished(reminder)}">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center">
                    <Checkbox :element="reminder" @changed="toggledPublish"></Checkbox>
                    <div>
                      <div>{{ reminder.subject }}</div>
                      <div class="text-muted small">{{ getReminderLabel(reminder) }}</div>
                    </div>
                  </div>
                  <div>
                    <button class="btn btn-xs" @click="editReminder(reminder)"><span class="dashicons dashicons-edit"></span></button>
                    <button v-if="isUnlocked(reminder)" class="btn btn-xs" @click="deleteReminder(reminder.id)"><span class="dashicons dashicons-trash"></span></button>
                    <button v-else class="btn btn-xs disabled" disabled aria-disabled="true" title="Impossible to delete this message, you can unpublish it though"><span class="dashicons dashicons-trash"></span></button>
                  </div>
                </div>
                
              </div>
              <div class="mt-2"><button class="btn btn-outline-primary my-2" @click="addReminder">Add reminder</button></div>
            
          </div>
          <div v-else >
            <div class="card" v-if="!loading"e>
              <p class="h6 m-0">You don't have any reminders <button class="btn btn-secondary btn-sm" @click="addReminder">Add one</button></p>
            </div>
          </div>

          <div class="mt-4">
            <hr/>
            <NotificationEmail :status="mail_status" @configureEmail="goToMailConfig"/>
          </div>
      </div>
      
  </div>
</template>

<script>

import hasBreadcrumbs from '../Mixins/hasBreadcrumbs'
import isReminder from '../Mixins/isReminder'
import NotificationEmail from '../Notification/Email'
import EditReminders from '../Components/EditReminders' 
import MailConfig from '../Components/MailConfig'
import Scroll from '../Modules/Scroll'
import Checkbox from '../Fields/Checkbox'
import abstractView from './Abstract'


export default {
  extends: abstractView,
  mixins: [Scroll, hasBreadcrumbs, isReminder], 
  components: { MailConfig, NotificationEmail, EditReminders,Checkbox},
  data() {
      return {
        multiple_service_type: false,
        showModal: false,
        errors: null,
      }
  },

 props:['tablabel'],
  created(){
    this.mainCrumbLabel = this.tablabel

    //console.log('settings reminder created')
    this.refreshInitValue()
  },
  computed: {

    remindersAreLoaded(){
      return this.remindersLoaded && this.reminders.length > 0
    },

  },
  methods: {
    goToMain() {
      this.currentView = false
      this.crumbs = []
      this.refreshInitValue()
    },
    goToMailConfig() {
      this.setCrumb('MailConfig', 'Mail Config', 'goToMailConfig')
    },
    goToEditReminder(props = {}) {
      this.setCrumb('EditReminders', 'Edit Reminder', 'goToEditReminder', props)
    },
    mailConfigured(){
      this.mail_status = true
    },
    
    isPublished(reminder){
      return reminder.published === true
    },
    isUnlocked(reminder) {
      return reminder.locked === false
    },
    
    loaded(d) {
        this.reminders = d.data.reminders
        this.viewData = {}
        for (const key in d.data) {
          if (d.data.hasOwnProperty(key)) {
            if(key!== 'reminders'){
              this.viewData[key] = d.data[key]
            }
          }
        }
        this.mail_status = d.data.mail_status
        this.allow_rescheduling = d.data.allow_rescheduling
        this.allow_cancellation = d.data.allow_cancellation
        this.reschedule_link = d.data.reschedule_link
        this.cancellation_link = d.data.cancellation_link
        this.save_appointment_text_link = d.data.save_appointment_text_link
        this.recipient = d.data.recipient
        this.labels = d.data.labels
        this.multiple_service_type = d.data.multiple_service_type
        this.remindersLoaded = true
        this.loading = false
        this.emptyModel = d.data.defaultReminder
        this.emptyModel.email_logo = this.viewData.email_logo
    },
    refreshInitValue() {
      this.remindersLoaded = false
      this.loading = true
      this.resetModel();
      //console.log('refreshInitvalue start 2 ')
      if(this.remindersLoaded === false) this.request(this.initValueRequest, undefined,undefined,false,  this.loaded)
    },
    async initValueRequest() {
         return await this.serviceReminder.call('get')
    },
    toggledPublish(reminder){
      this.request(this.togglePublishRequest, reminder,undefined,false,  this.refreshInitValue)
    },
    async togglePublishRequest(reminder) {
        return await this.serviceReminder.call('toggle', reminder)
    },
    
    resetModel() {
      this.model = null
      this.model = this.emptyModel
    },
    deleted() {
      this.refreshInitValue()
    },
    
    deleteReminder(reminder_id) {
      this.$WapModal().confirm({
        title: 'Do you really want to delete this reminder? ',
      }).then((result) => {
          if(result === true){
              this.request(this.deleteReminderRequest,  reminder_id,undefined,false,  this.deleted)
          } 
      })      
    },

    addReminder() {
      this.goToEditReminder({reminder: this.emptyModel})
    },

    editReminder(reminder) {
      
      this.model = reminder
      if(this.model.options === null) {
        this.model.options = this.emptyModel.options
      }
      this.model.email_logo = this.viewData.email_logo
      return this.goToEditReminder({reminder: this.model})
    },
    async deleteReminderRequest(reminder_id) {
        return await this.serviceReminder.call('delete',  {id: reminder_id})
    },
    onHidden() {
      this.resetModel()
    },
    hasErrors() {
      return (this.errors!== null && this.errors.length > 0)
    },
    onValidated(isValid, errors) {
      this.errors = errors
    },
    isEmail(reminder) {
      return reminder.type==1
    },
    isAppointmentStartEvent(reminder) {
      return reminder.event !== undefined && reminder.event==1
    },
    isAppointmentBookedEvent(reminder) {
      return reminder.event !== undefined && reminder.event==2
    },
    isAppointmentRescheduleEvent(reminder) {
      return reminder.event !== undefined && reminder.event==3
    },
    isAppointmentCancelEvent(reminder) {
      return reminder.event !== undefined && reminder.event==4
    },
    isAppointmentPendingEvent(reminder) {
      return reminder.event !== undefined && reminder.event==5
    },

    getEvent(event) {
      if(this.labels.events[event]!== undefined) return this.labels.events[event]
      return 'unknown event'
    },
    
    originalSchema(){
      return {
            fields: [
              
              {
                type: "bsslider",
                model: "options.when_number",
                required: true,
                default: 1,
                min: 1,
                max: 60,
                step: 1,
                int: true,
                unit: '',
                styleClasses: 'col-md-9'
              },
              
              {
                type: "select",
                model: "options.when_unit",
                required: true,
                values: function() {
                  return [
                    { id: 1, name: "minutes" },
                    { id: 2, name: "hours" },
                    { id: 3, name: "days" },
                  ]
                },
                hideNoneSelectedText: true,
                styleClasses: 'col-md-3'
              },
              
              {
                type: "input",
                inputType: "text",
                model: "subject",
                placeholder: "e.g.: Subject",
                required: true,
                validator: ['string'],
                styleClasses: 'col-md-12'
              },
              {
                type: "bstexteditor",
                model: "options.body",
                placeholder: "e.g.: Content",
                multiple_service_type: this.multiple_service_type,
                required: true,
                styleClasses: 'col-md-12',
                mail_status: this.mail_status,
                allow_rescheduling: this.allow_rescheduling,
                allow_cancellation: this.allow_cancellation,
                reschedule_link: this.reschedule_link,
                cancellation_link: this.cancellation_link,
                save_appointment_text_link: this.save_appointment_text_link,
              }
              
            ]
            
          };
    },
  }  
}
</script>
<style>
.btn-xs.disabled {
  background: none;
  cursor: not-allowed;
}
.titleHover .hoverShow{
  display:none;
}
.titleHover:hover .hoverHide{
  visibility:hidden;
}
.titleHover:hover .hoverShow{
  display:block;
  position:absolute;
  z-index: 2;
  background-color: #fff;
  border-bottom: 1px solid #ccc;
  padding-right: .5rem;
}

.lrow:hover{
  background-color:#f7f7f7;
}
.unpublished {
  color: #ccc;
}
</style>
