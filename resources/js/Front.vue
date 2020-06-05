<template>
    <div class="wap-front" :class="{'br-fixed': isBottomRight, 'large-version': largeVersion}" :id="elementId">
        <StyleGenerator :options="opts" :wrapper="elementId" :largeVersion="largeVersion"></StyleGenerator>
        <div v-if="isPage">
            <BookingForm v-if="isBookingPage" :options="opts"></BookingForm>
            <ViewingAppointment v-else  :options="opts" :view="getParameterByName('view')" :appointmentkey="getParameterByName('appointmentkey')"></ViewingAppointment>
        </div>
        
        <div class="wap-wid" v-if="isWidget">
            <span v-if="bookForm && isBottomRight" @click="backToButton" class="close-wid"></span>
            <BookingForm v-if="bookForm" :step="currentStep" :options="opts" :passedDataSent="dataSent"></BookingForm>
            <BookingButton v-else @click="toggleBookForm" class="btn btn-booking btn-primary" :options="opts" >{{ realButtonTitle }}</BookingButton>
        </div>
        <div class="wap-bg" v-if="bookForm"></div>
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
    props: ['classEl', 'options', 'step', 'attributesEl'],
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
        buttonTitle: '',
        brFixed: undefined,
        largeVersion: false
    }),
    created(){
      this.elementId = 'wapfrontwrapper-' + Date.now()
      if(this.step !== undefined) this.currentStep = this.step
      this.opts = this.options === undefined ? window.widgetWappointment : Object.assign ({}, this.options)
      if(this.opts.demoData !== undefined){
          this.disabledButtons = true
      }

      this.processShortcode()
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
        isBottomRight(){
          return this.brFixed !== undefined && this.brFixed === true
        }

    },
    methods: {
        processShortcode(){
          if(this.attributesEl !== undefined && Object.keys(this.attributesEl).length > 0){
            if(this.attributesEl.buttonTitle !== undefined) this.buttonTitle = this.attributesEl.buttonTitle
            if(this.attributesEl.brcFloats !== undefined) this.brFixed = true
            if([undefined,false].indexOf(this.attributesEl.largeVersion) === -1) this.largeVersion = true
            if([undefined,false].indexOf(this.attributesEl.autoOpen) === -1 ) this.bookForm = true
            if([undefined,false].indexOf(this.attributesEl.week) === -1) this.opts.selection.check_viewweek = true
            
            this.opts.attributesEl = this.attributesEl
          }
        },
        backToButton(){
          this.bookForm = false
        },
        toggleBookForm() {
            this.bookForm = !this.bookForm
        },
    }
}
</script>
<style>
.large-version .wap-wid{
    max-width: 100%;
}
.wap-wid{
    max-width: 320px;
    min-width: 200px;
}

.br-fixed .wap-wid .btn-booking{
  float:right;
}
.wap-wid .loader {
    min-height: 68px;
}
.wap-front{
    font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;
}

.wap-front .btn.btn-secondary.btn-cell{
  border-radius: .5em;
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
  margin: 0;
  background-color: transparent;
  background: transparent;
  height: auto;
  text-transform: none;
}


  .wap-front .btn:hover, 
  .wap-front .btn:focus {
    text-decoration: none;
  }

  .wap-front .btn:focus, 
  .wap-front .btn.focus {
    outline: 0;
  }

  .wap-front .btn.btn-disabled, 
  .wap-front .btn:disabled {
    opacity: 0.65;
  }

  .wap-front .btn:not(:disabled):not(.btn-disabled) {
    cursor: pointer;
  }

  .wap-front .btn:not(:disabled):not(.btn-disabled):active, 
  .wap-front .btn:not(:disabled):not(.btn-disabled).active {
    background-image: none;
  }

  .wap-front a.btn.btn-disabled,
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
.wap-front .justify-content-center {
    -webkit-box-pack: center !important;
    -ms-flex-pack: center !important;
    justify-content: center !important;
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

.wap-front.br-fixed{
    position: fixed;
    right: 0;
    bottom: 0;
    margin: 1rem;
    z-index: 9999999;
    min-width: 320px;
}

.close-wid::after {
    transform: translateX(15px) rotate(-45deg);
}
.close-wid::before, .close-wid::after {
    content: ' ';
    position: absolute;
    background-color: #b5b1b1;
}
.close-wid::before {
    transform: translateX(15px) rotate(45deg);
}
.close-wid:hover::before, .close-wid:hover::after {
    background-color: #575656;
    width: 2px;
}
.close-wid{
  cursor: pointer;
height: 25px;
width: 25px;
display: block;
position: absolute;
right: 0;
}

.close-wid::before, .close-wid::after  {
    height: 10px;
    width: 1px;
    top: 6px;
    right: 28px;
}

@media only screen and (max-width: 500px) {
  .wap-front.br-fixed .wap-bg{
    position: fixed;
    height: 100%;
    width: 100%;
    background-color: rgba(0, 0, 0, .7);
    top: 0;
    z-index: -1;
  }
  .wap-front.br-fixed{
    width: 100%;
    margin: 0;
  }
  .wap-front.br-fixed{
    width: 100%;
    margin: 0;
  }
  .wap-front.br-fixed .btn.btn-booking.btn-primary{
    margin: 0;
    width:100%;
    border-radius: .2rem .2rem 0 0;
  }

  .wap-front.br-fixed .wap-bf, .wap-front.br-fixed .wap-wid{
    max-width: 100%;
  }
}
</style>
