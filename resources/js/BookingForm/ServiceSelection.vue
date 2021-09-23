<template>
    <div v-if="services.length>0">
      <div class="title wtitle" v-if="options!==undefined">{{options.service_selection.select_service}}</div>
      <input v-if="services.length > 10" class="form-control" type="text" v-model="search">
      <template v-if="options.service_selection.check_full_width"> 
          <ServiceButton v-for="(service,idx) in filteredServices" class="wservice-large" :key="'service-sel-'+idx"  
        :service="service" 
        :options="options" 
        :viewData="viewData"
        @selectService="selectService" />
      </template>
      <div class="d-flex flex-wrap justify-content-around" v-else >
              <ServiceButton v-for="(service,idx) in filteredServices" :key="'service-sel-'+idx"  
        :service="service" 
        :options="options" 
        :viewData="viewData"
        @selectService="selectService" />
        
      </div>
    </div>
</template>

<script>
import ServiceButton from './ServiceButton'
import IsDemo from '../Mixins/IsDemo'
export default {
    props:['services','relations','options', 'admin', 'viewData'],
    mixins:[IsDemo],
    data: () => ({
        search: ''
    }),
    components:{ServiceButton},

    computed:{
        filteredServices(){
            let searchterm = this.search.toLowerCase()
            return this.services.filter(e => e.name.toLowerCase().indexOf(searchterm) !== -1)
        }
    },

    methods:{
        selectService(service){
            if(this.triggersDemoEvent('service_duration')){
                return
            }
            let data = {service:service}
            let nextScreen = ''
            if(service.options.durations.length > 1){
                nextScreen = 'BookingDurationSelection'
            }else{
                data.duration = service.options.durations[0].duration
                nextScreen = 'BookingLocationSelection'
                if(service.locations.length == 1){
                    data.location = service.locations[0]
                    nextScreen = 'BookingCalendar'
                }
            }
            
            this.$emit('serviceSelected', nextScreen, data)
            
        }
    }
}   
</script>
<style >
.wap-front.large-version .wap-wid.step-BookingServiceSelection {
    max-width:100%;
}
</style>