<template>
    <div class="container-fluid" v-if="dataLoaded">
      <ServiceModulable :dataPassed="model"/>
    </div>
</template>

<script>
import abstractView from '../Abstract'
import ServiceModulable from './ServiceNew'
export default {
  extends: abstractView,
  data() {
      return {
          viewName: 'service',
          parentLoad: false,
          model: {             
            name: '',
            duration: 60,
            type: '',
            address: '',
            options: {
              countries: []
            }
          },
      } 
  },

  components: { ServiceModulable },

  methods: {
    initMethod(){
      this.request(this.initValueRequest,  undefined,undefined,false,  this.loaded)
    },
    loaded(viewData){
      this.viewData = viewData.data
        this.model = viewData.data.service
        this.model.duration = parseInt(this.model.duration)
        if( ['', null, undefined].indexOf(this.model.options) !==-1 ){
          this.model.options = {
            countries: []
          }
        }
    },
    
  }  
}
</script>
