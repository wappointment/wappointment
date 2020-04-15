<script>
import Dates from "./Dates"
export default {
  mixins: [Dates],
  methods: {
    getPropertyCal(keyprop, calendar_id){
      return this.viewData.calendar_logs[calendar_id] !== undefined && this.viewData.calendar_logs[calendar_id][keyprop] !== undefined ? this.viewData.calendar_logs[calendar_id][keyprop]:false
    },
    lastChecked(calendar_id){
      return this.unixToDateTime(this.getPropertyCal('last-checked', calendar_id), this.viewData.timezone)
    },
    lastChanged(calendar_id){
      return this.unixToDateTime(this.getPropertyCal('last-parsed', calendar_id), this.viewData.timezone)
    },
    calDuration(calendar_id){
      return this.getPropertyCal('last-duration', calendar_id)+'s'
    },
    calInserted(calendar_id){
      return this.getPropertyCal('last_parser', calendar_id).inserted
    },
    calIgnored(calendar_id){
      return this.calDetected - this.calInserted
    },
    calDeleted(calendar_id){
      return this.getPropertyCal('last_parser', calendar_id).deleted
    },
    calDetected(calendar_id){
      return this.getPropertyCal('last_parser', calendar_id).detected
    },
  },
};
</script>