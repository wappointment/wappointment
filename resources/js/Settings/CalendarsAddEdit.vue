<template>
    <div>
        <div>
            <div>
                <p class="h6 text-muted">
                    <span class="bullet-wap">1</span> 
                    <span class="bullet-title">Account Selection</span>
                </p>
                <small class="text-muted mr-6" >Select an account or enter an email to create a new one</small>
                <div  class="d-flex align-items-top mb-2">
                    <StaffPicture v-if="calendarSelected.name!=''" :src="calendarSelected.avatar" :gravatar="calendarSelected.gravatar" @changed="changedPicture" />
                    <div v-if="calendarSelected.name!=''" class="mr-2 changename">
                        <InputPh v-model="calendarSelected.name" ph="Name" @updatedValue="updateStaffName" />
                    </div>
                    <div class="account-selector">
                        <StaffSelector :staffs="staffs" :activeStaffId="calendarSelected.wp_uid" @updateStaff="updateStaff"></StaffSelector>
                    </div>
                </div>
               
                
            </div>
            <div v-if="staffSelected">
                <p class="h6 text-muted">
                    <span class="bullet-wap">2</span> 
                    <span class="bullet-title"> Set a timezone</span>
                </p>
                <div class="cal-edit-margin">
                    <TimeZones classW="d-flex" :timezones="timezones_list" 
                :defaultTimezone="calendarSelected.timezone" @updateTimezone="updateTimezone" typeClass="small text-muted container-values d-flex justify-content-between align-items-center form-control" />
                </div>
                
                <hr>
            </div>
            
            <div v-if="staffSelected">
                <p  class="h6 text-muted">
                    <span class="bullet-wap">3</span> 
                    <span class="bullet-title"> Set standard weekly schedule</span>
                </p>

                <div class="cal-edit-margin">
                    <RegularAvailability :initValue="getRegav" :viewData="calendarSelected" :services="services"
                    @updatedDays="updatedRA"
                    @changedABD="changedABD" />
                </div>
            </div>
            
        </div>
        <div v-if="requireSave" class="save-buttons">
            <button class="btn btn-primary" @click="saveCalendar">Save</button>
        </div>
    </div>
</template>

<script>
import RegularAvailability from '../RegularAvailability/RegularAvailability'
import StaffSelector from '../Components/StaffSelector'
import TimeZones from '../Components/TimeZones'
import abstractView from '../Views/Abstract'
import StaffPicture from '../Components/StaffPicture'
import RequestMaker from '../Modules/RequestMaker'
import ServiceCalendar from '../Services/V1/Calendars'
import momenttz from '../appMoment'
export default {
    extends: abstractView,
    components: window.wappointmentExtends.filter('RegavComponents', {RegularAvailability,TimeZones,StaffSelector, StaffPicture},{'RequestMaker':RequestMaker} ), 
    props:{
        calendar:{
            type: Object,
            default: false
        },
        timezones_list:{
            type: Object,
        },
        staffs:{
            type: Array,
        },
        services:{
            type: Array,
        },
    },
    
    data() {
        return {
            calendarSelected: false,
            requireSave: false,
            hasWpuid:false
        } 
    },
    created(){
        this.calendarSelected = Object.assign({}, this.calendar)
        if(this.calendarSelected.wp_uid!== undefined && this.calendarSelected.wp_uid > 0){
            this.hasWpuid = this.calendarSelected.wp_uid
        }
        
        this.mainService = this.$vueService(new ServiceCalendar)
        if(this.calendarSelected.timezone == ''){
            this.calendarSelected.timezone = momenttz.tz.guess()
        }
    },
    watch: {
      calendarSelected: {
          handler: function(newValue, old){
            if(old !== false){
                this.requireSave = true
                this.hasWpuid = newValue.wp_uid
            }
          },
          deep: true
      },

    },
  computed: {
      getRegav(){
          let regav = Object.assign({},this.calendar.regav)
          if(this.calendar.regav.precise === undefined){
              regav = this.convertRegavToPrecise(regav)
          }
          return regav
      },

      staffSelected(){
          return this.calendarSelected !== false && this.hasWpuid > 0
      }
      
  },
  methods: {
    convertRegavToPrecise(regav){
        for (const key in this.calendar.regav) {
            if (this.calendar.regav.hasOwnProperty(key)) {
                for (let i = 0; i < this.calendar.regav[key].length; i++) {
                    const el = this.calendar.regav[key][i]
                    regav[key][i] = [el[0]*60, el[1]*60] //converting hours to minutes
                }
            }
        }
        return regav
    },

    updatedRA(regavUpdated){
        regavUpdated.precise = true
        this.calendarSelected.regav = regavUpdated
    },
    changedABD(avbUpdated){
        this.calendarSelected.avb = avbUpdated
    },
    saveCalendar(){
        this.request(this.saveCalendarRequest,this.calendarSelected,undefined,false,this.calendarSaved)
    },
    async saveCalendarRequest(params){
        return await this.mainService.call('save',params)
    },
    calendarSaved(response){
        this.serviceSuccess(response)
        this.$emit('saved', response)
    },
    changedPicture(value){
        if(value > 0){
            this.request(this.getAvatarRequest,{id:value},undefined,false,this.avatarLoaded)
        }else{
            this.calendarSelected.avatar = ''
            this.calendarSelected.avatar_id = ''
        }
    },
    avatarLoaded(response){
        this.calendarSelected.avatar_id = response.data.id
        this.calendarSelected.avatar = response.data.avatar
    },
    async getAvatarRequest(params){
        return await this.mainService.call('getAvatar',params)
    },

    updateTimezone(selectedTimezone){
        this.calendarSelected.timezone = selectedTimezone
    },
    updateStaffName(value){
        this.calendarSelected.name = value
    },
    updateStaff(selectedStaff){
        if(this.calendarSelected.wp_uid ==selectedStaff.ID){
            return
        }
        this.calendarSelected.wp_uid = selectedStaff.ID
        this.calendarSelected.gravatar = selectedStaff.gravatar
        this.calendarSelected.name = selectedStaff.display_name !== '' ? selectedStaff.display_name:'Jane Doe'
    },

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
.save-buttons{
    box-shadow: 0px 8px 34px 0 rgba(0,0,0,.1);
    position: fixed;
    bottom: 0;
    z-index: 999999;
    padding: 1rem;
    min-width: 200px;
    background: #fff;
    border-radius: .5rem .5rem 0 0;
}
.account-selector{
    width: 380px;
}
.cal-edit-margin{
    margin-left: 48px;
}
.mr-6{
    margin-right: 3rem !important;
}
</style>
