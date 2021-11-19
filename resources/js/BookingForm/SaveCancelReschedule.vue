<template>
    <div>
        <div v-if="isSaveEventPage">
            <p>{{options.confirmation.savetocal}}</p>
            <SaveButtons :selectedSlot="selectedSlot.start" :service="service" :appointment="appointment"
            :staff="staff" :currentTz="currentTz" :physicalSelected="physicalSelected" :options="options"/>
        </div>
        <div v-else>
            <RescheduleForm v-if="showReschedule" 
            :appointmentkey="appointmentkey" :rescheduleData="dataLoaded" :options="options" 
            @changedStep="changedRescheduleStep" />
            <div v-if="showCancelConfirmation">
                <p v-if="appointmentCanceled" class="h4">{{ getText('confirmed') }}</p>
                <div v-else>
                    <WLoader v-if="loading" />
                    <div v-else>
                        <p class="h4">{{ getText('confirmation') }}</p>
                        <button class="wbtn wbtn-primary" @click="cancelAppointmentConfirmed">{{getText('confirm')}}</button>
                    </div>
                </div>
            </div>
            <div v-if="!buttonClicked">
                <template v-if="isReschedulePage">
                    <button v-if="canStillReschedule" class="wbtn wbtn-primary" :class="'wbtn-'+viewData" @click="rescheduleEvent">{{getText('button')}}</button>
                    <p class="h4" v-else>{{ getText('toolate') }}</p>
                </template>
                
                <template v-if="isCancelPage">
                    <button v-if="canStillCancel" class="wbtn wbtn-primary" :class="'wbtn-'+viewData" @click="cancelAppointment">{{getText('button')}}</button>
                    <p class="h4" v-else>{{ getText('toolate') }}</p>
                </template>
            </div>
        </div>
    </div>
</template>

<script>

import Dates from '../Modules/Dates'
import SaveButtons from './SaveButtons'
import RescheduleForm from './RescheduleForm'
import MixinTypeSelected from './MixinTypeSelected'
import minText from './minText'
import MixinPageDetector from './MixinPageDetector'

export default {
    mixins: [minText, Dates,  MixinTypeSelected, MixinPageDetector],
    components: { SaveButtons,  RescheduleForm} , 
    props: ['appointmentkey', 'viewData', 'options', 'serviceAppointment', 'disabledButtons', 'currentTz', 'dataLoaded'],
    data: () => ({
        rescheduledConfirmed: false,
        showCancelConfirmation: false,
        appointmentCanceled: false,
        loading:false,
        buttonClicked: false,
        showReschedule: false
    }),

    methods: {
        changedRescheduleStep(rescheduleStep){
            if(rescheduleStep == 'BookingFormConfirmation'){
                this.rescheduledConfirmed = true
            }
        },

        cancelAppointmentConfirmed(){
            if(this.disabledButtons) {
                return false
            }
            this.loading = true
            this.buttonClicked = true
            this.cancelAppointmentRequest()
            .then(this.appointmentCanceledMet)
        },
        async cancelAppointmentRequest() {
            return await this.serviceAppointment.call('cancel', window.wappointmentExtends.filter('wappointment_cancel_reschedule_data', {
                appointmentkey: this.appointmentkey,
            }, this.dataLoaded))
        },

        appointmentCanceledMet(data){
            this.loading = false
            this.appointmentCanceled = true
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

        justRescheduled(){
            return this.showReschedule && this.rescheduledConfirmed
        },

        canStillReschedule(){
            return this.getUnixNow() < this.dataLoaded.appointment.can_reschedule_until
        },
        canStillCancel(){
            return this.getUnixNow() < this.dataLoaded.appointment.can_cancel_until
        },

        selectedSlot(){
            return this.dataLoaded.appointment.start_at
        },

    }
}
</script>
