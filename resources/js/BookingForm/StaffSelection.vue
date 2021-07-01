<template>
    <div v-if="calendars.length>0">
        <div class="title wtitle" v-if="options!==undefined ">{{ options.staff_selection.pickstaff }}</div>
        <input v-if="calendars.length > 10" class="form-control" type="text" v-model="search">
      <div class="d-flex flex-wrap justify-content-around" >
        <ServiceButton v-for="(staff,idx) in filteredStaff" :key="'staff-sel-'+staff.id" 
            extraClass="wbtn-staff" 
            :service="staff" 
            :options="options" 
            @selectService="selectStaff" >
            <FirstAvailabilities :options="options" :timeprops="timeprops" :initIntervalsCollection="getIntervalsCollection(staff)"
            :duration="60" :viewData="viewData" />
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
        search:''
    }),
    components:{FirstAvailabilities, ServiceButton},
    computed:{
        filteredStaff(){
            let searchterm = this.search.toLowerCase()
            
            return this.calendars.map(e => Object.assign(
                {name:e.n, options:{icon:{src:e.a.replace('?s=46','?s=80'),wp_id:true}}}, e)
                ).filter(e => e.name.toLowerCase().indexOf(searchterm) !== -1)
        },
    },
    methods:{
        getIntervalsCollection(staff){
            return new Intervals(staff.availability)
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