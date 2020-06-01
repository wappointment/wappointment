<template>
    <div class="container-fluid" v-if="dataLoaded">
        <div class="d-flex">
            <h1>Connect Personal Calendar</h1>
        </div>
        
        <p class="h6 text-muted">Connect your personal calendar, to automatically lock times when you're already busy</p>
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

                <div v-if="viewData.last_checked" class="alert alert-secondary">
                    <p class="text-muted m-0">Process duration: {{ calDuration }}</p>
                    <p class="text-muted m-0">{{ calDetected }} events were detected</p>
                    <p class="text-muted m-0">{{ calInserted }} events were inserted</p>
                    <p class="text-muted m-0">{{ calDeleted }} events were deleted</p>
                    <p class="text-muted m-0">{{ calIgnored }} events were ignored</p>
                </div>
            </div>

            <WapModal :screenshot="true"  :show="showPopup" @hide="hideModal">
                <h4 slot="title" class="modal-title">
                    <span v-if="showGoogle">Get your Google Calendar URL</span>
                    <span v-if="showIcal">Get your Apple iCal calendar URL</span>
                    <span v-if="showOutlook">Get your Outlook Live calendar URL</span>
                </h4>
                <div class="">
                    <VideoIframe :src="getYouTubeUrl" />
                </div>
            </WapModal>

        </div>
    
    </div>
</template>

<script>
import abstractView from '../Abstract'
import CalUrl from "../../Modules/CalUrl"
import VideoIframe from '../../Ne/VideoIframe'
export default {
  extends: abstractView,
  mixins: [CalUrl],
  components: {VideoIframe},
  data() {
      return {
          calurl: '',
          calendar: '',
          viewName: 'calsync',
          showGoogle: false,
          showIcal: false,
          showOutlook: false,
      } 
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
            this.showGoogle = !this.showGoogle
        },
        toggleModalIcal(){
            this.showIcal = !this.showIcal
        },
        toggleModalOutlook(){
            this.showOutlook = !this.showOutlook
        },
        loaded(viewData){
            this.viewData = viewData.data
        },
        
        skipStep(){
            this.$emit('skipStep')
        },

        saveCal(){
            this.request(this.saveCalRequest, {calurl: this.calurl}, null ,false,this.saveCalSuccess,this.saveCalError)
        },
        saveCalError(error){
            this.$emit('errorSaving',error)
        },
        saveCalSuccess(response){
            this.$emit('savedSync', response)
        },
        async saveCalRequest(params) {
            return await this.serviceSettingStaff.call('saveCal', params) 
        },

  }  
}
</script>