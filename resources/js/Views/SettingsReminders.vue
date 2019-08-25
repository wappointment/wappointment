<template>
  <div>
      <div class="reduced">
          <ErrorList :errors="errorMessages" ></ErrorList>

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
              <div class="mt-2"><button class="btn btn-secondary" @click="addReminder">Add reminder</button></div>
            
          </div>

          <div v-else >
            <div class="card" v-if="!loading"e>
              <p class="h6 m-0">You don't have any reminders <button class="btn btn-secondary btn-sm" @click="addReminder">Add one</button></p>
            </div>
             
          </div>
          <div class="mt-4" v-if="remindersLoaded">
            <hr>
            <MailConfig :status="mail_status" @status="mailConfigured"></MailConfig>
          </div>
      </div>
      
      <!-- Modal Component -->
      <WapModal v-if="showModal" :show="showModal" @hide="onHidden">
        <h4 slot="title" class="modal-title">{{titleModal}}</h4>
        <ErrorList :errors="errorMessages" className="popupErrors"></ErrorList>
        <form v-if="showModal"  autocomplete="off" >
          <div class="form-group col-md-12 field-input text-info">
            <span class="dashicons dashicons-clock"></span> {{ getLabelPopup }}
          </div>
          
          <vue-form-generator ref="vfgen" :schema="schema" :model="model" :options="formOptions" @validated="onValidated" ></vue-form-generator>
        </form>
        <div class="d-flex align-items-center">
          <button class="btn btn-primary" @click="save">Save</button>
          <div class="ml-2 d-flex align-items-center" v-if="mail_status">
            <button class="btn btn-secondary mr-2" @click="sendPreview">Send Preview</button>
            <div>
              <div v-if="showRecipient">
                <input id="preveiwemail" class="form-control mr-2" type="text" v-model="recipient" :placeholder="recipient" >
              </div>
              <a href="javascript:;" v-else title="Edit" class="text-muted" @mouseover="showRecipient=true" @click="showRecipient=!showRecipient">to {{ recipient }}</a>
            </div>
          </div>
          
          <div class="bg-danger p-2 text-white rounded small ml-2" v-else> <span class="dashicons dashicons-email"></span> No emails will be sent without configuring the sending method first</div>
        </div>
       </WapModal>
    
  </div>
</template>

<script>
import ServiceReminder from '../Services/Reminder' 
import MailConfig from '../Components/MailConfig'
import Scroll from '../Modules/Scroll'
import Vue from '../appVue'
import abstractView from './Abstract'
import Validation from '../Modules/Validation'
import Checkbox from '../Fields/Checkbox'
const FieldBsRadios = () => import(/* webpackChunkName: "group-fields" */ '../Plugins/vue-form-fields/field-bsRadios')
const FieldBsCheckList = () => import(/* webpackChunkName: "group-fields" */ '../Plugins/vue-form-fields/field-bsChecklist')
const FieldBsTextEditor = () => import(/* webpackChunkName: "group-fields" */ '../Plugins/vue-form-fields/field-bsTextEditor')
const fieldBSSlider = () => import(/* webpackChunkName: "group-fields" */ '../Plugins/vue-form-fields/field-bsSlider')
const fieldBSPassword = () => import(/* webpackChunkName: "group-fields" */ '../Plugins/vue-form-fields/field-bsPassword')

window.noUiSlider = require('nouislider');
import "nouislider/distribute/nouislider.css";
Vue.component("field-bsslider", fieldBSSlider);
Vue.component("field-bsradios", FieldBsRadios);
Vue.component("field-bschecklist", FieldBsCheckList);
Vue.component("field-bstexteditor", FieldBsTextEditor);
Vue.component("field-bspassword", fieldBSPassword);


