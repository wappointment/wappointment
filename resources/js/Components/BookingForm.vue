<template>
    <div class="wap-bf" :class="{show: canBeBooked}">
        <div v-if="canBeBooked">
            <BookingFormHeader :staffs="getStaffs" 
            :isStepSlotSelection="isStepSlotSelection"
            :appointmentSaved="appointmentSaved"
            :options="options"
            :service="service" :duration="duration" @refreshed="refreshClick"
            :services="services"
            :rescheduling="rescheduling"
            @changeStaff="childChangedStep"
            @changeService="childChangedStep"
            @changeDuration="childChangedStep"
            />

            <div class="wrap-calendar p-2">
                <div v-if="loading">
                    <Loader></Loader>
                </div>
                <div :class="{'hide-loading':loading}">
                    <div v-if="currentStep == loadingStep && isCompVisible(currentStep)">
                        <component :is="getComp(currentStep)"
                        v-bind="getCompProp(currentStep)"
                        v-on="getCompListeners(currentStep)"
                        :relations="getCompRelations(currentStep)"
                        ></component>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div v-if="dataloaded">
                <div v-if="service">No appointments available</div>
                <div v-else>Service not ready</div>
            </div>
            <div v-else><Loader></Loader></div>
        </div>
    </div>
</template>

<script>
import abstractFront from '../Views/abstractFront'
import Intervals from '../Standalone/intervals'
import Colors from "../Modules/Colors"
import Dates from "../Modules/Dates"
import BookingFormConfirmation from './BookingForm/Confirmation'
import RescheduleConfirm from './BookingForm/RescheduleConfirm'
import BookingCalendar from './BookingForm/Calendar'
import BookingFormInputs from './BookingForm/Form'
import BookingFormHeader from './BookingForm/Header'
import DurationCell from './BookingForm/DurationCell'
BookingFormHeader.components = {DurationCell}
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import convertDateFormatPHPtoJS from '../Standalone/convertDateFormatPHPtoJS'
import browserLang from '../Standalone/browserLang'

let compDeclared = {
    'BookingFormConfirmation' : BookingFormConfirmation,
    'RescheduleConfirm': RescheduleConfirm,
    'BookingCalendar': BookingCalendar,
    'BookingFormInputs':BookingFormInputs,
    'BookingFormHeader': BookingFormHeader,
    'DurationCell': DurationCell,
    'abstractFront':abstractFront
}
compDeclared = window.wappointmentExtends.filter('BookingFormComp', compDeclared )

