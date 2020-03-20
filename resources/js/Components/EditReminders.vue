<template>
    <div class="reduced" v-if="viewData!== null">
        <FormGenerator ref="mcformgenerator" :schema="schema" :data="model" 
        @submit="save" @back="$emit('back')" :buttons="false" @changedValue="changedValue" :key="formKey" 
        labelButton="Save" @ready="readytosubmit">
        </FormGenerator>

        <div class="d-flex align-items-center">
          <button class="btn btn-primary" :class="{disabled: !canSend}" 
                    :disabled="!canSend" @click="$refs.mcformgenerator.submitTrigger()">Save</button>
          
          <div class="ml-2 d-flex align-items-center" v-if="viewData.mail_status">
            <button class="btn btn-secondary mr-2" @click="sendPreview">Send Preview</button>
            <div>
              <div v-if="showRecipient">
                <input id="preveiwemail" class="form-control mr-2" type="text" v-model="recipient" :placeholder="recipient" >
              </div>
              <a href="javascript:;" v-else title="Edit" class="text-muted" @mouseover="showRecipient=true" @click="showRecipient=!showRecipient">to {{ recipient }}</a>
            </div>
          </div>
          
          <div class="bg-danger p-2 text-white rounded small ml-2" v-else> 
            <span class="dashicons dashicons-email"></span>
            <span>No emails will be sent without configuring the sending method first</span>  
          </div>
        </div>
    </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import FormGenerator from '../Form/FormGenerator'
import isReminder from '../Mixins/isReminder'
export default {
    extends: abstractView,
    mixins: [isReminder],
    components:{FormGenerator},
    props:['reminder', 'passedViewData'],
    data() {
      return {
          other: null,
          showRecipient:false,
          formready: false,
          recipient: '',
          errorMessages: [],

          formKey: 'formmailconfig',
          schema: [
              {
                  type: 'label',
              },
              {
                  type: 'label',
                  model: 'event',
                  conditions: [
                  { model:'event', values: ["9999"] }
                ],
              },  
              {
                type: 'row',
                class: 'd-flex flex-wrap flex-sm-nowrap align-items-center',
                classEach: 'mr-2',
                conditions: [
                  { model:'event', values: [1] }
                ],
                fields: [
                  {
                      type: 'duration',
                      label: 'Duration',
                      model: 'options.when_number',
                      cast: String,
                      class: 'w-100',
                      default: 1,
                      min: 1,
                      max: 60,
                      step: 1,
                      int: true,
                      validation: ['required'],
                  },
                  {
                    type: "select",
                    labelDefault: 'Select unit',
                    model: "options.when_unit",
                    default:1,
                    elements: [
                      { id: 1, name: "minutes" },
                      { id: 2, name: "hours" },
                      { id: 3, name: "days" } 
                    ],
                    labelKey: 'name',
                    validation: ['required'],
                },

                ]
              },
              {
                  type: "input",
                  label: "Subject",
                  model: "subject",
                  required: true,
                  cast: String,
                  validation: ['required'],
              },
              {
                  label: "Email header image",
                  type: 'imageselect',
                  model: "email_logo",
                  size: 'full',
                  preview:{
                    width:'',
                  },
                  required: true,
              },
              {
                  type: "tiptap",
                  model: "options.body",
                  required: true,
                  validation: ['required'],
                  multiple_service_type: this.passedViewData.multiple_service_type,
                  mail_status: this.passedViewData.mail_status,
                  allow_rescheduling: this.passedViewData.allow_rescheduling,
                  allow_cancellation: this.passedViewData.allow_cancellation,
                  reschedule_link: this.passedViewData.reschedule_link,
                  cancellation_link: this.passedViewData.cancellation_link,
                  save_appointment_text_link: this.passedViewData.save_appointment_text_link,
              },
              
          ]
          
      }
    },
    created(){
      this.recipient = this.passedViewData.recipient
      this.model = Object.assign({},this.reminder)
      this.viewData = Object.assign({},this.passedViewData)
      this.schema[0].label = this.getReminderLabel(this.model)
    },
    computed: {
        canSend(){
            return this.formready
        },

    },

    methods: {
        sendPreview() {
            this.request(this.sendPreviewReminderRequest, this.sent, this.failedSend)
        },
        async sendPreviewReminderRequest() {
            return await this.serviceReminder.call('sendPreview', {recipient: this.recipient, reminder: this.model.id})
        },
        changedValue(newReminder){
            this.model = newReminder
            this.schema[0].label = this.getReminderLabel(this.model)
        },
        readytosubmit(ready){
            this.formready = ready
        },

        save() {
            this.request(this.saveReminderRequest, undefined,undefined,false,  this.saved)
        },
        saved(e){
          this.successRequest(e)
        },
        async saveReminderRequest() {
            if(this.model.id > 0) return await this.serviceReminder.call('patch', this.model)
            return await this.serviceReminder.call('save', this.model)
        },
        
    },
}
</script>
