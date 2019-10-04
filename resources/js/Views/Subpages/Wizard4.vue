<template>
  <div>
    <topPane @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep"></topPane>
    <div class="container-fluid">
        <div class="col-12">
            <h1 class="wp-heading-inline">Booking Widget setup</h1>
            <Widget></Widget>
        </div>
    </div>
  </div>
</template>

<script>
import wizardLayout from '../abstractWizardLayout'
import BookingWidgetEditor from '../../Components/BookingWidgetEditor'
import Widget from './Widget'
export default {
  extends: wizardLayout,
  data() {
      return {
          currentStep: 3,
          totalStep: 3,
          
          colors: '#ffffff',
          viewName: 'widget',
      } 
  },

  components: { Widget },
  computed: {
      widgetDefault(){
          return (this.viewData !== null && this.viewData.widget !== undefined)?  this.viewData.widget:null
      },
      getBgColor(){
          return (this.viewData !== null && this.viewData.bgcolor !== undefined)?  this.viewData.bgcolor:'#fff'
      }
  },
  methods: {
      redirectNext(){
          this.$WapModal()
            .request(this.sleep(4000))
          window.location = window.apiWappointment.base_admin + '?page=wappointment_calendar'
      },
  }  
}
</script>

