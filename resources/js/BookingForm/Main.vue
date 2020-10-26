<template>
    <div class="wap-bf" :class="{show: canBeBooked, 'has-scroll':requiresScroll}">
        <template v-if="canBeBooked">
            <BookingFormHeader :staffs="getStaffs" 
            :isStepSlotSelection="isStepSlotSelection"
            :options="options"
            :service="service" 
            :services="services"
            :duration="duration" 
            :location="location"
            :rescheduling="rescheduling"
            :appointmentSaved="appointmentSaved"
            @refreshed="refreshClick"
            @changeService="childChangedStep"
            @changeDuration="childChangedStep"
            @changeLocation="childChangedStep"
            @changeStaff="childChangedStep"
            />
            <div class="wap-form-body" :id="getWapBodyId" >
                <BookingFormSummary v-if="!appointmentSaved && !isCompactHeader"
                :isStepSlotSelection="isStepSlotSelection"
                :options="options"
                :service="service" 
                :duration="duration" 
                :services="services"
                :rescheduling="rescheduling"
                :startsAt="appointmentStartsAt"
                :location="location"
                :appointmentSaved="appointmentSaved"
                @refreshed="refreshClick"
                @changeService="childChangedStep"
                @changeDuration="childChangedStep"
                @changeLocation="childChangedStep"
                @changeStaff="childChangedStep"
                />

                <div class="wrap-calendar p-2" :class="'step-'+currentStep">
                    <div v-if="loading">
                        <Loader></Loader>
                    </div>
                    <div :class="{'hide-loading':loading}">
                        <div v-if="currentStep == loadingStep && isCompVisible(currentStep)">
                            <component :is="getComp(currentStep)"
                            @hook:mounted="checkIfRequiresScrollDelay"
                            @hook:updated="checkIfRequiresScrollDelay"
                            v-bind="getCompProp(currentStep)"
                            v-on="getCompListeners(currentStep)"
                            :relations="getCompRelations(currentStep)"
                            ></component>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div v-else>
            <div v-if="dataloaded" class="wappointment-errors">
                <div v-if="service">No appointments available</div>
                <div v-else>Service not ready</div>
            </div>
            <template v-else>
                <div class="wappointment-errors" v-if="errorMessages.length > 0">
                    <div v-for="errorM in errorMessages">{{errorM}}</div>
                </div>
                <div v-else><Loader></Loader></div>
            </template>
        </div>
    </div>
</template>

<script>
import AbstractFront from './AbstractFront'
import Intervals from '../Standalone/intervals'
import Colors from "../Modules/Colors"
import Dates from "../Modules/Dates"
import BookingFormConfirmation from './Confirmation'
import RescheduleConfirm from './RescheduleConfirm'
import BookingCalendar from './Calendar'
import BookingFormInputs from './Form'
import BookingFormHeader from './Header'
import BookingFormSummary from './AppointmentSummary'
import DurationCell from './DurationCell'
BookingFormHeader.components = {DurationCell}
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import convertDateFormatPHPtoJS from '../Standalone/convertDateFormatPHPtoJS'
import browserLang from '../Standalone/browserLang'
import AppointmentTypeSelection from './AppointmentTypeSelection'

let compDeclared = {
    'BookingFormConfirmation' : BookingFormConfirmation,
    'RescheduleConfirm': RescheduleConfirm,
    'BookingCalendar': BookingCalendar,
    'BookingFormInputs':BookingFormInputs,
    'BookingFormHeader': BookingFormHeader,
    'DurationCell': DurationCell,
    'abstractFront':AbstractFront,
    'BookingFormSummary': BookingFormSummary,
    'AppointmentTypeSelection': AppointmentTypeSelection
}
compDeclared = window.wappointmentExtends.filter('BookingFormComp', compDeclared )

