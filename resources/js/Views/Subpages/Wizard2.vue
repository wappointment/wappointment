<template>
  <div class="hasTopPane">
    <topPane @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep" :nextEnabled="enabledNext"></topPane>
    <Regav noback></Regav>
  </div>
</template>

<script>
import Regav from './Regav'
import wizardLayout from '../abstractWizardLayout'

export default {
  extends: wizardLayout,
  components: {
      Regav
  }, 
  data() {
      return {
          currentStep: 1,
          totalStep: 3,
          enabledNext: true,
      } 
  },

}
</script>

