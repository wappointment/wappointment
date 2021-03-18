<template>
    <WapModal  :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Thanks for updating Wappointment
          </h4>
          <div class="update-section">
              <h3  class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>V2.1.0 more services more calendars</span>    
            </h3>
            <p>This version is a major version with lots of improvements for your experience in the backend.</p>
            <p>Give us your feedback from the <strong>Wappointment > Help</strong> menu, we'd love to hear it.</p>
            <h5>Changelog 2.1.0</h5>
            <ol>
                <li class="my-2">Can create up to 3 services</li>
                <li class="my-2">Can create up to 2 calendars</li>
                <li class="my-2">Lot of interfaces improvements and bug fixes</li>
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
