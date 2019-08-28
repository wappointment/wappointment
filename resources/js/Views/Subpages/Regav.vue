<template>
    <div class="container-fluid" v-if="dataLoaded">
        <div class="col-12">
            <div>
                <div class="d-flex">
                    <h1>Weekly Availability</h1>
                </div>
                <p class="h6 text-muted">This is you recurring availability, you can change it again anytime</p>
                <hr>
            </div>
            
            <p class="h6 text-muted">1 - Define the timezone in which you are operating</p>
            <TimeZones v-if="viewData" :wizard="noback" :timezones="viewData.timezones_list" 
            :defaultTimezone="viewData.timezone" @updateTimezone="updateTimezone" typeClass="btn btn-outline-primary"></TimeZones>
            <hr>
            <div v-if="hasRegav">
                <p class="h6 text-muted">2 - Drag and drop the times you want to open for appointments</p>
                <RegularAvailability :initValue="viewData.regav" @updatedDays="updatedRA"></RegularAvailability>
            </div>
        </div>
    </div>
</template>

<script>
import RegularAvailability from '../../Components/RegularAvailability'
import TimeZones from '../../Components/TimeZones'
import abstractView from '../Abstract'

export default {
  extends: abstractView,
  components: {
      RegularAvailability,
      TimeZones
  }, 
  props:['noback'],
  data() {
      return {
          viewName: 'regav',
      } 
  },
  computed: {
      
      hasRegav(){
          return (this.viewData !== null && this.viewData.regav !== undefined) ? true:false
      },
  },
  methods: {  
    updatedRA(openeDays){
        this.settingStaffSave('regav', openeDays) 
    },

    updateTimezone(selectedTimezone){
        this.settingStaffSave('timezone', selectedTimezone)
    },

    afterSuccess(result) {
        //console.log('regav after success')
        if(result.config.data.indexOf('timezone')!==-1) this.refreshInitValue()
    }
  }  
}
</script>

