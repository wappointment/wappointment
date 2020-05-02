<script>
import calendar from '../../Plugins/calendar-js'
import Dates from '../../Modules/Dates'
import momenttz from '../../appMoment'
import DaySlots from './DaySlots'
export default {
    props: ['options','service','initIntervalsCollection', 'timeprops', 'staffs','duration', 'viewData'],
    mixins: [Dates],
    components: {
        DaySlots,
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
        mounted: false,
        demoSelected: {
            week: false,
            day: false,
            init: false
        },
        disabledButtons: false,
        object_days: {}

    }),

    mounted(){
        this.time_format = this.timeprops.time_format
        this.currentTz = this.timeprops.currentTz
        this.intervalsCollection = this.initIntervalsCollection
        
        this.setMonth(this.todayYear, this.todayMonth - 1, false)
        this.resetIntervals()
       
        this.mounted = true
        let i = 0
        while(this.totalSlots == 0 && !this.isLastMonth && i < 40){
            this.changeMonth()
            i++
        }

        
        this.getLocalWeekDays()
        
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
            let localeweekdays = []
            for (let i = 0; i < weekDays.length; i++) {
                const element = weekDays[i]
                localeweekdays[i] = this.getLocaleDay(element)
            }
            
            return localeweekdays
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
        getMonthYear() {
            let objDate = new Date()
            objDate.setDate(1)
            objDate.setMonth(this.monthNumber)
            let month = objDate.toLocaleString(this.getBrowserLang(), { month: "long" })
            month = month[0].toUpperCase() + month.substring(1)
            return month +' '+ this.yearNumber
        },
        getBrowserLang(){
            return (navigator.languages && navigator.languages.length) ? navigator.languages[0] : navigator.userLanguage || navigator.language || navigator.browserLanguage || 'en-US'
        },
        getLocaleDay(dayname){
            for (const key in this.object_days) {
                if (this.object_days.hasOwnProperty(key)) {
                    const element = this.object_days[key];
                    if(dayname.toLowerCase() === element.en.toLowerCase()){
                        return element.locale
                    }
                }
            }
        },
        getLocalWeekDays(){

            let tomorrow = new Date()
            for (let i = 0; i < 7; i++) {
                this.object_days[i] = {
                    en:tomorrow.toLocaleDateString('en-US', { weekday: 'long'}), 
                    locale:new Intl.DateTimeFormat(this.getBrowserLang(), { weekday: 'long'}).format(tomorrow)
                }
                tomorrow.setDate(tomorrow.getDate() + 1)
            }

        },
        selectFirstDayAvail(){
            for (let i = 0; i < this.reorganiseDays.length; i++) {
                const week = this.reorganiseDays[i]
                for (let j = 0; j < week.length; j++) {
                    const day = week[j]
                    if(day>0){
                        let daySlots = this.hasAvailability(day)
                        if(daySlots !== undefined && daySlots > 0){
                            return this.selectDay(day, i)
                        }
                    }
                }
                
            }
  
        },
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
                setTimeout(this.setMonth.bind('', newMonth.year, newMonth.month), 100)
            }else{
                this.setMonth(newMonth.year, newMonth.month, false)
            }
        },
        nextMonth(){
            if(this.isLastMonth === true ) return false
            this.sideMonth = 'right'
            this.changeMonth(true, true)
        },
        prevMonth(){
            if(this.isCurrentMonth === true) return false
            this.sideMonth = 'left'
            this.changeMonth(false, true)
        },
        nowNextHour(){
            let now = this.now
            return now.add(1,'h').startOf('hour')
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
        realSlotDuration(){
            return (parseInt(this.duration) + parseInt(this.viewData.buffer_time)) *60
        },
        setIntervals(start, end){
            this.currentIntervals = this.intervalsCollection.get(start, end)
            this.totalSlots = this.currentIntervals.splits(this.realSlotDuration()).totalSlots()
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
            
            let dayIntervals = this.currentIntervals.get(start, until)
            return this.prepareDayInterval(dayIntervals, start,until)
        },
        prepareDayInterval(dayIntervals, start,until){
            return dayIntervals
        },
        initial(string){
            return string.toUpperCase().substring(0, 1)
        },
    }

}
</script>

<style >
.wap-front .btn-round {
    border-radius: 2em;
    width: 2.3em;
    height: 2.3em;
}

.wap-front .btn.btn-secondary.btn-round.btn-disabled, 
.wap-front .btn-round.btn-disabled:hover {
    background-color: transparent;
    border: none;
    box-shadow: none;
}

</style>

