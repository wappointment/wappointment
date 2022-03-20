<template>
    <div v-if="calendars.length>0">
        <div class="title wtitle" v-if="options!==undefined ">
            <div>{{ options.staff_selection.pickstaff }}</div>
            <div class="d-flex text-sm justify-content-center align-items-center" v-if="servicesAvailable && servicesAvailable.length > 1">
                <span>{{options.staff_selection.availabilityfor}}</span>
                <div class="mr-2 ml-2">
                    <a v-if="!showDropdownService" href="javascript:;" @click="showDropdownService = true">{{ selectedServiceObject.name }}</a>
                    <select v-model="selectedService" v-else>
                        <option v-for="service in servicesAvailable" :value="service.id">{{ service.name }}</option>
                    </select>
                </div>
                <div v-if="selectedService > 0">
                    {{ getServiceDuration }} {{options.general.min}}
                </div>
            </div>
        </div>
        
        <input v-if="calendars.length > 10" class="form-control" type="text" v-model="search">
      <div class="d-flex flex-wrap justify-content-around" v-if="!reload">
        <ServiceButton v-for="(staff,idx) in filteredStaff" :key="'staff-sel-'+staff.id" 
            extraClass="wbtn-staff" 
            :service="staff" 
            :options="options" 
            @selectService="selectStaff" >
            <FirstAvailabilities :staffs="filteredStaff" :location="selectedLocationObject" :options="options" :timeprops="timeprops" :duration="getServiceDuration" :viewData="viewData"
            :service="selectedServiceObject"
            :initIntervalsCollection="advancedStaff[idx].initIntervalsCollection" />
        </ServiceButton>
      </div>
    </div>
</template>

<script>
import ServiceButton from './ServiceButton'
import FirstAvailabilities from './FirstAvailabilities'
import Intervals from '../Standalone/intervals'
import MixinChange from './MixinChange'
import IsDemo from '../Mixins/IsDemo'
export default {
    mixins: [ window.wappointmentExtends.filter('MixinChange', MixinChange), IsDemo],
    props:['calendars','options', 'timeprops','viewData', 'attributesEl'],
    data: () => ({
        search:'',
        advancedStaff: [],
        selectedService:false,
        reload:false,
        showDropdownService: false
    }),
    components:{FirstAvailabilities, ServiceButton},
    watch:{
        selectedService(val){
            this.reload = true
            setTimeout(this.reloaded, 100)
        }
    },
    computed:{
        selectedServiceObject(){
            let selectedServiceId = this.selectedService
            return this.servicesAvailable.find(e => parseInt(e.id) === parseInt(selectedServiceId))
        },
        selectedLocationObject(){
            return this.selectedServiceObject.locations[0]
        },
        servicesAvailable(){
            let serviceLocked = this.attributesEl !== undefined && this.attributesEl.serviceSelection !== undefined ? this.attributesEl.serviceSelection:[]
            let services = serviceLocked.length > 1? this.viewData.services.filter(s => serviceLocked.indexOf(s.id)!==-1):this.viewData.services
            let dummyService = {}
            dummyService.locations = services[0].locations
            dummyService.sorting = services[0].sorting
            dummyService.id = -1
            dummyService.name = '60'+this.options.general.min
            dummyService.options = {}
            dummyService.options.durations = [{duration: 60}]

            services.unshift(dummyService)
            return services
        },
        getServiceDuration(){
            return this.selectedServiceObject.options.durations[0].duration
        },
        filteredStaff(){
            let searchterm = this.search.toLowerCase()
            let serviceSelected = this.selectedService
            return this.calendars.map(e => Object.assign(
                {name:e.n, options:{icon:{src:e.a.replace('?s=46','?s=80'),wp_id:true}}}, e)
                ).filter(e => e.name.toLowerCase().indexOf(searchterm) !== -1 && (serviceSelected < 0 || serviceSelected>0 && e.services.indexOf(serviceSelected) !== -1))
        },
    },
    created(){
        this.selectedService = this.servicesAvailable[0].id
        this.setAdvancedStaff()
    },
    methods:{
        reloaded(){
            this.reload = false
        },
        setAdvancedStaff(){
            for (let i = 0; i < this.calendars.length; i++) {
                this.advancedStaff[i] = {
                    initIntervalsCollection: this.getIntervalsCollection(this.calendars[i])
                } 
            }
        },
        getFirstService(staff){
            let serviceid = staff.services[0]
            return this.viewData.services.find(e => e.id == serviceid)
        },
        
        getIntervalsCollection(staff){
            return new Intervals(staff.availability, false)
        },
        hasIntervals(staff){
            let intervalsStaff = this.getIntervalsCollection(staff)
            return [null,undefined].indexOf(intervalsStaff.intervals) === -1 && intervalsStaff.intervals.length>0
        },
        selectStaff(staff){

            if(this.isDemo || !this.hasIntervals(staff)) {
              return
            } 
            this.$emit('staffSelected', staff)
        }
    }
}   
</script>
<style>
.wap-front .step-BookingStaffSelection .wap-form-body,
.overflowhidden.step-staff_selection .wap-form-body{
    margin-top: 0;
}

.wap-front.large-version .wap-wid.step-BookingStaffSelection {
    max-width:100%;
}

</style>