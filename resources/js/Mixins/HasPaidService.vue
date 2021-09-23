<script>

export default {

  computed:{
    mustPay() {
      return [undefined, false].indexOf(this.service.options.woo_sellable) === -1 && !this.wooAddonActive
    },
    hasPaidDurations() {
      for (let i = 0; i < this.service.options.durations.length; i++) {
        const duration = this.service.options.durations[i]
        if([undefined,false,''].indexOf(duration.woo_price) === -1 && duration.woo_price > 0){
          return true
        }
      }
    }
  },

};
</script>
