<template>
    <WapModal  :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Thanks for updating Wappointment
          </h4>
          <div class="update-section">
              <h3  class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>V2.0.2 Zoom and Google Calendar bugs fixing</span>     
            </h3>
            <div class="intro">
                <div>Our <strong><img :src="resourcesUrl+'zoom.png'" /> Zoom</strong>
                    , <strong><img :src="resourcesUrl+'google-calendar.png'" /> Google Calendar</strong> integrations are getting better by the day.
                </div>
                <div class="mb-2">
                    Few of you were experiencing issue in connecting your Site to your Wappointment.com account, it should be working for everyone now.
                </div>
                <div>
                    Also for those of you crazy about metrics, we've added a JS hook so that you can count each time a client successfully books an appointment. (it will work for any analytics tool) <a href="javascript:;" @click="showCode=true">View code sample</a>
                    <pre v-if="showCode"><code>
document.addEventListener('wappo_confirmed', function (e) {
    //insert analytics code below

}, false);
</code></pre>
                </div>
            </div>

            <h5>Changelog 2.0.2</h5>
            <ol>
                <li class="my-2">Added js hook for analytics on booking confirmation</li>
                <li class="my-2">Added missing editable text for appointment viewing</li>
                <li class="my-2">Fixed error when trying to Connect account to Zoom and Google Calendar</li>
                <li class="my-2">Fixed calendar loading unlimited loop error with recurrent event</li>
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
