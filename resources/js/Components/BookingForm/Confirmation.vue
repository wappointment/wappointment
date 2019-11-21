<template>
    <div class="confirmation-cell">
        <div class="success d-flex align-items-center">
            <div class="bigicon"><FontAwesomeIcon icon="calendar-check" /> </div>
            <div class="text-conf">{{options.confirmation.confirmation}}</div>
        </div>
        <ul class="li-unstyled my-2">
            <li><strong>{{ service.name }}</strong> - {{ service.duration }}min</li>
            <li><strong>{{ getMoment(selectedSlot, timeprops.currentTz).format(timeprops.fullDateFormat) }}</strong></li>
        </ul>
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
            <p>{{options.confirmation.phone}} <strong>{{ showResult.phone}}</strong></p>
        </div>
        <div v-if="skypeSelected">
            <p>{{options.confirmation.skype}} <strong>{{ showResult.skype}}</strong> </p>
        </div>
        <div >
            <hr>  

            <div v-if="!showSaveButtons">
                <transition name="slide-fade">
                    <span class="btn-secondary btn" @click="showSaveButtons=true">
                        <FontAwesomeIcon icon="calendar-alt" size="lg"/> {{options.confirmation.savetocal}}
                    </span>
                </transition>
            </div>
            
            <div  v-else="showSaveButtons">
                <transition name="slide-fade">
                    <SaveButtons :selectedSlot="selectedSlot" :service="service"
                    :staff="staff" :currentTz="timeprops.currentTz" :physicalSelected="physicalSelected"></SaveButtons>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
import BookingAddress from './Address'
import SaveButtons from './SaveButtons'
import Dates from "../../Modules/Dates";
import { library } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkedAlt, faPhone, faCalendarCheck, faCalendarAlt } from '@fortawesome/free-solid-svg-icons'
import { faSkype } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faMapMarkedAlt, faPhone, faSkype, faCalendarCheck,faCalendarAlt)
export default {
    mixins: [Dates],
    components: {
        FontAwesomeIcon,
        BookingAddress,
        SaveButtons
    }, 
    props: [
        'selectedSlot', 
        'service', 
        'result', 
        'isApprovalManual', 
        'timeprops',
        'staff',
        'options'
    ],
    data: () => ({
        showSaveButtons: false,
        selectedServiceType: '',
        showResult: null
    }),

    created(){
        
        this.showResult = this.result
        this.selectedServiceType = this.showResult.type

        if(this.options.demoData !== undefined){
            this.options.eventsBus.listens('dataDemoChanged', this.dataChanged)
        }
       
    },
    computed: {
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
