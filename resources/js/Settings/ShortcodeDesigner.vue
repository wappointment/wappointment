<template>
    <div>
        <div class="shortcode-editor">
            <div class="shortcode-gen">
                <ShortcodeGenerator @change="updateShortCode" :title="title" 
                :calendar_id="calendar_id" :service_id="service_id"
                :calendars="calendars" :services="services" :simple="simple" 
                :canLockStaff="canLockStaff" 
                :canLockService="canLockService" />
            </div>
            <div id="shortcode_block">
                <p class="m-0">Your shortcode: </p>
                <ClickCopy :value="shortcode" @clicked="clicked=true"/>
                <transition name="fade" >
                    <div v-if="clicked">
                        <div v-if="!canLockStaff && shortCodeHasCalendar" class="text-danger text-left small">The lock on calendar will work only with the <strong><a :href="addonLink" target="_blank">Calendars & Staff</a></strong> addon</div>
                        <div v-if="!canLockService && shortCodeHasService" class="text-danger text-left small mt-2">The lock on service will work only with the <strong><a :href="addonLink" target="_blank">Services Suite</a></strong> addon</div>
                    </div>
                </transition>
                
            </div>
        </div>
        <div class="mt-4" v-if="showingTip">
            <h4>How can I use this shortcode?</h4>
            <p>Use the shortcode in your page(s) or post(s) </p>
            <VideoIframe src="https://www.youtube.com/embed/VMi2Ry-JrGA" />
        </div>
        <a v-else href="javascript:;" @click="showingTip=true">How can I use this shortcode?</a>
    </div>
</template>

<script>
import ClickCopy from '../Fields/ClickCopy'
import ShortcodeGenerator from './ShortcodeGenerator'
import Helpers from '../Modules/Helpers'
import VideoIframe from '../Ne/VideoIframe'

export default {
    mixins: window.wappointmentExtends.filter('shortcodeDesignerMixins', [Helpers]),
    props:{
        title: {
            type: String,
            default: 'Book now!'
        },
        calendar_id: {
            default: false
        },
        service_id: {
            default: false
        },

        services:{
            default:null
        },
        calendars:{
            default:null
        },
        simple: {
            default: false
        },
    },
    data: () => ({
        shortcode: '',
        clicked: false,
        canLockStaff: false,
        canLockService: false,
        showingTip:false
    }),
    components: {ClickCopy, VideoIframe, ShortcodeGenerator}, 
    methods: {
        updateShortCode(shortcode){
            this.shortcode = shortcode
        },
    },
    computed:{
        shortCodeHasService(){
            return this.shortcode.indexOf('service="') !== -1
        },
        shortCodeHasCalendar(){
            return this.shortcode.indexOf('calendar="') !== -1
        },
        addonLink(){
            return apiWappointment.apiSite+'/addons'
        },
    }
}
</script>
<style>

#shortcode_block{
    border: 2px solid var(--primary);
    border-radius: .3rem;
    padding: 1rem;
    text-align: center;
}
#shortcode_block p{
    font-size: 1.4rem;
}
.shortcode-editor{
    max-width:600px;
}
.shortcode-gen{
    background-color: #f5f5f5;
    padding: .5em;
    border-radius: .4em;
}

</style>