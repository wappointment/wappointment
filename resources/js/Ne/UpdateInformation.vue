<template>
    <WapModal  :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Thanks for updating Wappointment
          </h4>
          <div class="update-section">
              <h3  class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>V2.0.1 is about fixing bugs and improvements</span>     
            </h3>
            <div class="intro">
                <div>We've worked hard these last 10 days after releasing a major release 2.0.0 .</div>
                <div><strong>2.0.0</strong> was about integrating with important tools : <strong><img :src="resourcesUrl+'zoom.png'" /> Zoom</strong>
                    , <strong><img :src="resourcesUrl+'google-calendar.png'" /> Google Calendar</strong> and <strong><img :src="resourcesUrl+'google-meet.png'" /> Google Meet</strong>.
                </div>
                <div class="mb-2">
                    Obviously being a new version, there were quite a few bugs and there might be a few more that we're still unaware of.
                </div>
                <div>Luckily some of you are really helping improving Wappointment every day with quality feedback.</div>
                <h3 class="text-white my-2">Many thanks for your patience!</h3>
                <div>Maintaining Wappointment is hard, but it gets a lot easier when you join in.</div>
                <div>So please don't hesitate to drop us a line for anything, we'll be happy to chat.</div>
            </div>

            <h5>Changelog 2.0.1</h5>
            <ol>
                <li class="my-2">Email sending is now working with the popular SMTP plugins: <strong>WP Mail SMTP</strong> and <strong>Post SMTP</strong></li>
                <li class="my-2">Added Meeting link straight in the admin email confirmation</li>
                <li class="my-2">Added Save to calendar button for Outlook Live</li>
                <li class="my-2">Fixing of the Save to calendar buttons, working now with Zoom and Google Meet</li>
            </ol>
            <h5>2 min - Introduction video about Zoom/Google Calendar integrations</h5>
            <iframe width="540" height="301" src="https://www.youtube.com/embed/wEE8yRh6pP4" 
            frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

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
        slideshow: false
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
</style>
