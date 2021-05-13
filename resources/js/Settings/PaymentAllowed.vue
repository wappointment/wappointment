<template>
    <div class="d-flex">
        <WPayment v-for="payment in filteredPayments" :payment="payment" :key="payment.key" @click="clicked" />
    </div>
</template>

<script>

import WPayment from '../WComp/WPayment'
import HasWooVariables from '../Mixins/HasWooVariables'
export default {
    components:{WPayment},
    mixins:[HasWooVariables],
    data: () => ({
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
    },
    methods:{
        clicked(payment){
            this.$emit('clicked', payment)
        },
    }
}
</script>

