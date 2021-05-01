<template>
    <div v-if="calendars.length>0">
        <div class="title wtitle" v-if="options!==undefined">Select staff</div>
        <input type="text" v-model="search">
      <!-- <div class="title wtitle" v-if="options!==undefined">{{ options.staff_Selection.select_staff }}</div> -->
      <div class="d-flex flex-wrap" >
        <ServiceButton v-for="(staff,idx) in filteredStaff" :key="'staff-sel-'+idx" 
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
export default {
    props:['calendars','options', 'timeprops','viewData'],
    data: () => ({
        disabledButtons: false,
        search:''
    }),
    components:{FirstAvailabilities, ServiceButton},
    created(){
        if(this.options !== undefined &&  this.options.demoData !== undefined){
            this.disabledButtons = true
        }
    },
    computed:{
        filteredStaff(){
            let searchterm = this.search.toLowerCase()
            return this.calendars.map(e => Object.assign({name:e.n, options:{icon:{src:e.a.replace('?s=46','?s=80'),wp_id:true}}}, e)).filter(e => e.name.toLowerCase().indexOf(searchterm) !== -1)
        }
    },
    methods:{
        getIntervalsCollection(staff){
            return new Intervals(staff.availability)
        },
        selectStaff(staff){
            if(this.disabledButtons && this.options !== undefined ) {
              //this.options.eventsBus.emits('stepChanged', 'service_duration')
              return
            } 

            this.$emit('staffSelected', staff)
        }
    }
}   
</script>
<style>
.wap-front .step-StaffSelectionScreen .wap-form-body{
    margin-top: 0;
}
</style>