<template>
    <div>
        <template v-if="view.indexOf('addonView') === 0">
          <component :is="view" @loading="changeLoading" :options="options" :momenttz="momenttz" :convertDateFormat="convertDateFormat" />
        </template>
        <template v-else>
            <div v-if="loadedAppointment">
                <div class="summary-event" :class="view">
                    <h2 v-if="!isSaveEventPage">{{ getText('title') }}</h2>
                    <div>{{ client.name }} - {{ client.email }}</div>
                    <div><strong>{{ service.name }}</strong> - <span class="wduration">{{getDuration}}{{getMinText}}</span></div>
                    <div :class="{'old-schedule': justRescheduled}">
                        <strong class="date-start">{{ startDatei18n }}</strong> 
                        <span v-if="!justRescheduled">{{timeLeft}}</span>
                    </div>
                    <div v-if="zoomSelected && isViewEventPage">
                        <a v-if="hasMeetingRoom" :href="hasMeetingRoom" class="wbtn wbtn-primary wbtn-lg">{{ options.view.join }}</a>
                        <div v-else>
                            <div class="h4">{{ options.view.missing_url }}</div>
                        </div>
                    </div>
                </div>
                <div v-if="isSaveEventPage">
                    <div>
                        <p>{{options.confirmation.savetocal}}</p>
                        <SaveButtons :selectedSlot="selectedSlot" :service="service" :appointment="appointment"
                        :staff="staff" :currentTz="currentTz" :physicalSelected="physicalSelected" />
                    </div>
                </div>
                <div v-else>
                    <RescheduleForm v-if="showReschedule" 
                    :appointmentkey="appointmentkey" :rescheduleData="rescheduleData" :options="options" 
                    @changedStep="changedRescheduleStep" />
                    <div v-if="showCancelConfirmation">
                        <div v-if="appointmentCanceled">
                            <p class="h4">{{ getText('confirmed') }}</p>
                        </div>
                        <div v-else>
                            <div v-if="loading">
                                <WLoader />
                            </div>
                            <div v-else>
                                <p class="h4">{{ getText('confirmation') }}</p>
                                <button class="wbtn wbtn-primary" @click="cancelAppointmentConfirmed">{{getText('confirm')}}</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="!buttonClicked">
                        <template v-if="isReschedulePage">
                            <button v-if="canStillReschedule" class="wbtn wbtn-primary" :class="'wbtn-'+view" @click="rescheduleEvent">{{getText('button')}}</button>
                            <p class="h4" v-else>{{ getText('toolate') }}</p>
                        </template>
                        
                        <template v-if="isCancelPage">
                            <button v-if="canStillCancel" class="wbtn wbtn-primary" :class="'wbtn-'+view" @click="cancelAppointment">{{getText('button')}}</button>
                            <p class="h4" v-else>{{ getText('toolate') }}</p>
                        </template>
                    </div>
                </div>
            </div>
            <div v-else>
                <div v-if="loading">
                    <WLoader />
                </div>
                <div v-else>{{errorLoading}}</div>
            </div>
        </template>
    </div>
</template>

<script>

import Dates from '../Modules/Dates'
import AbstractFront from './AbstractFront'
import SaveButtons from './SaveButtons'
import Iframe from '../Components/Iframe'
import AppointmentService from '../Services/V1/Appointment'
import DurationCell from './DurationCell'
import RescheduleForm from './RescheduleForm'
import ViewingAppointmentMixin from './ViewingAppointmentMixin'
import MixinTypeSelected from './MixinTypeSelected'
import momenttz from '../appMoment'
import minText from './minText'
let mixins = {ViewingAppointmentMixin:ViewingAppointmentMixin}
mixins = window.wappointmentExtends.filter('ViewingAppointmentMixin', mixins)

let compos = { 
    SaveButtons,
    Iframe,
    RescheduleForm,
    DurationCell,
  }
compos = window.wappointmentExtends.filter('FrontMainViews', compos )

