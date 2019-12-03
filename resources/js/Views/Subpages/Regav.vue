<template>
    <div v-if="dataLoaded">
        <p class="h6 text-muted"><span class="bullet-wap">1</span> <span class="bullet-title"> Select the staff providing the service</span></p>
        <div v-if="viewData" class="d-flex align-items-center mb-2">
            <StaffPicture :src="viewData.activeStaffAvatar" :gravatar="viewData.activeStaffGravatar" @changed="changed"></StaffPicture>
            <StaffSelector :staffs="viewData.staffs" :activeStaffId="viewData.activeStaffId" @updateStaff="updateStaff"></StaffSelector>
        </div>
        
        <p class="h6 text-muted"><span class="bullet-wap">2</span> <span class="bullet-title"> Define the timezone in which you are operating</span></p>
        <TimeZones v-if="viewData" classW="d-flex" :wizard="noback" :timezones="viewData.timezones_list" 
        :defaultTimezone="viewData.timezone" @updateTimezone="updateTimezone" typeClass="small text-muted container-values d-flex justify-content-between align-items-center form-control"></TimeZones>
        <hr>
        <div v-if="hasRegav">
            <p class="h6 text-muted">
                <span class="bullet-wap">3</span> <span class="bullet-title"> Drag and drop the times you want to open for appointments</span></p>
            <RegularAvailability :initValue="viewData.regav" :viewData="viewData" @updatedDays="updatedRA"></RegularAvailability>
        </div>
    </div>
</template>

<script>
import RegularAvailability from '../../Components/RegularAvailability'
import StaffSelector from '../../Components/StaffSelector'
import TimeZones from '../../Components/TimeZones'
import abstractView from '../Abstract'
import StaffPicture from '../../Components/StaffPicture'
import RequestMaker from '../../Modules/RequestMaker'

export default {
  extends: abstractView,
  components: window.wappointmentExtends.filter('RegavComponents', {RegularAvailability,TimeZones,StaffSelector, StaffPicture},{'RequestMaker':RequestMaker} ), 
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
<style >
.bullet-title{
    color: #777;

border-bottom: 1px solid #c2c1cc;
}
.bullet-wap{
    border: 1px solid var(--primary);

padding: .35rem;

border-radius: 1.2rem;

height: 2rem;

width: 2rem;

display: inline-block;

text-align: center;

font-size: 1rem;

color: var(--primary);

margin-right: .5rem;

background-color: #fff;
}
</style>
