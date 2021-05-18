<template>
    <div>
      <div class="wdescription" v-if="sellIndividually && !showGPacks">{{options.woo_payment.complete}}</div>
      <div class="wdescription">{{ getAppointmentReservedString }}</div>
      
    </div>
</template>

<script>
import OrderService from '../Services/V1/Order'
import AbstractFront from './AbstractFront'
import IsDemo from '../Mixins/IsDemo'
import HasWooVariables from '../Mixins/HasWooVariables'
export default {
    extends: AbstractFront,
    mixins:[IsDemo, HasWooVariables],
    props: ['options', 'relations', 'appointmentKey', 'appointmentData', 'service'],
     data: () => ({
        showGPacks: false,
        selectedPack: false,
        servicesOrder: null
    }),
    created(){
      this.servicesOrder = this.$vueService(new OrderService) 
    },

    computed: {
      sellPacks(){
        return this.service.options.woo_packs_defined === true && this.service.options.woo_packs.length > 0
      },
      getPacks(){
        return this.service.options.woo_packs
      },
      sellIndividually(){
        return [undefined,false].indexOf(this.service.options.woo_individually) === -1
      },
      getAppointmentReservedString(){
        return this.options.woo_payment.slot_reserved.replace('[minutes]', this.reserved_for) 
      },

      checkout_url(){
        let url = window.wappointment_woocommerce.checkout_url
        url += url.indexOf('?') === -1 ? '?':'&'
        url += 'wappo_module_off=1'
        return url
      },
      serviceDuration(){
        return (this.appointmentData.end_at - this.appointmentData.start_at) / 60
      },
      servicePrice(){
        if(this.service.options.durations !== undefined){
          for (let i = 0; i < this.service.options.durations.length; i++) {
            if(this.serviceDuration == this.service.options.durations[i].duration){
              return this.service.options.durations[i].woo_price 
            }
          }
        }else{
          return this.service.options.woo_price
        }
      }
    },
    methods: {
      
      selectPack(pack){
        this.selectedPack = false
        this.$emit('loading', {loading:true})
        //send a query to update order
        this.updateOrderRequest({action: 'selectPack', pack: pack, 'edit_key':this.appointmentKey})
            .then(this.updatedOrderSuccess)
       
      },

      async updateOrderRequest(params) {
            return await this.servicesWooBooking.call('updateOrder', params) 
      },
      updatedOrderSuccess(r){
        this.$emit('loading', {loading:false})
         this.selectedPack = r.data.pack
      },

      cancelReservation(){
        if(this.triggersDemoEvent('form')){
            return
        }
        this.$emit('loading', {loading:true})
        this.cancelReservationRequest()
            .then(this.canceledReservationSuccess)
            .catch(this.canceledReservationFailure)
      },
      
      async cancelReservationRequest() {
            return await this.servicesWooBooking.call('cancel', {'edit_key':this.appointmentKey}) 
      },

      canceledReservationFailure(){
        this.serviceError({message:'Error Cancelling reservation'})
        this.$emit('loading', {loading:false})
      },

      canceledReservationSuccess(re){
        this.$emit('cancelledPayment', this.relations.prev, {appointmentSaved:false,loading:false})
      },

    }
}   
</script>