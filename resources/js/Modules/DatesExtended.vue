<script>
import luxonApp from '../appLuxon'
export default {

  computed:{
    
    minTodayHour(){
        return Math.floor(this.viewData.min_bookable)
    },
    minTodayMin(){
        return Math.floor(this.getDecimalPart(this.viewData.min_bookable-this.minTodayHour) * 60)
    },
    now() {
        return luxonApp.now()
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
        let nowmin = this.now.setZone(this.timeprops.currentTz).plus({hours:this.minTodayHour, minutes:this.minTodayMin})
        let nowcopy = nowmin.startOf('hour')
        
        if((this.currentTime() + (this.minTodayHour * 3600))  >= nowcopy.toSeconds()){
            let i = 0
            while (nowcopy.toSeconds() < nowmin.toSeconds() && i <13) {
                nowcopy.plus({minutes:5}).set({second: 0})
                i++
            }
        }
        return nowcopy
    },
  }
}
</script>