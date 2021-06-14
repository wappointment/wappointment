<script>

export default {
    computed:{
        wooAddonIsActive(){
            return window.wappointment_woocommerce !== undefined
        },
        currency(){
            return this.wooAddonIsActive ? window.wappointment_woocommerce.currency_symbol:''
        },
        priceFormat(){
            return this.wooAddonIsActive ? window.wappointment_woocommerce.currency_format:'[price][currency]'
        }
    },
    methods: {
      
        canSell(duration){
            return this.sellable && this.currency !== '' && [undefined, ''].indexOf(duration.woo_price) === -1
        },
        formatPrice(price){
            return this.priceFormat
            .replace('[currency]',this.currency)
            .replace('[price]', price)
        },
           
    }
}   
</script>

