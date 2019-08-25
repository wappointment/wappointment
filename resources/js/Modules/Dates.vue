<script>
import momenttz from '../appMoment'
export default {
  data() {
    return {
      shortDateFormat: 'YYYY-MM-DD HH:mm',
    };
  },
  methods: {
    unixToDateTime(unix, timezone){
      return unix ? momenttz.unix(unix).tz(timezone).format(this.shortDateFormat) : '---'
    },
    getMoment(date,tz) {
        return momenttz.unix(date).tz(tz)
    }
  }
};
</script>