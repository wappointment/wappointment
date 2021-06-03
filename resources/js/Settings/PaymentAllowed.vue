<template>
    <div class="d-flex">
        <WPayment v-for="payment in filteredPayments" :payment="payment" :key="payment.key" @click="clicked" />
        <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" large noscroll>
            <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
            <span>{{paymentEditing.description}}</span>
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
        payment_methods: [
            {
                key: 'onsite',
                name: 'On site', 
                description: 'Customers pay you in person at your business\' address or wherever you deliver the service',
                status:1
            },
            {
                key: 'paypal',
                name: 'Paypal', 
                description: 'Customers pay online with their Paypal Account, VISA, Mastercard, Amex etc ... in 25 currencies and 200 countries',
                hideLabel: true,
                status:0
                //https://www.paypal.com/uk/business/accept-payments
                // available 200 countries and 25 currencies 
                //https://www.paypal.com/webapps/mpp/country-worldwide
            },
            {
                key: 'stripe',
                name: 'Stripe', 
                description: 'Customers pay online with their VISA, Mastercard, Amex etc ... in 44 countries and 135 currencies',
                hideLabel: true,
                status:0
                //#https://stripe.com/docs/currencies
                //https://stripe.com/en-fr/payments/payment-methods-guide
                // available in 44 countries and 135 currencies : https://stripe.com/global
            },
            {
                key: 'woocommerce',
                name: 'WooCommerce', 
                description: 'WooCommerce is the most popular ecommerce plugin for WordPress. Already familiar with WooCommerce? Then selling your time with Wappointment and WooCommerce will be real easy.',
                hideLabel: true,
                status:1
            }
        ],

    }),
    created(){
        let wooIndex = this.payment_methods.findIndex(e => e.key == 'woocommerce')
        this.payment_methods[wooIndex].status = this.wooAddonActive ? 1:0
    },
    computed:{
        filteredPayments(){
            let paymentNew = this.wooAddonActive ? this.payment_methods.filter(a => a.key == 'woocommerce'):this.payment_methods
            return paymentNew.sort((a,b) => a.status < b.status)
        },
        paymentEditing(){
            let payment_edit = this.payment_edit
            return this.filteredPayments.find(e => e.key == payment_edit)
        }
    },
    methods:{
        clicked(payment){
            this.payment_edit = payment
            if(this.paymentEditing.status > 0){
                this.openPopup(this.paymentEditing.name)
            }else{
                this.$emit('clicked', payment)
            }
            

            
        },
    }
}
</script>

