<template>
    <div :id="elementId">
      <div class="wap-front" :class="getDynaClasses" >
          <StyleGenerator :options="opts" :wrapper="elementId" :largeVersion="largeVersion"></StyleGenerator>
          <div v-if="isPage" :class="'step-'+stepName">
              <BookingForm v-if="isBookingPage" :options="opts" :wrapperid="elementId" @changedStep="stepChanged" :attributesEl="attributesElProcess" />
              <ViewingAppointment v-else  :options="opts" :view="getView" :appointmentkey="getParameterByName('appointmentkey')" />
          </div>
          
          <div class="wappo_module" :class="getWidClass" >
              <div class="wap-wid wclosable" :class="getStepName" v-if="isWidget">
                <span v-if="hasCloseCross" @click="backToButton" class="wclose"></span>
                <BookingForm v-if="bookForm" :demoAs="demoAs" :step="currentStep" :options="opts" :attributesEl="attributesElProcess" :wrapperid="elementId" :passedDataSent="dataSent" @changedStep="stepChanged" />
                <BookingButton v-else @click="toggleBookForm" class="wbtn wbtn-booking wbtn-primary" :options="opts" >{{ realButtonTitle }}</BookingButton>
            </div>
          </div>
          <div class="wap-bg" v-if="bgEnabled" @click="backToButton"></div>
      </div>
    </div>
    
</template>

<script>

