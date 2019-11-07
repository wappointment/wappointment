<script>
export default {
  computed:{
    debugIsOn(){
      return window.apiWappointment.debug !== undefined && window.apiWappointment.debug == 1
    }
  },
  methods: {
      pathAssets(arg){
          if(arg===undefined) arg=''
          return apiWappointment.resourcesUrl + arg
      }, 
      async sleep(milliseconds) {
        return new Promise(resolve => setTimeout(resolve, milliseconds))
      },
      cleanObject(ObjectToClean) {
        return Object.entries(ObjectToClean).reduce((a,[k,v]) => (v === undefined ? a : {...a, [k]:v}), {})
      }
    }
}
</script>