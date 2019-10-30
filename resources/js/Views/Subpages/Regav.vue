<template>
    <div v-if="dataLoaded">
        <p class="h6 text-muted">1 - Select the staff providing the service</p>
        <div v-if="viewData" class="d-flex align-items-center mb-2">
            <StaffPicture :src="viewData.activeStaffAvatar" :gravatar="viewData.activeStaffGravatar" @changed="changed"></StaffPicture>
            <StaffSelector :staffs="viewData.staffs" :activeStaffId="viewData.activeStaffId" @updateStaff="updateStaff"></StaffSelector>
        </div>
        
        <p class="h6 text-muted">2 - Define the timezone in which you are operating</p>
        <TimeZones v-if="viewData" :wizard="noback" :timezones="viewData.timezones_list" 
        :defaultTimezone="viewData.timezone" @updateTimezone="updateTimezone" typeClass="btn btn-outline-primary"></TimeZones>
        <hr>
        <div v-if="hasRegav">
            <p class="h6 text-muted">3 - Drag and drop the times you want to open for appointments</p>
            <RegularAvailability :initValue="viewData.regav" @updatedDays="updatedRA"></RegularAvailability>
        </div>
    </div>
</template>

<script>
import RegularAvailability from '../../Components/RegularAvailability'
import StaffSelector from '../../Components/StaffSelector'
import TimeZones from '../../Components/TimeZones'
import abstractView from '../Abstract'
import StaffPicture from '../../Components/StaffPicture'
export default {
  extends: abstractView,
  components: {
      RegularAvailability,
      TimeZones,
      StaffSelector,
      StaffPicture
  }, 
  props:['noback'],
  data() {
      return {
          viewName: 'regav',
          reload:false
      } 
  },
  computed: {
      hasRegav(){
          return (this.viewData !== null && this.viewData.regav !== undefined) ? true:false
      },
  },
  methods: {
    changed(){
        this.refreshInitValue()
    },
    updatedRA(openeDays){
        this.settingStaffSave('regav', openeDays) 
    },

    updateTimezone(selectedTimezone){
        this.settingStaffSave('timezone', selectedTimezone)
    },
    updateStaff(selectedStaff){
        this.reload = true
        this.settingSave('activeStaffId', selectedStaff)
    },

    afterSuccess(result) {
        //console.log('regav after success')
        if(result.config.data.indexOf('timezone')!==-1 || this.reload) {
            this.refreshInitValue()
            this.reload = false
        }
        
    }
  }  
}
</script>

