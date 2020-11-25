<template>
    <div>
        <div v-if="physicalSelected">
            <p>{{options.confirmation.physical}} </p>
            <div class="address-service">
                <BookingAddress :service="service">
                    <WapImage :faIcon="'map-marked-alt'" size="md" /></BookingAddress>
            </div>
            
            <BookingAddress :iframe="true" :service="service">
                <WapImage :faIcon="'map-marked-alt'" size="md" />
            </BookingAddress>
        </div>
        <div v-if="phoneSelected">
            <div class="wdescription">{{options.confirmation.phone}} <strong>{{ showResult.phone}}</strong></div>
        </div>
        <div v-if="skypeSelected">
            <div class="wdescription">{{options.confirmation.skype}} <strong>{{ showResult.skype}}</strong> </div>
        </div>
        <div v-if="zoomSelected">
            <div class="wdescription">{{options.confirmation.zoom}} <strong>{{ showResult.zoom}}</strong> </div>
        </div>
    </div>
</template>
<script>
import BookingAddress from './Address'
import MixinTypeSelected from './MixinTypeSelected'
export default {
    props: ['options', 'service', 'selectedServiceType', 'showResult', 'appointment'],
    components: {
        BookingAddress,
    },
    mixins:[MixinTypeSelected],
    data: () => ({
        selection: '',
        presentResult: null
    }),
    created(){
        if(this.appointment!== undefined){
            this.selection == this.appointment.type
            this.presentResult = this.appointment
        }else{
            if(this.selectedServiceType!== undefined) {
                this.selection == this.selectedServiceType
                this.presentResult = showResult
            }
            
        }
    },
}
</script>