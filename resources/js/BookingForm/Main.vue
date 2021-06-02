<template>
    <div class="wap-bf" :class="{show: canBeBooked, 'has-scroll':requiresScroll}">
        <template v-if="canBeBooked">
            <BookingFormHeader v-if="showHeader && noStaffSelectionNeeded" 
            :isStepSlotSelection="isStepSlotSelection"
            :options="options"
            :attributesEl="attributesEl"
            :service="service" 
            :services="services"
            :staffs="getStaffs" 
            :duration="duration" 
            :location="location"
            :rescheduling="rescheduling"
            :appointmentSaved="appointmentSaved"
            :staff="selectedStaff"
            :mustSelectStaff="mustSelectStaff"
            @changeService="childChangedStep"
            @changeDuration="childChangedStep"
            @changeLocation="childChangedStep"
            @showStaffScreen="childChangedStep"
            />
            <div class="wap-form-body" :id="getWapBodyId" >
                <BookingFormSummary v-if="!appointmentSaved && !isCompactHeader"
                :isStepSlotSelection="isStepSlotSelection"
                :options="options"
                :attributesEl="attributesEl"
                :service="service" 
                :duration="duration" 
                :services="services"
                :staffs="getStaffs"
                :rescheduling="rescheduling"
                :startsAt="appointmentStartsAt"
                :location="location"
                :appointmentSaved="appointmentSaved"
                @changeService="childChangedStep"
                @changeDuration="childChangedStep"
                @changeLocation="childChangedStep"
                />
                <div class="wrap-calendar p-2" :class="'step-'+currentStep">
                    <div v-if="loading">
                        <Loader />
                    </div>
                    <div v-if="currentStep!=''" :class="{'hide-loading':loading}">
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
                <div>{{options.general.noappointments}}</div>
            </div>
            <template v-else>
                <div class="wappointment-errors" v-if="errorMessages.length > 0">
                    <div v-for="errorM in errorMessages">{{errorM}}</div>
                </div>
                <div v-else><Loader /></div>
            </template>
        </div>
    </div>
</template>

<script>
import AbstractFront from './AbstractFront'
import Intervals from '../Standalone/intervals'
import Colors from '../Modules/Colors'
import Dates from '../Modules/Dates'
import BookingFormConfirmation from './Confirmation'
import RescheduleConfirm from './RescheduleConfirm'
import BookingCalendar from './Calendar'
import BookingFormInputs from './Form'
import BookingFormHeader from './Header'
import BookingFormSummary from './AppointmentSummary'
import DurationCell from './DurationCell'
BookingFormHeader.components = {DurationCell}
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import browserLang from '../Standalone/browserLang'
import AppointmentTypeSelection from './AppointmentTypeSelection'
import BookingServiceSelection from './ServiceSelection'
import BookingDurationSelection from './DurationSelection'
import BookingLocationSelection from './LocationSelection'
import BookingStaffSelection from './StaffSelection'
import BookingPaymentStep from './Payment'
import MixinLegacy from './MixinLegacy'

