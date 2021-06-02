<template>
    <div>
      <div>
          <div class="witem" v-for="charge in order.prices">
          {{ charge.price.name }} : {{ displayPrice(charge.price.price) }}
          </div>
          <div class="wtotal">
            Total: <strong>{{ displayPrice(order.total) }}</strong>
          </div>
      </div>
      <WPaymentMethods :methods="methods" @selected="selected" />
      <div class="wpayment" v-if="activeMethod">
        
        <div class="wfooter">
          <component :is="activeMethod" @confirm="confirm" @cancel="cancel" />
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
import onsite from './PayOnSite'

export default {
    extends: AbstractFront,
    mixins:[IsDemo, HasWooVariables, HasPaidService, GetImage],
    props: ['options', 'relations', 'appointmentKey', 'appointmentData', 'service', 'order'],
    components: window.wappointmentExtends.filter('PaymentMethods', { WPaymentMethods, WImage, onsite }, {asyncLoad: CanLoadScriptAsync} ),
     data: () => ({
        servicesOrder: null,
        activeMethod: '',
        methods: window.apiWappointment.methods
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
      cancel(){
        console.log('cancel')
      },
      confirm(){
        console.log('confirm')
        this.$emit('loading', {loading:true})
        this.confirmRequest().then(this.confirmFailure).catch(this.confirmSuccess)
      },
      
      async confirmRequest() {
        console.log('this.appointmentSavedData.order.transaction_id')
        alert('hello')
            return await this.servicesOrder.call('confirm', {'transaction_id':this.order.transaction_id}) 
      },

      confirmFailure(){
        this.serviceError({message:'Error confirming order'})
        this.$emit('loading', {loading:false})
      },

      confirmSuccess(re){
        this.$emit('confirmedPayment', this.relations.next, {appointmentSaved:true,loading:false})
      },

    }
}   
</script>
<style>
.wpayment{
  border: 2px solid var(--wappo-sec-bg);
  border-radius: .2em;
  position:relative;
  overflow: hidden;
}
.witem{
  font-size:.7em;
  border-bottom: 1px solid var(--wappo-sec-bg);
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

</style>