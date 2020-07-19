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
            
            let component_name = window.wappointmentExtends.filter('BFDemoGetChildComponentForStep', this.getChildComponentForStep(step_name), {step_name: step_name} ) 
            let component_data = window.wappointmentExtends.filter('BFDemoGetChildComponentDataForStep', this.getChildComponentDataForStep(step_name, this.options.editionsSteps), 
            {step_name: step_name, bookingFormObject: this, editionsSteps: this.options.editionsSteps}) 

            this.childChangedStep(component_name ,component_data)
        },

        getChildComponentDataForStep(step_name, editionsSteps){
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
            if(cursor_at >= calendar_at) {
                let laskey = this.intervalsCollection.intervals.length -1
                data.selectedSlot = this.intervalsCollection.intervals[laskey].start
            }
            
            if(cursor_at > form_at) {
                data.appointmentSaved = true
                
                if(this.passedDataSent !== null)data.dataSent = this.passedDataSent
                else data.dataSent = this.options.demoData.form
            }
            return data
        },

        getChildComponentForStep(step_name){
            switch (step_name) {
                case 'selection':
                    return 'BookingCalendar'
                case 'form':
                    return 'BookingFormInputs'
                case 'confirmation':
                    return 'BookingFormConfirmation'
            }
        }

    }
}
</script>
