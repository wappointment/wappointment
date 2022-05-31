<template>
    <div v-if="updatedIntervals !== false && started">
        <CalendarWeek v-if="isWeekView" @selectSlot="selectSlot" :options="options" :service="service" :initIntervalsCollection="updatedIntervals"
                    :timeprops="timeprops" :staffs="staffs" :duration="duration" :location="location" :viewData="viewData"  />
        <CalendarMonth v-else @selectSlot="selectSlot" :options="options" :service="service" :initIntervalsCollection="updatedIntervals"
                    :timeprops="timeprops" :staffs="staffs" :duration="duration" :location="location" :viewData="viewData"  />
    </div>
</template>

<script>

import CalendarMonth from './CalendarMonth'
import CalendarWeek from './CalendarWeek'
import Dates from '../Modules/Dates'
import DatesExtended from '../Modules/DatesExtended'
import IncrementTime from './IncrementTime'

export default {
    props: ['options','service','initIntervalsCollection', 'timeprops', 'staffs', 'duration', 'location', 'viewData','rescheduling', 'relations'],
    data: () => ({
        updatedIntervals: false,
        intervalId: false,
        started: false,
        cleanedIntervals: false
    }),
    mixins: [Dates, DatesExtended, IncrementTime],
    components: {
        CalendarMonth, CalendarWeek
    }, 
    mounted(){
         this.cleanedIntervals = this.cleanStartingFromEnd(this.initIntervalsCollection, this.getMinStart().unix())
        // clear today's intervals as they expire with time
        this.setAutoRefreshIntervals()
    },
    computed: {
        isWeekView(){
            return [undefined,false].indexOf(this.options.selection.check_viewweek) === -1
        },
    },
    methods: {
        setAutoRefreshIntervals(){
            this.updatedIntervals = this.cleanedIntervals
            //if today has slots we register an event
            if(this.updatedIntervals.intervals[0] !== undefined && this.isTSToday(this.updatedIntervals.intervals[0].start)){
                this.refreshIntervals()
                setTimeout(this.initTodayInterval,  100)
                this.intervalId = setInterval(this.refreshIntervals, 60 * 1000)
            }else{
                this.started = true
            }
        },
        
        /**
         * avoid screen flashing on first load
         */
        initTodayInterval(){
            this.refreshIntervals()
            this.started = true
        },
        
        refreshIntervals(){
            // min is now 
            let newStart = this.getMinStart().unix()
            this.updatedIntervals = false
            setTimeout(this.setNewValue.bind(null,newStart), 50)
        },
        setNewValue(newStart){
            let newIntervals = this.cleanedIntervals
            
            newIntervals = this.incrementStartingValue(newIntervals, newStart) //increment 
            newIntervals = this.cleanStartingFromEnd(newIntervals, newStart)
            
            this.updatedIntervals = newIntervals
        },

        cleanStartingFromEnd(newIntervals, newStart){
            let intervals = newIntervals.intervals
            let validIntervals = []
            for (const element of intervals) {
                if(element.end > newStart){
                    validIntervals.push(element)
                }
            }
            newIntervals.intervals = validIntervals
            return newIntervals
        },
        /**
         * increment starting value for today's date until it's an acceptable value
         */
        incrementStartingValue(newIntervals, newStart){
            let intervals = newIntervals.intervals
            if(intervals[0].start < newStart){
                if(this.viewData.availability_fluid){
                    intervals[0].start = newStart //we get automatically the first value with 5 min increments
                }else{
                    let min_unix_time = this.now.unix() + (this.minTodayHour * 3600) + (this.minTodayMin * 60)
                     while (intervals[0].start < min_unix_time) { //standard increment by duration selected
                         intervals[0].start += this.incrementTime()
                    }
                }
            }
            newIntervals.intervals = intervals
            return newIntervals
        },
        
        selectSlot(slot){
            let next = this.relations.next
            if(this.intervalId){
                clearInterval(this.intervalId)
            }
            this.$emit('selectSlot', this.rescheduling ? 'RescheduleConfirm':next, {selectedSlot: slot})
        },
    }

}
</script>


