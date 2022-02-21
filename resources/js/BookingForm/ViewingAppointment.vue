<template>
    <div>
        <template v-if="view.indexOf('addonView') === 0">
          <component :is="view" @loading="changeLoading" :options="options" :momenttz="momenttz" :convertDateFormat="convertDateFormat" />
        </template>
        <template v-else>
            <div v-if="loadedAppointment">
                <div class="summary-event" :class="view">
                    <div v-for="line in dataLoaded.display" v-html="parseBBAndGetText(line)"></div>
                    <div :class="{'old-schedule': justRescheduled}">
                        <strong class="date-start">{{ startDatei18n }}</strong> 
                        <span v-if="!justRescheduled">{{ctdString}}</span>
                    </div>
                    <div v-if="zoomSelected && isViewEventPage && !isOver">
                        <a v-if="hasMeetingRoom" :href="meetingUrl" class="wbtn wbtn-primary wbtn-lg">{{ options.view.join }}</a>
                        <div v-else class="h4">{{ options.view.missing_url }}</div>
                    </div>
                </div>
                <SaveCancelReschedule :appointmentkey="appointmentkey" :viewData="viewData" :options="options" 
                :serviceAppointment="serviceAppointment" :disabledButtons="disabledButtons" :currentTz="currentTz" :dataLoaded="dataLoaded" 
                @rescheduled="rescheduled"/>
            </div>
            <div v-else>
                <WLoader v-if="loading"/>
                <div v-else>{{errorLoading}}</div>
            </div>
        </template>
    </div>
</template>

<script>


import AbstractFront from './AbstractFront'
import AppointmentService from '../Services/V1/Appointment'
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import momenttz from '../appMoment'
import minText from './minText'
import Dates from '../Modules/Dates'
import MixinTypeSelected from './MixinTypeSelected'
import parseBB from '../Mixins/parseBB'
import MixinPageDetector from './MixinPageDetector'
import Countdown from '../Mixins/Countdown'
import SaveCancelReschedule from './SaveCancelReschedule'

export default {
    mixins: [minText, Dates,  MixinTypeSelected, parseBB, MixinPageDetector, Countdown],
    extends: AbstractFront,
    components: window.wappointmentExtends.filter('FrontMainViews', { SaveCancelReschedule} ), 
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
        errorLoading: '',
        dataLoaded: null,
        momenttz: momenttz,
        zoom_browser: false,
        justRescheduled: false,
        disabledButtons: false
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
        if(
            (this.isReschedulePage || this.isCancelPage || this.isSaveEventPage || this.isViewEventPage) 
            && this.options.demoData === undefined
            ) {
            this.refreshAppointment()
        }
    },
    methods: {
        parseBBAndGetText(sample){
            let html = this.parseBB(sample)
            let matchesGetText = html.match(/getText\((.*?)\)/)
            return matchesGetText !== null? html.replace(/getText\((.*?)\)/, this.getText(matchesGetText[1])):html;
        },
        
        rescheduled(){
            this.justRescheduled = true
        },

        autoRedirect(){
            if(this.isViewEventPage && this.zoomSelected && this.hasMeetingRoom && this.isStarted && !this.isOver){ // auto redirect once the meeting has started
                window.location.replace(this.meetingUrl)
            }
        },
        

        refreshAppointment(){
            this.loadedAppointment = false
            this.loadAppointment()
        },
        changeLoading(loading){
            this.loading = loading
        },
        convertDateFormat(date){
            return convertDateFormatPHPtoMoment(date)
        },
        loadAppointment(){
            this.loading = true
            this.loadAppointmentRequest()
            .then(this.appointmentLoaded)
            .catch(this.appointmentLoadingError)
        },
        async loadAppointmentRequest() {
            return await this.serviceAppointment.call(
                'get', 
                window.wappointmentExtends.filter('loadAppointmentParams', {appointmentkey: this.appointmentkey}, this.getParameterByName)
                )
        }, 
        appointmentLoadingError(e){
            this.loading = false
            this.errorLoading = e.response.data.message
        },
        appointmentLoaded(d){
            this.dataLoaded = d.data
            this.appointment = d.data.appointment
            this.selection = this.appointment.type
            this.client = d.data.client
            this.service = d.data.service
            this.staff = d.data.staff
            this.time_format = this.convertDateFormat(d.data.time_format)
            this.date_format = this.convertDateFormat(d.data.date_format)
            this.zoom_browser = d.data.zoom_browser
            this.date_time_union = d.data.date_time_union
            this.loadedAppointment = true
            this.loading = false
            this.autoRedirect()
            this.initCountDown(this.appointmentStarts, this.options.view.timeleft)
        },
        
    },
    computed: {
        isStarted(){
            return this.appointmentStarts < Math.round(Date.now()/1000)
        },
        isOver(){
            return this.appointment.end_at < Math.round(Date.now()/1000)
        },

        hasMeetingRoom(){
            return [undefined,false,''].indexOf(this.appointment.video_meeting) === -1 ? this.appointment.video_meeting:false
        },
        isVideoZoom(){
            let locationid = parseInt(this.appointment.location_id)
            let location = this.service.locations.find(item => parseInt(item.id) === locationid)
            return location.options.type == 'zoom' && location.options.video == 'zoom'
        },
        meetingUrl(){
            let zoomflag = '.zoom.us/j/'
            if(this.isVideoZoom && this.zoom_browser && this.hasMeetingRoom.indexOf(zoomflag) !== false){
                let meetingsplit = this.hasMeetingRoom.split(zoomflag)[1].split('?')
                return 'https://zoom.us/wc/'+meetingsplit[0]+'/start?'+meetingsplit[1]
            }
            return this.hasMeetingRoom
        },

        startDatei18n(){
            return this.appointment.converted !== undefined ? this.appointment.converted :this.getMoment(this.appointmentStarts, this.currentTz).format(this.fullDateFormat)
        },
        
        fullDateFormat(){
           return this.date_format + '[' + this.date_time_union + ']' + this.time_format
        },
        
        appointmentStarts(){
            return this.appointment.start_at
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