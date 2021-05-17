<template>
    <WapModal :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Thanks for updating Wappointment
          </h4>
          <div class="update-section">
              <h3  class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>V2.2.1</span>   
                <span class="ml-2">Bug fix and optimization off</span>
            </h3>
            <div>
               You can now turn ON cache from <strong>Wappointment > Settings > Advanced > Wappointment's Cache</strong> in order to speed up the availability check in the frontend booking form.
            </div>
            <h5>Changelog 2.2.1</h5>
            <ol>
                <li>disabling Wappointment's cache by default</li>
                <li>added hook after booking</li>
                <li>fixed style on button in booking form relative size</li>
                <li>fixed issue undefined email when editing Staff</li>
            </ol>
            <div>
                <span class="h5">Below is how your staff selection page looks like now.</span>
                <img :src="getVersionImage('220', 'staff_page.gif')" alt="staff page" class="img-fluid img-update" title="staff page"/>
            </div>
            <h5>Changelog 2.2.0</h5>
            <ol>
                <li>Added staff selection page in booking form </li>
                <li>First booking widget screen can now be a staff selection page using a shortcode attribute </li>
                <li><strong>Optimization made</strong> on calendars availability check requests</li>
                <li>Added Staff field, for extra information describing your staff(to be used in emails and SMS reminders)</li>
                <li>Added <strong>staff permissions</strong> to allow staff to modify their own availability, etc...</li>
                <li>added <strong>appointment history</strong> shortcode for logged in users [wap_history]</li>
                <li>Admin notifications emails are now translatable with LocoTranslate or other translations system alike</li>
                <li>Improved overall usability in the backend interfaces</li>
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
        getVersionImage(version, img){
            return window.apiWappointment.apiSite +'/images/v'+version+'/'+img
        },
        hideModal(){
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
.img-update{
    border: 2px solid #f0f0f0;
    border-radius: 1em;
    margin-bottom: 1em;
}
</style>
