<script>
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
export default {
    methods:{
        convertDateFormat(date){
            return convertDateFormatPHPtoMoment(date)
        },
        loadAppointment(){
            this.loading = true
            this.loadAppointmentRequest()
            .then(this.appointmentLoaded)
            .catch(this.appointmentLoadingError)
        },
        async loadAppointmentRequest() {
            return await this.serviceAppointment.call('get', {appointmentkey: this.appointmentkey})
        }, 
        appointmentLoadingError(e){
            this.loading = false
            this.errorLoading = e.response.data.message
            //console.log('appointmentBookingError',e)
        },
        appointmentLoaded(d){
            this.rescheduleData = d.data
            this.appointment = d.data.appointment
            this.client = d.data.client
            this.service = d.data.service
            this.staff = d.data.staff
            this.time_format = this.convertDateFormat(d.data.time_format)
            this.date_format = this.convertDateFormat(d.data.date_format)
            this.date_time_union = d.data.date_time_union
            this.loadedAppointment = true
            this.loading = false
        },
    }
}
</script>