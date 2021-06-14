<script>
import Dates from "./Dates"
export default {
  mixins: [Dates],
  methods: {
    getPropertyCal(keyprop, calendar_id,calendar){
      return calendar.calendar_logs[calendar_id] !== undefined && calendar.calendar_logs[calendar_id][keyprop] !== undefined ? calendar.calendar_logs[calendar_id][keyprop]:false
    },
    lastChecked(calendar_id, calendar){
      return this.unixToDateTime(this.getPropertyCal('last-checked', calendar_id,calendar), calendar.timezone)
    },
    lastChanged(calendar_id, calendar){
      return this.unixToDateTime(this.getPropertyCal('last-parsed', calendar_id, calendar), calendar.timezone)
    },
    calDuration(calendar_id,calendar){
      return this.getPropertyCal('last-duration', calendar_id,calendar)+'s'
    },
    calInserted(calendar_id, calendar){
      return this.getPropertyCal('last_parser', calendar_id).inserted
    },
    calIgnored(calendar_id){
      return this.calDetected - this.calInserted
    },
    calDeleted(calendar_id,calendar){
      return this.getPropertyCal('last_parser', calendar_id,calendar).deleted
    },
    calDetected(calendar_id,calendar){
      return this.getPropertyCal('last_parser', calendar_id,calendar).detected
    },

 
    
    importCalendarSuccess(response){
        this.$WapModal().notifySuccess(response.data.message)
        this.hideAndRefresh()
    },
    
    savedSync(response){
        this.hideAndRefresh()
        this.$WapModal().notifySuccess(response.data.message)
    },
    hideAndRefresh(){
      this.hideModal()
      this.loadElements()
    },
    
  },
};
</script>
<style >
.cal-icon .card{
  max-width: none;
  width: 640px;
}
.wappointment-wrap p.vsmall{
  font-size:.8rem;
}
.unclickable{
  cursor:default !important;
}
.data-item{
  border: 1px solid #d9d9d9;
  border-radius: .25rem;
  padding: .2rem;
  background-color: #f0f0f0;
}
</style>