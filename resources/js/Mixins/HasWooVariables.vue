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
      return this.currency
    },
    wappoCurrency(){
      return this.currency
    },
    currencySymb(){
      return this.wooAddonActive ? this.wooCurrency:this.wappoCurrency
    },
    currencyText(){
      return this.wooAddonActive ? this.wooCurrencyText:this.wappoCurrencyText
    }
  },

};
</script>
