<template>
  <div class="hasTopPane">
    <topPane @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep" :nextEnabled="true"></topPane>
      <div class="container-fluid">
        <div class="col-12">
          <div>
              <div class="d-flex">
                  <h1>Weekly Availability</h1>
              </div>
              <p class="h6 text-muted">This is you recurring availability, you can change it again at anytime</p>
              <hr>
          </div>
          <Regav noback></Regav>
        </div>
      </div>
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
      } 
  },

}
</script>

