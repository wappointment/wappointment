<script>

export default {

  computed:{
    minTodayHour(){
        return parseInt(this.viewData.min_bookable)
    },
    now() {
        return this.getMomentObject()().tz(this.timeprops.currentTz)
    },
  },

  methods: {
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
        let nowmin = this.getMomentObject().tz(this.now.clone(), this.timeprops.currentTz).add(this.minTodayHour,'hours')
        let nowcopy = nowmin.clone().startOf('hour')
        
        if((this.currentTime() + (this.minTodayHour * 3600))  >= nowcopy.unix()){
            let i = 0
            while (nowcopy.unix() < nowmin.unix() && i <13) {
                nowcopy.add( 5, 'minutes')
                i++
            }
        }
        return nowcopy
    },
  }
}
</script>