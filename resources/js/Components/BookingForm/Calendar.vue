<template>
        <transition>
            <div v-if="mounted">
                <transition :name="slideMonth">
                    <div v-if="currentMonth" class="calendarMonth">
                        <div class="d-flex justify-content-between align-items-center">
                            <span @click="prevMonth" class="btn btn-link" :class="{'btn-disabled' : isCurrentMonth}" :disabled="isCurrentMonth"><</span> 
                            <div>{{ currentMonth.month + ' ' + currentMonth.year }}</div> 
                            <span @click="nextMonth" class="btn btn-link" :class="{'btn-disabled' : isLastMonth}" :disabled="isLastMonth" >></span>
                        </div>
                        <div class="d-flex justify-content-between ddays" >
                            <div v-for="(dayH, idy) in weekHeader">
                                {{ initial(dayH) }}
                            </div>
                        </div>
                        <div v-for="(week, idweek) in reorganiseDays">
                            <div class="d-flex justify-content-between ddays" >
                                <div v-for="(day, idday) in week" :class="{dayselected: isSelected(day)}">
                                    <template v-if="day > 0">
                                        <span v-if="noAvailability(day)"
                                            class="no-avail">
                                            {{ day }}
                                        </span>
                                        <span v-else
                                            :class="getClassAvailability(day,idweek)"
                                            :data-tt="hasTooltip(day)" @click="selectDay(day,idweek)">
                                            {{ day }}
                                        </span>
                                    </template>
                                    
                                </div>
                                
                            </div>
                            <transition name="slide-fade-sm">
                                <div class="slotsPane p-2" v-if="dayWeekSelected(idweek)">
                                    <div class="d-flex flex-wrap" >
                                        <template v-for="(segment, idseg) in availableIntervals.intervals">
                                            <BookingButton @click="selectSlot(slot)" 
                                            v-for="(slot, slid) in daySlots(segment)" 
                                            className="btn btn-primary btn-slot mr-2" :key="slot" :options="options" >
                                                {{ getMoment(slot, currentTz).format(time_format) }}
                                            </BookingButton>
                                        </template>
                                    </div>
                                    <small class="timezone">{{ timezoneDisplay(currentTz) }}</small>
                                </div>
                            </transition>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
</template>

<script>

