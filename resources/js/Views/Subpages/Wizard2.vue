<template>
  <div class="hasTopPane">
    <topPane @next="saveAndNextStep" @back="prevStep" :step="currentStep" :total="totalStep" :nextEnabled="true"></topPane>
      <div class="container-fluid">
        <div class="col-12">
          <div>
              <div class="d-flex">
                  <h1>Weekly Availability</h1>
              </div>
              <p class="h6 text-muted">This is you recurring availability, you can change it anytime</p>
              <hr>
          </div>
          <CalendarsAddEdit v-if="dataLoaded" ref="caledit" :calendar="viewData.calendar" 
          :timezones_list="viewData.timezones_list" :calendarsUsed="calendarsUsed"
          :staffs="viewData.staffs" 
            @saved="nextStep"/>
        </div>
      </div>
  </div>
</template>

<script>
import CalendarsAddEdit from '../../Settings/CalendarsAddEdit'
import wizardLayout from '../abstractWizardLayout'

export default {
  extends: wizardLayout,
  components: {
      CalendarsAddEdit,
  }, 
  data() {
      return {
          viewName: 'regav',
          currentStep: 1,
          totalStep: 3,
          calendarsUsed:[]
      } 
  },
  methods:{
    saveAndNextStep(){
      this.$refs.caledit.saveCalendar()
    }
  }

}
</script>

