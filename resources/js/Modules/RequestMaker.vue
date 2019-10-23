<script>
export default {

  methods: {
      request(serviceRequest, params, finalCallback = undefined, staff = false, successCallback = false, failureCallback = false){
        if(this.beforeRequest !== undefined) this.beforeRequest()
        
        if(!successCallback &&  !failureCallback){
          this.$WapModal().request(serviceRequest(params, staff), finalCallback).then(this.successRequest).catch(this.failedRequest)
        }else if(!successCallback &&  failureCallback!==false){
          this.$WapModal().request(serviceRequest(params, staff), finalCallback).then(this.successRequest).catch(failureCallback)
        }else if(successCallback!==false &&  !failureCallback){
          this.$WapModal().request(serviceRequest(params, staff), finalCallback).then(successCallback).catch(this.failedRequest)
        }else{
          this.$WapModal().request(serviceRequest(params, staff), finalCallback).then(successCallback).catch(failureCallback)
        }
        
  
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