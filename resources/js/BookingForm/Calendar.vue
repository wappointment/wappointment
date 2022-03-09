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

export default {
    props: ['options','service','initIntervalsCollection', 'timeprops', 'staffs', 'duration', 'location', 'viewData','rescheduling', 'relations'],
    data: () => ({
        updatedIntervals: false,
        intervalId: false,
        started: false
    }),
    mixins: [Dates, DatesExtended],
    components: {
        CalendarMonth, CalendarWeek
    }, 
    mounted(){
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
            this.updatedIntervals = this.initIntervalsCollection
            //if today has slots we register an event
            if(this.isTSToday(this.updatedIntervals.intervals[0].start)){
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
            let newIntervals = this.initIntervalsCollection
            newIntervals = this.incrementStartingValue(newIntervals, newStart) //increment 
            newIntervals = this.cleanStartingFromEnd(newIntervals, newStart)
            
            this.updatedIntervals = newIntervals
        },

        cleanStartingFromEnd(newIntervals, newStart){
            let intervals = newIntervals.intervals
            for (let i = 0; i < intervals.length; i++) {
                const element = intervals[i]
                if(element.end < newStart){
                    intervals.splice(i, 1)
                }
            }
            newIntervals.intervals = intervals
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
                     while (intervals[0].start < this.now.unix()) { //standard increment by duration selected
                         intervals[0].start += this.duration *60
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