export default {
     extends: abstractFront,
     mixins: [Colors, Dates],
     props: ['serviceAction', 'appointmentkey', 'rescheduleData', 'options', 'step','passedDataSent'],
     components: compDeclared, 
    data: () => ({
        viewName: 'availability',
        viewData: null,
        intervalsCollection: null,
        currentTz : 'UTC',
        selectedSlot: false,
        time_format: '',
        date_format: '',
        appointmentSavedData: false,
        appointmentSaved: false,
        appointmentKey: false,
        dataloaded: false,
        isApprovalManual: false,
        dataSent: {},
        errors: null,
        componentsList: null,
        services: [],
        service: false,
        location: false,
        duration: false,
        currentStep: 'BookingCalendar',
        loadingStep: '',
        converted:false
    }),

    mounted () {
        
        this.refreshInitValue()
        this.currentTz = this.tzGuess()
        this.createdAt = this.getUnixNow()
        
        if(this.step == 'button') {
            this.$emit('changedStep','selection')
        }

    },

    computed: {
        appointmentStartsAt(){
            return this.converted 
        },
        getStaffs(){
            return this.viewData.staffs !== undefined ? this.viewData.staffs:[]
        },
        serviceSelected(){
            return this.service !== false
        },
        durationSelected(){
            return this.duration !== false
        },
        locationSelected(){
            return this.location !== false
        },
        slotSelected(){
            return this.selectedSlot !== false && this.selectedSlot > 0
        },
        isStepForm(){
            return !this.appointmentSaved && !this.reschedulingSelectedSlot && this.selectedSlot
        },
        isStepSlotSelection(){
            return !this.appointmentSaved && !this.reschedulingSelectedSlot && !this.selectedSlot
        },
       rescheduling(){
           return this.serviceAction === 'rescheduling'
       },
       reschedulingSelectedSlot(){
           return this.rescheduling && this.selectedSlot
       },
       fullDateFormat(){
           return this.date_format + '[' + this.viewData.date_time_union + ']' + this.time_format
       },
       canBeBooked(){
           return this.dataloaded && this.intervalsCollection!== null && this.intervalsCollection.intervals.length > 0
       },
       
       staff(){
           return this.getDefaultStaff()
       },
       timeprops(){
           let timeprops = {
               currentTz: this.currentTz,
               time_format: this.time_format,
               fullDateFormat: this.fullDateFormat,
               ctz: this.currentTz
           }
           if(this.appointmentkey !== undefined){
               timeprops.appointmentkey = this.appointmentkey
           }
           return timeprops
       },
       getStaffs(){
           return this.viewData !== undefined && this.viewData.staffs !== undefined ? this.viewData.staffs: []
       }
    },
    methods: {

        async convertDateRequest(data) {
            return await this.serviceBooking.call('convertDate', data)
        }, 

        convertedDate(result){
            this.converted = result.data.converted
        },

        loadStep(step){
            this.loadingStep = step
            this.fetchFormattedDate()
        },
        childChangedStep(newStep, dataChanged){
            if(typeof dataChanged == 'object' && Object.keys(dataChanged).length > 0) {
                this.childChangedData(dataChanged)
            }
            this.currentStep = newStep
            setTimeout(this.loadStep.bind(null,newStep), 100)
        },
        childChangedData(dataChanged){
            for (const key in dataChanged) {
                if (dataChanged.hasOwnProperty(key)) {
                    this[key] = dataChanged[key]
                }
            }
        },

        getDefaultService(){
            return this.dataloaded && this.viewData.services[0] !== undefined ?this.viewData.services[0]:false
        },
        getCompProp(component_name){
            
            let props = this.componentsList[component_name].props !== undefined ? this.componentsList[component_name].props:{}
            let nprops = {}
            for (const key in props) {
                if (props.hasOwnProperty(key)) {
                    const element = props[key]
                    if(typeof element == 'string'){
                        if(element.indexOf('.')!==-1){
                            let splited_name = element.split('.')
                            let temp = Object.assign({},this)

                            for (let i = 0; i < splited_name.length; i++) {
                                temp = temp[splited_name[i]]
                            }
                            nprops[key] = temp
                            
                        }else{
                            nprops[key] = this[element]
                        }
                        
                    }else{
                        nprops[key] = element
                    }
                }
            }
            return nprops
        },
        getCompRelations(component_name){
            return this.componentsList[component_name].relations !== undefined ? this.componentsList[component_name].relations:{}
        },
        getCompListeners(component_name){
            let listeners = this.componentsList[component_name].listeners !== undefined ? this.componentsList[component_name].listeners:{}
            for (const key in listeners) {
                if (listeners.hasOwnProperty(key)) {
                    const element = listeners[key]
                    if(typeof element == 'string'){
                        listeners[key] = this[element]
                    }
                }
            }
            return listeners
        },
        
        getComp(component_name){
            return this.componentsList[component_name].name
        },

        isCompVisible(component_name){
            let conditions = this.componentsList[component_name].conditions !== undefined ? this.componentsList[component_name].conditions : false
            if(conditions !== false) {
                let conditionKeys = Object.keys(conditions)
                for (let i = 0; i < conditionKeys.length; i++) {
                    const keyCondition = conditionKeys[i]
                    if(this[keyCondition] !== conditions[keyCondition]) {
                        return false
                    }
                }
            }
            return true
        },



        refreshClick() {
            if(!this.isStepSlotSelection) return false
            this.currentStep = ''
            this.loading = true
            this.refreshInitValue()
        },

        getDefaultStaff(){
            if(this.viewData.staffs!== undefined && this.viewData.staffs.length > 0){
                return this.viewData.staffs[0]
            }
        },

        loadedAfter() {
            this.time_format = convertDateFormatPHPtoMoment(this.viewData.time_format)
            this.date_format = convertDateFormatPHPtoMoment(this.viewData.date_format)

            this.startDay = this.viewData.week_starts_on
            let firstStaff = this.getDefaultStaff()
            this.intervalsCollection = new Intervals(this.viewData.availability[firstStaff.id])

            this.setMomentLocale()

            this.dataloaded = true
            
            this.setServiceDurationLocation()

            this.setComponentLists()

            if(this.rescheduling) {
                this.currentStep = 'BookingCalendar'

                this.service = this.rescheduleData.service
                let buffer_time_sec = this.rescheduleData.appointment.options.buffer_time !== undefined ? parseInt(this.rescheduleData.appointment.options.buffer_time) *60:0
                this.duration = (this.rescheduleData.appointment.end_at - this.rescheduleData.appointment.start_at - buffer_time_sec)/60
                this.location = this.rescheduleData.location

            }else{
                this.currentStep = window.wappointmentExtends.filter('BFFirstStep','BookingCalendar', {service:this.service, duration:this.duration, location: this.location})
            }
            
            this.loadStep(this.currentStep)

            if(this.loadedInit !== undefined){
                this.loadedInit(this.step)
            }
            
        },

        fetchFormattedDate(){
            if(this.selectedSlot === false) {
                this.converted = false
            }
            if(this.selectedSlot !== false && this.converted === false){
                if(this.viewData.site_lang!== 'en' && browserLang().substr(0,2)!=='en'){ // if the browser is not english we fetch for a localized date
                    this.convertDateRequest({
                        timezone: this.timeprops.currentTz,
                        timestamp: this.selectedSlot
                    }).then(this.convertedDate) 
                }else{
                    this.converted = this.getMoment(this.selectedSlot, this.currentTz).format(this.fullDateFormat)
                }
            }
            
        },

        setServiceDurationLocation(){
            this.services = this.viewData.services

            this.service = window.wappointmentExtends.filter('serviceDefault', this.getDefaultService(), {services: this.services})
            
            if(this.service !== false){
                this.duration = this.service.duration !== undefined ? this.service.duration : window.wappointmentExtends.filter('durationDefault', this.service)
                this.location = this.service.type !== undefined ? this.service.type : window.wappointmentExtends.filter('locationDefault', this.service)
            } 
        },

        setComponentLists(){
            let componentsList = {
                BookingCalendar: {
                    name: 'BookingCalendar',
                    conditions: {
                        'serviceSelected':true,
                        'durationSelected':true,
                        'locationSelected':true,
                        'appointmentSaved':false,
                    },
                    props: {
                        service: 'service',
                        initIntervalsCollection: 'intervalsCollection',
                        options: 'options',
                        duration:"duration",
                        timeprops: 'timeprops',
                        staffs: 'getStaffs',
                        viewData: 'viewData',
                        rescheduling: 'rescheduling'
                    },
                    listeners: {
                        selectSlot:'childChangedStep',
                        loading: 'childChangedData',
                    },
                    relations:{
                        'next': 'BookingFormInputs',
                    }
                },
                RescheduleConfirm: {
                    name: 'RescheduleConfirm',
                    conditions: {
                        'slotSelected':true,
                        'rescheduling':true,
                        'appointmentSaved':false,
                    },
                    props: {
                        selectedSlot:"selectedSlot",
                        timeprops: 'timeprops', 
                        options:"options",
                        rescheduleData: 'rescheduleData'
                    },
                    listeners: {
                        back:'childChangedStep',
                        loading: 'childChangedData',
                        confirmed: 'childChangedStep',
                        serviceError: 'serviceError'
                    },
                    relations:{
                        'next': 'BookingFormConfirmation',
                        'prev': 'BookingCalendar',
                    }
                },
                BookingFormInputs: {
                    name: 'BookingFormInputs',
                    conditions: {
                        'slotSelected':true,
                        'rescheduling':false,
                        'appointmentSaved':false,
                    },
                    props: {
                        selectedSlot:"selectedSlot",
                        timeprops: 'timeprops', 
                        service:"service",
                        duration:"duration",
                        location:"location",
                        errors:"errorMessages",
                        data:"dataSent",
                        options:"options",
                        relatedComps: 'relatedComps', 
                        appointment_starts_at: 'appointmentStartsAt',
                    },
                    listeners: {
                        back:'childChangedStep',
                        loading: 'childChangedData',
                        confirmed: 'childChangedStep',
                        serviceError: 'serviceError'
                    },
                    relations:{
                        'next': 'BookingFormConfirmation',
                        'prev': 'BookingCalendar',
                    }
                },
                
                BookingFormConfirmation: {
                    name: 'BookingFormConfirmation',
                    conditions: {
                        'appointmentSaved':true,
                    },
                    props: {
                        timeprops: 'timeprops', 
                        service:"service",
                        errors:"errorMessages",
                        appointment:"appointmentSavedData",
                        result:"dataSent",
                        options:"options",
                        isApprovalManual:"isApprovalManual",
                        staff:"staff", 
                        appointment_starts_at: 'appointmentStartsAt',
                    },
                    listeners: {
                        loading: 'childChangedData',
                    }

                },
            }
            
            this.componentsList = window.wappointmentExtends.filter('componentsList', componentsList,
             {service: this.service, rescheduling:this.rescheduling} )
        },
        
        
    }
}
</script>
<style>
.wap-bf{
    transition: box-shadow ease-in-out .3s;
}
.wap-bf.show{
    box-shadow: 0px 8px 10px 0 rgba(0,0,0,.08);
}


