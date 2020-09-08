<template>
    <WapModal :screenshot="true" :right="true" :show="show" @hide="hideModal">
          <h4 slot="title" class="modal-title">You've just updated</h4>
          <div>
              <h3>We've changed the design of the booking form</h3>
              <div>Please review your booking form in <strong>Wappointment > Settings > General > Booking Widget Editor</strong></div> 
              <div><small class="text-muted">We've added a section below the header to describe what your client is booking</small></div>
              <img src="https://ps.w.org/wappointment/assets/booking_form_updated.png?rev=2151020" alt="Updated booking form" >   
          </div>
    </WapModal>

</template>
<script>
import abstractView from '../Views/Abstract'

export default {
    extends: abstractView,
    data: () => ({
        show: true
    }),

    methods: {
        hideModal(experience){
            this.show = false
        },
    },
   
    created(){
        this.show = [undefined,''].indexOf(window.wappointmentAdmin.canSeeUpdate) === -1
        if(this.show){
            this.settingStaffSave('viewed_updates', true) 
        }
        
    },
}
</script>