import BookingButton from '../BookingButton'
import calendar from '../../Plugins/calendar-js'
import Dates from '../../Modules/Dates'
import momenttz from '../../appMoment'
export default {
    props: ['options','service','initIntervalsCollection', 'timeprops', 'staffs','duration', 'viewData','rescheduling'],
    mixins: [Dates],
    components: {
        BookingButton,
    }, 
    data: () => ({
        currentTz: '',
        time_format:'',
        currentMonth : null,
        yearNumber: 0,
        monthNumber: 0,
        dayNumber: 0,
        startDay: 1, //monday
        weekLength: 7,
        intervalsCollection: null,
        currentIntervals: null,
        totalSlots: null,
        cachedSlots: {},
        availableIntervals: {},
        selectedWeek: false,
        selectedDay: false,
        sideMonth: 'left',
        localmomenttz: momenttz,
        mounted: false,
        demoSelected: {
            week: false,
            day: false,
            init: false
        },
        disabledButtons: false

    }),

    mounted(){
        this.time_format = this.timeprops.time_format
        this.currentTz = this.timeprops.currentTz

        this.setMonth(this.todayYear, this.todayMonth - 1)
        this.intervalsCollection = this.initIntervalsCollection
        this.resetIntervals()
       
        this.mounted = true
        
        
    },
    updated: function () {
        if(this.isDemo && this.demoSelected.init == false && this.demoSelected.day !== false){
            this.$nextTick(function () {
                this.daySelected(this.demoSelected.day, this.demoSelected.week)
                this.demoSelected.init = true
            })
        }
    },

    computed: {       
        isDemo(){
            return this.options.demoData !== undefined
        }, 
        now() {
            return momenttz().tz(this.currentTz)
        },
        weekHeader() {
            let endWeekDays = this.currentMonth.weekdays.slice(0,this.startDay)
            let startWeekDays  = this.currentMonth.weekdays.slice(this.startDay);
            let orderedDays = []
            let startingDay = this.startDay
            let weekDays = startWeekDays.concat(endWeekDays)
            return weekDays
        },
        realMonthNumber() {
            return this.monthNumber + 1
        },
        firstDayMonth() {
             return momenttz().tz(this.currentTz).year(this.yearNumber).month(this.realMonthNumber).startOf('month')
        },
        lastDayMonth() {
            if(this.isCurrentMonth) {
                return this.now.clone().endOf('month')
            } else {
                return momenttz().tz(this.currentTz).year(this.yearNumber).month(this.realMonthNumber).endOf('month')
            }
        },
        todayIs() {
            return this.now.format()
        },
        isCurrentMonth() {
            return this.todayMonth === this.realMonthNumber && this.todayYear === this.yearNumber;
        },
        isLastMonth(){
            if(this.lastAvailableSlot !== undefined) {
                return this.monthNumber === momenttz.unix(this.lastAvailableSlot).tz(this.currentTz).month()
            }
            return true
        },
        
        
        todayYear() {
            return parseInt(this.now.format('YYYY'))
        },
        todayDay() {
            return parseInt(this.now.format('DD'))
        },
        todayMonth() {
            return parseInt(this.now.format('M'))
        },
        reorganiseDays() {
            let newCalendar = []
            for (let weekIndex = 0; weekIndex < this.currentMonth.calendar.length; weekIndex++) {
                let newWeek = []
                let week = this.currentMonth.calendar[weekIndex]
                let nextWeek = this.currentMonth.calendar[weekIndex+1]
                let endWeekDays = week.slice(0,this.startDay)
                let startWeekDays  = week.slice(this.startDay)
                 

                if(nextWeek!== undefined) {
                    if(endWeekDays[0] === 0){
                        for (let index = 0; index < endWeekDays.length; index++) {
                            if(endWeekDays[index]!== 0){
                                //insert new week
                                while (endWeekDays.length < this.weekLength) {
                                    endWeekDays.splice(0,0,0)
                                }
                                newCalendar.push(endWeekDays)
                                break;
                            }
                        }
                    }
                    let nendWeekDays = nextWeek.slice(0,this.startDay)
                    let nstartWeekDays  = nextWeek.slice(this.startDay)
                    week = startWeekDays.concat(nendWeekDays)
                }else{
                    week = startWeekDays
                    while (week.length < this.weekLength) {
                        week.push(0)
                    }
                }


                newCalendar.push(week)
                
            }

            return newCalendar
        },
        getSlotTooltip(){
            return (this.options!== undefined && this.options.selection.title!== undefined) ? this.options.selection.title: ''
        },
        getTzString(){
            return (this.options!== undefined && this.options.selection.timezone!== undefined) ? this.options.selection.timezone: ''
        },
        lastAvailableSlot(){
           if(this.intervalsCollection !== null){
                return this.intervalsCollection.intervals.slice(-1)[0].end
           }
           return undefined
        },
        slideMonth(){
            return 'slide-fade-side-sm-' + this.sideMonth
        },
    },
    methods: {

        getClassAvailability(day, idweek){
            if(this.isDemo && this.demoSelected.day == day){
                this.demoSelected.week = idweek
            }
            return {
            'no-avail': this.noAvailability(day),
            'avail': this.hasAvailability(day),
            'last-avail': this.lastAvailability(day),
            'few-avail': this.fewAvailability(day),
            'enough-avail': this.enoughAvailability(day),
            'plenty-avail': this.plentyAvailability(day),
            'hover':(this.isDemo && this.isSelected(day))
            }
        },
        timezoneDisplay(timezoneString){
            return this.getTzString.replace('[timezone]', timezoneString + ' [' + this.now.format('Z') + ']')
        },
        hasTooltip(daynumber){
            let avail = this.hasAvailability(daynumber)
            return avail > 0 ?  this.getSlotTooltip.replace('[total_slots]', avail) : false
        },
        resetWeekSelection() {
            this.selectedWeek = false
            this.availableIntervals = {}
            this.selectedDay = false
            
        },
        selectDay(daynumber, idweek){
            this.resetWeekSelection()
            if(this.cachedSlots[daynumber] < 1 || this.isPast(daynumber)){
                 return false
            }
            setTimeout(this.daySelected.bind('', daynumber, idweek), 100)
        },
        selectSlot(slot){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'form')
              return
            } 
            
            this.$emit('selectSlot', this.rescheduling?'RescheduleConfirm':'BookingFormInputs', {selectedSlot: slot})

        },
        daySelected(daynumber, idweek){
            this.selectedWeek = idweek
            this.selectedDay = daynumber
            this.availableIntervals = this.getDayIntervals(daynumber)
        },
        
        prevMonth(){
            this.resetWeekSelection()
            if(this.isCurrentMonth === true) return false
            let monthNumber = this.monthNumber - 1
            let yearNumber = this.yearNumber
            if(monthNumber < 0) {
                monthNumber = 11
                yearNumber = this.yearNumber - 1
            }
            
            this.sideMonth = 'left'
            
            setTimeout(this.setMonth.bind('', yearNumber, monthNumber), 100)
            //this.setMonth(yearNumber, monthNumber)
        },

        nextMonth(){
            if(this.isLastMonth === true ) return false
            this.resetWeekSelection()
            let monthNumber = this.monthNumber + 1
            let yearNumber = this.yearNumber
            if(monthNumber > 11) {
                monthNumber = 0
                yearNumber = this.yearNumber + 1
            }
            
            this.sideMonth = 'right'
            
            setTimeout(this.setMonth.bind('', yearNumber, monthNumber), 100)
            //this.setMonth(yearNumber, monthNumber)
        },

        resetIntervals(){
            if(this.intervalsCollection === null) return false
            this.cachedSlots = {}
            if(this.isCurrentMonth) {
                this.setIntervals(this.now, this.lastDayMonth)
            } else {
                this.setIntervals(this.firstDayMonth, this.lastDayMonth)
            }
        },
        setIntervals(start, end){

            this.currentIntervals = this.intervalsCollection.get(start, end)
            this.totalSlots = this.currentIntervals.splits(parseInt(this.duration)*60).totalSlots()
        },
        setMonth(yearNumber, monthNumber){
            this.monthNumber = monthNumber 
            this.yearNumber = yearNumber
            this.selectedDay = false
            this.currentMonth = false
            setTimeout(this.monthSelected.bind('',yearNumber, monthNumber), 100)
/*             this.currentMonth = calendar().of(yearNumber, monthNumber)
            this.resetIntervals() */

        },
        monthSelected(yearNumber, monthNumber){
            this.currentMonth = calendar().of(yearNumber, monthNumber)
            this.resetIntervals()
        },

        isPast(day) {
            return this.isCurrentMonth === true && this.todayDay > day
        },
        hasAvailability(daynumber){
            if(this.isPast(daynumber)) return 0
            if(this.cachedSlots[daynumber] !== undefined) return this.cachedSlots[daynumber]

            let dayIntervals = this.getDayIntervals(daynumber)
            
            this.cachedSlots[daynumber] = dayIntervals.splits(parseInt(this.duration)*60).totalSlots()
            if(this.isDemo && this.demoSelected.day == false && this.cachedSlots[daynumber] > 0){
                
                this.demoSelected.day = daynumber
                this.disabledButtons = true
            }
            return this.cachedSlots[daynumber]
        },
        
        isSelected(day){
            return this.selectedDay !== false && this.selectedDay == day
        },
        noAvailability(day){
            return this.hasAvailability(day) < 1
        },
        lastAvailability(day){
            return this.hasAvailability(day) === 1
        },
        fewAvailability(day){
            let avail = this.hasAvailability(day)
            return avail > 1 && avail <= 3
        },
        enoughAvailability(day){
            let avail = this.hasAvailability(day)
            return avail > 3 && avail <= 10
        },
        plentyAvailability(day){
            let avail = this.hasAvailability(day)
            return avail > 10
        },
       
        daySlots(segment) {
            let end = segment.end - this.duration * 60
            let allslots = []
            while (end >= segment.start) {
                allslots.push(end)
                end -= this.duration * 60
            }
            allslots.reverse()
            return allslots
        },
        
        dayWeekSelected(idweek) {
            return idweek === this.selectedWeek
        },
        
        getDayIntervals(daynumber){
            let start = null
            let today = false
            let until = null
            if(this.isCurrentMonth && daynumber === this.todayDay) {
                today = true
                start = momenttz.tz(this.now, this.currentTz).add(parseInt(this.viewData.min_bookable),'hours')
                until = start.clone().add(1, 'day').startOf('day')
                //console.log('Today is ', this.todayDay, start.format())
                //let dayIntervals = this.intervalsCollection.get(start, start.clone().endOf('day'), true)
                //console.log(start.format(), start.clone().endOf('day').format(),dayIntervals)
            }else {
                let prefixDay = ''
                let prefixMonth = ''
                if( daynumber < 10 ) prefixDay = '0' //otherwise invalid moment
                if( this.realMonthNumber < 10 ) prefixMonth = '0'
                //console.log(this.yearNumber+'-'+prefixMonth+this.realMonthNumber+'-'+prefixDay+daynumber)
                start = momenttz.tz(this.yearNumber + '-' + prefixMonth + this.realMonthNumber + '-' + prefixDay+daynumber, this.currentTz).startOf('day')
                until = start.clone().add(1, 'day')
            }
            
            let dayIntervals = this.intervalsCollection.get(start, until)
            return this.prepareDayInterval(dayIntervals, start,until)
        },
        prepareDayInterval(dayIntervals, start,until){
            return dayIntervals
        },
        initial(string){
            return string.substring(0, 1)
        },
    }

}
</script>
<style>
.btn.btn-slot {
    min-width: 29%;
}
</style>