.wap-front .calendarMonth .ddays {
    min-height: 1.1em;
    margin: .4em 0;
}

.wap-front .mr-2{
    margin-right: .3em !important;
}
.wap-front .mb-2{
    margin-bottom: .3em !important;
}
.wap-front .p-2 {
    padding: .5em !important;
}
.wap-front .pl-2 {
    padding-left: .5em !important;
}
.wap-front .slotsPane{
    box-shadow: inset 0px 0px 10px rgba(0,0,0,.14);
    border-radius: .2em;
    overflow: hidden;
    margin-top: .3em;
}
.wap-front [data-tt] {
  position: relative;
  z-index: 2;
  cursor: pointer;
}

.wap-front [data-tt]:before,
.wap-front [data-tt]:after {
  visibility: hidden;
  opacity: 0;
  pointer-events: none;
}

.wap-front [data-tt]:before {
  position: absolute;
  bottom: 150%;
  left: 50%;
  margin-bottom: -5px;
  margin-left: -48px;
  padding: 7px;
  width: auto;
  border-radius: 3px;
  content: attr(data-tt);
  text-align: center;
  font-size: 14px;
  line-height: 1.2;
}
.wap-front .first-day[data-tt]::before {
  left: calc( 100% + 100%/3);
  right: auto;
}
.wap-front .last-day[data-tt]::before {
  right: calc( -100% /3 );
  left: auto;
}


