<template>
    <div class="container-fluid" v-if="dataLoaded">
      <ServiceLegacy v-if="legacy" :dataPassed="model" :buttons="true"/>
      <ServiceModulable v-else :dataPassed="model" :buttons="true"/>
    </div>
</template>

<script>
import abstractView from '../Abstract'
import ServiceModulable from './ServiceNew'
import ServiceLegacy from './ServiceLegacy'
export default {
  extends: abstractView,
  props: ['legacy'],
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

  components: { ServiceModulable, ServiceLegacy},

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
        this.$emit('fullyLoaded')
    },
    
  }  
}
</script>
