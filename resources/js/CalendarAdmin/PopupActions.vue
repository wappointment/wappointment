
<template>
    <WapModal :show="true" @hide="hideModal" noscroll>
        <h4 slot="title" class="modal-title">{{ get_i18n('calendar_popup', 'calendar') }}</h4>
        <h3 class="mb-4" v-if="selectionSingleDay"> {{ startDayDisplay }} - 
            <span class="text-muted">{{ sprintf_i18n('calendar_popup_from_until', 'calendar', [startTimeDisplay,endTimeDisplay]) }}</span>
            <span class="small text-muted" v-if="viewData.buffer_time > 0">{{ sprintf_i18n('calendar_popup_includes', 'calendar', viewData.buffer_time) }}</span>
        </h3>
        <h3 class="mb-4" v-else> {{ shortStDayDisplay }} - {{ shortEdDayDisplay }}</h3>
        <div class="d-flex flex-column flex-md-row justify-content-between" v-if="!selectedChoice">
            <ButtonPopup v-for="button in buttons"  @click="showRightSection(button)"
            :key="button.key" :disabled="button.disabled" :classIcon="button.icon" :title="button.title" :subtitle="button.subtitle" />
        </div>
        <div v-else>
            <component :is="activeComp.name"
            v-bind="activeComp.props"
            v-on="activeComp.listeners"
            />
        </div>
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
    components:calendar_components,
    data: () =>({
        selectedChoice: false,
        componentsList: null,
    }),
    created(){
        this.setComponentList()
    },
    computed:{
        buttons(){
            return window.wappointmentExtends.filter('PopupButtons', [
                {
                    key:'book',
                    title: this.get_i18n('calendar_popup_1', 'calendar'),
                    subtitle: this.get_i18n('calendar_popup_1_sub', 'calendar'),
                    disabled: !this.selectionSingleDay,
                    icon: 'dashicons-admin-users',
                    confirm: 'confirmNewBooking', 
                    shows: 'BehalfBooking',
                },
                {
                    key:'open',
                    title: this.get_i18n('calendar_popup_2', 'calendar'),
                    subtitle: this.get_i18n('calendar_popup_2_sub', 'calendar'),
                    disabled: !this.selectionSingleDay || this.isAvailable,
                    icon: 'dashicons-unlock txt blue',
                    confirm: 'confirmFree', 
                    shows: 'StatusFreeConfirm',
                },
                {
                    key:'block',
                    title: this.get_i18n('calendar_popup_3', 'calendar'),
                    subtitle: this.get_i18n('calendar_popup_3_sub', 'calendar'),
                    disabled: this.isBusy,
                    icon: 'dashicons-lock txt red',
                    confirm: 'confirmBusy', 
                    shows: 'StatusBusyConfirm',
                }
            ])
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
        activeCompName(){
            return this.selectedChoice
        },

        selectionSingleDay(){
            return (this.startTime.day() === this.endTime.day() && 
                this.startTime.month() === this.endTime.month() && 
                this.startTime.year() === this.endTime.year())
        },
        isAvailable(){
            if(this.getThisWeekIntervals!==0) {
                for (const element of this.getThisWeekIntervals.intervals) {
                    if(this.selectIsWithin(element)){
                        return true
                    }
                }
            }
            return false
        },


        isBusy(){
            if(this.getThisWeekIntervals!==0) {
                for (const element of this.getThisWeekIntervals.intervals) {
                    if(
                        this.selectWraps(element) ||
                        this.selectIntersectsLeft(element) ||
                        this.selectIntersectsRight(element) ||
                        this.selectIsWithin(element)
                        ){
                            return false
                        }
                }
            }

            return true
        },
    },
    methods:{
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
            this.componentsList = window.wappointmentExtends.filter('PopupActionsComponents', [
                Object.assign({name: 'BehalfBooking'}, default_object),
                Object.assign({name: 'StatusFreeConfirm'}, default_object),
                Object.assign({name: 'StatusBusyConfirm'}, default_object),
            ])

        },
        updateEndTime(newEndTime){
            this.endTime = newEndTime
        },
        showRightSection(button){
            this[button.confirm](button)
        },
        formatTime(myMoment, format = false){
            if(format === false) format = this.viewData.time_format
            return myMoment.format(format)
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
        confirmFree(button){
            if(!this.selectionSingleDay || this.isAvailable) return
            if(this.selectionSingleDay){
                this.selectedChoice = button.shows
            }
        },
        confirmBusy(button){
            if(this.isBusy) return
            this.selectedChoice = button.shows
        },
        confirmNewBooking(button){
            if(this.selectionSingleDay){
                this.selectedChoice = button.shows
            }
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