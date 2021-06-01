<template>
    <div class="confirmation-cell">
        <div class="success d-flex align-items-center">
            <div class="bigicon"><WapImage :faIcon="'calendar-check'" size="lg" /> </div>
            <div class="text-conf">{{options.confirmation.confirmation}}</div>
        </div>
        <div class="confirmation-summary">
            <div>
                <span class="wlabel">{{options.confirmation.when}}</span>
                <span>{{ appointment_starts_at }}</span>
            </div>
            <div>
                <span class="wlabel">{{options.confirmation.service}}</span>
                <span>{{ service.name }}</span>
            </div>
            <div>
                <span class="wlabel">{{options.confirmation.duration}}</span>
                <span>{{calculateDuration}}{{getMinText}}</span>
            </div>
            
        </div>
        <div class="wdescription" v-if="isApprovalManual">{{options.confirmation.pending}}</div>
        <div v-if="physicalSelected">
            <div class="wdescription">{{options.confirmation.physical}} </div>
            <div class="address-service">
                <BookingAddress :service="isLegacy ? service:locationObj">
                    <WapImage :faIcon="'map-marked-alt'" size="md" /> 
                </BookingAddress>
            </div>
            
            <BookingAddress :iframe="true" :service="isLegacy ? service:locationObj">
                <WapImage :faIcon="'map-marked-alt'" size="md" /> 
            </BookingAddress>
        </div>
        <div class="wdescription" v-if="phoneSelected">
            {{options.confirmation.phone}} <strong>{{ getClientPhone}}</strong>
        </div>
        <div class="wdescription" v-if="skypeSelected">
            {{options.confirmation.skype}} <strong>{{ getClientSkype }}</strong> 
        </div>
         <div class="wdescription" v-if="zoomSelected" v-html="getZoomWithLink">
        </div>
        <div class="wdescription my-2 text-center">
            <transition name="slide-fade">
                <SaveButtons v-if="showSaveButtons" :service="service" :showResult="showResult" :appointment="appointment"
                :staff="staff" :currentTz="timeprops.currentTz" :physicalSelected="physicalSelected"></SaveButtons>
                <span v-else class="wbtn-primary-light wbtn d-flex align-items-center d-flex-inline" @click="showSaveButtons=true">
                    <WapImage :faIcon="'calendar-alt'" size="md" /> <span class="ml-2">{{options.confirmation.savetocal}}</span>
                </span>
            </transition>
        </div>
    </div>
</template>

<script>
import BookingAddress from './Address'
import SaveButtons from './SaveButtons'
import minText from './minText'
import MixinTypeSelected from './MixinTypeSelected'
import MixinLegacy from './MixinLegacy'

export default {
    components: {
        BookingAddress,
        SaveButtons,
    }, 
    mixins: [minText, MixinTypeSelected, MixinLegacy],
    props: [
        'appointment',
        'service', 
        'result', 
        'isApprovalManual', 
        'timeprops',
        'staff',
        'options',
        'appointment_starts_at',
    ],
    data: () => ({
        showSaveButtons: false,
        showResult: null,
        locationObj: null
    }),

    created(){
        this.showResult = this.result
        this.selection = this.showResult.type

        if(this.showResult.location_id !== undefined) this.showResult.location = this.showResult.location_id
        if(this.service.locations !== undefined){
            for (let i = 0; i < this.service.locations.length; i++) {
                const element = this.service.locations[i]
                if(element.id == this.showResult.location){
                    this.locationObj = element
                }
            }
        }

        if(this.options.demoData !== undefined){
            this.options.eventsBus.listens('dataDemoChanged', this.dataChanged)
        }else{
            this.triggerWEvent('wappo_confirmed', {
                appointment: {start:this.appointment.start_at, end:this.appointment.end_at, key:this.appointment.edit_key}, 
                modality: this.appointment.location_label, 
                service: this.service.name, 
                staff: this.staff.n,
                client: this.result
                } )
        }
    },
    computed: {

        demoDuration(){
            return this.service.duration !== undefined ? this.service.duration:this.service.options.durations[0].duration
        },
        calculateDuration(){
            return this.appointment === false ? this.demoDuration: this.appointment.duration_sec / 60
        },
        getClientPhone(){
            return this.showResult.client !== undefined ? this.showResult.client.options.phone : this.showResult.phone
        },
        getClientSkype(){
            return this.showResult.client !== undefined ? this.showResult.client.options.skype : this.showResult.skype
        },
        getZoomWithLink(){
            let url = apiWappointment.frontPage + (apiWappointment.frontPage.indexOf('?') === -1 ? '?':'&' )+'view=view-event&appointmentkey=' + this.appointment.edit_key
            return this.options.confirmation.zoom
            .replace('[meeting_link]', '<a href="'+url+'" target="_blank">')
            .replace('[/meeting_link]', '</a>')
        },
        phoneSelected(){
             if(this.locationObj!== null){
                 return this.locationObj.type == 2
            }else{
                return this.selectedServiceType == 'phone'
            }
        },
        physicalSelected(){
            if(this.locationObj!== null){
                 return this.locationObj.type == 1
            }else{
                return this.selectedServiceType == 'physical'
            }
        },
        skypeSelected(){
            if(this.locationObj!== null){
                 return this.locationObj.type == 3
            }else{
                return this.selectedServiceType == 'skype'
            }
        },
    },
    methods: {
        dataChanged(dataNew){
            this.showResult = dataNew
            this.selection = this.showResult.type
            if(this.options.demoData !== undefined && this.selection === undefined && this.service.type !== undefined){
                this.selection = this.service.type[0]
            }
            this.selectedServiceType = this.showResult.type
        }
    }
}
</script>
<style>
.bigicon{
    font-size: 3em;
    margin-right: .2em;
}
.success .text-conf{
    text-align: left;
    font-size: 1.6em;
    line-height: 1.2em;
}

.mb-2, .my-2 {
    margin-bottom: .5em !important;
}
.mt-2, .my-2 {
    margin-top: .5em !important;
}

.confirmation-summary{
    padding: 2em 1em 1.5em 1em;
    font-size: .8em;
}
.confirmation-summary > div{
    margin-bottom:.5em;
    line-height: 1em;
}
.confirmation-summary .wlabel{
    font-weight: bold;
}
.wdescription{
    font-size: .8em;
    line-height: 1.4em;
}

</style>
