<script>
export default {
    data: () => ({
        serviceLocked: false,
        staffLocked:false
    }),
    created(){
        this.checkServiceLocked()
        this.checkStaffLocked()
    },
    computed: {
        canChangeAnything(){
            return this.rescheduling === false 
        },
        canChangeDuration(){
            return this.canChangeAnything && this.service.options.durations !== undefined && this.service.options.durations.length > 1 && !this.appointmentSaved
        },
        canChangeService(){
            return this.canChangeAnything && this.services.length > 1 && !this.appointmentSaved && !this.serviceLocked
        },
        canChangeLocation(){
            return this.canChangeAnything && this.service.locations !== undefined && this.service.locations.length > 1 && !this.appointmentSaved
        },
        canChangeStaff(){
            return this.canChangeAnything && this.staffs.length > 1 && !this.appointmentSaved && !this.staffLocked
        },
    },
    methods:{
        checkServiceLocked(){
            return false
        },
        checkStaffLocked(){
            return false
        },
        refreshClicked(){
            if(this.disabledButtons || !this.isStepSlotSelection) {
              this.options.eventsBus.emits('stepChanged', 'service_selection')
              return
            } 
            this.changeService()
            this.$emit('refreshed')
        },
        changeStaff(){
            //this.$emit('changeDuration', 'BookingDurationSelection', {location:false,duration:false})
        },
        changeService(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'service_selection')
              return
            } 
            this.$emit('changeService', 'BookingServiceSelection', {service:false, location:false, duration:false, selectedSlot: false})
        },
        changeDuration(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'service_duration')
              return
            } 
            this.$emit('changeDuration', 'BookingDurationSelection', {location:false, duration:false, selectedSlot: false})
        },
        changeLocation(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'service_location')
              return
            } 
            this.$emit('changeLocation', 'BookingLocationSelection', {location:false})
        }
    }
}
</script>
