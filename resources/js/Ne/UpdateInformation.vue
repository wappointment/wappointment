<template>
    <WapModal :screenshot="true" :right="true" :show="show" @hide="hideModal">
          <h4 slot="title" class="modal-title">You've just updated to version 1.8</h4>
          <div>
              <h3>We've made many small improvements</h3>
              <ol>
                  <li>The booking form is improved again, it works better on mobile phone</li>
                  <li>You can now quickly modify the active staff name appearing in the booking widget's header</li>
                  <li>There is a new clients' listing; shortly new features will improve it</li>
                  <li>You can now <strong>sell your services with packages</strong> of hours and sessions in <a href="https://wappointment.com/addons/appointments-for-woocommerce?utm_source=plugin&utm_medium=link&utm_campaign=update18" target="_blank">our WooCommerce Addon</a>
                  <div class="small text-muted"> See below how your booking form will look like with the package option</div></li>
              </ol> 
             
              <div class="wrapping-update text-center">
                  <div class="d-inline-flex m-2">
                      <div v-for="n in images">
                          <div class="btn btn-xs" :class="[n==image_number ? 'btn-primary':'btn-secondary']" @click="goTo(n)">{{n}}</div>
                      </div>
                  </div>
                  <div class="d-flex">
                      <button class="btn btn-secondary" @click="prev"><span class="dashicons dashicons-arrow-left-alt2"></span></button>
                      <img :src="getUpdateImage" alt="Package installed" > 
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
        images : [1,2,3,4,5,6,7],
        image_number: 1,
        interval:false
    }),


    computed:{
        getUpdateImage(){
            return apiWappointment.apiSite + '/pack/view_' + this.image_number + '.png'
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
