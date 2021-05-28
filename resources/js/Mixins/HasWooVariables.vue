<script>

export default {

  computed:{
    wooAddonActive(){
        return window.wappointment_woocommerce !== undefined && window.wappointment_woocommerce.installed_at
    },
    wooCurrency(){
        return this.wooAddonActive ? window.wappointment_woocommerce.currency_symbol:''
    },
    wooCurrencyText(){
        return this.wooAddonActive ? window.wappointment_woocommerce.currency_text:''
    },
    wappoCurrencyText(){
      return  window.apiWappointment.currency.code + ' - '+this.wappoCurrency
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
  methods:{
    displayPrice(priceValue){
      return this.displayCents(priceValue)+this.currencySymb
    },
    displayCents(priceValue){
      return (priceValue/100).toFixed(2)
    }
  }

};
</script>
