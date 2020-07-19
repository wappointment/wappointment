<template>
    <div>
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
    </div>
</template>
<script>
import BookingAddress from './Address'
export default {
    props: ['options', 'service', 'selectedServiceType', 'showResult', 'appointment'],
    components: {
        BookingAddress,
    },
    data: () => ({
        selectedServiceTypeDD: '',
        presentResult: null
    }),
    created(){
        if(this.appointment!== undefined){
            this.selectedServiceTypeDD == this.appointment.type
            this.presentResult = this.appointment
        }else{
            if(this.selectedServiceType!== undefined) {
                this.selectedServiceTypeDD == this.selectedServiceType
                this.presentResult = showResult
            }
            
        }
    },
    computed: {
        phoneSelected(){
            return this.selectedServiceTypeDD == 'phone'
        },
        physicalSelected(){
            return this.selectedServiceTypeDD == 'physical'
        },
        skypeSelected(){
            return this.selectedServiceTypeDD == 'skype'
        },
    },
}
</script>