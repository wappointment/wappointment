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
                <a v-if="isPhysical" class="wml-2 map-link lnh-1" :href="getMapAdress" target="_blank" >{{ getAddress }}</a>
                <span v-else class="welementname wml-2 lnh-1">{{ getLocationLabel }}</span>
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
        <div class="wsummary-section wsec-package" v-if="[false,null].indexOf(selectedPackage)===-1">
            <div class="wlabel"  v-if="hasText(['general','package'])">{{options.general.package}}</div>
            <div class="wselected wclosable wmy-4 d-flex align-items-center d-flex-inline">
                <WapImage v-if="packageHasIcon" :element="selectedPackage" :desc="selectedPackage.options.name" size="auto" />
                <span class="welementname wml-2">{{ selectedPackage.options.name }}</span>
                <span class="wcredits wsep">{{selectedVariation.credits}} {{selectedPackage.type_label}}</span>
                <span class="wprice wsep">{{ formatPrice(selectedVariation.price) }}</span>
                <span  class="wclose" @click="changePackage" ></span>
            </div>
        </div>
    </div>
</template>

<script>
import ElementSelected from './ElementSelected'
import MixinChange from './MixinChange'
import MixinChangeCommands from './MixinChangeCommands'
import IsDemo from '../Mixins/IsDemo'
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    mixins:[window.wappointmentExtends.filter('MixinChange', MixinChange), MixinChangeCommands,IsDemo, CanFormatPrice],
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
        attributesEl: {
            type:Object
        },
        staff:{
            type:Object
        },
        services:{
            type: Array
        },
        staffs:{
            type: Array
        },
        rescheduling:{},
        appointmentSaved: {},
        selectedVariation:{},
        selectedPackage:{
            type:[Object, Boolean],
            default:null
        },
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
        },
        packageHasIcon(){
            return this.selectedPackage.options.icon != ''
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
        },
        changePackage(){
            this.$emit('changeService', 'PackagesSelection', {selectedPackage:false, selectedVariation: false})
        }
    }
    
}
</script>
<style>
@import '../../css/closable.css';
.wap-front .wsummary-section {
    font-size: .8em;
    padding: .4em;
}

.wmy-4{
     margin: .4em 0;
}
.wap-front .wselected{
    border-radius: 1em;
    padding: .2em .7em;
    display: inline-block;
}
.wselected .map-link{
    color: var(--wappo-pri-tx) !important;
}
.wselected .map-link:after{
    content: "\2192";
}

</style>

