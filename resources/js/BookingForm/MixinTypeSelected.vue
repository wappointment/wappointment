<script>
export default {

    data: () => ({
        selection: false,
    }),

    methods:{
        appointmentIsType(type){
            return this.selection == type || (this.appointment !== undefined && this.appointment.type!== undefined && this.appointment.type == type)
        }
    },

    computed: {
        phoneSelected(){
            return this.appointmentIsType('phone')
        },
        physicalSelected(){
            return this.appointmentIsType('physical')
        },
        skypeSelected(){
            return this.appointmentIsType('skype')
        },
        zoomSelected(){
            return this.appointmentIsType('zoom')
        },
    },
}
</script>