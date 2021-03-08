<template>
    <div class="appointment-summary">
        <div class="wsummary-section wsec-service" v-if="service!== false">
            <div class="wlabel" v-if="hasText(['general','service'])">{{options.general.service}}</div>
            <div class="wselected wclosable wmy-4 d-flex align-items-center d-flex-inline" v-if="service" >
                <WapImage v-if="serviceHasIcon" :element="service" :desc="service.name" size="auto" />
                <span class="wml-2">
                    <ElementSelected :service="service" :duration="duration" :options="options" :cancellable="false"/>
                </span>
                <span v-if="canChangeService" class="wclose" @click="changeService"></span>
            </div>
        </div>
        <div class="wsummary-section wsec-location" v-if="location">
            <div class="wlabel" v-if="hasText(['general','location'])">{{options.general.location}}</div>
            <div class="wclosable wselected wmy-4 d-flex align-items-center d-flex-inline">
                <WapImage :element="location" :desc="location.name" size="auto" />
                <span class="welementname wml-2 lnh-1">{{ getLocationLabel }}</span>
                <a v-if="isPhysical" class="map-link lnh-1" :href="getMapAdress" target="_blank" >{{ getAddress }}</a>
                <span v-if="canChangeLocation" class="wclose" @click="changeLocation" ></span>
            </div>
        </div>
        <div class="wsummary-section wsec-starts" v-if="startsAt">
            <div class="wlabel"  v-if="hasText(['general','when'])">{{options.general.when}}</div>
            <div class="wselected wclosable wmy-4 d-flex align-items-center d-flex-inline">
                <WapImage :faIcon="['far','clock']" size="auto" />
                <span class="welementname wml-2">{{ startsAt }}</span>
                <span  class="wclose" @click="changeTime" ></span>
            </div>
        </div>
    </div>
</template>

<script>
import ElementSelected from './ElementSelected'
import MixinChange from './MixinChange'
export default {
    mixins:[window.wappointmentExtends.filter('MixinChange', MixinChange)],
    props: {
        service: {
            type: [Object, Boolean], 
        },
        duration:{
            type: [Number, Boolean],
        },
        startsAt:{
            type: [String, Boolean],
        },
        location:{
        },
        options:{
            type: Object
        },
        services:{
            type: Array
        },
        staffs:{
            type: Array
        },
        rescheduling:{},
        appointmentSaved: {}
    },
    components: { 
        ElementSelected: window.wappointmentExtends.filter('ElementSelected', ElementSelected)
     },
    computed:{
        getLocationLabel(){
            if([undefined,''].indexOf(this.location.name) === -1) return this.location.name
            if(this.isPhysical) return this.getAddress
            if(['phone',2].indexOf(this.location) !==-1) return this.options.form.byphone
            if(['skype',3].indexOf(this.location) !==-1) return this.options.form.byskype
            if(['zoom',5].indexOf(this.location) !==-1) return this.options.form.byzoom
        },
        isPhysical(){
            return ['physical',1].indexOf(this.location) !==-1
        },

        getAddress(){
            if(this.service.options.address !== undefined) return this.service.options.address
            if(this.service.address !== undefined) return this.service.address
        },

        getMapAdress(){
            return 'https://www.google.com/maps/search/?api=1&query=' + this.getEncodedAdress
        },
        getEncodedAdress(){
            return encodeURIComponent(this.getAddress);
        },
        serviceHasIcon(){
            return this.service.options.icon != ''
        }
    },
    methods:{
        
        hasText(searchOptions){
            let element = this.options
            for (let i = 0; i < searchOptions.length; i++) {
                if([undefined,''].indexOf(element[searchOptions[i]])){
                    element = element[searchOptions[i]]
                }else{
                    return false
                }
            }
            return true
        },
        changeTime(){
            this.$emit('changeService', 'BookingCalendar', {selectedSlot:false})
        }
    }
    
}
</script>
<style>
@import '../../css/closable.css';
.wap-front .wsummary-section {
    padding: .4em;
    font-size: .8em;
}

.wmy-4{
     margin: .4em 0;
}
.wap-front .wselected{
    border-radius: 1em;
    padding: .2em .7em;
    display: inline-block;
}

</style>

