<template>
    <div>
      <div class="wdescription" v-if="hasPaidDurations">Payment time!</div>
      <div class="wdescription">Your appointment time is reserved for the next 20 minutes giving you time to pay</div>
      <div class="wtabs d-flex">
        <div class="wtab active">
            <div class="d-flex">
              <WImage :image="getImage('visa')" class="wstripe"/>
              <WImage :image="getImage('mastercard')" class="wstripe"/>
              <WImage :image="getImage('amex')" class="wstripe"/>
            </div>
            <div class="d-flex wpowered">
              <span>Pay safely with</span> <WImage :image="getImage('stripe','.png')" class="wstripe"/>
            </div>
        </div>
        <div class="wtab">
            <div class="d-flex">
              <WImage :image="getImage('paypal','.png')" />
            </div>
        </div>
      </div>
      <div class="wpayment">

      </div>
    </div>
</template>

<script>
import WImage from '../WComp/WImage'
import OrderService from '../Services/V1/Order'
import AbstractFront from './AbstractFront'
import IsDemo from '../Mixins/IsDemo'
import HasWooVariables from '../Mixins/HasWooVariables'
import HasPaidService from '../Mixins/HasPaidService'
export default {
    extends: AbstractFront,
    mixins:[IsDemo, HasWooVariables, HasPaidService],
    props: ['options', 'relations', 'appointmentKey', 'appointmentData', 'service'],
    components:{WImage},
     data: () => ({
        servicesOrder: null
    }),
    created(){
      this.servicesOrder = this.$vueService(new OrderService) 
    },

    computed: {
      
      getAppointmentReservedString(){
        //return this.options.woo_payment.slot_reserved.replace('[minutes]', this.reserved_for) 
      },
      serviceDuration(){
        return (this.appointmentData.end_at - this.appointmentData.start_at) / 60
      },
      durationPrice(){
        if(this.service.options.durations !== undefined){
          let duration = this.serviceDuration
          return this.service.options.durations.find(e => e.duration == duration)
        }
      }
    },
    methods: {
      getImage(method, ext = '.svg'){
          return {
              icon: 'methods/'+method+ext,
              alt: method,
              title: method
          }
      },
      selectPack(pack){
        this.selectedPack = false
        this.$emit('loading', {loading:true})
        //send a query to update order
        this.updateOrderRequest({action: 'selectPack', pack: pack, 'edit_key':this.appointmentKey})
            .then(this.updatedOrderSuccess)
       
      },

      async updateOrderRequest(params) {
            return await this.servicesOrder.call('updateOrder', params) 
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
            return await this.servicesOrder.call('cancel', {'edit_key':this.appointmentKey}) 
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
<style>
.wstripe{
  max-width: 34px;
}
.wtabs{
  border-bottom:1px solid #ececec;
}
.wtab{
  max-width: 33%;
  padding: .2em;
  margin-bottom:-1px;
}
.wtab.active{
  border-radius: .2em .2em 0 0;
  border: 1px solid #ececec;
  border-bottom: 0;
}
.wpowered{
  font-size:10px;
}
.wpayment{
  min-height: 200px;
  border: 1px solid #ececec;
  border-radius: .2em;
}
</style>