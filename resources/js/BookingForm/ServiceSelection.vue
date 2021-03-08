<template>
    <div v-if="services.length>0">
      <div class="title wtitle" v-if="options!==undefined">{{options.service_selection.select_service}}</div>
      <div class="d-flex flex-wrap" >
              <ServiceButton v-for="(service,idx) in services" :key="'service-sel-'+idx"  
        :service="service" 
        :options="options" 
        :viewData="viewData"
        @selectService="selectService" />
        
      </div>
    </div>
</template>

<script>
import ServiceButton from './ServiceButton'
export default {
    props:['services','relations','options', 'admin', 'viewData'],
          
    data: () => ({
        disabledButtons: false,
    }),
    components:{ServiceButton},
    created(){
        if(this.options !== undefined &&  this.options.demoData !== undefined){
            this.disabledButtons = true
        }
    },

    methods:{
        selectService(service){
            if(this.disabledButtons && this.options !== undefined ) {
              this.options.eventsBus.emits('stepChanged', 'service_duration')
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