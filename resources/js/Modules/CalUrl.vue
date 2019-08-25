<script>
import Dates from "./Dates";
export default {
  mixins: [Dates],
  computed: {
    lastChecked(){
      return this.unixToDateTime(this.viewData.last_checked, this.viewData.timezone)
    },
    lastChanged(){
      return this.unixToDateTime(this.viewData.last_parsed, this.viewData.timezone)
    },
    calDuration(){
      return this.viewData.last_process.duration+'s'
    },
    calInserted(){
      return this.viewData.last_process.parser.inserted
    },
    calIgnored(){
      return this.calDetected - this.calInserted
    },
    calDeleted(){
      return this.viewData.last_process.parser.deleted
    },
    calDetected(){
      return this.viewData.last_process.parser.detected
    },
  },
};
</script>