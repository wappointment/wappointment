<template>
    <div>
        <div v-if="loadedAppointment">
            <h2 v-if="!isSaveEventPage">{{getText('title')}}</h2>
            <ul>
                <li>{{ client.name }} - {{ client.email }}</li>
                <li><strong>{{ getMoment(selectedSlot, currentTz).format(fullDateFormat) }}</strong></li>
                <li><strong>{{ service.name }}</strong> <DurationCell :show="true" :duration="service.duration"/></li>
                <li v-if="physicalSelected">
                    <p>{{options.confirmation.physical}} </p>
                    <p><a :href="getMapAdress" target="_blank">{{ service.address}}</a></p>
                    <Iframe :height="200" :src="getIframeMap"></Iframe>
                </li>
                <li v-if="phoneSelected">
                    {{options.confirmation.phone}} <strong>{{ clientPhone }}</strong>
                </li>
                <li v-if="skypeSelected">
                    {{options.confirmation.skype}} <strong>{{ clientSkype}}</strong> 
                </li>
            </ul>
            <div v-if="isSaveEventPage">
                <div>
                    <p>{{options.confirmation.savetocal}}</p>
                    <SaveButtons :selectedSlot="selectedSlot" :service="service"
                    :staff="staff" :currentTz="currentTz" :physicalSelected="physicalSelected"></SaveButtons>
                </div>
            </div>
            <div v-else>
                <RescheduleForm v-if="showReschedule" :appointmentkey="appointmentkey" :options="options" ></RescheduleForm>
                <div v-if="showCancelConfirmation">
                    <div v-if="appointmentCanceled">
                        <p class="h4">{{getText('confirmed')}}</p>
                    </div>
                    <div v-else>
                        <div v-if="loading">
                            <WLoader></WLoader>
                        </div>
                        <div v-else>
                            <p class="h4">{{getText('confirmation')}}</p>
                            <button class="btn btn-primary" @click="cancelAppointmentConfirmed">{{getText('confirm')}}</button>
                        </div>
                    </div>
                    
                </div>
                <div v-if="!buttonClicked">
                    <div v-if="isReschedulePage">
                        <button v-if="canStillReschedule" class="btn btn-primary" @click="rescheduleEvent">{{getText('button')}}</button>
                        <p class="h4" v-else>{{getText('toolate')}}</p>
                    </div>
                    
                    <div v-if="isCancelPage">
                        <button v-if="canStillCancel" class="btn btn-primary" @click="cancelAppointment">{{getText('button')}}</button>
                        <p class="h4" v-else>{{getText('toolate')}}</p>
                    </div>
                    
                </div>
            </div>
        </div>
        <div v-else>
            <div v-if="loading">
                <WLoader></WLoader>
            </div>
            <div v-else>{{errorLoading}}</div>
        </div>
    </div>
</template>

<script>
import momenttz from '../../appMoment'

import Dates from '../../Modules/Dates'
import abstractFront from '../../Views/abstractFront'
import SaveButtons from './SaveButtons'
import Iframe from '../Iframe'
import Helpers from '../../Standalone/helpers'
import AppointmentService from '../../Services/V1/Appointment'
import DurationCell from './DurationCell'
import RescheduleForm from '../RescheduleForm'

