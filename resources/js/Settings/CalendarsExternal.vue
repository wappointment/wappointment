<template>
    <div class="container-fluid">
        <div class="d-flex">
            <h1>Connect External Calendar</h1>
        </div>
        
        <p class="h6 text-muted">Connect your external calendars, to automatically lock times when you're already busy</p>
        <div id="buttons-block">
            <div>
                <label for="calurl">Paste your calendar URL</label>
                <div class="input-group input-group-sm ">
                    
                    <input type="text" id="calurl" v-model="calurl" class="form-control" placeholder="http://" @keyup.enter.prevent="saveCal" >
                    <div class="input-group-append">
                        <button @click="saveCal" class="btn btn-primary btn-sm" type="button">Save Calendar</button>
                    </div>
                </div>
                <p class="small text-right">See how to get the URL of your calendar : 
                    <span class="btn btn-link btn-xs" @click="toggleModalGoogle">Google Calendar</span> 
                    <span class="btn btn-link btn-xs" @click="toggleModalIcal">Apple iCal</span> 
                    <span class="btn btn-link btn-xs" @click="toggleModalOutlook">Outlook Calendar</span>
                </p>

            </div>

            <div v-if="showPopup" >
                <a @click="hideModal" href="javascript:;">Hide</a>
                <h4 slot="title" class="modal-title">
                    <span v-if="showGoogle">Get your Google Calendar URL</span>
                    <span v-if="showIcal">Get your Apple iCal calendar URL</span>
                    <span v-if="showOutlook">Get your Outlook Live calendar URL</span>
                </h4>
                <div class="">
                    <VideoIframe :src="getYouTubeUrl" />
                </div>
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
    props: ['calendar_id'],
    data() {
        return {
            calurl: '',
            calendar: '',
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
                if(newval.indexOf('outlook')!=-1) this.calendar = 'outlook'
                if(newval.indexOf('calendar.google')!=-1) this.calendar = 'google'
                if(newval.indexOf('icloud.com')!=-1) this.calendar = 'ical'
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
                calendar_id: this.calendar_id,
                calurl: this.calurl
                }, null ,false,this.saveCalSuccess,this.saveCalError)
        },
        saveCalError(error){
            this.$emit('errorSaving',error)
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