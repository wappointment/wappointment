<template>
    <WapModal :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Thanks for updating Wappointment
          </h4>
          <div class="update-section">
              <h3  class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>V2.1.2 bug fixes</span>    
            </h3>
            <p>Version 2.1.0 was a major version allowing you to create more services and calendars.</p>
            <p>Being such a big release, there are a few bugs that came with it so here comes 2.1.1</p>
            <p>Like or Dislike something about the new version? </p>
            <p>Give us your feedback from the <strong>Wappointment > Help</strong> menu, we'd love to hear it.</p>
            <h5>Changelog 2.1.2</h5>
            <ol>
                <li>Fixed triple email notifications sent</li>
                <li>Fixed issue installation on WordPress Multisite (db foreign keys name issue)</li>
            </ol>
            <h5>Changelog 2.1.1</h5>
            <ol>
                <li class="my-2">Fixed problem to replace calendar/staff image</li>
                <li class="my-2">Fixed improper service duration appearing in reschedule process of appointment</li>
                <li class="my-2">Fixed missing translatable strings in booking widget editor</li>
                <li class="my-2">Fixed multiduration when locked bookable service not being editable</li>
                <li class="my-2">Fixed false duration in header compact mode</li>
                <li class="my-2">Fixed issue calendar synch and non standard timezones</li>
                <li class="my-2">Fixed issue emails not sending when "WP PGP Encrypted Emails" installed</li>
                <li class="my-2">Fixed issue backend calendar page not showing with out orf range value of regav</li>
                <li class="my-2">Fixed issue with booking confirmation message initial import</li>
            </ol>
          </div>
    </WapModal>
</template>
<script>
import abstractView from '../Views/Abstract'

export default {
    extends: abstractView,
    data: () => ({
        show: true,
        images : [1,2],
        image_number: 1,
        interval:false,
        slideshow: false,
        showCode: false
    }),

    computed:{
        getUpdateImg(){
            return window.apiWappointment.apiSite + '/plugin/' + window.apiWappointment.version + '/update.png'
        },
        resourcesUrl(){
            return window.apiWappointment.resourcesUrl+'images/'
        },
    },

    methods:{

        hideModal(experience){
            this.show = false
        },

        prev(){
            this.clearInterval()
            if(this.image_number > 1){
                this.image_number --;
            }
        },
        next(manual = false){
            if(manual !== false){
                this.clearInterval()
            }
            if(this.image_number < 7){
                this.image_number++;
            }else{
                this.image_number = 1
            }
        },
        goTo(n){
            this.clearInterval()
            this.image_number = n
        },
        clearInterval(){
            if(this.interval){
                clearInterval(this.interval)
            }
        },
        settingsSaved(){
            this.interval = setInterval(this.next.bind(null,false), 2000)
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
</style>
