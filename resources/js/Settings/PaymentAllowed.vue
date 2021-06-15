<template>
    <div class="d-flex">
        <WPayment v-for="payment in filteredPayments" :payment="payment" :key="payment.key" @click="clicked" />
        <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" large noscroll>
            <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
            <h5>{{payment_edit.description}}</h5>
            <component :is="payment_edit.key+'Settings'" :method="payment_edit" @close="hidePopup"></component>
        </WapModal>
    </div>
</template>

<script>

import WPayment from '../WComp/WPayment'
import CanFormatPrice from '../Mixins/CanFormatPrice'
import HasPopup from '../Mixins/HasPopup'
import onsiteSettings from './PayOnSiteSettings'
export default {
    components:window.wappointmentExtends.filter('PaymentMethodsSettings',  {WPayment, onsiteSettings}),
    mixins:[CanFormatPrice, HasPopup],
    data: () => ({
        payment_edit:null,
        payment_methods: apiWappointment.methods,
    }),
    computed:{
        filteredPayments(){
            return this.payment_methods.sort((a,b) => a.installed < b.installed)
        },
    },
    methods:{
        clicked(payment){
            this.payment_edit = payment
            if(this.payment_edit.installed){
                this.openPopup(this.payment_edit.name)
            }else{
                this.$emit('clicked', payment)
            }

        },
    }
}
</script>

