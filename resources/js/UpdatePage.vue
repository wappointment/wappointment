<template>
    <WapModal :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Thanks for updating!
          </h4>
          <div class="update-section">
              <h3 class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>V2.4.11</span>   
                <span class="ml-2">Bug fixes and improvements</span>
              </h3>
              <div class="d-flex flex-align-items-center">
                  <div class="ml-3">
                    
                    <p>Changelog:</p>
                    <ol>
                        <li>added search box and improved pagination on media </li>
                        <li>fixed phone number and skype missing from notification email </li>
                        <li>fixed issues with .ics calendar import</li>
                        <li>fixed set staff page on new event link</li>
                        <li>fixed availability filter out old slots</li>
                        <li>fixed issue auto-increment on availability slots generation in the frontend</li>
                        <li>fixed jitsi meeting link showing too early </li>
                        <li>fixed missing jitsi link from backend </li>
                        <li>fixed admin duration when buffer, in emails and in admin</li>
                        <li>fixed save to calendar button when booking confirmed for firefox and chrome</li>
                        <li>fixed wrong duration sent to google cal </li>
                        <li>fixed when no timezone is set in ics calendar default to staff timezone </li>
                    </ol>
                    
                  </div>
              </div>

            <div class="wprevious-version" >
                <a href="javascript:;" @click="showPrevious">View previous version 2.4.0</a>
                <div v-if="previous">
                    <div>
                        <p>Our latest release comes with the release of a new addon too "Group Events". It will let you create events for which multiple participants will be able to attend.</p>
                    <div class="d-flex">
                        <img :src="wappoVersionImage('240', 'group_event.png')" alt="Group Event" class="imgv240 img-fluid img-update" title="Group Event"/>
                        <img :src="wappoVersionImage('240', 'group_event_slots_selection.png')" alt="Slot selection" class="imgv240 img-fluid img-update" title="Slot selection"/>
                    </div>
                    </div>
                    <div class="text-muted">
                        <h5 class="text-muted">Changelog 2.4.0</h5>
                        <ol>
                    <li>Added default settings for availability and assigned services for new staff</li>
                    <li>Added option to open Zoom meeting straight in the browser without the app</li>
                    <li>Added mark as paid and cancel button in orders listing</li>
                    <li>Added option to differentiate free event from Outlook</li>
                    <li>Added cancel, reschedule and join meeting links to [wap_history] page</li>
                    <li>Added allow to cancel 30 minutes before appointments take place</li>
                    <li>Added pending appointment admin notification</li>
                    <li>Added many translatable strings</li>
                    <li>Added compatibility with fluent SMTP plugin</li>
                    <li>Fixed cache issue, staff listing in <strong>Settings > Calendars & Staff</strong> was dissappearing</li>
                    <li>Improved, plugin is lighter</li>
                    <li>Improved installation issue detection</li>
                    <li>Many improvements and refactoring</li>
                </ol>
                    </div>
                </div>
            </div>
          </div>
    </WapModal>
</template>
<script>
import abstractView from './Views/Abstract'

export default {
    extends: abstractView,
    data: () => ({
        show: true,
        images : [1,2],
        image_number: 1,
        previous: false
    }),
     
    computed:{
        getUpdateImg(){
            return window.apiWappointment.apiSite + '/plugin/' + window.apiWappointment.version + '/update.png'
        },
    },

    methods:{
        showPrevious(){
            this.previous = !this.previous
        },
        hideModal(){
            this.show = false
        },

        settingsSaved(){
            //nothing happens yet
        }
    },
   
    created(){
        this.show = [undefined,''].indexOf(window.wappointmentAdmin.canSeeUpdate) === -1
        if(this.show){
            this.settingStaffSave('viewed_updates', true, this.settingsSaved) 
        }
    },
}
</script>
<style >
.wrapping-update{
    width: 450px;
}
.update-section .intro{
    background-color: #6b6a8e;
    font-size: 1.25rem;
    padding: 1rem;
    border-radius: 1rem;
    color: #fff;
    line-height: 1.8;
    margin: 1rem 0;
}
.update-section pre{
    background: #353535;
    border-radius: 1rem;
    padding: 0 1rem;
    font-size: .8em;
}
.update-section a{
    color: var(--orange);
}
.img-update{
    border: 2px solid #f0f0f0;
    border-radius: 1em;
    margin-bottom: 1em;
}
.wprevious-version{
    background: #eee;
    border-radius: 1.4em;
    padding: 1em;
}
.img-update.imgv240{
    max-width:360px;
}
</style>
