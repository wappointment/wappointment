<template>
  <div>
    <topPane ref="wizardpane" @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep"></topPane>
    <div class="container-fluid">
      <div class="reduced">
        <div class="col-12">
          <div>
              <h1>Service Setup</h1>
              <div class="mb-2">Set the first service you will provide to your clients</div>
          </div>
          <ServicePage v-if="viewData!==null" ref="servicepage" :buttons="false" :minimal="true" 
           :dataPassed="viewData.service" @saved="finallyGoNext" @ready="ready" />
        </div>
        
      </div>
      
    </div>
  </div>
</template>

<script>
import wizardLayout from '../abstractWizardLayout'
import ServicePage from '../Subpages/ServiceLegacy'

export default {
  extends: wizardLayout,
  components: {
      ServicePage
  }, 
  data() {
      return {
          viewName: 'service',
          currentStep: 2,
          totalStep: 3,
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

  methods: {
    ready(ready){
      this.$refs.wizardpane.toggleNext(ready)
    },
    nextStep() {
        this.$refs.servicepage.saveExternal()
    },
    async saveServiceRequest() {
        return await this.serviceService.call('save', this.model)
    },
    finallyGoNext(){

        this.setPrevStep()
        this.request(this.nextStepRequest,  undefined,undefined,false,  this.redirectNext)
    }
  }  
}
</script>