export default {
     
    mixins: [minText, Dates, mixins.ViewingAppointmentMixin, MixinTypeSelected],
    extends: AbstractFront,
    components: compos, 
    props: ['appointmentkey', 'view', 'options'],
    data: () => ({
        viewName: 'appointment',
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
        rescheduleData: null,
        momenttz: momenttz,
        timeLeft: '',
        timeLeftId: false,
        rescheduledConfirmed: false
    }),
    created(){
        this.currentTz = this.tzGuess()
        this.serviceAppointment = this.$vueService(new AppointmentService)
        this.viewData = this.view
        if(this.options.demoData !== undefined){
            this.appointmentLoaded({
                data: this.options.demoData.appointmentData
            })
            this.viewData = this.options.demoData.view
            this.disabledButtons = true
        }
    },
    mounted () {
        if(this.isReschedulePage || this.isCancelPage || this.isSaveEventPage || this.isViewEventPage) {
            if(this.options.demoData === undefined) {
                this.refreshAppointment()
            }
        }
    },
    methods: {
        changedRescheduleStep(rescheduleStep){
            if(rescheduleStep == 'BookingFormConfirmation'){
                this.rescheduledConfirmed = true
            }
        },
        initCountDown(){
            this.initDate = new Date()
            this.initDate.setTime(this.selectedSlot*1000)
            // Update the count down every 1 second
            if(this.initDate.getTime()){
                this.timeLeftId = setInterval(this.countDownInterval , 1000)
            }
            
        },
        countDownInterval(){

            // mseconds left
            let milisecondsleft = this.initDate.getTime() - new Date().getTime()

            // return results
            if (milisecondsleft < 0) {
                this.timeLeft = ''
                clearInterval(this.timeLeftId)
            }else{
                // unites left
                let days = Math.floor(milisecondsleft / this.oneDayInMs())
                let hours = Math.floor((milisecondsleft % this.oneDayInMs()) / this.oneHourInMs())
                let minutes = Math.floor((milisecondsleft % this.oneHourInMs()) / this.oneMinInMs())
                let seconds = Math.floor((milisecondsleft % this.oneMinInMs()) / this.oneSecInMs())

                this.timeLeft = this.options.view.timeleft
                .replace('[days_left]', days)
                .replace('[hours_left]', hours)
                .replace('[minutes_left]', minutes)
                .replace('[seconds_left]', seconds)

                //this.timeLeft = days + "d " + hours + "h " + minutes + "m " + seconds + "s"
            }
        },
        oneDayInMs(){
            return this.oneHourInMs() * 24
        },
        oneHourInMs(){
            return this.oneMinInMs()  * 60
        },
        oneMinInMs(){
            return this.oneSecInMs() * 60
        },
        oneSecInMs(){
            return 1000
        },

        refreshAppointment(){
            this.loadedAppointment = false
            this.loadAppointment()
        },
        changeLoading(loading){
            this.loading = loading
        },
        getText(textKey){
            return this.isCancelPage? this.options.cancel[textKey]:this.options.reschedule[textKey]
        },

        cancelAppointmentConfirmed(){
            if(this.disabledButtons) {
                return false
            }
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
        },
        rescheduleEvent(){
            if(this.disabledButtons) {
                return false
            }
            this.buttonClicked = true
            this.showReschedule = true
        },
        cancelAppointment(){
            this.buttonClicked = true
            this.showCancelConfirmation = true
        },
        
    },
    computed: {
        getDuration(){
            return this.appointment.duration_sec / 60
        },
        justRescheduled(){
            return this.showReschedule && this.rescheduledConfirmed
        },
        hasMeetingRoom(){
            return [undefined,false,''].indexOf(this.appointment.video_meeting) === -1 ? this.appointment.video_meeting:false
        },

        startDatei18n(){
            return this.appointment.converted !== undefined ? this.appointment.converted :this.getMoment(this.selectedSlot, this.currentTz).format(this.fullDateFormat)
        },
        canStillReschedule(){
            return this.getUnixNow() < this.appointment.canRescheduleUntil
        },
        canStillCancel(){
            return this.getUnixNow() < this.appointment.canCancelUntil
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
        isViewEventPage(){
            return this.viewData == 'view-event'
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

        clientPhone(){
            return this.client.options.phone
        },
        clientSkype(){
            return this.client.options.skype
        },
    }
}
</script>
<style >
.summary-event{
    margin: 1rem 0;
}
.wbtn.wbtn-lg{
    font-size: 1.4em;
    margin: .4em 0;
}
.old-schedule{
    text-decoration: line-through;
}
</style>