<script>
import momenttz from '../appMoment'
import browserLang from '../Standalone/browserLang'

export default {
  data() {
    return {
      shortDateFormat: 'YYYY-MM-DD HH:mm',
    };
  },

  methods: {
    getMomentObject(){
      return momenttz
    },
    setMomentLocale(){
      momenttz.locale(browserLang())
    },
    unixToDateTime(unix, timezone){
      return unix ? momenttz.unix(unix).tz(timezone).format(this.shortDateFormat) : '---'
    },
    getMoment(date,tz) {
        return momenttz.unix(date).tz(tz)
    },
    tzGuess(){
        return momenttz.tz.guess()
    },
    getUnixNow(){
        return momenttz().unix()
    },
  }
}
</script>