<template>
    <div class="confirmation-cell max400">
        <div class="success d-flex align-items-center">
            <div class="bigicon"><FontAwesomeIcon icon="calendar-check" /> </div>
            <div class="text-conf">{{options.confirmation.confirmation}}</div>
        </div>
        <div class="my-2">
            <div><strong>{{ service.name }}</strong> <DurationCell :show="true" :duration="service.duration"/></div>
            <div><strong>{{ appointment_starts_at }}</strong></div>
        </div>
        <p v-if="isApprovalManual">{{options.confirmation.pending}}</p>
        <div v-if="physicalSelected">
            <p>{{options.confirmation.physical}} </p>
            <div class="address-service">
                <BookingAddress :service="service"><FontAwesomeIcon icon="map-marked-alt" size="lg"/></BookingAddress>
            </div>
            
            <BookingAddress :iframe="true" :service="service">
                <FontAwesomeIcon icon="map-marked-alt" size="lg"/>
            </BookingAddress>
        </div>
        <div v-if="phoneSelected">
            <p>{{options.confirmation.phone}} <strong>{{ getClientPhone}}</strong></p>
        </div>
        <div v-if="skypeSelected">
            <p>{{options.confirmation.skype}} <strong>{{ getClientSkype }}</strong> </p>
        </div>
        <div class="my-2">
            <transition name="slide-fade">
                <SaveButtons v-if="showSaveButtons" :service="service" :showResult="showResult" :appointment="appointment"
                :staff="staff" :currentTz="timeprops.currentTz" :physicalSelected="physicalSelected"></SaveButtons>
                <span v-else class="wbtn-secondary wbtn" @click="showSaveButtons=true">
                    <FontAwesomeIcon icon="calendar-alt" size="lg"/> {{options.confirmation.savetocal}}
                </span>
            </transition>
        </div>
    </div>
</template>

<script>
import BookingAddress from './Address'
import DurationCell from './DurationCell'
import SaveButtons from './SaveButtons'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkedAlt, faPhone, faCalendarCheck, faCalendarAlt } from '@fortawesome/free-solid-svg-icons'
import { faSkype } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faMapMarkedAlt, faPhone, faSkype, faCalendarCheck,faCalendarAlt)
export default {
    components: {
        FontAwesomeIcon,
        BookingAddress,
        SaveButtons,
        DurationCell
    }, 
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
        selectedServiceType: '',
        showResult: null,
    }),

    created(){
        this.showResult = this.result
        this.selectedServiceType = this.showResult.type

        if(this.options.demoData !== undefined){
            this.options.eventsBus.listens('dataDemoChanged', this.dataChanged)
        }
    },
    computed: {
        getClientPhone(){
            if(this.showResult.client !== undefined){
                return this.showResult.client.options.phone
            }
            return this.showResult.phone
        },
        getClientSkype(){
            if(this.showResult.client !== undefined){
                return this.showResult.client.options.skype
            }
            return this.showResult.skype
        },
        phoneSelected(){
            return this.selectedServiceType == 'phone'
        },
        physicalSelected(){
            return this.selectedServiceType == 'physical'
        },
        skypeSelected(){
            return this.selectedServiceType == 'skype'
        },
    },
    methods: {
        dataChanged(dataNew){
            this.showResult = dataNew
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
    font-size: 1.65em;
    line-height: 1.2em;
}
.align-items-center {
    -webkit-box-align: center !important;
    -ms-flex-align: center !important;
    align-items: center !important;
}
.mb-2, .my-2 {
    margin-bottom: .5em !important;
}
.mt-2, .my-2 {
    margin-top: .5em !important;
}
</style>
