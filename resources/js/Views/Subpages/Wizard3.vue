<template>
  <div>
    <topPane ref="wizardpane" @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep"></topPane>
    <div class="container-fluid">
      <div class="reduced">
        <div class="col-12">
          <div class="d-flex">
              <h1>Service Setup</h1>
          </div>
          <p class="h6 text-muted">The service visitors will book you for</p>
          <ServicePage v-if="viewData!==null" ref="servicepage" :buttons="false"
           :dataPassed="viewData.service" @saved="finallyGoNext" @ready="ready"></ServicePage>
        </div>
        
      </div>
      
    </div>
  </div>
</template>

<script>
import wizardLayout from '../abstractWizardLayout'
import ServicePage from '../Subpages/ServiceNew'

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