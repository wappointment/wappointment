<template>
    <div class="my-4 mx-2">
        <p class="h6 text-muted">{{get_i18n('cals_connect_external_desc', 'settings') }}</p>
        <div>
            <label for="calurl">{{get_i18n('cals_connect_external_paste', 'settings') }}</label>
            <div class="input-group input-group-sm ">
                
                <input type="text" id="calurl" v-model="calurl" class="form-control" placeholder="http://" @keyup.enter.prevent="saveCal" >
                <div class="input-group-append">
                    <button @click="saveCal" class="btn btn-primary btn-sm" type="button">{{get_i18n('save', 'common')}}</button>
                </div>
            </div>
            <p class="small text-right">{{get_i18n('cals_connect_external_how_url', 'settings') }} 
                <span class="btn btn-link btn-xs" @click="toggleModalGoogle">Google Calendar</span> 
                <span class="btn btn-link btn-xs" @click="toggleModalIcal">Apple iCal</span> 
                <span class="btn btn-link btn-xs" @click="toggleModalOutlook">Outlook Calendar</span>
            </p>

        </div>

        <div v-if="showPopup" >
            <a @click="hideModal" href="javascript:;">{{get_i18n('hide', 'common')}}</a>
            <h4 slot="title" class="modal-title">
                <span v-if="showGoogle">{{get_i18n('cals_connect_external_get_url', 'settings').replace('%s','Google Calendar') }}</span>
                <span v-if="showIcal">{{get_i18n('cals_connect_external_get_url', 'settings').replace('%s','Apple iCal') }}</span>
                <span v-if="showOutlook">{{get_i18n('cals_connect_external_get_url', 'settings').replace('%s','Outlook Live') }}</span>
            </h4>
            <div>
                <VideoIframe :src="getYouTubeUrl" />
            </div>
        </div>
    </div>
</template>

<script>
import RequestMaker from '../Modules/RequestMaker'
import VideoIframe from '../Ne/VideoIframe'
import ServiceCalendar from '../Services/V1/Calendars'

export default {
    mixins: [ RequestMaker],
    components: {VideoIframe},
    props: ['calendar'],

    data() {
        return {
            calurl: '',
            calendar_type: '',
            showGoogle: false,
            showIcal: false,
            showOutlook: false,
        } 
    },
    created(){
        this.mainService = this.$vueService(new ServiceCalendar)
    },

    watch: {
    // whenever question changes, this function will run
        calurl(newval,val){
            if(newval !== undefined) {
                if(newval.substr(0,9) == 'webcal://') this.calurl = newval.replace('webcal://','http://')
                if(newval.indexOf('outlook')!=-1) this.calendar_type = 'outlook'
                if(newval.indexOf('calendar.google')!=-1) this.calendar_type = 'google'
                if(newval.indexOf('icloud.com')!=-1) this.calendar_type = 'ical'
            }
        }
    },
    computed: {
        showPopup(){
            return this.showGoogle || this.showIcal || this.showOutlook
        },
        getYouTubeUrl(){
            if(this.showGoogle) return 'https://www.youtube.com/embed/5D_CfTJ9FzA'
            if(this.showIcal) return 'https://www.youtube.com/embed/H0A7Jc0G84Y'
            if(this.showOutlook) return 'https://www.youtube.com/embed/-BXafWQs8wg'
        }
    },
    methods: {
 
        hideModal(){
            this.showGoogle = this.showIcal = this.showOutlook = false
        },
        toggleModalGoogle(){
            this.hideModal()
            this.showGoogle = !this.showGoogle
        },
        toggleModalIcal(){
            this.hideModal()
            this.showIcal = !this.showIcal
        },
        toggleModalOutlook(){
            this.hideModal()
            this.showOutlook = !this.showOutlook
        },

        skipStep(){
            this.$emit('skipStep')
        },

        saveCal(){
            this.request(this.saveCalRequest, {
                calendar_id: this.calendar.id,
                calurl: this.calurl
                }, null ,false,this.saveCalSuccess)
        },
        saveCalSuccess(response){
            this.$emit('savedSync', response)
        },
        async saveCalRequest(params) {
            return await this.mainService.call('saveCal', params) 
        },

    }  
}
</script>