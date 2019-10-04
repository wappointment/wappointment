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
    mainElements: [], 
    formCanBeSubmitted: false,
    isPreLoading: false,
    loading: false,
    errorMessages:[],
    model: {             
      id: false,
      name: '',
      options: {
      }
    },
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
      loaded(viewData) {
          this.viewData = viewData.data
          this.loading = false
          if(this.loadedAfter !== undefined) this.loadedAfter()
      },

      beforeRequest(){
        this.errorMessages = []
      },

      async initValueRequest() {
          return await this.serviceAvailability.call('get')
      }, 
      

      serviceSuccess(result) {
        if(result.data.message!==undefined) {
          if(this.afterSuccess !== undefined) this.afterSuccess(result) 

        }
      },

      serviceError(error) {
       this.loading = false
       //console.log('serviceError Start',error)
       if(error.response.data.message !== undefined)  this.errorMessages.push(error.response.data.message)
       
       if(error.response.data.data.errors!==undefined && this.lengthGreaterThan(error.response.data.data.errors, 0)){
          if(this.lengthGreaterThan(error.response.data.data.errors, 0)){
            this.errorMessages = error.response.data.data.errors
          }else{
            //console.log(error.response.data)
            //this.errorMessages.push('An error occured')
          }
          
        }

        //console.log('serviceError End',error)

      },

      lengthGreaterThan(arrayOrObject,X){
        if(typeof arrayOrObject === 'object' && Object.keys(arrayOrObject).length > X) return true
        if(arrayOrObject.length > X) return true
      },

    }
}
</script>