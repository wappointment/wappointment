<template>
    <div v-if="dataLoaded">
        <div v-if="minimal === false">
            <p class="h6 text-muted"><span class="bullet-wap">1</span> 
                <span class="bullet-title" v-if="viewData.staffs.length > 1"> Set who provides the appointments </span>
                <span class="bullet-title" v-else> Modify your picture </span>
            </p>
            <div v-if="viewData" class="d-flex align-items-center mb-2">
                <StaffPicture :src="viewData.activeStaffAvatar" :gravatar="viewData.activeStaffGravatar" @changed="changed"></StaffPicture>
                <div class="mr-2 changename">
                    <InputPh v-model="viewData.activeStaffName" ph="Change name" @updatedValue="updateStaffName" />
                </div>
                <StaffSelector :staffs="viewData.staffs" :activeStaffId="viewData.activeStaffId" @updateStaff="updateStaff"></StaffSelector>
            </div>
            
            <small class="text-muted" > In order to change the user in charge <a href="users.php" target="_blank">add a WordPress user</a> or edit the current one </small>
            
            <p class="h6 text-muted"><span class="bullet-wap">2</span> <span class="bullet-title"> Set your timezone</span></p>
            <TimeZones v-if="viewData" classW="d-flex" :wizard="noback" :timezones="viewData.timezones_list" 
            :defaultTimezone="viewData.timezone" @updateTimezone="updateTimezone" typeClass="small text-muted container-values d-flex justify-content-between align-items-center form-control"></TimeZones>
            <hr>
        </div>
        
        <div v-if="hasRegav">
            <p v-if="minimal === false" class="h6 text-muted">
                <span class="bullet-wap">3</span> 
                <span class="bullet-title"> Set your standard weekly schedule</span>
            </p>
 
            <RegularAvailability :initValue="getRegav" :viewData="viewData" :minimal="minimal"
            @updatedDays="updatedRA"
            @changedABD="changedABD"></RegularAvailability>
        </div>
    </div>
</template>

<script>
import RegularAvailability from './RegularAvailability'
import StaffSelector from '../Components/StaffSelector'
import TimeZones from '../Components/TimeZones'
import abstractView from '../Views/Abstract'
import StaffPicture from '../Components/StaffPicture'
import RequestMaker from '../Modules/RequestMaker'

export default {
  extends: abstractView,
  components: window.wappointmentExtends.filter('RegavComponents', {RegularAvailability,TimeZones,StaffSelector, StaffPicture},{'RequestMaker':RequestMaker} ), 
  props:{
      noback:{},
      minimal:{
          type: Boolean,
          default: false
      }
  },
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
      getRegav(){
          let regav = Object.assign({},this.viewData.regav)
          if(this.viewData.regav.precise === undefined){
              regav = this.convertRegavToPrecise(regav)
          }
          return regav
      }
  },
  methods: {
    convertRegavToPrecise(regav){
        for (const key in this.viewData.regav) {
            if (this.viewData.regav.hasOwnProperty(key)) {
                for (let i = 0; i < this.viewData.regav[key].length; i++) {
                    const el = this.viewData.regav[key][i]
                    regav[key][i] = [el[0]*60, el[1]*60] //converting hours to minutes
                }
                 
            }
        }
        return regav
    },
    changed(){
        this.refreshInitValue()
    },
    updatedRA(openeDays){
        openeDays.precise = true
        this.settingStaffSave('regav', openeDays) 
    },
    changedABD(value){
        this.settingStaffSave('availaible_booking_days', value) 
    },

    updateTimezone(selectedTimezone){
        this.settingStaffSave('timezone', selectedTimezone)
    },
    updateStaffName(value){
        this.reload = true
        this.viewData.activeStaffName = value
        this.settingStaffSave('display_name', value)
    },
    updateStaff(selectedStaff){
        this.reload = true
        this.settingSave('activeStaffId', selectedStaff)
    },

    afterSuccess(result) {
        //console.log('regav after success')
        if(result.config.data.indexOf('timezone')!==-1 || this.reload) {
            //this.refreshInitValue()
            //this.reload = false
        }
        
    }
  }  
}
</script>
<style >
.bullet-title{
    color: #777;
    border-bottom: 1px solid #c2c1cc;
    font-weight: bold;
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
.changename .label-wrapper{
    margin-bottom: 0 !important;
}
</style>
