<template>
    <div class="d-flex">
        <WPayment v-for="payment in filteredPayments" :payment="payment" :key="payment.key" @click="clicked" />
        <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" large noscroll>
            <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
            <span>{{payment_edit.description}}</span>
            <!-- <component :is="getAddon.componentKey" @close="hidePopup"></component> -->
      </WapModal>
    </div>
</template>

<script>

import WPayment from '../WComp/WPayment'
import HasWooVariables from '../Mixins/HasWooVariables'
import HasPopup from '../Mixins/HasPopup'
export default {
    components:{WPayment},
    mixins:[HasWooVariables, HasPopup],
    data: () => ({
        payment_edit:null,
        payment_methods: apiWappointment.methods,

    }),
    computed:{
        filteredPayments(){
            let paymentNew = this.wooAddonActive ? this.payment_methods.filter(a => a.key == 'woocommerce'):this.payment_methods
            return paymentNew.sort((a,b) => a.installed < b.installed)
        },
    },
    methods:{
        clicked(payment){
            this.payment_edit = payment
            console.log('payment', this.payment_edit)
            if(this.payment_edit.installed){
                this.openPopup(this.payment_edit.name)
            }else{
                this.$emit('clicked', payment)
            }

        },
    }
}
</script>