export default {
     
    mixins: [Dates],
    extends: abstractFront,
    components: {
        SaveButtons,
        Iframe,
        RescheduleForm,
        DurationCell
    }, 
    props: ['appointmentkey', 'view', 'options'],
    data: () => ({
        viewName: 'appointment',
        viewData: null,
        appointment: null,
        client: null,
        service: null,
        staff: null,
        serviceAppointment: null,
        loadedAppointment: false,
        loading: false,
        currentTz: 'UTC',
        date_format: '',
        date_time_union: '',
        time_format: '',
        buttonClicked: false,
        showReschedule: false,
        showCancelConfirmation: false,
        appointmentCanceled: false,
        errorLoading: '',
        disabledButtons: false
        
    }),
    created(){
        this.currentTz = momenttz.tz.guess()
        this.serviceAppointment = this.$vueService(new AppointmentService)
        this.viewData = this.view
        if(this.options.demoData !== undefined){
            let data = {
                data: this.options.demoData.appointmentData
            }
            this.appointmentLoaded(data)
            this.viewData = this.options.demoData.view
            this.disabledButtons = true
        }
    },
    mounted () {
        if(this.isReschedulePage || this.isCancelPage || this.isSaveEventPage) {
            if(this.options.demoData === undefined)this.loadAppointment()
        }
        
    },
    methods: {
        getText(textKey){
            return this.isCancelPage? this.options.cancel[textKey]:this.options.reschedule[textKey]
        },
        loadAppointment(){
            this.loading = true
            this.loadAppointmentRequest()
            .then(this.appointmentLoaded)
            .catch(this.appointmentLoadingError)
        },
        async loadAppointmentRequest() {
            let data = {}
            data.appointmentkey = this.appointmentkey
            return await this.serviceAppointment.call('get', data)
        }, 

        appointmentLoaded(d){
            this.appointment = d.data.appointment
            this.client = d.data.client
            this.service = d.data.service
            this.staff = d.data.staff
            this.time_format = (new Helpers()).convertPHPToMomentFormat(d.data.time_format)
            this.date_format = (new Helpers()).convertPHPToMomentFormat(d.data.date_format)
            this.date_time_union = d.data.date_time_union
            this.loadedAppointment = true
            this.loading = false
        },
        appointmentLoadingError(e){
            this.loading = false
            this.errorLoading = e.response.data.message
            //console.log('appointmentBookingError')
        },

        cancelAppointmentConfirmed(){
            if(this.disabledButtons) return false
            this.loading = true
            this.buttonClicked = true
            this.cancelAppointmentRequest()
            .then(this.appointmentCanceledMet)
            .catch(this.appointmentCancelError)
        },
        async cancelAppointmentRequest() {
            return await this.serviceAppointment.call('cancel', {
                appointmentkey: this.appointmentkey,
            })
        }, 

        appointmentCanceledMet(data){
            this.loading = false
            this.appointmentCanceled = true
        },
        appointmentCancelError(){
            //console.log('couldnt cancel')
        },
        rescheduleEvent(){
            if(this.disabledButtons) return false
            this.buttonClicked = true
            this.showReschedule = true
        },
        cancelAppointment(){
            this.buttonClicked = true
            this.showCancelConfirmation = true
        },

        
    },
    computed: {
        canStillReschedule(){
            let now = momenttz()
            return now.unix() < this.appointment.canRescheduleUntil
        },
        canStillCancel(){
            let now = momenttz()
            return now.unix() < this.appointment.canCancelUntil
        },
        isReschedulePage(){
            return this.viewData == 'reschedule-event'
        },
        isCancelPage(){
            return this.viewData == 'cancel-event'
        },
        isSaveEventPage(){
            return this.viewData == 'add-event-to-calendar'
        },
        fullDateFormat(){
           return this.date_format + '[' + this.date_time_union + ']' + this.time_format
        },
        getEncodedAdress(){
            return encodeURIComponent(this.service.address)
        },
        selectedSlot(){
            return this.appointment.start_at
        },
        getIframeMap(){
            return 'https://maps.google.com/maps?width=100%&height=200&hl=en&q=' + this.getEncodedAdress + '&ie=UTF8&t=&z=14&iwloc=B&output=embed'
        },
        getMapAdress(){
            return 'https://www.google.com/maps/search/?api=1&query=' + this.getEncodedAdress
        },
        phoneSelected(){
            return this.appointment !== null && this.appointment.type == 'phone'
        },
        physicalSelected(){
            return this.appointment !== null && this.appointment.type == 'physical'
        },
        skypeSelected(){
            return this.appointment !== null && this.appointment.type == 'skype'
        },
        clientPhone(){
            return this.client.options.phone
        },
        clientSkype(){
            return this.client.options.skype
        },
    }
}
</script>