<script>
import calendar from '../Plugins/calendar-js'
import Dates from '../Modules/Dates'
import momenttz from '../appMoment'
import DaySlots from './DaySlots'
import DaysOfWeek from './DaysOfWeek'
import WeekHeader from './WeekHeader'
import weekdaysLocale from '../Standalone/weekdaysLocale'
import monthLocale from '../Standalone/monthLocale'
import IsDemo from '../Mixins/IsDemo'
/**
 * TODO Review moment usage
 */
export default {
    props: ['options','initIntervalsCollection', 'timeprops', 'duration', 'viewData', 'staffs', 'location','service'],
    mixins: [Dates, IsDemo],
    components: {
        DaySlots, DaysOfWeek, WeekHeader
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
        sideMonth: 'right',
        slotsAnimation: 'slide-fade-sm',
        mounted: false,
        demoSelected: {
            week: false,
            day: false,
            init: false
        },
        object_days: {},
        weekHeader: []
    }),
    created(){
        this.intervalsCollection = this.filteredServices()
    },
    mounted(){
        this.time_format = this.timeprops.time_format
        this.currentTz = this.timeprops.currentTz
        
        
        this.setMonth(this.todayYear, this.todayMonth - 1, false)
        this.resetIntervals()
        
        this.mounted = true
        
        this.autoRunOnMount()
        this.object_days = weekdaysLocale()
        this.setWeekHeader()
    },
    updated: function () {
        if(this.isDemo && this.demoSelected.init === false && this.demoSelected.day !== false){
            this.$nextTick(function () {
                this.daySelected(this.demoSelected.day, this.demoSelected.week)
                this.demoSelected.init = true
            })
        }
    },

    computed: {   
        minTodayHour(){
            return parseInt(this.viewData.min_bookable)
        },
        isDemo(){
            return this.options.demoData !== undefined
        }, 
        now() {
            return momenttz().tz(this.currentTz)
        },
        
        realMonthNumber() { //month number go from 0 to 11 in momentjs
            return this.monthNumber + 1
        },
        firstDayMonth() {
             return momenttz().tz(this.currentTz).year(this.yearNumber).month(this.monthNumber).startOf('month')
        },
        lastDayMonth() {
            if(this.isCurrentMonth) {
                return this.now.clone().endOf('month')
            } else {
                return momenttz().tz(this.currentTz).year(this.yearNumber).month(this.monthNumber).endOf('month')
            }
        },
        todayIs() {
            return this.now.format()
        },
        isCurrentMonth() {
            return this.todayMonth === this.realMonthNumber && this.todayYear === this.yearNumber
        },
        isLastMonth(){
            if(this.lastAvailableSlot !== undefined) {
                let lavs = momenttz.unix(this.lastAvailableSlot).tz(this.currentTz)
                return this.monthNumber === lavs.month() && this.yearNumber === lavs.year()
            }
            
            return true
        },
        getMonthYear() {
            return monthLocale(this.monthNumber) +' '+ this.yearNumber
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

            if(this.currentMonth.firstWeekday === this.startDay - 1){ //exception week of 1 day
                newCalendar.push([0,0,0,0,0,0,1])
            }
            for (let weekIndex = 0; weekIndex < this.currentMonth.calendar.length; weekIndex++) {
                let week = this.currentMonth.calendar[weekIndex]
                let nextWeek = this.currentMonth.calendar[weekIndex+1]
                let endWeekDays = week.slice(0,this.startDay)
                let startWeekDays  = week.slice(this.startDay)
                 

                if(nextWeek!== undefined) {
                    if(endWeekDays[0] === 0){
                        for (const iterator of endWeekDays) {
                            if(iterator!== 0){
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
        
        lastAvailableSlot(){
            return this.intervalsCollection !== null && this.intervalsCollection.intervals.length > 0 ?this.intervalsCollection.intervals.slice(-1)[0].end:undefined
        },
        getTzString(){
            return (this.options!== undefined && this.options.selection.timezone!== undefined) ? this.options.selection.timezone: ''
        },
    },
    methods: {
        filteredServices(){
            //let serviceSelected = this.service.id
            let intervalsCollection = this.initIntervalsCollection
            //intervalsCollection.intervals = intervalsCollection.intervals.filter(interval => [undefined,serviceSelected].indexOf(interval.service)!==-1)
            // filter used within group
            return window.wappointmentExtends.filter('CalendarAbstractFilterIntervals', intervalsCollection, this.service, this.conditionMatches)
        },
        timezoneDisplay(timezoneString){
            return this.getTzString.replace('[timezone]', timezoneString + ' [' + this.now.format('Z') + ']')
        },
        autoRunOnMount(){
            this.findFirstMonthwithAvail()
        },
        findFirstMonthwithAvail(){
            let i = 0
            while(this.totalSlots == 0 && !this.isLastMonth && i < 40){
                this.changeMonth()
                i++
            }
        },
        setWeekHeader() {
            let endWeekDays = this.currentMonth.weekdays.slice(0,this.startDay)
            let startWeekDays  = this.currentMonth.weekdays.slice(this.startDay)
            let weekDays = startWeekDays.concat(endWeekDays)
            let localeweekdays = []
            for (let i = 0; i < weekDays.length; i++) {
                localeweekdays[i] = this.getLocaleDay(weekDays[i])
            }
            
            this.weekHeader = localeweekdays
        },

        
        getLocaleDay(dayname){
            for (const key in this.object_days) {
                if (this.object_days.hasOwnProperty(key)) {
                    const element = this.object_days[key]
                    if(dayname.toLowerCase() === element.en.toLowerCase()){
                        return element.locale
                    }
                }
            }
        },

        

        selectFirstDayAvail(){
            for (let i = 0; i < this.reorganiseDays.length; i++) {
                const week = this.reorganiseDays[i]
                for (const day of week) {
                    if(day > 0){
                        let daySlots = this.hasAvailability(day)
                        if(daySlots !== undefined && daySlots > 0){
                            return this.selectDay(day, i, false)
                        }
                    }
                }
                
            }
        },

        resetWeekSelection() {
            this.selectedWeek = false
            this.resetDaySelection()
            this.slotsAnimation = 'slide-fade-sm'
        },
        resetDaySelection() {
            this.availableIntervals = {}
            this.selectedDay = false
        },

        selectDay(daynumber, idweek, manual = true){
            
            let timeout = 100
            if(idweek === this.selectedWeek ) { // week didnt change, only the day
                this.slotsAnimation = manual? 'fade':'slide-fade-sm'
                this.resetDaySelection()
            }else {
                this.slotsAnimation = 'slide-fade-sm'
                timeout = 600
                this.resetWeekSelection()
            }
            if(this.cachedSlots[daynumber] < 1 || this.isPast(daynumber)){
                 return false
            }
            manual ? setTimeout(this.daySelected.bind('', daynumber, idweek), timeout): this.daySelected(daynumber, idweek)
        },

        selectSlot(slot){
            if(this.triggersDemoEvent('form')){
                return
            }
            
            this.$emit('selectSlot', slot)
        },

        daySelected(daynumber, idweek){
            this.selectedWeek = idweek
            this.selectedDay = daynumber
            this.availableIntervals = this.getDayIntervals(daynumber)
        },
        
        
        decrementMonth(){
            let monthNumber = this.monthNumber - 1
            let yearNumber = this.yearNumber
            if(monthNumber < 0) {
                monthNumber = 11
                yearNumber = this.yearNumber - 1
            }
            return {month:monthNumber, year: yearNumber}
        },

        incrementMonth(){
            let monthNumber = this.monthNumber + 1
            let yearNumber = this.yearNumber
            if(monthNumber > 11) {
                monthNumber = 0
                yearNumber = this.yearNumber + 1
            }
            return {month:monthNumber, year: yearNumber}
        },

        changeMonth(increment = true, animate = false){
            this.resetWeekSelection()
            let newMonth = increment ===false ? this.decrementMonth():this.incrementMonth()
            if(animate) {
                setTimeout(this.setMonth.bind('', newMonth.year, newMonth.month), 600)
            }else{
                this.setMonth(newMonth.year, newMonth.month, false)
            }
        },

        nextMonth(){
            if(this.isLastMonth === true ) return false
            this.sideMonth = 'left'
            this.changeMonth(true, true)
        },

        prevMonth(){
            if(this.isCurrentMonth === true) return false
            this.sideMonth = 'right'
            this.changeMonth(false, true)
        },
        
        nowNextHour(){
            return this.now.clone().add(this.minTodayHour,'h').startOf('hour')
        },
        
        resetIntervals(){
            if(this.intervalsCollection === null) return false
            this.cachedSlots = {}

            if(this.isCurrentMonth) {
                this.setIntervals(this.nowNextHour(), this.lastDayMonth)
            } else {
                this.setIntervals(this.firstDayMonth, this.lastDayMonth)
            }
        },
        
        /**
         * service slot duration in seconds
         */
        realSlotDuration(){
            return (parseInt(this.duration) + parseInt(this.viewData.buffer_time)) *60
        },

        setIntervals(start, end){
            this.currentIntervals = this.intervalsCollection.get(start, end)
            this.totalSlots = this.currentIntervals.splits(this.realSlotDuration()).totalSlots()

            this.cacheAvailability()
        },

        cacheAvailability(){
            //cache availability
            for (const idweek in this.reorganiseDays) {
                if (this.reorganiseDays.hasOwnProperty(idweek)) {
                    const week = this.reorganiseDays[idweek]
                    for (const idday in week) {
                        if (week.hasOwnProperty(idday)) {
                            const day = week[idday]
                            this.hasAvailability(day)
                        }
                    }
                }
            }
        },

        setMonth(yearNumber, monthNumber, delay=true){
            this.monthNumber = monthNumber 
            this.yearNumber = yearNumber
            this.selectedDay = false
            this.currentMonth = false
            if(delay) {
                setTimeout(this.monthSelected.bind('',yearNumber, monthNumber), 100)
            }else{
                this.monthSelected(yearNumber, monthNumber)
            }
        },

        monthSelected(yearNumber, monthNumber){
            this.currentMonth = calendar().of(yearNumber, monthNumber)
            this.resetIntervals()
            if(this.afterMonthSelected !== undefined) this.afterMonthSelected()
        },

        isPast(day) {
            return this.isCurrentMonth === true && this.todayDay > day
        },

        hasAvailability(daynumber){
            if(this.isPast(daynumber) || daynumber < 1) return 0
            if(this.cachedSlots[daynumber] !== undefined) return this.cachedSlots[daynumber]
    
            let dayIntervals = this.getDayIntervals(daynumber)

            this.cachedSlots[daynumber] = dayIntervals.splits(this.realSlotDuration()).totalSlots()
            if(this.isDemo && this.demoSelected.day === false && this.cachedSlots[daynumber] > 0){
                this.demoSelected.day = daynumber
                this.disabledButtons = true
            }
            return this.cachedSlots[daynumber]
        },
        
        dayWeekSelected(idweek) {
            return idweek === this.selectedWeek
        },
        getTodayStart(){
            let nowmin = momenttz.tz(this.now.clone(), this.currentTz).add(this.minTodayHour,'hours')
            let nowcopy = nowmin.clone().startOf('hour')
            
            if(this.currentTime() > nowcopy.unix()){
                let i = 0
                while (nowcopy.unix() < nowmin.unix() && i <10) {
                    nowcopy.add( 10, 'minutes')
                    i++
                }
            }
            return nowcopy
        },
        currentTime(){
            return Math.round(Date.now() / 1000)
        },
        getTodayInterval(){
            let start = this.getTodayStart()
    
            if(start.day() != this.now.day()){ //exception when today changes to tomorrow with the adition of min_bookable
                //that means that's the end of the day and there is nothing new
                start = momenttz.tz(this.now.clone(), this.currentTz)
            }
            return {start:start, end:start.clone().add(1, 'day').startOf('day')}
        },
        getNotTodayInterval(daynumber){
            let prefixDay = daynumber < 10 ?'0':''
            let prefixMonth = this.realMonthNumber < 10 ? '0':''
            let formattedDayString = this.yearNumber + '-' + prefixMonth + this.realMonthNumber + '-' + prefixDay + daynumber
            let start = momenttz.tz(formattedDayString, this.currentTz).startOf('day')

            return {
                start: start, 
                end: start.clone().add(1, 'day')
            }
        },
        getDayIntervals(daynumber){
            let intervalsObject = this.isCurrentMonth && daynumber === this.todayDay ? this.getTodayInterval():this.getNotTodayInterval(daynumber)
            
            return this.formatDayInterval(intervalsObject)
        },

        formatDayInterval(intervalsObject){

            let min_start = this.getTodayStart()
            if(min_start.unix() >= intervalsObject.end.unix()) {
                return this.currentIntervals.get(false) // we skip returnin an empty interval
            } else if(min_start.unix() > intervalsObject.start.unix()){
                intervalsObject.start = min_start.clone()
            }
            
            let dayIntervals = this.currentIntervals.get(intervalsObject.start, intervalsObject.end, this.service.id)
            return this.prepareDayInterval(dayIntervals, intervalsObject.start, intervalsObject.end)
        },

        /** used to filter more when overidding from an addon */
        prepareDayInterval(dayIntervals, start,until){
            return dayIntervals
        },

        getMomentObj() {
            return momenttz.tz(this.currentTz)
        },

    }

}
</script>

<style >

.wap-front .wbtn-round,
.wap-front .calendarMonth .ddays div.wbtn {
    border-radius: 50%;
    text-align: center;
    padding: 0;
    transition: all .3s ease-in-out;
    margin: 0 auto !important;
}

.wap-front .calendarMonth .ddays div.wbtn {
    font-size: .65em;
    max-width: 2.4em;
}

.wap-front.over280 .calendarMonth .ddays div.wbtn {
    font-size: .67em;
    max-width: 2.6em;
}
.wap-front.over320 .calendarMonth .ddays div.wbtn {
    font-size: .73em;
    max-width: 2.8em;
}
.wap-front.over360 .calendarMonth .ddays div.wbtn {
    font-size: .75em;
    max-width: 3em;
}

.wap-front .wbtn-top.wbtn-round {
    min-width: 36px;
    margin: 0 !important;
    font-size: .7em;
    max-width: 2.3em;
}
.wap-front .wbtn.wbtn-secondary.wbtn-round.wbtn-disabled, 
.wap-front .wbtn-round.wbtn-disabled:hover {
    background-color: transparent;
    border: none;
    box-shadow: none;
}

</style>

