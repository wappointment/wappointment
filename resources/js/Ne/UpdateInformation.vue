<template>
    <WapModal  :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">This is Wappointment 2.0 
          </h4>
          <div>
              <h3  class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>Connect your appointments to Zoom, Google Calendar, Google Meet</span>     
            </h3>
            <iframe width="540" height="301" src="https://www.youtube.com/embed/wEE8yRh6pP4" 
            frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
              <h5 class="my-2"> <span>Other Improvements</span></h5>
              <ol>
                  <li class="my-2">Email editor has been improved</li>
                  <li class="my-2">Phone input improved, flags can be reordered</li>
                  <li class="my-2">Works with WP Mail SMTP plugin</li>
              </ol>
              <div class="wrapping-update text-center" v-if="slideshow">
                  <div class="d-inline-flex m-2">
                      <div v-for="n in images">
                          <div class="btn btn-xs" :class="[n==image_number ? 'btn-primary':'btn-secondary']" @click="goTo(n)">{{n}}</div>
                      </div>
                  </div>
                  <div class="d-flex">
                      <button class="btn btn-secondary" @click="prev"><span class="dashicons dashicons-arrow-left-alt2"></span></button>
                      <img :src="getUpdateImage" alt="Version 1.9" >
                      <button class="btn btn-secondary" @click="next(true)"><span class="dashicons dashicons-arrow-right-alt2"></span></button>
                  </div>
              </div>
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
        getUpdateImage(){
            return apiWappointment.apiSite + '/versions_update/v19/' + this.image_number + '.png'
        }
    },

    methods:{
        getImage(number){
            return apiWappointment.apiSite + '/versions_update/v19/' + number + '.png'
        },
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
</style>