let compDeclared = {
    'BookingFormConfirmation' : BookingFormConfirmation,
    'RescheduleConfirm': RescheduleConfirm,
    'BookingCalendar': BookingCalendar,
    'BookingFormInputs':BookingFormInputs,
    'BookingFormHeader': BookingFormHeader,
    'BookingServiceSelection': BookingServiceSelection,
    'BookingDurationSelection': BookingDurationSelection,
    'BookingLocationSelection': BookingLocationSelection,
    'BookingStaffSelection': BookingStaffSelection,
    'BookingPaymentStep': BookingPaymentStep,
    'DurationCell': DurationCell,
    'abstractFront':AbstractFront,
    'BookingFormSummary': BookingFormSummary,
    'AppointmentTypeSelection': AppointmentTypeSelection
}
compDeclared = window.wappointmentExtends.filter('BookingFormComp', compDeclared )
let mixinsDeclared = window.wappointmentExtends.filter('BookingFormMixins', [Colors, Dates, MixinLegacy] )
export default {
     extends: AbstractFront,
     mixins: mixinsDeclared,
     props: ['serviceAction', 'appointmentkey', 'rescheduleData', 'options', 'step','passedDataSent','wrapperid', 'demoAs', 'attributesEl'],
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
        requiresScroll: false,
        selectedStaff: null,
        showHeader:true,
        checkCacheIntervalid: false,
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
        showStaffSelection(){
            return this.mustSelectStaff || this.loadingStep == "BookingStaffSelection"
        },
        
        mustSelectStaff(){
            return this.attributesEl !== undefined && this.attributesEl.staffPage !== undefined && this.attributesEl.staffPage === true
        },
        staffIsSelected(){
            return [null, undefined, false].indexOf(this.selectedStaff) === -1
        },

        noStaffSelectionNeeded(){
            return !this.showStaffSelection || (this.showStaffSelection && (this.staffIsSelected && this.bfdemo !== true))
        },
        isLegacyOrNotServiceSuite(){
            return this.isLegacy || this.service.type !== undefined
        },
        isCompactHeader(){
            return this.options.general === undefined || !this.__isEmpty(this.options.general.check_header_compact_mode)
        },
        appointmentStartsAt(){
            return this.converted 
        },
        serviceSelected(){
            return this.service !== false
        },
        durationSelected(){
            return this.duration !== false
        },
        locationSelected(){
            return this.isLegacyOrNotServiceSuite || this.location !== false
        },
        slotSelected(){
            return this.selectedSlot !== false && this.selectedSlot > 0
        },
        serviceIsNotFree(){
            return this.service !== false && this.service.options.woo_sellable === true
            //return this.serviceUNotFree || this.serviceMNotFree
        },
        serviceUNotFree(){
            return this.service !== false && this.service.options.woo_sellable === true && this.service.options.woo_price > 0
        },
        serviceMNotFree(){
            return this.service !== false && this.service.options.woo_sellable === true && this.service.options.durations !== undefined && this.service.options.durations[0].woo_price > 0
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
           return this.dataloaded && 
           (
               (this.intervalsCollection!== null && this.intervalsCollection.intervals.length > 0) 
           || 
                (this.mustSelectStaff && !this.staffIsSelected)
           )
       },
       
       staff(){
           return this.selectedStaff
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
           return this.__get(this,'viewData.staffs', [])
       },
       getWapBodyId(){
           return 'wapbody'+this.wrapperid
       }
    },
    methods: {
        checkForCache(){
            return window.wappoAvailability !== undefined? window.wappoAvailability:false
        },
        cacheValue(){
            window.wappoAvailability = this.viewData
            setTimeout(this.clearCache, 120000)
            window.wappoAvailabilityRunning = undefined
        },
        clearCache(){
            window.wappoAvailability = undefined
        },
        isCacheReady(){
            if(window.wappoAvailabilityRunning !== true){
                clearInterval(this.checkCacheIntervalid)
                this.refreshInitValue()
            }
        },
        refreshInitValue(){
            if(window.wappoAvailabilityRunning === true){
                return this.checkCacheIntervalid = setInterval(this.isCacheReady, 400);
            }
            let cacheFound = this.checkForCache()
            if(cacheFound){
                return this.loaded({data:cacheFound})
            }
            this.runRequestAvail()
        },
        runRequestAvail(){
            window.wappoAvailabilityRunning = true
            this.loading = true
            this.initValueRequest()
            .then(this.loaded)
            .catch(this.serviceError)
        },

        setStaff(newStaff){
            this.selectedStaff = newStaff
            this.setAvailableServices()
            this.refreshAvail()
            this.autoSelectingOptions()
        },
        autoSelectingOptions(){
            this.autoSelService()
            this.autoSelectDuration() 
            this.autoSelectModality()
        },
        changeStaff(newStaff){
            this.service = false
            this.location = false
            this.setStaff(newStaff)
            
            this.showHeader = false

            this.currentStep = ''

            setTimeout(this.showHeaderLater.bind(null, this.selectFirstStep('BookingServiceSelection', 
            {
                service: this.service, 
                location:this.location,
                duration: this.duration
            }
            )), 100)
            
        },

        showHeaderLater(newStep){
            this.showHeader = true
            this.childChangedStep(newStep)
        },
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
            setTimeout(this.checkIfRequiresScroll, 200)
        },
        checkIfRequiresScroll(){
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

            this.requiresScroll = heightDiv > parentWindowHeight // add or remove scrollbar
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
                    if(this.bfdemo !== true && this[keyCondition] !== conditions[keyCondition]) {
                        if(this.componentsList[component_name].skip !== undefined){
                            if(this.conditionSkipPass(component_name)){
                                this.childChangedStep(this.componentsList[component_name].relations.next)
                            }
                        }
                        return false
                    }
                }
            }
            return true
        },
        conditionSkipPass(component_name){
            let skipConditions = Object.keys(this.componentsList[component_name].skip)
            for (let j = 0; j < skipConditions.length; j++) {
                const keyConditionSkip = skipConditions[j]
                if(this[keyConditionSkip] !== this.componentsList[component_name].skip[keyConditionSkip]) {
                    return false
                }
            }
            return true
        },


        getDefaultStaff(){
            let ordered = []
            if(this.viewData.staffs!== undefined && this.viewData.staffs.length > 1){
                for (let i = 0; i < this.viewData.staffs.length; i++) {
                    if(this.viewData.staffs[i].services.length > 0 && this.viewData.staffs[i].availability.length > 0){
                        ordered.push({
                            id: i,
                            start:this.viewData.staffs[i].availability[0][0],
                            end:this.viewData.staffs[i].availability[0][1]
                        }) 
                    
                    }
                }
                ordered.sort((a, b) => a.start > b.start)
                return this.viewData.staffs[ordered[0].id]
            }else{
                return this.viewData.staffs[0]
            }
        },
        
        refreshAvail(){
            this.intervalsCollection = new Intervals(this.selectedStaff.availability)
        },

        loadedAfter() {
            this.cacheValue()
            this.time_format = convertDateFormatPHPtoMoment(this.viewData.time_format)
            this.date_format = convertDateFormatPHPtoMoment(this.viewData.date_format)

            this.startDay = this.viewData.week_starts_on
            
            if(this.viewData.staffs.length == 0){
                this.dataloaded = true
                return
            }

            this.dataloaded = true
            if(!this.mustSelectStaff || this.mustSelectStaff && this.getStaffs.length == 1){
                this.selectedStaff = this.getDefaultStaff()
                this.refreshAvail()
                this.setMomentLocale()
                this.initServiceStaffDurationLocation()
            }
    
            this.setComponentLists()

            if(this.rescheduling) {
                this.currentStep = 'BookingCalendar'

                this.service = this.rescheduleData.service
                let buffer_time_sec = this.rescheduleData.appointment.options.buffer_time !== undefined ? parseInt(this.rescheduleData.appointment.options.buffer_time) *60:0
                this.duration = (this.rescheduleData.appointment.end_at - this.rescheduleData.appointment.start_at - buffer_time_sec)/60
                this.location = this.rescheduleData.location

            }else{
                let stepdata = {service:this.service, duration:this.duration, location: this.location}

                let stepfirst = window.wappointmentExtends.filter('BFFirstStep','BookingCalendar', stepdata)
                this.currentStep = this.selectFirstStep(stepfirst, stepdata)
                this.autoSelectLocation()
            }
            this.$emit('changedStep',this.currentStep)
            this.loadStep(this.currentStep)

            if(this.loadedInit !== undefined){
                this.loadedInit(this.step)
            }
        },

        selectFirstStep(step_name, params) {
            if(this.isLegacyOrNotServiceSuite === false){
                return params.service === false? this.getStepFirst():this.getStepAfterService(params)
            }
            return step_name
        },
        getStepFirst(){
            return this.noStaffSelectionNeeded ? 'BookingServiceSelection': 'BookingStaffSelection'
        },
        getStepAfterService(params){
            return params.duration === false ? 'BookingDurationSelection': this.getStepAfterDuration(params)
        },
        getStepAfterDuration(params){
            return params.location === false ? 'BookingLocationSelection': 'BookingCalendar'
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

        setAvailableServices(){
            if(this.viewData.services.length == 1 && this.viewData.services[0].type !== undefined){
                this.services = this.viewData.services.filter(e => true)
            }else{
                let services_id = this.selectedStaff.services
                this.services =  this.viewData.services.filter(e => services_id.indexOf(e.id) !== -1)
            }
            
        },
        autoSelService(){
            if(this.isLegacyOrNotServiceSuite){
                this.service = window.wappointmentExtends.filter('serviceDefault', this.getDefaultService(), {services: this.services})
            }else{
                if(this.services.length == 1){
                    this.service = this.services[0]
                }else{
                    this.testLockedService()
                }
            }
        },
        demoAutoSelect(){
            if(this.demoAs === true){
                if( this.service === false ){
                    this.service = this.services[0]
                }
                if(this.service !== false){
                    this.duration = this.getFirstDuration(this.service)
                    this.location = this.service.type !== undefined ? false : (this.service.locations.length === 1 ? this.service.locations[0]:false) 
                } 
                if(this.service!== false && !this.location){
                    this.location = this.service.locations[0]
                }
            }
        },
        autoSelectDuration(){
            if(this.service !== false && !this.duration && this.service.type === undefined && this.service.options.durations.length == 1){
                this.duration = this.service.options.durations[0].duration
            }
        },
        autoSelectModality(){
            if(this.service !== false && !this.location && this.service.type === undefined && this.service.locations.length == 1){
                this.location = this.service.locations[0]
            }
        },
        initServiceStaffDurationLocation(){
            this.setAvailableServices()
            this.testLockedStaff()
            this.autoSelectingOptions()
            this.demoAutoSelect() 
        },
        testLockedStaff(){
            if(this.attributesEl !== undefined && 
            this.attributesEl.staffSelection !== undefined){
                for (let i = 0; i < this.viewData.staffs.length; i++) {
                    if(parseInt(this.attributesEl.staffSelection) === parseInt(this.viewData.staffs[i].id)){
                        return this.setStaff(this.viewData.staffs[i])
                    }
                }
            }
        },
        testLockedService(){
            if(this.attributesEl !== undefined && 
                this.attributesEl.serviceSelection !== undefined){
                    let lockToServiceID = this.attributesEl.serviceSelection
                    if([undefined,false,''].indexOf(lockToServiceID) === -1){
                        this.service = this.services.find(e => e.id == lockToServiceID)
                    }
                }
        },

        getFirstDuration(service){
            return this.__get(service, 'options.durations.0.duration') || service.duration
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
                        next: 'BookingFormInputs',
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
                        next: 'BookingFormConfirmation',
                        prev: 'BookingCalendar',
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
                        selectedStaff:"selectedStaff",
                        timeprops: 'timeprops', 
                        service:"service",
                        duration:"duration",
                        location:"location",
                        errors:"errorMessages",
                        data:"dataSent",
                        options:"options",
                        relatedComps: 'relatedComps', 
                        appointment_starts_at: 'appointmentStartsAt',
                        custom_fields: 'viewData.custom_fields',
                        staffs:  'viewData.staffs'
                    },
                    listeners: {
                        back:'childChangedStep',
                        selectedLocation: 'selectedLocation',
                        loading: 'childChangedData',
                        confirmed: 'childChangedStep',
                        serviceError: 'serviceError'
                    },
                    relations:{
                        next: 'BookingFormConfirmation',
                        prev: 'BookingCalendar',
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
                        staff:"selectedStaff", 
                        appointment_starts_at: 'appointmentStartsAt',
                    },
                    listeners: {
                        loading: 'childChangedData',
                    }

                },
            }
            if(!this.isLegacyOrNotServiceSuite){
                componentsList = this.updateComponentList(componentsList)
                componentsList = this.setPaymentStep(componentsList)
            }
            this.componentsList = window.wappointmentExtends.filter('componentsList', componentsList,
             {service: this.service, rescheduling:this.rescheduling} )
        },

        setPaymentStep(componentsList){
            componentsList['BookingPaymentStep'] = {
                name: 'BookingPaymentStep',
                conditions: {
                    'appointmentSaved':true,
                    'serviceIsNotFree':true,
                },
                skip: {
                'serviceIsNotFree':false,
                },
                props: {
                    options:"options",
                    appointmentKey:"appointmentKey",
                    appointmentData:"appointmentSavedData",
                    order:"order",
                    service:"service"
                },
                listeners: {
                    confirmedPayment:'childChangedStep',
                    cancelledPayment:'childChangedStep',
                    loading: 'childChangedData',
                },
                relations: {
                    prev: 'BookingFormInputs',
                    next: 'BookingFormConfirmation'
                }
            }
            return componentsList
        },

        updateComponentList(componentsList){

            componentsList['BookingFormInputs'].props.custom_fields = "viewData.custom_fields"

            componentsList['BookingCalendar'].props.location = "location"
            componentsList['BookingCalendar'].props.viewData = "viewData"

            componentsList['BookingStaffSelection'] = {
                name: 'BookingStaffSelection',
                conditions: {
                    'serviceSelected': false,
                    'appointmentSaved': false,
                    'rescheduling': false,
                    'showStaffSelection': true
                },
                props: {
                    calendars: 'viewData.staffs',
                    options: 'options',
                    timeprops: 'timeprops',
                    viewData: 'viewData',
                },
                listeners: {
                    staffSelected:'changeStaff'
                },
                relations:{
                    next: 'BookingServiceSelection',
                }
            }
            
            componentsList['BookingServiceSelection'] = {
                name: 'BookingServiceSelection',
                conditions: {
                    'serviceSelected':false,
                    'appointmentSaved':false,
                    'rescheduling':false,
                    'noStaffSelectionNeeded': true
                },
                props: {
                    services:"services",
                    options: 'options',
                    viewData: 'viewData',
                },
                listeners: {
                    serviceSelected:'childChangedStep'
                },
                relations:{
                    next: 'BookingDurationSelection',
                }
            }

            componentsList['BookingDurationSelection'] = {
                name: 'BookingDurationSelection',
                conditions: {
                    'serviceSelected':true,
                    'durationSelected':false,
                    'appointmentSaved':false,
                    'rescheduling':false,
                },
                props: {
                    service:"service",
                    options: 'options'
                },
                listeners: {
                    durationSelected:'childChangedStep',
                    backToService:'childChangedStep'
                },
                relations:{
                    next: 'BookingLocationSelection',
                    prev: 'BookingServiceSelection',
                }
            }

            componentsList['BookingLocationSelection'] = {
                name: 'BookingLocationSelection',
                conditions: {
                    'serviceSelected':true,
                    'durationSelected':true,
                    'locationSelected':false,
                    'appointmentSaved':false,
                    'rescheduling':false,
                },
                props: {
                    service:"service",
                    options: 'options'
                },
                listeners: {
                    locationSelected:'childChangedStep',
                    backToDuration:'childChangedStep'
                },
                relations:{
                    next: 'BookingCalendar',
                    prev: 'BookingDurationSelection',
                }
            }
            return componentsList
        }
        
        
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
}

