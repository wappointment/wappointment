<script>
export default {
  methods: {
    getPrice(packageObject){
        return this.getPricePerNumberOfSites(packageObject.options.prices, 1)
    },
    getPricePerNumberOfSites(prices, number){
      let found = prices.filter((e) => e.number == number)
      return found.length > 0? found[0].price:undefined
    },
    roundToTwo(num) {    
        return num.toFixed(2)
    },
    getPackagePrice(packagekey){
        return this.getRealPrice( this.getPrice( this.packageByKey(packagekey) ) )
    },
    getRealPrice(price){
        return this.roundToTwo(price/100)
    },
    packageByKey(pkey){
        for (let i = 0; i < window.packages.length; i++) {
            if(window.packages[i].key == pkey) return window.packages[i]
        }
    },
  },
};
</script>