export default {
     extends: AbstractFront,
     mixins: [Colors, Dates],
     props: ['serviceAction', 'appointmentkey', 'rescheduleData', 'options', 'step','passedDataSent','wrapperid'],
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
        converted:false,
        requiresScroll: false
    }),

    mounted () {
        
        this.refreshInitValue()
        this.currentTz = this.tzGuess()
        this.createdAt = this.getUnixNow()
        
    
        if(this.step !== null) {
            this.requiresScroll = true //booking widget editor requires scroll always
        }
        window.addEventListener('resize', this.windowResized);
        
    },
    beforeDestroy(){
        window.removeEventListener('resize', this.windowResized);
    },

    computed: {
        isCompactHeader(){
            return this.options.general === undefined || [undefined, false].indexOf(this.options.general.check_header_compact_mode) === -1
        },
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
       },
       getWapBodyId(){
           return 'wapbody'+this.wrapperid
       }
    },
    methods: {
        windowResized(){
            this.checkIfRequiresScroll()
        },
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
        checkIfRequiresScrollDelay(){
            //console.log('first')
            setTimeout(this.checkIfRequiresScroll, 200)
        },
        checkIfRequiresScroll(){
            //console.log('second')
            if(this.step !== null) {
                return true //booking widget editor requires scroll always
            }
            let wrapperDiv = document.getElementById(this.wrapperid)
            let wrapperDivHead = wrapperDiv.getElementsByClassName("wap-head")

            let headHeight = 60
            if(wrapperDivHead!== undefined && Array.isArray(wrapperDivHead) && wrapperDivHead[0] !== undefined) {
                headHeight = wrapperDivHead[0].scrollHeight
            }

            let parentWindowHeight = wrapperDiv.scrollHeight - headHeight
            let heightDiv = document.getElementById(this.getWapBodyId).scrollHeight
/*             let heightWindow = window.innerHeight / 100 * 95
            console.log(heightDiv, heightWindow, parentWindowHeight) */
            if(heightDiv > parentWindowHeight){
                //add scrollbar
                //console.log(' TRUE 85vh', heightDiv ,heightWindow)
                this.requiresScroll = true
            }else{
                //remove scrollbar
                //console.log(' FALSE 85vh',  heightDiv ,heightWindow)
                this.requiresScroll = false
            }
        },
        childChangedStep(newStep, dataChanged){
            if(typeof dataChanged == 'object' && Object.keys(dataChanged).length > 0) {
                this.childChangedData(dataChanged)
            }
            this.currentStep = newStep
            setTimeout(this.loadStep.bind(null,newStep), 100)
            this.$emit('changedStep',newStep)
        },

        selectedLocation(location){
            this.location = location
        },
        childChangedData(dataChanged){
            for (const key in dataChanged) {
                if (dataChanged.hasOwnProperty(key)) {
                    this[key] = dataChanged[key]
                }
            }
            this.autoSelectLocation()
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
            if(!this.isStepSlotSelection) {
                return false
            }
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
                this.autoSelectLocation()
            }
            this.$emit('changedStep',this.currentStep)
            this.loadStep(this.currentStep)

            if(this.loadedInit !== undefined){
                this.loadedInit(this.step)
            }
            
        },

        autoSelectLocation(){
            if(Array.isArray(this.service.type) && this.service.type.length == 1){
                this.location = this.service.type[0]
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
                this.location = this.service.type !== undefined ? '' : window.wappointmentExtends.filter('locationDefault', this.service)
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
                        selectedLocation: 'selectedLocation',
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
    overflow: hidden;
    position: relative;
    min-width: 280px;
}


.wap-front .calendarMonth .ddays {
    min-height: 1.1em;
    margin: .4em 0;
}

.wap-front .mr-2{
    margin-right: .3em !important;
}
.wap-front .ml-2{
    margin-left: .3em !important;
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

.wap-front .confirmation-cell .success,
.wap-front .wap-form-body .success .text-conf {
    color: var(--wappo-success-tx);
}

.wclosable .wclose::before, 
.wclosable .wclose::after {
    background-color: var(--wappo-pri-tx);
}

.wclosable .wclose:hover::before, 
.wclosable .wclose:hover::after {
    background-color: var(--wappo-pri-tx-lt);
}

.wap-bf button {
    font-size: .7em;
}

.wap-front .confirm-form form{
    text-align: left;
}

.wap-front .wap-form-body .timezone {
    text-align: center;
    font-size: .75em;
}
.wap-front.large-version .wrap-calendar {
    max-height: none;
}
.wap-front .wrap-calendar {
    border-top: none;
}
.wap-front .wrap-calendar.step-BookingCalendar .slotsPane {
    overflow-y: scroll;
    overflow-x: hidden;
    max-height: 200px;
}


.wap-form-body p, .wap-form-body hr{
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

.wap-front .has-scroll .wap-form-body{
    overflow-y: scroll;
}

.wap-front .wap-form-body,
.wap-front .wlabel,
.wap-front .wrap-calendar div
{
    color: var(--wappo-body-tx);
}
.wbtn.wbtn-secondary .wduration,
.wbtn.wbtn-secondary .wap-img svg,
.wbtn.wbtn-secondary .service-label .service-name,
.wbtn.wbtn-secondary .service-label .service-price,
.wbtn.wbtn-secondary .service-label .service-price .price-currency{
    color: var(--wappo-sec-tx); 
}

.wap-front .wap-head .staff-desc, 
.wap-front .wap-head strong {
    color: var(--wappo-header-tx);
}

.wap-front .wap-head img{
    border-radius: 50%;
}
.wap-front .wbtn-cell{
    text-align: center;
    padding: .4em;
}

.wap-front .form-control:focus {
    outline: 0;
}

.wap-front .confirmation-cell .success {
    padding: .2em .8em;
    border-radius: 5px;
    overflow: hidden;
    font-size: .8em;
}

.wap-booking-fields .wap-field{
    margin-bottom: .4em !important;
}

.wap-wid.wclosable > .wclose:hover::before, 
.wap-wid.wclosable > .wclose:hover::after {
    background-color: var(--wappo-header-tx);
}

.wap-front .wappointment-errors{
    border-radius:.25em;
    padding: .3em;
    margin: .5em 0;
}


/* @media only screen and (max-width: 500px) {
    .wap-front .has-scroll .wap-form-body{
        max-height:75vh;
    }
} */

</style>