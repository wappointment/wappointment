<template>
    <div>
      <WPaymentMethods :methods="activeMethods" @selected="selected" :options="options" />
      <div class="wpayment" v-if="activeMethod">
        <div class="wfooter">
          <component :is="activeMethod" :order="order" :options="options" :method="selectedMethod" 
          @loading="loadingTransfer" @confirm="confirm" @cancel="cancel" />
          <Terms :options="options" :tos="true"/>
        </div>
      </div>
    </div>
</template>

<script>
import OrderService from '../Services/V1/Order'
import AbstractFront from './AbstractFront'
import IsDemo from '../Mixins/IsDemo'
import GetImage from '../Mixins/GetImage'
import HasPaidService from '../Mixins/HasPaidService'
import WPaymentMethods from '../WComp/WPaymentMethods'
import WImage from '../WComp/WImage'
import CanLoadScriptAsync from '../Mixins/CanLoadScriptAsync'
import onsite from './PayOnSite'
import ElementSelected from './ElementSelected'
import CanFormatPrice from '../Mixins/CanFormatPrice'
import Terms from './Terms'

export default {
    extends: AbstractFront,
    mixins:[IsDemo, HasPaidService, GetImage],
    props: ['options', 'relations', 'appointmentKey', 'appointmentData', 'service', 'order'],
    components: window.wappointmentExtends.filter('PaymentMethods', { WPaymentMethods, WImage, onsite, ElementSelected, Terms}, {asyncLoad: CanLoadScriptAsync, GetImage:GetImage, CanFormatPrice:CanFormatPrice} ),
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
      },
      confirm(alreadyConfirmed = false){
        if(alreadyConfirmed !== false){
          return this.confirmSuccess(alreadyConfirmed)
        }
        this.$emit('loading', {loading:true})
        this.confirmRequest().then(this.confirmSuccess).catch(this.confirmFailure)
      },
      
      async confirmRequest() {
        return await this.servicesOrder.call('confirm', {'transaction_id':this.order.transaction_id}) 
      },

      confirmFailure(e){
        this.serviceError({message:'Error confirming order'})
        this.$emit('loading', {loading:false})
      },

      confirmSuccess(response){
        let appointment_data = response.data.appointment !== undefined ? response.data.appointment:response.data.orderCompleted.appointments[0]
        this.$emit('confirmedPayment', this.relations.next, {appointmentSaved:true,loading:false, appointmentSavedData:appointment_data})
      },

    }
}   
</script>
<style>
.wpayment{
  border: 2px solid var(--wappo-payment-border);
  border-radius: 0 .4em .4em .4em;
  position:relative;
  overflow: hidden;
}
.witem{
  font-size:.7em;
  border-bottom: 1px solid var(--wappo-payment-border);
}

.wcards{
  height: 25px;
}
.wcard{
  margin-right: .2em;
}

</style>