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
      <WPaymentMethods :methods="activeMethods" @selected="selected" />
      <div class="wpayment" v-if="activeMethod">
        
        <div class="wfooter">
          <component :is="activeMethod" :order="order" @loading="loadingTransfer" :method="selectedMethod" @confirm="confirm" @cancel="cancel" />
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
    components: window.wappointmentExtends.filter('PaymentMethods', { WPaymentMethods, WImage, onsite }, {asyncLoad: CanLoadScriptAsync, GetImage:GetImage} ),
     data: () => ({
        servicesOrder: null,
        activeMethod: '',
        methods: window.apiWappointment.methods
    }),
    created(){
      this.servicesOrder = this.$vueService(new OrderService) 
    },

    computed: {
      activeMethods(){
        return this.methods.filter(e => e.installed > 0 && e.active > 0)
      },
      selectedMethod(){
        let activeMethod = this.activeMethod
        if(activeMethod == ''){
          return false
        }
        return this.activeMethods.find(e => e.key == activeMethod)
        
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
      loadingTransfer(data){
        this.$emit('loading', data)
      },
      selected(methodKey){
        this.activeMethod = methodKey
      },
      cancel(){
        console.log('cancel')
      },
      confirm(alreadyConfirmed = false){
        if(alreadyConfirmed !== false){
          return this.confirmSuccess()
        }
        this.$emit('loading', {loading:true})
        this.confirmRequest().then(this.confirmFailure).catch(this.confirmSuccess)
      },
      
      async confirmRequest() {
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