.wap-front [data-tt]:after {
  position: absolute;
  bottom: 150%;
  left: 50%;
  margin-left: -5px;
  width: 0;
  border-right: 5px solid transparent;
  border-left: 5px solid transparent;
  content: " ";
  font-size: 0;
  line-height: 0;
  margin-bottom: -10px;
}

.wap-front [data-tt]:hover:before,
.wap-front [data-tt]:hover:after,
.wap-front [data-tt].hover:before,
.wap-front [data-tt].hover:after {
  visibility: visible;
  opacity: 1;
}


.wap-bf button {
    font-size: .7em;
}

.wap-front .confirm-form form{
    text-align: left;
}


.wrap-calendar p, .wrap-calendar hr{
    margin: 0 0 .4em;
    font-size: .9em;
}

.wap-front .dayselected{
    font-weight: bold;
    border-radius: 2em;
    box-shadow: 0px 0px 6px rgba(0,0,0,.1);
}

.wap-front .dayselected span{
    text-decoration: none !important;
    color:#fff;
}

.wap-front .w100{
    width:100%;
}

.wap-front .wbtn-confirm span.wbtn, 
.wap-front .wbtn-confirm .wbtn.wbtn-secondary {
    font-size: 1em;
    margin: 0 !important;
}

.wap-front .wbtn-confirm .wbtn-secondary.wbtn {
    margin-right: .5em !important;
}

.slide-fade-enter-active, 
.slide-fade-leave-active, 
.slide-fade-side-sm-right-enter-active, 
.slide-fade-side-sm-right-leave-active,
.slide-fade-side-sm-left-enter-active,  
.slide-fade-side-sm-left-leave-active{
  transition: all .3s ease;
}

.slide-fade-sm-enter-active, 
.slide-fade-sm-leave-active 
{
  transition: all .6s ease;
}

.slide-fade-enter, 
.slide-fade-leave-to {
  transform: translateY(100px);
  opacity: 0;
}

.slide-fade-sm-enter-to, 
.slide-fade-sm-leave {
  max-height: 100vh;
  opacity:1;
}
.slide-fade-sm-enter, 
.slide-fade-sm-leave-to {
  max-height: 0;
  opacity:0;
}

.slide-fade-side-sm-right-leave-to, 
.slide-fade-side-sm-left-enter  {
  transform: translateX(40px);
  opacity: 0;
}
.slide-fade-side-sm-right-enter, 
.slide-fade-side-sm-left-leave-to  {
  transform: translateX(-40px);
  opacity: 0;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to  {
  opacity: 0;
}

.wap-bf .field-required inoput{
    transition: border-right ease-in-out .3s;
}

.wap-front .form-control, .wap-front .phone-field{
    width: 100%;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25em;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.wap-front .form-control{
    display: block;
    padding: .375em .75em;
}

.wap-front .phone-field input.tel, .wap-front input.form-control {
    font-size: 16px;
    height: calc(2.25em + 2px);
}

.wap-front .form-control::-ms-expand {
  background-color: transparent;
  border: 0;
}

.wap-front .form-control::-webkit-input-placeholder {
  color: #6c757d;
  opacity: 1;
}

.wap-front .form-control::-moz-placeholder {
  color: #6c757d;
  opacity: 1;
}

.wap-front .form-control:-ms-input-placeholder {
  color: #6c757d;
  opacity: 1;
}

.wap-front .form-control::-ms-input-placeholder {
  color: #6c757d;
  opacity: 1;
}

.wap-front .form-control::placeholder {
  color: #6c757d;
  opacity: 1;
}

.wap-front .form-control:disabled, .form-control[readonly] {
  background-color: #e9ecef;
  opacity: 1;
}

.wap-front .field-required .phone-field{
    width: 100%;
}

.wap-front .mr-0 {
    margin-right: 0 !important;
}

.wap-front .m-0 {
    margin: 0 !important;
}

.wap-front .hide-loading{
    display:none;
}
.wap-front .max400{
    max-width:400px;
    margin:0 auto;
}
</style>