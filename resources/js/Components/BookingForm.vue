<template>
    <div class="wap-bf" :class="{show: canBeBooked}">
        <div v-if="canBeBooked">
            <div v-for="staff in this.viewData.staffs"> 
                <div class="d-flex wap-head align-items-center">
                    <div class="staff-av" :class="{norefresh: !isStepSlotSelection}" @click="refreshClick">
                        <img :src="staff.a" :alt="staff.n">
                        
                        <div class="after" v-if="isStepSlotSelection">
                            <svg viewBox="0 0 32 32" class="ic-refresh" aria-hidden="true"><path d="M27.1 14.313V5.396L24.158 8.34c-2.33-2.325-5.033-3.503-8.11-3.503C9.902 4.837 4.901 9.847 4.899 16c.001 6.152 5.003 11.158 11.15 11.16 4.276 0 9.369-2.227 10.836-8.478l.028-.122h-3.23l-.022.068c-1.078 3.242-4.138 5.421-7.613 5.421a8 8 0 0 1-5.691-2.359A7.993 7.993 0 0 1 8 16.001c0-4.438 3.611-8.049 8.05-8.049 2.069 0 3.638.58 5.924 2.573l-3.792 3.789H27.1z"/></svg>
                        </div>
                    </div>
                    <div class="staff-desc">
                        <div><strong>{{ staff.n }}</strong></div>
                        <div>{{ service.name }}</div>
                    </div>
                </div>
            </div>   
            <div class="wrap-calendar p-2">
                <div v-if="loading">
                    <Loader></Loader>
                </div>
                <div v-else>
                        <div v-if="isStepSlotSelection">
                                <BookingCalendar 
                                :service="service"
                                :initIntervalsCollection="intervalsCollection"
                                :currentTz="currentTz"
                                :options="options"
                                :time_format="time_format"
                                @selectSlot="selectSlot"
                                ></BookingCalendar>
                        </div>

                        <div v-if="!savedAppointment && reschedulingSelectedSlot" >
                                <RescheduleConfirm v-if="!savedAppointment && reschedulingSelectedSlot"
                                :selectedSlot="selectedSlot" 
                                :currentTz="currentTz" 
                                :fullDateFormat="fullDateFormat"
                                :options="options"
                                @back="backToCalendar"
                                @confirm="confirmReschedule"
                                ></RescheduleConfirm>
                        </div>

                        <div v-if="isStepForm" class="confirm-form">
                                <BookingFormInputs
                                :selectedSlot="selectedSlot" 
                                :currentTz="currentTz" 
                                :fullDateFormat="fullDateFormat"
                                :service="service"
                                :options="options"
                                :errors="errorMessages"
                                :data="dataSent"
                                @back="backToCalendar"
                                @confirm="confirmBooking"
                                ></BookingFormInputs>
                        </div>
                        <div v-if="savedAppointment">
                            <component :is="alternateComp" @switchToRegular="switchToRegular" :selectedSlot="selectedSlot" 
                            :currentTz="currentTz" 
                            :fullDateFormat="fullDateFormat"
                            :service="service" 
                            :result="dataSent"
                            :options="options"
                            :isApprovalManual="isApprovalManual"
                            :staff="staff"></component>
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
import Helpers from '../Standalone/helpers'
import Colors from "../Modules/Colors";

import BookingFormConfirmation from './BookingForm/Confirmation'
import RescheduleConfirm from './BookingForm/RescheduleConfirm'
import BookingCalendar from './BookingForm/Calendar'
import BookingFormInputs from './BookingForm/Form'

import momenttz from '../appMoment'


let compDeclared = {
        'BookingFormConfirmation' : BookingFormConfirmation,
        'RescheduleConfirm': RescheduleConfirm,
        'BookingCalendar': BookingCalendar,
        'BookingFormInputs':BookingFormInputs
    };
compDeclared = window.wappointmentExtends.filter('BookingFormComp', compDeclared )

