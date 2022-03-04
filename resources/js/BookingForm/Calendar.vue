<template>
    <div v-if="updatedIntervals !== false">
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
        updatedIntervals: false
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
                setInterval(this.refreshIntervals, 60 * 1000)
            }
            
            
        },
        
        refreshIntervals(){
            // min is now 
            let newStart = this.getMinStart().unix()
            this.updatedIntervals = false
            setTimeout(this.setNewValue.bind(null,newStart), 100);
        },
        setNewValue(newStart){
            let newIntervals = this.initIntervalsCollection
            console.log('newStart',newStart)
            newIntervals.intervals[0].start = newStart
            this.updatedIntervals = newIntervals
        },
        selectSlot(slot){
            let next = this.relations.next
            this.$emit('selectSlot', this.rescheduling ? 'RescheduleConfirm':next, {selectedSlot: slot})
        },
    }

}
</script>


