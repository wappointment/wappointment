<template>
    <WapModal :screenshot="true" :right="true" :show="show" @hide="hideModal">
          <h4 slot="title" class="modal-title">You've just updated to version 1.9</h4>
          <div>
              <h3>Precision on weekly availability and a few usability improvements</h3>
              <ol>
                  <li>You can now set your weekly availability with more precision each 10min, 15min, 20min, 30min instead of only each 60min</li>
                  <li>Your admin calendar view becomes friendlier, you can now decide the start and end time to be displayed (e.g.: from 8am til 11pm)</li>
                  <li>The booking form is now full screen always on your phone. It is so much better!</li>
              </ol> 
             
              <div class="wrapping-update text-center">
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
        interval:false
    }),


    computed:{
        getUpdateImage(){
            return apiWappointment.apiSite + '/versions_update/v19/' + this.image_number + '.png'
        }
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
</style>
