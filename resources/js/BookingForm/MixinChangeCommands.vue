<script>
export default {
    computed: {
        canChangeAnything(){
            return this.rescheduling === false 
        },
        canChangeDuration(){
            return this.canChangeAnything && this.service.options.durations !== undefined && this.service.options.durations.length > 1 && !this.appointmentSaved
        },
        canChangeService(){
            return this.canChangeAnything && this.services.length > 1 && !this.appointmentSaved
        },
        canChangeLocation(){
            return this.canChangeAnything && this.service.locations !== undefined && this.service.locations.length > 1 && !this.appointmentSaved
        },
        canChangeStaff(){
            return this.canChangeAnything && this.staffs.length > 1 && !this.appointmentSaved && !this.staffLocked
        },
    },
    methods:{
        changeService(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'service_selection')
              return
            } 
            if(this.staff.services.length > 1 && this.canChangeService){
                this.$emit('changeService', 'BookingServiceSelection', {service:false, location:false, duration:false, selectedSlot: false})
            }
            
        },
        changeDuration(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'service_duration')
              return
            } 
            if(this.canChangeDuration){
            this.$emit('changeDuration', 'BookingDurationSelection', {location:false, duration:false, selectedSlot: false})
            }
        },
        changeLocation(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'service_location')
              return
            } 
            if(this.canChangeLocation){
                this.$emit('changeLocation', 'BookingLocationSelection', {location:false})
            }
            
        }

    }
}
</script>
