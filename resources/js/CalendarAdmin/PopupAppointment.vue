
<template>
    <WapModal :show="true" @hide="hideModal" noscroll>
        <h4 slot="title" class="modal-title">{{ get_i18n('calendar_popup', 'calendar') }}</h4>
        <component :is="activeComp.name"
            v-bind="activeComp.props"
            v-on="activeComp.listeners" />
    </WapModal>
</template>

<script>
import ButtonPopup from './ButtonPopup'
import CancelBooking from './CancelBooking'

export default {
    props:['displayTimezone', 'activeStaff', 'appointment', 'viewData', 'getThisWeekIntervals', 'momenttz'],
    components: {
        ButtonPopup,
        CancelBooking,
    },
    data: () =>({
        activeCompName: false,
        componentsList: null,
    }),
    created(){  
        this.setComponentList()
        this.activeCompName = 'CancelBooking'
    },
    computed:{
        buttons(){
            return this.viewData.buttons_appointment
        },
        activeComp(){
            let name = this.activeCompName
            return this.componentsList.find(e => e.name == name)
        },

    },
    methods:{

        setComponentList(){
            let default_object = {
                props: {
                    appointment: this.appointment,
                    timezone: this.displayTimezone,
                    activeStaff: this.activeStaff,
                    viewData: this.viewData,
                },
                listeners: {
                    confirmed: this.confirmedStatus,
                    cancelled: this.hideModal,
                },
            }
            this.componentsList = this.buttons.map(e => Object.assign({name:e.component}, default_object))

        },
        formatTime(myMoment, format = false){
            if(format === false) format = this.viewData.time_format
            return myMoment.format(format)
        },

       
        hideModal(){
            this.$emit('hide')
        },
        confirmedStatus(){
            this.$emit('refreshEvents')
            this.hideModal()
            
        },
    }
}
</script>