.d-flex.ddays > div{
    width: 14.3%;
    text-align: center;
}
.wap-front .calendarMonth .ddays {
    min-height: 1.1em;
    margin: .4em 0;
}

.wap-front .mr-2,
.wap-front .mx-2{
    margin-right: .4em !important;
}
.wap-front .ml-2,
.wap-front .mx-2{
    margin-left: .4em !important;
}

.wap-front .mb-2,
.wap-front .my-2{
    margin-bottom: .4em !important;
}

.wap-front .mt-2,
.wap-front .my-2{
    margin-top: .4em !important;
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
.wap-front [data-tt]:not([data-tt=""]) {
  cursor: pointer;
}
.wap-front [data-tt]:hover {
  position: relative;
  z-index: 2;
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

.wap-front .wap-form-body{
    background-color: var(--wappo-body-bg);
}

.wclosable .wclose::before, 
.wclosable .wclose::after {
    background-color: var(--wappo-pri-tx);
}

.wclosable .wclose:hover::before, 
.wclosable .wclose:hover::after {
    background-color: #fff;
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

.wap-front .form-control, 
.wap-front .phone-field{
    width: 100%;
    font-weight: 400;
    line-height: 1.5;
    color: var(--wappo-input-col);
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid var(--wappo-input-bor);
    border-radius: .25em;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.wap-front .form-control{
    display: block;
    padding: .375em .75em;
}

.wap-front .phone-field input.tel, 
.wap-front input.form-control {
    font-size: 16px;
    height: calc(2.25em + 2px);
    margin: 0;
}

.wap-front .form-control::-ms-expand {
  background-color: transparent;
  border: 0;
}

.wap-front .form-control::-webkit-input-placeholder,
.wap-front .form-control::-moz-placeholder,
.wap-front .form-control:-ms-input-placeholder,
.wap-front .form-control::-ms-input-placeholder,
.wap-front .form-control::placeholder  {
  color: var(--wappo-input-ph);
  opacity: 1;
}


.wap-front .form-control:disabled, 
.form-control[readonly] {
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
    overflow-x: hidden;
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