import BookingButton from './BookingForm/BookingButton'
import StyleGenerator from './Components/StyleGenerator'
import Colors from './Modules/Colors'
import UrlParam from './Modules/UrlParam'
import ckl from './Standalone/chunkloader.js'
import IsDemo from './Mixins/IsDemo'
export default {
    mixins: [Colors, UrlParam, IsDemo],
    props: ['classEl', 'options', 'step', 'attributesEl'],
    components: { 
        BookingButton,
        StyleGenerator,
        ViewingAppointment: () => (ckl(import(/* webpackChunkName: "group-viewingappointment" */ './BookingForm/ViewingAppointment'))),
        BookingForm: () => (ckl(import(/* webpackChunkName: "group-bookingform" */ './BookingForm/Main')))
    }, 
    data: () => ({
        bookForm: false,
        currentStep: null,
        dataSent: null,
        opts: null,
        elementId: '',
        stepName: '',
        buttonTitle: '',
        brFixed: undefined,
        popup: false,
        largeVersion: false,
        autoPop: true,
        demoAs: false,
        stepChanging: false,
    }),
    created(){
      this.elementId = 'wapfrontwrapper-' + Date.now()
      if(this.step !== undefined) {
        this.currentStep = this.step
      }
      this.opts = this.options === undefined ? window.widgetWappointment : Object.assign ({}, this.options)
      this.processShortcode()
    },
    mounted(){

      this.processAutoOpens()
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
      attributesElProcess(){
        let attributesEl = Object.assign({},this.attributesEl)
         if(this.getParameterByName('staff')){
          attributesEl.staffSelection = this.getParameterByName('staff')
        }
          
        return this.castAttributes(attributesEl)
      },
      getStepName(){
        return 'step-' + (this.bookForm? this.stepName:'button')
      },
        getWidClass(){
          return {
            'wap-abs':this.hasCloseCross && this.isMobilePhone && this.autoPop,
            'd-flex justify-content-center':this.attributesEl.center == 1
          }
        },
        bgEnabled(){
          return this.bookForm && (this.isBottomRight || this.isMobilePhone || this.popup)
        },
        canPop(){
          return this.bgEnabled && this.isMobilePhone && this.autoPop
        },
        isExpanded(){
          return this.bookForm && this.canPop
        },
        isPoppedUp(){
          return this.popup && this.bookForm
        },
        hasCloseCross(){
          return this.bgEnabled && (this.isBottomRight || this.isExpanded || this.isPoppedUp)
        },
        getDynaClasses(){
          let classes = {
            'br-fixed': this.isBottomRight, 
            'large-version': this.largeVersion,
            'wmobile': this.isMobilePhone,
            'wdesk': !this.isMobilePhone,
            'wexpanded': this.isExpanded,
            'poppedup': this.isPoppedUp
          }
          if([null,undefined,false].indexOf(this.getWidthClass) === -1){
            classes[this.getWidthClass] = true
          }
          if([null,undefined,false].indexOf(this.getParameterByName('view')) === -1){
            classes[this.getParameterByName('view')] = true
          }
          return classes
        },
        getElement(){
          return this.elementId && this.stepChanging === false ? document.getElementById(this.elementId):false
        },
        getWidthClass(){
          if([null,false].indexOf(this.getElement) === -1){
            if(this.getElement.clientWidth > 360){
              return 'over360'
            }
            if(this.getElement.clientWidth > 320){
              return 'over320'
            }
            if(this.getElement.clientWidth > 280){
              return 'over280'
            }
          }
        },
        isPortraitMode(){
          return window.innerHeight > window.innerWidth
        },
        isMobilePhone(){
          return window.innerWidth < 500 && this.isPortraitMode
        },

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
        },
        getView(){
          return this.step || this.getParameterByName('view')
        },
        hasAttributesToProcess(){
          return this.attributesEl !== undefined && Object.keys(this.attributesEl).length > 0
        }
    },
    methods: {
        serviceSelectionAttribute(){
          let arrayids = this.attributesEl.serviceSelection.indexOf(',') !== -1 ? this.attributesEl.serviceSelection.split(','):[this.attributesEl.serviceSelection]

          for (let i = 0; i < arrayids.length; i++) {
            arrayids[i] = parseInt(arrayids[i])
          }
          return arrayids
        },
        castAttributes(attributes){
          for (const el of ['staffSelection']) {
            if(attributes[el]){
              attributes[el] = parseInt(attributes[el])
            }
          }
          if(attributes['serviceSelection'] !== undefined){
            attributes['serviceSelection'] = this.serviceSelectionAttribute()
          }
          return attributes
        },
        stepChanged(stepName){
          this.stepName = stepName
          this.stepChanging = true
          setTimeout(this.changedStep, 200);
        },
        changedStep(){
          this.stepChanging = false
        },
        processShortcode(){
          if(this.hasAttributesToProcess){
            this.buttonTitle = this.attributesEl.buttonTitle !== undefined ? this.attributesEl.buttonTitle:this.buttonTitle
            this.brFixed = this.attributesEl.brcFloats !== undefined
            this.demoAs = this.attributesEl.demoAs !== undefined
            this.largeVersion = [undefined,false].indexOf(this.attributesEl.largeVersion) === -1
            this.opts.selection.check_viewweek = [undefined,false].indexOf(this.attributesEl.week) === -1
            this.autoPop = [undefined,false].indexOf(this.attributesEl.popOff) !== -1
            this.popup = this.attributeIsEmpty('popup') && !this.demoAs

            this.opts.attributesEl = Object.assign({},this.attributesEl)

          }
        },

        attributeIsEmpty(attributestring){
          return [false,undefined].indexOf(this.attributesEl[attributestring]) === -1
        },

        translateAttributesEl(){
          let attributes = {}
          for (const key in this.attributesEl) {
            if (this.attributesEl.hasOwnProperty(key) && 
            ['buttonTitle','brFixed','demoAs','largeVersion',
            'week','popOff','autoOpen','autoPop','staffSelection','serviceSelection'].indexOf(key) !== -1) {

              attributes[key] = this.attributesEl[key]
              
            }
          }
          return attributes
        },
        processAutoOpens(){
          if(this.hasAttributesToProcess && [undefined,false].indexOf(this.attributesEl.autoOpen) === -1 ) {
            this.autoPop = [undefined,false].indexOf(this.attributesEl.autoPop) === -1 //no auto pop on 
            this.toggleBookForm() // this one goes last
          }
        },
        backToButton(){
          if(this.canPop || this.popup){
            document.documentElement.classList.remove('wappo-popped')
            document.body.classList.remove('wappo-popped')
          }
          if(this.popup){
            let clone = document.getElementById('clone-'+this.elementId)
            let wappo_module = clone.getElementsByClassName('wap-front')[0]
              document.getElementById(this.elementId).appendChild(wappo_module)
              clone.remove()
          }
          this.bookForm = false
        },
        toggleBookForm() {
            this.bookForm = !this.bookForm
            if(this.popup){
              let wappo_module = document.getElementById(this.elementId).getElementsByClassName('wap-front')[0]
              let cloneWrapper = document.createElement('div')
              cloneWrapper.setAttribute('id', 'clone-'+this.elementId)
              document.getElementById('wap-footer-container').appendChild(cloneWrapper)
              cloneWrapper.appendChild(wappo_module)
              
            }
            if(this.canPop || this.popup){
              document.body.classList.add('wappo-popped')
              document.documentElement.classList.add('wappo-popped')
            }
        },
    }
}
</script>
<style>

