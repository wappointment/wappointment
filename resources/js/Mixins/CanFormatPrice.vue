<script>

export default {
    computed:{
        currency(){
            return this.currencySymb
        },
        priceFormat(){
            return this.wooAddonActive ? window.wappointment_woocommerce.currency_format:window.apiWappointment.currency.format
        },

        wooAddonActive(){
            return window.wappointment_woocommerce !== undefined && window.wappointment_woocommerce.installed_at
        },
        wooCurrency(){
            return this.wooAddonActive ? window.wappointment_woocommerce.currency_symbol:''
        },
        wooCurrencyText(){
            return this.wooAddonActive ? window.wappointment_woocommerce.currency_text:''
        },
        currencySeparator(){
            return this.wooAddonActive ? window.wappointment_woocommerce.decimals_sep:window.apiWappointment.currency.decimals_sep
        },
        wappoCurrencyText(){
            return window.apiWappointment.currency.code + ' - '+this.wappoCurrency
        },
        wappoCurrency(){
            return window.apiWappointment.currency.symbol
        },
        currencyCode(){
            return window.apiWappointment.currency.code
        },
        currencySymb(){
            return this.wooAddonActive ? this.wooCurrency:this.wappoCurrency
        },
        currencyText(){
            return this.wooAddonActive ? this.wooCurrencyText:this.wappoCurrencyText
        }

    },
    methods: {
      
        canSell(duration){
            return this.sellable && this.currency !== '' && [undefined, ''].indexOf(duration.woo_price) === -1
        },
        formatPrice(price, cents = false){
            return this.priceFormat
            .replace('[currency]',this.currency)
            .replace('[price]', this.displayCents(price * (cents ?1:100)))
        },

        formatCentsPrice(price){
            return this.formatPrice(price, true)
        },

        displayCents(priceValue){
            return (priceValue/100).toFixed(2).replace('.', this.currencySeparator)
        }
           
    }
}   
</script>

