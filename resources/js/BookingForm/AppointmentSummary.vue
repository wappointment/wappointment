<template>
    <div class="appointment-summary">
        <div class="wsummary-section wsec-service" v-if="service!== false">
            <div class="wlabel" v-if="hasText(['general','service'])">{{options.general.service}}</div>
            <div class="wselected wmy-4">
                <ElementSelected :service="service" :duration="duration" :options="options" :cancellable="false"/>
            </div>
        </div>
        <div class="wsummary-section wsec-starts" v-if="startsAt">
            <div class="wlabel"  v-if="hasText(['general','when'])">{{options.general.when}}</div>
            <div class="wselected wmy-4">
                <FontAwesomeIcon :icon="['far','clock']" /> {{ startsAt }}
            </div>
        </div>
        <div class="wsummary-section wsec-location" v-if="location && false">
            <div class="wlabel" v-if="hasText(['general','location'])">{{options.general.location}}</div>
            <div class="closable wselected wmy-4">
                <FontAwesomeIcon :icon="getLocationIcon" /> 
                <span v-if="!isPhysical">{{ getLocationLabel }}</span>
                <a v-else :href="getMapAdress" target="_blank" >{{ getLocationLabel }}</a>
                <span class="close" @click="changeLocation"></span>
            </div>
        </div>
    </div>
</template>

<script>
const FontAwesomeIcon = () => import(/* webpackChunkName: "appFawesome" */ '../appFawesome')
import ElementSelected from './ElementSelected'
export default {
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
        rescheduling:{}
    },
    components: {
        ElementSelected, FontAwesomeIcon
    },
    computed:{
        getLocationLabel(){
            if(this.location == 'physical') return this.getAddress
            if(this.location == 'phone') return this.options.form.byphone
            if(this.location == 'skype') return this.options.form.byskype
        },
        isPhysical(){
            return this.location == 'physical'
        },
        getLocationIcon(){
            if(this.isPhysical) return 'map-marked-alt'
            if(this.location == 'phone') return 'phone'
            if(this.location == 'skype') return ['fab','skype']
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
    },
    methods:{
        changeLocation(){
            this.$emit('changeLocation', {loading:false, location:''})
        },
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