export default {
  extends: abstractView,
  mixins: [Scroll, Validation],
  components: { MailConfig, Checkbox },
  data() {
      return {
        parentLoad: false,
        showModal: false,
        remindersLoaded: false,
        multiple_service_type: false,
        reminders: [],
        mail_status: false,
        allow_rescheduling: false,
        allow_cancellation: false,
        reschedule_link: '',
        allow_cancellation: '',
        save_appointment_text_link: '',
        serviceReminder: null,
        showRecipient:false,
        errors: null,
        labels: null,
        recipient: '',
        emptyModel: null,
        model: null,
          formOptions: {
            validateAfterLoad: false,
            validateAfterChanged: false,
          },
          loading: false,
      }
  },

  created(){

    this.serviceReminder = this.$vueService(new ServiceReminder)

    //console.log('settings reminder created')
    this.refreshInitValue()
  },
  computed: {
    schema(){

      let originalCopy = this.originalSchema()

      let fieldsSchemaBookingEvent = _.map(originalCopy.fields, function(o) {
        if (o.model===undefined || o.model.indexOf("options.when_") === -1) return o;
      });

      return this.isAppointmentStartEvent(this.model) ? {fields: _.compact(originalCopy.fields)} : {fields: _.compact(fieldsSchemaBookingEvent)};
    },

    titleModal(){
      if(this.model.id > 0){
        if(!this.isAppointmentStartEvent(this.model)){
          return 'Edit Confirmation'
        }else{
          return 'Edit Reminder'
        }
      }else{
        return 'Create Reminder'
      } 
    },

    remindersAreLoaded(){
      return this.remindersLoaded && this.reminders.length > 0
    },

    getLabelPopup(){
      return this.getReminderLabel(this.model)
    }

  },
  methods: {
    mailConfigured(){
      this.mail_status = true
    },
    getReminderLabel(reminder){
      let labelString = '' 

      if(this.isAppointmentStartEvent(reminder)){
        labelString += 'Sent before appointment takes place.(' + this.getDelay(reminder) + ')'
      }else if(this.isAppointmentBookedEvent(reminder)){
        labelString += 'Sent after appointment has been confirmed.'
      }else if(this.isAppointmentRescheduleEvent(reminder)){
        labelString += 'Sent after appointment has been rescheduled.'
      }else if(this.isAppointmentCancelEvent(reminder)){
        labelString += 'Sent after appointment has been cancelled.'
      }else if(this.isAppointmentPendingEvent(reminder)){
        labelString += 'Sent after appointment has been booked when admin approval is required.'
      }
      return labelString
    },
    
    sendPreview() {
        this.showModal = false
        this.request(this.sendPreviewReminderRequest, this.sent, this.failedSend)
    },
    async sendPreviewReminderRequest() {
        return await this.serviceReminder.call('sendPreview', {recipient: this.recipient, reminder: this.model.id})
    },

    parseSubject(subject){
      return subject.length > 20 ? subject.substring(0,20) + ' ...':subject
    },
    isPublished(reminder){
      return reminder.published === true
    },
    isUnlocked(reminder) {
      return reminder.locked === false
    },
    
    loaded(d) {
        this.reminders = d.data.reminders
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
    },
    refreshInitValue() {
      this.remindersLoaded = false
      this.loading = true
      this.resetModel();
      //console.log('refreshInitvalue start 2 ')
      if(this.remindersLoaded === false) this.request(this.initValueRequest, undefined, this.loaded)
    },
    async initValueRequest() {
         return await this.serviceReminder.call('get')
    },
    toggledPublish(reminder){
      this.request(this.togglePublishRequest, reminder, this.refreshInitValue)
    },
    async togglePublishRequest(reminder) {
        return await this.serviceReminder.call('toggle', reminder)
    },
    closeAndRefresh(){
      this.showModal = false
      this.refreshInitValue()
    },
    save() {
        this.$refs.vfgen.validate();
        if(!this.hasErrors()){
          this.request(this.saveReminderRequest,  this.saved, this.closeAndRefresh)
        } 
    },
    async saveReminderRequest() {
        if(this.model.id > 0) return await this.serviceReminder.call('patch', this.model)
        return await this.serviceReminder.call('save', this.model)
    },
    saved() {
        this.$emit('saved')
        this.refreshInitValue()
        this.showModal=false
    },
    failedSave(){
      this.scrollToElement('.modal-title')
    },
    addReminder() {
      this.resetModel()
      this.modalOn()
    },
    modalOn(){
      this.showModal=true
    },
    resetModel() {
      this.model = null
      this.model = this.emptyModel
    },
    deleted() {
      this.refreshInitValue()
    },
    goToAddReminder() {
      this.$router.push({ name: "addreminder" })
    },
    
    deleteReminder(reminder_id) {
      this.$WapModal().confirm({
        title: 'Do you really want to delete this reminder? ',
      }).then((result) => {
          if(result === true){
              this.request(this.deleteReminderRequest,  reminder_id, this.deleted)
          } 
      })      
    },

    editReminder(reminder) {
      this.model = reminder
      if(this.model.options === null) {
        this.model.options = this.emptyModel.options
      }
      this.modalOn()
    },
    async deleteReminderRequest(reminder_id) {
        return await this.serviceReminder.call('delete',  {id: reminder_id})
    },
    onHidden() {
      this.resetModel()
      this.showModal=false
      //this.refreshInitValue()
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
    getDelay(reminder) {
      if(reminder.options['when_number']!== undefined && reminder.options['when_number'] > 0){
         return 'sent ' + reminder.options['when_number'] + ' ' + this.convertUnit(reminder.options['when_unit']) + ' before'
      }
      return 'sent immediately'
    },
    convertUnit(unit){
     if(unit ==1)  return 'minute(s)'
     else if(unit == 2) return 'hour(s)'
     else if(unit == 3) return 'day(s)'
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
