<template>
    <div class="reduced" v-if="viewData!== null">
        <WAPFormGenerator ref="mcformgenerator" :schema="schema" :data="model" :errors="errors"
        @submit="save" @back="$emit('back')" :buttons="false" @changedValue="changedValue" :key="formKey" 
         @ready="readytosubmit">
        </WAPFormGenerator>

        <div class="d-flex align-items-center">
          <button class="btn btn-primary" :class="{disabled: !canSend}" 
                    :disabled="!canSend" @click="$refs.mcformgenerator.submitTrigger()">{{ get_i18n('save', 'common') }}</button>
          
          <component v-if="model.id !== undefined" :is="'preview-'+typeLabel(model.type, viewData.labels.types)" 
           :recipient="recipient" :viewData="viewData" @sendPreview="sendPreview"></component>
        </div>
    </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import SendPreview from '../Components/SendPreview'
import isReminder from '../Mixins/isReminder'
import reminderTypeLabel from '../Mixins/reminderTypeLabel'
export default {
    extends: abstractView,
    mixins: [isReminder, reminderTypeLabel],
    props:['reminder', 'passedViewData'],
    components: window.wappointmentExtends.filter('EditRemindersComponents', {'preview-email': SendPreview}, {InputPh: window.wappoGet('InputPh')}),
    data() {
      return {
          other: null,
          formready: false,
          recipient: '',
          errorMessages: [],
          errors:{},
          formKey: 'formmailconfig',
          schema: []
          
      }
    },
    created(){
      this.recipient = this.passedViewData.recipient
      this.model = Object.assign({},this.reminder)
      this.viewData = Object.assign({},this.passedViewData)
      this.schema = this.schemai18n
      this.schema[0].label = this.model.label
      if(this.model.type == 1){
        this.schema.push({
            type: "opt-tiptap",
            model: "options.body",
            required: true,
            validation: ['required'],
            multiple_service_type: this.passedViewData.multiple_service_type,
            mail_status: this.passedViewData.mail_status,
            allow_rescheduling: this.passedViewData.allow_rescheduling,
            allow_cancellation: this.passedViewData.allow_cancellation,
            email_footer: this.passedViewData.email_footer,
            reschedule_link: this.passedViewData.reschedule_link,
            cancellation_link: this.passedViewData.cancellation_link,
            save_appointment_text_link: this.passedViewData.save_appointment_text_link,

        })

      }else{
        if(this.model.ignore !== undefined){
          this.filterSchema()
        }
        this.schema.push({
            type: "opt-tiptap",
            model: "options.body",
            simple: true,
            required: true,
            sms: true,
            validation: ['required'],
            multiple_service_type: this.passedViewData.multiple_service_type,
        })
        
      }
      if(this.viewData.languages!==false){
        this.model.canTranslate = true
      }
    },
    computed: {
        canSend(){
            return this.formready
        },
        schemai18n(){
          return [
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
                      label: this.get_i18n('when_reminder_sent','settings'),
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
                    labelDefault: this.get_i18n('select_unit','settings'),
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
                  type: "select",
                  label: this.get_i18n('language','common'),
                  model: "lang",
                  elements: this.viewData.languages.map(function(item){
                    item.id = item.locale
                    item.name = item.name + '('+item.locale+')'
                    return item
                  }),
                  cast: String,
                  conditions: [
                    { model:'canTranslate', values: [true] }
                  ],
              },
              {
                  type: "input",
                  label: this.get_i18n('subject','settings'),
                  model: "subject",
                  required: true,
                  cast: String,
                  validation: ['required'],
              },
              {
                  label: this.get_i18n('header_image','settings'),
                  type: 'opt-imageselect',
                  model: "email_logo",
                  size: 'full',
                  preview:{
                    width:'',
                  },
                  required: true,
              },
              
          ]
        }
    },


    methods: {
      filterSchema(){
        for (let i = 0; i < this.model.ignore.length; i++) {
            const ignore = this.model.ignore[i]
            let schema = this.schema
            for (let j = 0; j < schema.length; j++) {
              const schema_entry = schema[j]
              if(schema_entry.model == ignore){
                 this.schema = this.schema.slice(0,j).concat(this.schema.slice(j+1))
              }
            }
            
          }
          this.model.ignore = undefined
      },
      

        changedValue(newReminder){
          let needsaving = this.model.email_logo != newReminder.email_logo

            this.model = Object.assign({},newReminder)
            this.schema[0].label = this.model.label
            if(needsaving){
              this.save()
            }
        },
        readytosubmit(ready){
            this.formready = ready
        },

        save() {
            this.request(this.saveReminderRequest, undefined,undefined,false,  this.saved, this.failedValidation)
        },
        

      failedValidation(e){
        if(e.response!== undefined && e.response.data!== undefined 
        && e.response.data.data!== undefined && 
        e.response.data.data.errors!== undefined &&
        e.response.data.data.errors.validations !== undefined){
          this.errors = e.response.data.data.errors.validations
          this.formKey = 'form' + ((new Date()).getTime())
        }
        this.$refs.mcformgenerator.reRender()
        this.serviceError(e)
      },
        saved(e){
          this.successRequest(e)
        },
        async saveReminderRequest() {
            if(this.model.id > 0) return await this.serviceReminder.call('patch', this.model)
            return await this.serviceReminder.call('save', this.model)
        },
        sendPreview(recipient) {
            this.request(this.sendPreviewReminderRequest, {recipient:recipient, reminder: this.model.id})
        },
        async sendPreviewReminderRequest(params) {
            return await this.serviceReminder.call('sendPreview', params)
        },
        
    },
}
</script>
