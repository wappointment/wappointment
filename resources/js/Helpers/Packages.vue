<script>
export default {
  methods: {
    getPrice(packageObject, number_of_sites){
      return parseInt(packageObject.options.prices.find(element => element.number == number_of_sites).price)
    },

    roundToTwo(num) {    
        return num.toFixed(2)
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
