<template>
    <div>
      <div class="wdescription" v-if="hasPaidDurations">Payment time!</div>
      <div class="wdescription">Your appointment time is reserved for the next 20 minutes giving you time to pay</div>
      <WPaymentMethods :methods="methods" @selected="selected" />
      <div class="wpayment">
        <component :is="activeMethod" :crumb="false" />
      </div>
    </div>
</template>

<script>
import OrderService from '../Services/V1/Order'
import AbstractFront from './AbstractFront'
import IsDemo from '../Mixins/IsDemo'
import HasWooVariables from '../Mixins/HasWooVariables'
import HasPaidService from '../Mixins/HasPaidService'
import WPaymentMethods from '../WComp/WPaymentMethods'
export default {
    extends: AbstractFront,
    mixins:[IsDemo, HasWooVariables, HasPaidService],
    props: ['options', 'relations', 'appointmentKey', 'appointmentData', 'service'],
    components: window.wappointmentExtends.filter('PaymentMethods', { WPaymentMethods } ),
     data: () => ({
        servicesOrder: null,
        activeMethod: '',
        methods: [
          {
            key: 'stripe',
            cards: ['visa', 'mastercard', 'amex'],
            desc: 'Pay safely with',
            logo: true,
          },
          {
            key: 'paypal',
            cards: ['visa', 'mastercard', 'amex'],
            desc: 'Pay safely with',
            logo: true
          },
          {
            key: 'onsite',
            cards: ['onsite.png'],
            desc: 'Pay later on site'
          }
        ]
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
      selected(methodKey){
        this.activeMethod = methodKey
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
.wpayment{
  min-height: 200px;
  border: 2px solid var(--wappo-sec-bg);
  border-radius: .2em;
}
</style>