<template>
  <div>
    <topPane @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep"></topPane>

    <ServicePage ref="servicepage" @saved="finallyGoNext" noback></ServicePage>
  </div>
</template>

<script>
import wizardLayout from '../abstractWizardLayout'
import ServicePage from '../Subpages/Service'

export default {
  extends: wizardLayout,

  components: {
      ServicePage
  }, 
  data() {
      return {
          currentStep: 2,
          totalStep: 3,
      } 
  },

  methods: {

    nextStep() {
        this.$refs.servicepage.save();
    },
    async saveServiceRequest() {
        return await this.serviceService.call('save', this.model)
    },
    finallyGoNext(){

        this.setPrevStep()
        this.request(this.nextStepRequest,  undefined, this.redirectNext)
    }
  }  
}
</script>