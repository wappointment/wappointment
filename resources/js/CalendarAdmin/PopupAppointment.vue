
<template>
    <WapModal :show="true" @hide="hideModal" noscroll>
        <h4 slot="title" class="modal-title">{{ get_i18n('calendar_popup', 'calendar') }}</h4>

        <div class="d-flex flex-column flex-md-row justify-content-between" v-if="!activeCompName">
            <ButtonPopup v-for="button in buttons"  @click="confirmButtonClick(button)"
            :key="button.key" :classIcon="button.icon" :title="button.title" :subtitle="button.subtitle" />
        </div>
        <component v-else :is="activeComp.name"
            v-bind="activeComp.props"
            v-on="activeComp.listeners" />
    </WapModal>
</template>

<script>
import ButtonPopup from './ButtonPopup'
import CancelBooking from './CancelBooking'
import RescheduleBooking from './RescheduleBooking'

export default {
    props:['displayTimezone', 'activeStaff', 'appointment', 'viewData', 'getThisWeekIntervals', 'momenttz'],
    components: {
        ButtonPopup,
        CancelBooking,
        RescheduleBooking
    },
    data: () =>({
        activeCompName: false,
        componentsList: null,
    }),
    created(){  
        this.setComponentList()
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
        confirmButtonClick(button){
            this.activeCompName = button.component
        },

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
        confirmedStatus(rescheduled=false){
            
            if(rescheduled){
                this.$emit('rescheduleOn')
            }else{
                this.$emit('refreshEvents')
            }
            this.hideModal()
            
        },
    }
}
</script>