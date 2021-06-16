<template>
    <div class="d-flex">
        <WPayment class="text-muted small" v-for="payment in filteredPayments" :payment="payment" :key="payment.key" @click="clicked" />
        <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" large noscroll>
            <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
            <component :is="payment_edit.key+'Settings'" :method="payment_edit" @close="hidePopup"></component>
        </WapModal>
    </div>
</template>

<script>

import WPayment from '../WComp/WPayment'
import CanFormatPrice from '../Mixins/CanFormatPrice'
import HasPopup from '../Mixins/HasPopup'
import onsiteSettings from './PayOnSiteSettings'
import RequestMaker from '../Modules/RequestMaker'
import abstractView from '../Views/Abstract'
export default {
    components:window.wappointmentExtends.filter('PaymentMethodsSettings',  {WPayment, onsiteSettings}, {extends: abstractView, mixins: [RequestMaker]}),
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
        hideElementsPopup(){
            this.$emit('closed')
        },
        clicked(payment){
            this.payment_edit = payment
            if(this.payment_edit.installed){
                this.openPopup(this.payment_edit.name)
                this.$emit('opened')
            }else{
                this.$emit('clicked', payment)
            }

        },
    }
}
</script>

