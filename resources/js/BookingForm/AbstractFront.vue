<script>

import BookingService from '../Services/V1/Booking'
import AvailabilityService from '../Services/V1/Availability'
import Loader from '../Components/Loaders/BigCalendar'
import Helpers from '../Modules/Helpers'
import eventsBus from '../eventsBus'
export default {
  mixins: [Helpers],
  data: () => ({
    serviceBooking: null, 
    serviceAvailability: null,
    loading: false,
    errorMessages:[],
    viewName: null,
    viewData: null,
  }),

  created(){
    eventsBus.listens('beforeRequest', this.beforeRequest)
    this.serviceBooking = this.$vueService(new BookingService)
    this.serviceAvailability = this.$vueService(new AvailabilityService)
  },

  components: {
      Loader
  }, 

  methods: {
      
      refreshInitValue(){
        this.loading = true
        this.initValueRequest()
        .then(this.loaded)
        .catch(this.serviceError)
      },

      loaded(response) {
        let detected_issue = this.issueResponseDetected(response)
          if(detected_issue){
            this.serviceError({
              message: "Server response altered, make sure your WordPress' site does not display server errors.",
              submessage: [
                '-----------',
                'Read this:',
                'https://wappointment.com/docs/unidentified-error/',
                '-----------',
                'Error detected: ',
                detected_issue
              ]
              })
            return false;
          }
          this.viewData = response.data
          this.loading = false
          if(this.loadedAfter !== undefined) {
            this.loadedAfter()
          }
      },
      issueResponseDetected(response){
        
        if(!this.responseDataIsObject(response) && response.request !== undefined && response.request.response !== undefined){
          let string_clean = response.request.response.trim()
          if(string_clean.charAt(0) !== '{'){
            let split = string_clean.split('{')
            return split[0]
          }
        }
        return false
      },

      responseDataIsObject(response){
        return typeof response.data == 'object' && Object.keys(response.data).length > 0
      },

      beforeRequest(){
        this.errorMessages = []
      },

      async initValueRequest() {
        return await this.serviceAvailability.call('get')
      },       

      serviceError(error) {
        this.loading = false
        if(error.response !== undefined){
          if(error.response.data.message !== undefined){
            this.errorMessages.push(error.response.data.message)
          }

          if(error.response.data.data.errors!==undefined && this.lengthGreaterThan(error.response.data.data.errors, 0)){
            if(this.lengthGreaterThan(error.response.data.data.errors, 0)){
              this.errorMessages = error.response.data.data.errors
            }
          }
        }else{
          this.errorMessages = []
          this.errorMessages.push(error.message !== undefined ? error.message : 'An unidentified error occured')
          if(error.submessage !== undefined ){
            let submessages = error.submessage
            if(!Array.isArray(submessages)){
              submessages = [submessages]
            }
            for (const submessage of submessages) {
                this.errorMessages.push(submessage)
            }

          }
        }

      },

      lengthGreaterThan(arrayOrObject, X){
        if(
          (typeof arrayOrObject === 'object' && Object.keys(arrayOrObject).length > X) 
          || 
          (typeof arrayOrObject === 'object' && arrayOrObject.length > X)
        ) {
          return true
        }
      },

    }
}
</script>