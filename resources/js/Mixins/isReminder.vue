<script>
import ServiceReminder from '../Services/V1/Reminder'
export default {

    data() {
        return {
            parentLoad: false,
            remindersLoaded: false,
            reminders: [],
            mail_status: false,
            allow_rescheduling: false,
            allow_cancellation: false,
            reschedule_link: '',
            allow_cancellation: '',
            save_appointment_text_link: '',
            model: null,
            serviceReminder: null,
            labels: null,
            emptyModel: null,
            loading: false,
        };
    },
    created(){
        this.serviceReminder = this.$vueService(new ServiceReminder)
    },
    methods: {

        goToMailConfig() {
            this.setCrumb('MailConfig', 'Mail Config', 'goToMailConfig')
        },
        goToEditReminder(props = {}) {
            this.setCrumb('EditReminders', 'Edit Reminder', 'goToEditReminder', props)
        },
        mailConfigured(){
            this.mail_status = true
        },
        getReminderLabel(reminder){
            return reminder.label
            
            let labelString = '' 
            
            if(this.isAppointmentStartEvent(reminder)){
            labelString += 'Sent before appointment takes place.(' + this.getDelay(reminder) + ')'
            }else if(this.isAppointmentBookedEvent(reminder)){
            labelString += 'Sent after appointment has been confirmed.'
            }else if(this.isAppointmentRescheduleEvent(reminder)){
            labelString += 'Sent after appointment has been rescheduled.'
            }else if(this.isAppointmentCancelEvent(reminder)){
            labelString += 'Sent after appointment has been cancelled.'
            }else if(this.isAppointmentPendingEvent(reminder)){
            labelString += 'Sent after appointment has been booked when admin approval is required.'
            }
            return labelString
        },
        isEmail(reminder) {
            return reminder.type==1
        },
        isAppointmentStartEvent(reminder) {
            return reminder.event !== undefined && reminder.event==1
        },
        isAppointmentBookedEvent(reminder) {
            return reminder.event !== undefined && reminder.event==2
        },
        isAppointmentRescheduleEvent(reminder) {
            return reminder.event !== undefined && reminder.event==3
        },
        isAppointmentCancelEvent(reminder) {
            return reminder.event !== undefined && reminder.event==4
        },
        isAppointmentPendingEvent(reminder) {
            return reminder.event !== undefined && reminder.event==5
        },

        getDelay(reminder) {
            if(reminder.options['when_number']!== undefined && reminder.options['when_number'] > 0){
                return 'sent ' + reminder.options['when_number'] + ' ' + this.convertUnit(reminder.options['when_unit']) + ' before'
            }
            return 'sent immediately'
        },

        convertUnit(unit){
            if(unit ==1)  return 'minute(s)'
            else if(unit == 2) return 'hour(s)'
            else if(unit == 3) return 'day(s)'
        },

    }
};
</script>
