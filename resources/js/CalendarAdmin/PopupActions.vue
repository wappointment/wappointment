
<template>
    <WapModal :show="true" @hide="hideModal" noscroll>
        <h4 slot="title" class="modal-title">{{ get_i18n('calendar_popup', 'calendar') }}</h4>
        <h3 class="mb-4" v-if="selectionSingleDay"> {{ startDayDisplay }} - 
            <span class="text-muted">{{ sprintf_i18n('calendar_popup_from_until', 'calendar', [startTimeDisplay,endTimeDisplay]) }}</span>
            <span class="small text-muted" v-if="viewData.buffer_time > 0">{{ sprintf_i18n('calendar_popup_includes', 'calendar', viewData.buffer_time) }}</span>
        </h3>
        <h3 class="mb-4" v-else> {{ shortStDayDisplay }} - {{ shortEdDayDisplay }}</h3>
        <div class="d-flex flex-column flex-md-row justify-content-between" v-if="!activeCompName">
            <ButtonPopup v-for="button in buttons"  @click="confirmButtonClick(button)"
            :key="button.key" :disabled="!isButtonEnabled(button.key)" :classIcon="button.icon" :title="button.title" :subtitle="button.subtitle" />
        </div>
        <component v-else :is="activeComp.name"
            v-bind="activeComp.props"
            v-on="activeComp.listeners" />
    </WapModal>
</template>

<script>
import ButtonPopup from './ButtonPopup'
import BehalfBooking from './BehalfBooking'
import StatusBusyConfirm from './StatusBusyConfirm'
import StatusFreeConfirm from './StatusFreeConfirm'

let calendar_components = window.wappointmentExtends.filter('BackendCalendarComponents', {
      BehalfBooking,
      StatusFreeConfirm,
      StatusBusyConfirm,
      ButtonPopup
  })

export default {
    props:['displayTimezone', 'activeStaff', 'startTime', 'endTime', 'realEndTime', 'viewData', 'getThisWeekIntervals', 'momenttz'],
    mixins: window.wappointmentExtends.filter('PopupActionsMixin', []),
    components: calendar_components,
    data: () =>({
        activeCompName: false,
        componentsList: null,
    }),
    created(){
        this.setComponentList()
    },
    computed:{
        buttons(){
            return this.viewData.buttons
        },
        startTimeDisplay(){
            return this.formatTime(this.startTime)
        },
        endTimeDisplay(){
            return this.formatTime(this.endTime)
        },
        shortStDayDisplay(){
            return this.startTime.format(this.shortDayFormat+' '+this.viewData.time_format)
        },
        shortEdDayDisplay(){
            return this.endTime.format(this.shortDayFormat+' '+this.viewData.time_format)
        },
        startDayDisplay() {
            return this.startTime.format(this.viewData.date_format)
        },
        activeComp(){
            let name = this.activeCompName
            return this.componentsList.find(e => e.name == name)
        },

        selectionSingleDay(){
            return this.startTime.day() === this.endTime.day() && 
                this.startTime.month() === this.endTime.month() && 
                this.startTime.year() === this.endTime.year()
        },
        isAvailable(){
            return this.hasOpenedSlots && this.selectionWithinInterval
        },
        selectionWithinInterval(){
            if(this.hasOpenedSlots){
                for (const interval of this.getThisWeekIntervals.intervals) {
                    if(this.selectIsWithin(interval)){
                        return true
                    }
                }
            }
            
            return false
        },
        selectionXInterval(){
            if(this.hasOpenedSlots){
                for (const interval of this.getThisWeekIntervals.intervals) {
                    if(this.selectionTouchesInterval(interval)){
                        return true
                    }
                }
            }
            
            return false
        },

        canBook(){
            return this.selectionSingleDay
        },
        canFree(){
            return this.selectionSingleDay && !this.isAvailable
        },
        canBusy(){
            return this.hasOpenedSlots && this.selectionXInterval
        },
        hasOpenedSlots(){
            return this.getThisWeekIntervals!==0
        }
    },
    methods:{
        confirmButtonClick(button){
            if(this.isButtonEnabled(button.key)){
                this.activeCompName = button.component
            }
        },
        isButtonEnabled(buttonKey){
            return this['can'+this.ucFirst(buttonKey)]
        },

        setComponentList(){
            let default_object = {
                props: {
                    startTime: this.startTime,
                    endTime: this.endTime,
                    realEndTime: this.realEndTime,
                    timezone: this.displayTimezone,
                    activeStaff: this.activeStaff,
                    viewData: this.viewData,
                },
                listeners: {
                    confirmed: this.confirmedStatus,
                    cancelled: this.hideModal,
                    updateEndTime: this.updateEndTime,
                },
            }
            this.componentsList = this.buttons.map(e => Object.assign({name:e.component}, default_object))

        },
        updateEndTime(newEndTime){
            this.endTime = newEndTime
        },
        formatTime(myMoment, format = false){
            if(format === false) format = this.viewData.time_format
            return myMoment.format(format)
        },

        selectionTouchesInterval(interval){
            return this.selectWraps(interval) ||
                        this.selectIntersectsLeft(interval) ||
                        this.selectIntersectsRight(interval) ||
                        this.selectIsWithin(interval)
        },
        selectIsWithin(element){
            let selStart = this.momenttz.tz(this.startTime.format(), this.timezone)
            let selEnd = this.momenttz.tz(this.endTime.format(), this.timezone)
            return selStart.unix() >= element.start 
            && selEnd.unix() <= element.end
        },
        selectWraps(element){
            let selStart = this.momenttz.tz(this.startTime.format(), this.timezone)
            let selEnd = this.momenttz.tz(this.endTime.format(), this.timezone)
            return selStart.unix() <= element.start 
            && selEnd.unix() >= element.end
        },
        selectIntersectsLeft(element){
            return this.startTime.unix() < element.start 
            && this.endTime.unix() > element.start 
            && this.endTime.unix() <= element.end
        },
        selectIntersectsRight(element){
            return this.startTime.unix() >= element.start 
            && this.startTime.unix() < element.end 
            && this.endTime.unix() > element.end
        },
        hideModal(){
            this.$emit('hide')
        },
        confirmedStatus(){
            this.hideModal()
            this.$emit('refreshEvents')
        },
    }
}
</script>