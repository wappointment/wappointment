<template>
    <div :id="elementId">
      <div class="wap-front" :class="getDynaClasses" >
          <StyleGenerator :options="opts" :wrapper="elementId" :largeVersion="largeVersion" />
          <div v-if="isPage" :class="'step-'+stepName">
              <BookingForm v-if="isBookingPage" :options="opts" :wrapperid="elementId" @changedStep="stepChanged" :attributesEl="attributesElProcess" />
              <ViewingAppointment v-else  :options="opts" :view="getView" :appointmentkey="getParameterByName('appointmentkey')" />
          </div>
          
          <div class="wappo_module" :class="getWidClass" v-if="isWidget">
            <div class="wap-wid wclosable" :class="getStepName" >
                <EventList v-if="showEventList" @selectedEvent="selectedEvent" :list="attributesEl.list" :options="opts"/>
                <template v-else>
                  <span v-if="hasCloseCross" @click="backToButton" class="wclose"></span>
                  <BookingForm v-if="bookForm" :demoAs="demoAs" :step="currentStep" :options="opts" :eventSelected="eventSelected"
                  :attributesEl="attributesElProcess" :wrapperid="elementId" :passedDataSent="dataSent" @changedStep="stepChanged" @backToStart="backToStart" />
                  <BookingButton v-else @click="toggleBookForm" class="wbtn wbtn-booking wbtn-primary" :options="opts" >{{ realButtonTitle }}</BookingButton>
                </template>
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
import FrontStyle from './FrontStyle'
import EventList from './EventList/Main'
export default {
    mixins: [Colors, UrlParam, IsDemo, FrontStyle],
    props: ['classEl', 'options', 'step', 'attributesEl'],
    components: { 
        BookingButton,
        StyleGenerator,
        EventList,
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
        eventSelected:false
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
      showEventList(){
        return this.attributeIsActive('list') && !this.eventSelected
      },
      attributesElProcess(){
        let attributesEl = Object.assign({},this.attributesEl)
        if(this.getParameterByName('staff')){
          attributesEl.staffSelection = this.getParameterByName('staff')
        }
        if(this.isNewEventPage){
          attributesEl.staffPage = 1
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
            return this.isPage && this.isNewEventPage
        },

        isNewEventPage(){
          return this.getParameterByName('view') === 'new-event'
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
      backToStart(){
        this.currentStep = null
        this.eventSelected = false
      },
      selectedEvent(event){
        this.eventSelected = event
        this.currentStep = 'BookingCalendar'
        this.toggleBookForm()
        
        return event
      },
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
            this.largeVersion = this.attributeIsActive('largeVersion')
            this.opts.selection.check_viewweek = this.attributeIsActive('week')
            this.autoPop = !this.attributeIsActive('popOff')
            this.popup = this.shouldPopup()
            this.opts.attributesEl = Object.assign({},this.attributesEl)
          }
        },

        shouldPopup(){
          return (this.attributeIsActive('popup') || this.brFixed) && !this.demoAs
        },

        attributeIsActive(attributestring){
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
          if(this.hasAttributesToProcess && this.attributeIsActive('autoOpen')) {
            this.autoPop = this.attributeIsActive('autoPop')
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
            if(typeof wappo_module == 'object'){
              document.getElementById(this.elementId).appendChild(wappo_module)
            }
              
              clone.remove()
          }
          this.bookForm = false
          if(this.eventSelected){
            this.eventSelected = false
          }
        },
        toggleBookForm() {
            this.bookForm = !this.bookForm
            if(this.popup){
              let wappo_module = document.getElementById(this.elementId).getElementsByClassName('wap-front')[0]
              let cloneWrapper = document.createElement('div')
              cloneWrapper.setAttribute('id', 'clone-'+this.elementId)
              if(typeof cloneWrapper == 'object'){
                document.getElementById('wap-footer-container').appendChild(cloneWrapper)
              }
              
              if(typeof wappo_module == 'object'){
                cloneWrapper.appendChild(wappo_module)
              }
              
            }
            if(this.canPop || this.popup){
              document.body.classList.add('wappo-popped')
              document.documentElement.classList.add('wappo-popped')
            }
        },
    }
}
</script>
