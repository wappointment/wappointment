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

    }
};
</script>