export default {
     extends: abstractFront,
     mixins: [Colors],
     props: ['serviceAction', 'appointmentkey', 'options', 'step','passedDataSent'],
     components: compDeclared, 
    data: () => ({
        viewName: 'availability',
        viewData: null,
        intervalsCollection: null,
        currentTz : 'UTC',
        selectedSlot: false,
        time_format: '',
        date_format: '',
        savedAppointment: false,
        dataloaded: false,
        isApprovalManual: false,
        dataSent: {},
        errors: null,
        alternateComp: 'BookingFormConfirmation'
    }),

    mounted () {
        
        this.refreshInitValue()
        this.currentTz = momenttz.tz.guess()
        this.createdAt = momenttz().unix()
        if(this.step == 'button') {
            this.$emit('changedStep','selection')
        }
    },

    watch:{
        step(val){
            if([undefined, null].indexOf(val) === -1 ) {
                this.demoConfigure(val)
            }
        }
    },
    computed: {
        isStepForm(){
            return !this.savedAppointment && !this.reschedulingSelectedSlot && this.selectedSlot
        },
        isStepSlotSelection(){
            return !this.savedAppointment && !this.reschedulingSelectedSlot && !this.selectedSlot
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
       service(){
            return this.getDefaultService
        },
        getDefaultService(){
            return this.dataloaded && this.viewData.services[0] !== undefined ?this.viewData.services[0]:false
        },

    },
    methods: {
        switchToRegular(){
            this.alternateComp = 'BookingFormConfirmation'
        },
        demoConfigure(val){
            if(['button'].indexOf(val) === -1) {
                this.selectedSlot = false
                this.savedAppointment = false
                this.dataSent = {}
            }
            if(['button', 'selection'].indexOf(val) === -1) {
                let laskey = this.intervalsCollection.intervals.length -1
                this.selectedSlot = this.intervalsCollection.intervals[laskey].start
            }
            if(['button', 'selection', 'form'].indexOf(val) === -1) {
                this.savedAppointment = true
                
                if(this.passedDataSent !== null)this.dataSent = this.passedDataSent
                else this.dataSent = this.options.demoData.form
                if(val == 'confirmation') this.switchToRegular()
            }
        },
        selectSlot(slot){
            this.selectedSlot = slot
            this.$emit('changedStep','form')
        },

        confirmBooking(data){
            this.loading = true
            this.dataSent = data
            this.saveBookingRequest(data)
            .then(this.appointmentBooked)
            .catch(this.appointmentBookingError)
        },

        async saveBookingRequest(data) {
            return await this.serviceBooking.call('save', data)
        }, 

        appointmentBooked(result){
            this.isApprovalManual = (result.data.status == 0) ? true:false
          
            this.savedAppointment = true
            this.loading = false
            this.$emit('changedStep','confirmation',this.dataSent, this.selectedSlot)
        },
        appointmentBookingError(error){
            this.serviceError(error)
        },
        confirmReschedule(){
            this.loading = true
            this.saveRescheduleRequest()
            .then(this.appointmentBooked)
            .catch(this.appointmentBookingError)
        },
        async saveRescheduleRequest() {
            let data = {
                appointmentkey: this.appointmentkey,
                time: this.selectedSlot,
                ctz: momenttz.tz.guess(),
            }
            return await this.serviceBooking.call('reschedule', data)
        }, 
        
        backToCalendar(){
            this.selectedSlot = false
        },
        refreshClick() {
            if(!this.isStepSlotSelection) return false
            this.loading = true
            this.refreshInitValue()
        },

        getDefaultStaff(){
            if(this.viewData.staffs!== undefined && this.viewData.staffs.length > 0){
                return this.viewData.staffs[0]
            }
        },
        loadedAfter() {
            this.time_format = (new Helpers()).convertPHPToMomentFormat(this.viewData.time_format)
            this.date_format = (new Helpers()).convertPHPToMomentFormat(this.viewData.date_format)

            this.startDay = this.viewData.week_starts_on
            let firstStaff = this.getDefaultStaff()
            this.intervalsCollection = new Intervals(this.viewData.availability[firstStaff.id])

            momenttz.locale(this.getLocale())

            this.dataloaded = true
            
            if(!this.rescheduling){
                this.alternateComp = window.wappointmentExtends.filter('alternateComp', this.alternateComp, this.service )
            }
            if([undefined, null].indexOf(this.step) === -1) {
                this.demoConfigure(this.step)
                
            }
        },
        getLocale(){
            return (navigator.languages && navigator.languages.length) ? navigator.languages[0] : navigator.language;
        },
        
    }
}
</script>
<style>
.wap-bf{
    max-width: 300px;
    
    transition: box-shadow ease-in-out .3s;
}
.wap-bf.show{
    box-shadow: 0px 8px 10px 0 rgba(0,0,0,.08);
}

.wap-front .calendarMonth .ddays div {
    width: 1.4em;
    text-align: center;
    font-size: .75em;
}
.wap-front .no-avail {
    color:#ccc;
}

.wap-front .mr-2{
    margin-right: .3em;
}
.wap-front .mb-2{
    margin-bottom: .3em;
}
.wap-front .p-2 {
    padding: .5em !important;
}
.wap-front .pl-2 {
    padding-left: .5em !important;
}
.wap-front .slotsPane{
    box-shadow: inset 0px 0px 10px rgba(0,0,0,.3);
    transition: all .3s ease-in-out;
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
  width: 80px;
  border-radius: 3px;
  content: attr(data-tt);
  text-align: center;
  font-size: 14px;
  line-height: 1.2;
}
.wap-front .ddays div:first-child [data-tt]:before {
  margin-left: -8px;
}
.wap-front .ddays div:last-child [data-tt]:before {
  margin-left: -85px;
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

.wap-front .staff-av {
    position:relative;
    cursor: pointer;
}
.wap-front .staff-av .after{
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100%;
  width: 100%;
  content: "\f463";
  transition: .3s ease;
  background-color: transparent;
  border-radius: 3px;
  opacity:0;
}
.wap-front .staff-av:hover .after{
  background-color: rgba(210, 210, 210, 0.8);
  opacity:1;
}
.wap-front .staff-av .ic-refresh {
  fill: #fff;
}
.wap-front .wap-head .staff-desc {
    padding-left: .4em;
    line-height: 1.2;
    font-size: 1em;
}
.wap-bf button {
    font-size: .7em;
}

.wap-front .confirm-form form{
    text-align: left;
}


.wrap-calendar p, .wrap-calendar hr{
    margin: 0 0 .4em;
    font-size: .8em;
}

.wap-front .dayselected{
    font-weight: bold;
    border-radius: .2em .2em 0 0;
    box-shadow: 0px 0px 6px rgba(0,0,0,0.3);
}

.wap-front .dayselected span{
    text-decoration: none;
}

.wap-front .w100{
    width:100%;
}

.wap-front .btn-confirm button {
    font-size: 1em;
}

.slide-fade-enter-active, .slide-fade-sm-enter-active, 
.slide-fade-side-sm-right-enter-active, .slide-fade-side-sm-right-leave-active,
.slide-fade-side-sm-left-leave-active,  .slide-fade-side-sm-left-leave-active{
  transition: all .3s ease;
}

.slide-fade-leave-active, .slide-fade-sm-leave-active{
  transition: all .1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.slide-fade-enter, .slide-fade-leave-to {
  transform: translateY(100px);
  opacity: 0;
}

.slide-fade-sm-enter, .slide-fade-sm-leave-to {
  transform: translateY(10px);
  opacity: 0;
}

.slide-fade-side-sm-right-enter, .slide-fade-side-sm-left-leave-to  {
  transform: translateX(40px);
  opacity: 0;
}
.slide-fade-side-sm-right-leave-to, .slide-fade-side-sm-left-enter  {
  transform: translateX(-40px);
  opacity: 0;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to  {
  opacity: 0;
}

.saveButtons span {
    width: 80px;
}
.wap-bf .field-required inoput{
    transition: border-right ease-in-out .3s;
}
.wap-front hr {
    margin: 1em auto;
    width: 100%;
    height: 1px;
    max-width: 100%;
    text-align: center;
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

.wap-front .li-unstyled {
    padding-left: 0;
    list-style: none;
    font-size: 1em;
}
.wap-front .staff-av img{
    max-width: 40px;
    display: block;
    overflow: hidden;
    font-size: 12px;
}
.wap-front .staff-av.norefresh {
    cursor: default;
}
</style>