.large-version .wap-wid{
    max-width: 420px;
}
.large-version .wap-wid.step-BookingCalendar{
    max-width: 100%;
    min-width: 100%;
}
.wap-wid{
    max-width: 360px;
}

.br-fixed .wap-wid .wbtn-booking{
  float:right;
}
.wap-wid .loader {
    min-height: 68px;
}
/* .wap-front{
    font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;
} */

.wap-front .wbtn.wbtn-secondary.wbtn-cell{
  border-radius: .5em;
  margin: .4em 0;
}

.wap-front .wbtn {
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

.wap-front .wbtn-block{
  width: 100%;
}


  .wap-front .wbtn:hover, 
  .wap-front .wbtn:focus {
    text-decoration: none;
  }

  .wap-front .wbtn:focus, 
  .wap-front .wbtn.focus {
    outline: 0;
  }

  .wap-front .wbtn.wbtn-disabled, 
  .wap-front .wbtn:disabled {
    opacity: 0.65;
  }

  .wap-front .wbtn:not(:disabled):not(.wbtn-disabled) {
    cursor: pointer;
  }

  .wap-front .wbtn:not(:disabled):not(.wbtn-disabled):active, 
  .wap-front .wbtn:not(:disabled):not(.wbtn-disabled).active {
    background-image: none;
  }

  .wap-front a.wbtn.wbtn-disabled,
  .wap-front fieldset:disabled a.wbtn {
    pointer-events: none;
  }

.wap-front .d-flex {
  display: -ms-flexbox !important;
  display: flex !important;
}
.wap-front .d-flex.d-flex-inline{
    display: inline-flex !important;
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
.wap-front .align-items-start {
    -webkit-box-align: start !important;
    -ms-flex-align: start !important;
    align-items: flex-start !important;
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


.wap-wid.wclosable > .wclose {
    position: absolute;
    z-index: 2;
    right: .3em;
    top: .3em;
}

.wap-front .wml-2{
    margin-left:.4em;
}
html[dir=rtl] .wap-front .wml-2{
  margin-right: .4em !important;
  margin-left: 0 !important;
}
.wprice{
  font-weight: bold;
}

.wap-front .text-center{
    text-align: center;
}

.wap-front .text-sm{
  font-size: .7em;
}

.wappo-popped{
  overflow: hidden;
  position: relative;
}

.wap-front.wdesk.br-fixed {
  min-width: 360px;
}

.wap-front.br-fixed{
    position: fixed;
    right: 0;
    bottom: 0;
    margin: 1rem;
    max-height: 95%;
    min-width: 320px;
}

.wap-booking-fields {
    text-align: left;
    margin: .5em 0;
}

html[dir=rtl] .wap-front.br-fixed{
    right: auto;
    left: 0;
}

html[dir=rtl] .br-fixed .wap-wid .wbtn-booking {
  float: left;
}
html[dir=rtl] .wap-booking-fields{
    text-align: right;
}


.wap-booking-fields input,
.wap-booking-fields textarea{
  color: var(--wappo-body-tx) !important;
}

.wap-booking-fields .isInvalid input[type="text"], 
.wap-booking-fields .isInvalid input[type="email"], 
.wap-booking-fields .isInvalid input[type="url"], 
.wap-booking-fields .isInvalid input[type="tel"],
.wap-booking-fields .isInvalid textarea{
    border-right: 4px solid var(--wappo-error-tx) !important;
}
.wap-booking-fields .isValid input[type="text"], 
.wap-booking-fields .isValid input[type="email"], 
.wap-booking-fields .isValid input[type="url"], 
.wap-booking-fields .isValid input[type="tel"],
.wap-booking-fields .isValid textarea{
    border-right: 4px solid var(--wappo-valid-tx) !important;
}

html[dir=rtl] .wap-booking-fields .isInvalid input[type="text"], 
html[dir=rtl] .wap-booking-fields .isInvalid input[type="email"], 
html[dir=rtl] .wap-booking-fields .isInvalid input[type="url"], 
html[dir=rtl] .wap-booking-fields .isInvalid input[type="tel"],
html[dir=rtl] .wap-booking-fields .isInvalid textarea{
    border-left: 4px solid var(--wappo-error-tx) !important;
    border-right:1px solid var(--wappo-input-bor) !important;
}
html[dir=rtl] .wap-booking-fields .isValid input[type="text"], 
html[dir=rtl] .wap-booking-fields .isValid input[type="email"], 
html[dir=rtl] .wap-booking-fields .isValid input[type="url"], 
html[dir=rtl] .wap-booking-fields .isValid input[type="tel"],
html[dir=rtl] .wap-booking-fields .isValid textarea{
    border-left: 4px solid var(--wappo-valid-tx) !important;
    border-right:1px solid var(--wappo-input-bor) !important;
}


.wap-booking-fields .field-required label::after {
    content:" *";
    color:var(--wappo-error-tx);
}

.wap-front .wappointment-errors{
    background-color:var(--wappo-error-tx);    
    border-radius:.25em;
    padding: .3em;
    margin: .5em 0;
}

.wap-front div.wappointment-errors div{
    color: var(--wappo-pri-tx);
}

.wap-front .wrap-calendar div.wappointment-errors{
    color: var(--wappo-pri-tx);
    font-size: .9em;
}
.phone-field-wrap .dpselect .selection::after,
.phone-field-wrap strong{
    color: #645a5a;
}

.wap-front.poppedup{
  width: 100%;
  height: 100%;
  margin: 0;
  position: fixed;
  left: 0;
  bottom: 0;
  overflow-y: scroll;
}

.wap-front.poppedup .wappo_module{
  display: flex;
  align-items: center;
  width: 100%;
  height: 100%;
  position: relative;
  z-index: 99;
}
.wap-front.poppedup .wappo_module .wap-wid{
  width: 500px;
  margin: 0 auto;
  position: relative;
  max-width:none;
}
.wap-front.poppedup .wap-bg{
  position: fixed;
  height: 100%;
  width: 100%;
  background-color: rgba(0, 0, 0, .7);
  top: 0;
  z-index: 0;
  overflow-y: scroll;
  margin: 0 auto;
}



@media only screen and (max-width: 500px) {
  .wap-front.poppedup .wappo_module .wap-wid{
    min-width:100%;
  }
  .wap-abs{
    position: absolute;
    bottom: 0;
    width: 100%;
    max-height: 95%;
    overflow-y: scroll;
  }

  .wap-front.wexpanded .wap-bg
  .wap-front.br-fixed .wap-bg,
  .wap-front.wexpanded .wap-bg{
    position: fixed;
    height: 100%;
    width: 100%;
    background-color: rgba(0, 0, 0, .7);
    top: 0;
    z-index: 0;
    overflow-y: scroll;
  }
  .wap-front.br-fixed,
  .wap-front.wexpanded{
    width: 100%;
    margin: 0;
    position: fixed;
    left: 0;
    bottom: 0;
    overflow-y: scroll;
  }

  .wap-front.wexpanded{
    z-index: 999999999;
    height: 100%;
  }

  .wap-front.br-fixed .wbtn.wbtn-booking.wbtn-primary{
    margin: 0;
    width:100%;
    border-radius: .2rem .2rem 0 0;
  }

  .wap-front.br-fixed .wap-bf,
  .wap-front.br-fixed .wap-wid,
  .wap-front.wmobile .wap-bf,
  .wap-front.wmobile .wap-wid{
    max-width: 100%;
    position:relative;
    z-index: 1;
  }

  .wap-front.wmobile .wap-bf,
  .wap-front.wmobile .wap-wid{
    width:100%;
  }

  .wap-front.wmobile .wap-wid.step-button{
    width: auto;
  }
}
</style>
