<template>
    <transition name="fade-in">
        <div v-if="sent">
          <h4 class="text-primary p-2">Thank you! Your message has been sent!</h4>
        </div>
        <div v-else>
          <h4 v-if="title">{{ title }}</h4>
          <WAPFormGenerator v-if="dataLoaded" :schema="schema" :data="modelHolder" 
          @submit="submitMessage" labelButton="Send Message" classWrapper="contact-wrapper" >
            <div class="mb-2">
              <a href="javascript:;" @click="hiddenData = !hiddenData" class="small">Show data sent with message</a>
              <div class="hidden-data small text-muted" v-if="hiddenData">
                <div class="p-4" v-for="(options,label) in optionsData">
                  <h5><input type="checkbox" @change="switchOption(label)" :checked="willBeSent(label)"> {{ label }}</h5>
                  <div v-if="willBeSent(label)">{{ options }}</div>
                </div>
              </div>
            </div>
          </WAPFormGenerator>
        </div>
    </transition>
</template>

<script>
import WappointmentService from '../Services/V1/Wappointment'
import BrowserInfo from 'browser-info'
import abstractview from '../Views/Abstract'
let inputStyle = {'max-width':'200px'}
export default {
  extends: abstractview,
    props: {
        type: {
            type: String,
            default: 'error'
        },
        title: {
            type: String,
        },
        messages: {
            type: Array,
        },
        autofill:{
          type: Object,
        },
        errors:{
          type: Array,
        }
    },
    data: () => ({
        modelHolder: {},
        serviceWappointment: null,
        cannot_contact: false,
        optionsData: {},
        doNotSend: {},
        hiddenData:false,
        viewName: 'wizardinit',
        sent: false,
        schema: [
            {
              type: 'row',
              fields: [
                {
                    type: 'input',
                    label: 'Your Name',
                    model: 'name',
                    cast: String,
                    styles: inputStyle
                },
                {
                    type: 'input',
                    label: 'Your Email',
                    model: 'email',
                    cast: String,
                    styles: inputStyle
                },
              ]
            },
            {
                type: 'input',
                label: 'Subject',
                model: 'subject',
                cast: String,
            },
            {
                type: 'editor',
                label: 'Your message',
                model: 'message',
                cast: String,
            },
    
        ]
    }),
    created(){
      this.serviceWappointment = this.$vueService(new WappointmentService)
      if(this.autofill !== undefined){
        if(this.autofill.subject !== undefined) this.modelHolder.subject = this.autofill.subject
        if(this.autofill.message !== undefined) this.modelHolder.message = this.autofill.message
        if(this.autofill.extra !== undefined) this.optionsData.extra = this.autofill.extra
        if(this.autofill.server !== undefined) {
          for (const key in this.autofill.server) {
            if (this.autofill.server.hasOwnProperty(key)) {
              this.optionsData[key] =  this.autofill.server[key]
            }
          }
        } 
        this.optionsData.browser = BrowserInfo()
      } 
      this.optionsData.urlerror = window.location.href
      if(this.errors!==undefined)this.optionsData.errors = this.errors
      
    },
    computed: {
      optionsParsed(){
        let optionsSent = {}
        for (const key in this.optionsData) {
          if (this.optionsData.hasOwnProperty(key)) {
            const element = this.optionsData[key]
            if(this.doNotSend[key] === undefined){
              optionsSent[key] = this.optionsData[key]
            }
          }
        }
        return optionsSent
      }
    },
    methods: {
        willBeSent(label){
          return this.doNotSend[label] === undefined
        },
        switchOption(label){
          let doNotSend = Object.assign({},this.doNotSend)

          if(doNotSend[label] === undefined) doNotSend[label] =  true
          else doNotSend[label] = undefined
          
          this.doNotSend = doNotSend
        },
        loaded(response){
          this.viewData = response.data
          this.modelHolder.email = this.viewData.admin_email
          this.modelHolder.name = this.viewData.admin_name
        },
       submitMessage(data){
            data.options = this.optionsParsed
            return this.sendMessage(data)
        },
        sendMessage(data){
            this.$WapModal()
                .request(this.requestSendMessage(data)).then(this.messageSent).catch(this.failedSending)
        },

        async requestSendMessage(params){
           return await this.serviceWappointment.call('contact', params)
        },

        messageSent(response){
            this.$WapModal().notifySuccess(response.data.message)
            this.sent = true
            this.$emit('sent')
        },
        failedSending(e){
          this.cannot_contact = true
          this.$WapModal().notifyError(e.response.data.message)
        }
    }
}
</script>
<style>
.fade-in-enter-active{
  transition: all .3s ease;
}

.fade-in-leave-active{
  transition: all .1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.fade-in-enter {
  transform: translateY(120%);
  opacity: 0;
}
.fade-in-leave-to {
  transform: translateY(200%);
  opacity: 0;
}
.contact-wrapper {
  padding: 2rem;
  border: 2px solid #eee;
  max-width: 500px;
}

.hidden-data {
  border: 2px dashed #ccc;
  padding: .5rem;
  margin-bottom: 1rem;
}
</style>