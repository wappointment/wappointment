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
            return await this.serviceAppointment.call(
                'get', 
                window.wappointmentExtends.filter('loadAppointmentParams', {appointmentkey: this.appointmentkey}, this.getParameterByName)
                )
        }, 
        appointmentLoadingError(e){
            this.loading = false
            this.errorLoading = e.response.data.message
        },
        appointmentLoaded(d){
            this.rescheduleData = d.data
            this.appointment = d.data.appointment
            this.selection = this.appointment.type
            this.client = d.data.client
            this.service = d.data.service
            this.staff = d.data.staff
            this.time_format = this.convertDateFormat(d.data.time_format)
            this.date_format = this.convertDateFormat(d.data.date_format)
            this.zoom_browser = d.data.zoom_browser
            this.date_time_union = d.data.date_time_union
            this.loadedAppointment = true
            this.loading = false
            this.initCountDown()
        },
    }
}
</script>