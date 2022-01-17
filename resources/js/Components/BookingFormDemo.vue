<script>
import BookingForm from '../BookingForm/Main'

export default {
    extends: BookingForm,
    data: () => ({
        bfdemo: true,
    }),
    watch:{
        step(val){
            if([undefined, null].indexOf(val) === -1 ) {
                this.demoConfigure(val)
            }
        }
    },

    methods: {
        refreshInitValue(){
            this.viewData = this.options.frontAvailability
            this.loadedAfter()
        },

        loadedInit(step){
            if([undefined, null].indexOf(step) === -1) {
                this.demoConfigure(step)
            }
        },

        demoConfigure(step_name){
            let comp_data_step = this.isLegacy ? this.getChildComponentDataForStepLegacy(step_name, this.options.editionsSteps): this.getChildComponentDataForStep(step_name, this.options.editionsSteps)
            let component_name = window.wappointmentExtends.filter('BFDemoGetChildComponentForStep', this.getChildComponentForStep(step_name), {step_name: step_name} ) 
            let component_data = window.wappointmentExtends.filter('BFDemoGetChildComponentDataForStep', comp_data_step, 
            {step_name: step_name, bookingFormObject: this, editionsSteps: this.options.editionsSteps}) 
            this.childChangedStep(component_name ,component_data)
        },

        getChildComponentDataForStepLegacy(step_name, editionsSteps){

            let data = {}
            let calendar_at = editionsSteps.findIndex((element) => element.key == 'selection')
            let form_at = editionsSteps.findIndex((element) => element.key == 'form')
            let confirmation_at = editionsSteps.findIndex((element) => element.key == 'confirmation')
            var cursor_step_name_id = step_name
            let cursor_at = editionsSteps.findIndex((element) => element.key == cursor_step_name_id)

            if(cursor_at < calendar_at) {
                data.selectedSlot = false
                data.appointmentSaved = false
                data.dataSent = {}
            }
            if(cursor_at > calendar_at) {
                data.selectedSlot = this.getSlotAvailableForDemo()
                data.location =  this.service.type !== undefined ? this.service.type[0]:''
            }
            
            if(cursor_at > form_at) {
                data.appointmentSaved = true
                
                if(this.passedDataSent !== null)data.dataSent = this.passedDataSent
                else{
                    data.dataSent = this.options.demoData.form
                    /* data.dataSent.type = this.service.type[0]
                    data.dataSent.time = this.getSlotAvailableForDemo() */
                } 
            }

            return data
        },

        getChildComponentDataForStep(step_name, editionsSteps){
            let data = {}

            let service_selection = editionsSteps.findIndex((element) => element.key == 'service_selection')
            let duration_selection = editionsSteps.findIndex((element) => element.key == 'service_duration')
            let location_selection = editionsSteps.findIndex((element) => element.key == 'service_location')
            let calendar_at = editionsSteps.findIndex((element) => element.key == 'selection')
            let form_at = editionsSteps.findIndex((element) => element.key == 'form')
            let confirmation_at = editionsSteps.findIndex((element) => element.key == 'confirmation')

            let cursor_at = editionsSteps.findIndex((element) => element.key == step_name)

            if(cursor_at < calendar_at) {
                data.selectedSlot = false
                data.appointmentSaved = false
                data.dataSent = {}
            }

            if(cursor_at > 0) {
                data.service = false
                data.duration = false
                data.location = false
            }

            if(cursor_at > service_selection) {
                data.service = this.services[0]
            }
            if(cursor_at > duration_selection) {
                data.duration = data.service.options.durations[0].duration
            }
            if(cursor_at > location_selection) {
                data.location =  data.service.locations[0]
            }

            if(cursor_at > calendar_at) {
                data.selectedSlot = this.getSlotAvailableForDemo()
                
            }
            
            if(cursor_at > form_at) {
                data.appointmentSaved = true
                
                data.dataSent = this.passedDataSent !== null ? this.passedDataSent:this.options.demoData.form
            }

            return data
        },


        getChildComponentForStep(step_name) {

            switch (step_name) {
                case 'staff_selection':
                    return 'BookingStaffSelection'
                case 'service_selection':
                    return 'BookingServiceSelection'
                case 'service_duration':
                    return 'BookingDurationSelection'
                case 'service_location':
                    return 'BookingLocationSelection'
                case 'selection':
                    return 'BookingCalendar'
                case 'swift_payment':
                    return 'BookingPaymentStep'
                case 'form':
                    return 'BookingFormInputs'
                case 'confirmation':
                    return 'BookingFormConfirmation'
            }
        },

        getSlotAvailableForDemo(){
            return {
                start: this.intervalsCollection.intervals[0].start
            }
        }

    }
}
</script>
