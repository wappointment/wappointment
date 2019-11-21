<script>
import BookingForm from './BookingForm'

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

        loadedInit(step){

            if([undefined, null].indexOf(step) === -1) {
                this.demoConfigure(step)
            }
        },

        demoConfigure(step_name){
            if(['button'].indexOf(step_name) === -1) {
                this.selectedSlot = false
                this.appointmentSaved = false
                this.dataSent = {}
            }
            if(['button', 'selection'].indexOf(step_name) === -1) {
                let laskey = this.intervalsCollection.intervals.length -1
                this.selectedSlot = this.intervalsCollection.intervals[laskey].start
            }
            if(['button', 'selection', 'form'].indexOf(step_name) === -1) {
                this.appointmentSaved = true
                
                if(this.passedDataSent !== null)this.dataSent = this.passedDataSent
                else this.dataSent = this.options.demoData.form
            }
            let component_name = window.wappointmentExtends.filter('BFDemoGetChildComponentForStep', this.getChildComponentForStep(step_name), {step_name: step_name} ) 
            console.log('component_name', component_name)
            this.childChangedStep(component_name )
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
