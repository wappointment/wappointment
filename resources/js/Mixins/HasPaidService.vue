<script>

export default {

  computed:{
    mustPay() {
      return [undefined, false].indexOf(this.service.options.woo_sellable) === -1 && !this.wooAddonActive
    },
    hasPaidDurations() {
      for (const duration of this.service.options.durations) {
        if([undefined,false,''].indexOf(duration.woo_price) === -1 && duration.woo_price > 0){
          return true
        }
      }
    }
  },

};
</script>
