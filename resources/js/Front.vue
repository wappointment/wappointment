<template>
    <div class="wap-front" :id="elementId">
        <StyleGenerator :options="opts" :wrapper="elementId"></StyleGenerator>
        <div v-if="isPage">
            <BookingForm v-if="isBookingPage" :options="opts"></BookingForm>
            <ViewingAppointment v-else  :options="opts" :view="getParameterByName('view')" :appointmentkey="getParameterByName('appointmentkey')"></ViewingAppointment>
        </div>
        
        <div class="wap-wid" v-if="isWidget">
            <BookingForm v-if="bookForm" :step="currentStep" :options="opts" :passedDataSent="dataSent"></BookingForm>
            <BookingButton v-else @click="toggleBookForm" class="btn btn-booking btn-primary" :options="opts" >{{ realButtonTitle }}</BookingButton>
        </div>
    </div>
</template>

<script>

import BookingButton from './Components/BookingButton'
import StyleGenerator from './Components/StyleGenerator'
import Colors from './Modules/Colors'
import UrlParam from './Modules/UrlParam'
import ckl from './Standalone/chunkloader.js'

export default {
    mixins: [Colors, UrlParam],
    props: ['classEl', 'buttonTitle', 'options', 'step'],
    components: { 
        BookingButton,
        StyleGenerator,
        ViewingAppointment: () => (ckl(import(/* webpackChunkName: "group-viewingappointment" */ './Components/BookingForm/ViewingAppointment'))),
        BookingForm: () => (ckl(import(/* webpackChunkName: "group-bookingform" */ './Components/BookingForm')))
    }, 
    data: () => ({
        bookForm: false,
        currentStep: null,
        dataSent: null,
        opts: null,
        elementId: '',
        disabledButtons: false,
    }),
    created(){
      this.elementId = 'wapfrontwrapper-' + Date.now()
      if(this.step !== undefined) this.currentStep = this.step
      this.opts = this.options === undefined ? widgetWappointment : Object.assign ({}, this.options)
      if(this.opts.demoData !== undefined){
            this.disabledButtons = true
            
        }
    },
     watch:{
        step(val){
            this.currentStep = val
        },
        currentStep(val){
            if(val !== undefined) {
              
                this.bookForm = (['button'].indexOf(val) === -1) ? true:false
                //this.bookForm = val > 1 ? true:false
            }
        },
        bookForm(val){
          if(val === false) this.$emit('changedStep','button')
          //else this.currentStep = 2
        }
    },
    computed:{

        realButtonTitle(){
          return this.options!== undefined && this.options.button.title !== undefined ? this.options.button.title : this.buttonTitle
        },
        isPage(){
            return this.classEl === 'wappointment_page'
        },
        isBookingPage(){
            return this.isPage && this.getParameterByName('view') === 'new-event'
        },
        isWidget(){
            return this.classEl === 'wappointment_widget'
        },

    },
    methods: {
        
        toggleBookForm() {
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'selection')
              return
            } 
            this.bookForm = !this.bookForm
        },
    }
}
</script>
<style>
.wap-wid{
    max-width: 300px;
}
.wap-wid .loader {
    min-height: 68px;
}
.wap-front{
    font-size: 18px;  
    font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;
}

.wap-front .btn {
  display: inline-block;
  font-weight: 400;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  border: 1px solid transparent;
  padding: 0.375em;
  font-size: .8em;
  line-height: 1.5;
  border-radius: 0.25em;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  margin: 0.2em;
}


  .wap-front .btn:hover, 
  .wap-front .btn:focus {
    text-decoration: none;
  }

  .wap-front .btn:focus, 
  .wap-front .btn.focus {
    outline: 0;
  }

  .wap-front .btn.disabled, 
  .wap-front .btn:disabled {
    opacity: 0.65;
  }

  .wap-front .btn:not(:disabled):not(.disabled) {
    cursor: pointer;
  }

  .wap-front .btn:not(:disabled):not(.disabled):active, 
  .wap-front .btn:not(:disabled):not(.disabled).active {
    background-image: none;
  }

  .wap-front a.btn.disabled,
  .wap-front fieldset:disabled a.btn {
    pointer-events: none;
  }

  .wap-front .d-flex {
  display: -ms-flexbox !important;
  display: flex !important;
}
.wap-front .flex-wrap {
    -ms-flex-wrap: wrap !important;
    flex-wrap: wrap !important;
}

.wap-front .flex-fill{
    -ms-flex: 1 1 auto !important;
    flex: 1 1 auto !important;
}
.wap-front .justify-content-between {
  -ms-flex-pack: justify !important;
  justify-content: space-between !important;
}

.wap-front .justify-content-around {
  -ms-flex-pack: distribute !important;
  justify-content: space-around !important;
}
.wap-front .align-items-center {
  -ms-flex-align: center !important;
  align-items: center !important;
}
.wap-front .align-content-around {
  -ms-flex-line-pack: distribute !important;
  align-content: space-around !important;
}
.wap-front .align-self-center {
  -ms-flex-item-align: center !important;
  align-self: center !important;
}

.wap-front .align-self-stretch {
  -ms-flex-item-align: stretch !important;
  align-self: stretch !important;
}


</style>
