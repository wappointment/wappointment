<template>
    <div>
      <div class="wdescription" v-if="hasPaidDurations">Payment time!</div>
      <div class="witem" v-for="charge in order.prices">
      {{ charge.price.name }} : {{ displayPrice(charge.price.price) }}
      </div>
      <WPaymentMethods :methods="methods" @selected="selected" />
      <div class="wpayment" v-if="activeMethod">
        <component :is="activeMethod" />
        <div class="wfooter">
          <div class="wtotal">
            Total: <strong>{{ displayPrice(order.total) }}</strong>
          </div>
          <div class="d-flex wcards" v-if="selectedMethod.cards!== undefined">
            <WImage v-for="card in selectedMethod.cards":image="getImage(card)" class="wcard" :key="card"/>
          </div>
          <div class="d-flex wpowered align-items-center" v-if="selectedMethod.desc">
            <span v-if="selectedMethod.desc" >{{ selectedMethod.desc }}</span> 
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import OrderService from '../Services/V1/Order'
import AbstractFront from './AbstractFront'
import IsDemo from '../Mixins/IsDemo'
import HasWooVariables from '../Mixins/HasWooVariables'
import GetImage from '../Mixins/GetImage'
import HasPaidService from '../Mixins/HasPaidService'
import WPaymentMethods from '../WComp/WPaymentMethods'
import WImage from '../WComp/WImage'
import CanLoadScriptAsync from '../Mixins/CanLoadScriptAsync'

export default {
    extends: AbstractFront,
    mixins:[IsDemo, HasWooVariables, HasPaidService, GetImage],
    props: ['options', 'relations', 'appointmentKey', 'appointmentData', 'service', 'order'],
    components: window.wappointmentExtends.filter('PaymentMethods', { WPaymentMethods, WImage }, {asyncLoad: CanLoadScriptAsync} ),
     data: () => ({
        servicesOrder: null,
        activeMethod: '',
        methods: [
          {
            key: 'stripe',
            cards: ['visa', 'mastercard', 'amex'],
            desc: 'Pay securely with Stripe',
          },
          {
            key: 'paypal',
            cards: ['visa', 'mastercard', 'amex'],
            desc: 'Pay securely with Paypal',
          },
          {
            key: 'onsite',
            desc: 'Pay later on site'
          }
        ]
    }),
    created(){
      this.servicesOrder = this.$vueService(new OrderService) 
    },

    computed: {
      selectedMethod(){
        let activeMethod = this.activeMethod
        if(activeMethod == ''){
          return false
        }
        return this.methods.find(e => e.key == activeMethod)
        
      },
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
  position:relative;
  padding: .4em;
}
.witem{
  font-size:.7em;
}

.wpowered{
  font-size:10px;
}
.wcards{
  height: 25px;
}
.wcard{
  margin-right: .2em;
}
.wfooter{
  position: absolute;
  bottom: 0;
}

</style>