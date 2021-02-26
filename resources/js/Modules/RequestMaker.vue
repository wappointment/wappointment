<script>
if(window.wapRequests === undefined) {
  window.wapRequests = []
  window.wapRunning = false
}
export default {
  data: () => ({
    errorMessages:[],
  }),
  methods: {
      queueExecuteOne(){
        if(window.wapRunning === false){
          
          window.wapRunning = window.wapRequests.shift()
          if(this.beforeRequest !== undefined) this.beforeRequest()
          
          if(!window.wapRunning.successCallback &&  !window.wapRunning.failureCallback){
            this.$WapModal().request(window.wapRunning.serviceRequest(window.wapRunning.params, window.wapRunning.staff), this.finalCallbackWrapper).then(this.successRequest).catch(this.failedRequest)
          }else if(!window.wapRunning.successCallback &&  window.wapRunning.failureCallback!==false){
            this.$WapModal().request(window.wapRunning.serviceRequest(window.wapRunning.params, window.wapRunning.staff), this.finalCallbackWrapper).then(this.successRequest).catch(window.wapRunning.failureCallback)
          }else if(window.wapRunning.successCallback!==false &&  !window.wapRunning.failureCallback){
            this.$WapModal().request(window.wapRunning.serviceRequest(window.wapRunning.params, window.wapRunning.staff), this.finalCallbackWrapper).then(window.wapRunning.successCallback).catch(this.failedRequest)
          }else{
            this.$WapModal().request(window.wapRunning.serviceRequest(window.wapRunning.params, window.wapRunning.staff), this.finalCallbackWrapper).then(window.wapRunning.successCallback).catch(window.wapRunning.failureCallback)
          }
        }else{
          //waiting turn
        }  
      },
      finalCallbackWrapper(data){
         if(window.wapRunning.finalCallback !== undefined && typeof(window.wapRunning.finalCallback) === 'function') window.wapRunning.finalCallback(data)
         window.wapRunning = false
         if(window.wapRequests.length > 0) this.queueExecuteOne()
      },
      
      enqueueRequest(requestObject){
        window.wapRequests.push(requestObject)
      },
      request(serviceRequest, params, finalCallback = undefined, staff = false, successCallback = false, failureCallback = false){
        let requestObject = {
            'serviceRequest':serviceRequest, 
            'params':params, 
            'finalCallback':finalCallback, 
            'staff':staff, 
            'successCallback':successCallback , 
            'failureCallback':failureCallback
        }
        this.enqueueRequest(requestObject)
        this.queueExecuteOne()
      },
      successRequest(result) {
        if(result.data.message!==undefined) {
          if(this.afterSuccess !== undefined) this.afterSuccess(result) 
          if(result.data.result!== undefined && result.data.result == false) return this.$WapModal().notifyError(result.data.message)
          return this.$WapModal().notifySuccess(result.data.message)
        }
      },

      // this method is called after response wrong from request make by execute method
      failedRequest(error) {
        return this.serviceError(error)
      },
      
      // this method is called after response rigth from request make by execute method
      serviceSuccess(result) {
        if(result.data.message!==undefined) {
          if(this.afterSuccess !== undefined) this.afterSuccess(result) 
          if(result.data.result!== undefined && result.data.result == false) return this.$WapModal().notifyError(result.data.message)
          return this.$WapModal().notifySuccess(result.data.message)
        }
      },

      // this method is called after response wrong from request make by execute method
      serviceError(error) {
        if(error.response !== undefined){
          if(error.response.data.data.errors!==undefined && this.lengthGreaterThan(error.response.data.data.errors, 0)){

            if(this.lengthGreaterThan(error.response.data.data.errors, 1)){
              this.errorMessages = error.response.data.data.errors

            }else{

              return this.$WapModal().notifyError( this.firstError(error.response.data.data.errors) )
            }
            
          }

          if(error.response.data.message !== undefined)  return this.$WapModal().notifyError( error.response.data.message)
        }
        
        if(error.message!== undefined) {
          this.$WapModal().notifyError( error.message)
        }else{
          this.$WapModal().notifyError( 'Unidentified service error')
        }
         
      },

      lengthGreaterThan(arrayOrObject,X){
        if(typeof arrayOrObject === 'object' && Object.keys(arrayOrObject).length > X) return true
        if(arrayOrObject.length > X) return true
      },

      firstError(arrayOrObject){
        if(arrayOrObject.length > 0) return arrayOrObject[0]
        for (const key in arrayOrObject) {
          if (arrayOrObject.hasOwnProperty(key)) {
            return arrayOrObject[key][0]
          }
        }
      },      
    }
}
</script>