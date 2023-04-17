<script>

export default {

  computed:{
    
    minTodayHour(){
        return Math.floor(this.viewData.min_bookable)
    },
    minTodayMin(){
        return Math.floor(this.getDecimalPart(this.viewData.min_bookable-this.minTodayHour) * 60)
    },
    now() {
        return getLuxonObj.now()
    },
  },

  methods: {
    getDecimalPart(num) {
      if (Number.isInteger(num)) {
        return 0
      }

      const decimalStr = num.toString().split('.')[1]
      return Number('0.'+decimalStr)
    },
    isTSToday(tsvalue){
        let firstTS = new Date(tsvalue * 1000)
        let today = new Date()

        return firstTS.getDate() == today.getDate() &&
            firstTS.getMonth() == today.getMonth() &&
            firstTS.getFullYear() == today.getFullYear()
    },
    currentTime(){
        return Math.round(Date.now() / 1000)
    },

    getMinStart(){
        let nowmin = this.getMomentObject().tz(this.now.clone(), this.timeprops.currentTz).add(this.minTodayHour,'hours').add(this.minTodayMin,'minutes')
        let nowcopy = nowmin.clone().startOf('hour')
        
        if((this.currentTime() + (this.minTodayHour * 3600))  >= nowcopy.unix()){
            let i = 0
            while (nowcopy.unix() < nowmin.unix() && i <13) {
                nowcopy.add( 5, 'minutes')
                nowcopy.set('second', 0)
                i++
            }
        }
        return nowcopy
    },
  }
}
</script>