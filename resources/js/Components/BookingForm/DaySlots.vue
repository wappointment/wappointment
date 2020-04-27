<template>
    <div @mouseover="showText=true" 
             @mouseout="showText=false" >
        <small class="timezone" :class="[showText?'show-section':'hide-section']">{{ timezoneDisplay(currentTz) }}</small>
        <div class="d-flex" v-if="ready">
            <div v-for="(slots, part) in dayParts" class="day-section">
                <small class="day-part">{{getLabel(part)}}</small>
                <BookingButton @click="$emit('selected', slot)" 
                v-for="(slot, slid) in slots" 
                className="btn btn-primary btn-slot" :key="slot" :options="options" >
                    {{ getMoment(slot, currentTz).format(time_format) }}
                </BookingButton>
            </div>

        </div>
    </div>
</template>

<script>
import BookingButton from '../BookingButton'
import Dates from '../../Modules/Dates'
export default {
    mixins: [Dates],
    props: {
        intervals: {
            type: Array
        },
        duration:{
            type: Number
        },
        currentTz: {
            type: String
        },
        time_format: {
            type: String
        },
        options:{
            type: Object
        },
        now:{
            type: Object
        }
    },
    data: () => ({
        ready: false,
        dayParts: {
            morning:[],
            afternoon: [],
            evening:[],
        },
        showText: false
    }),
    components: {
        BookingButton,
    }, 
    created(){
        //separate the slots in morning, afternoon and evening
        this.convertIntervalsToSlots()
        this.ready = true
    },
    
    methods: {
        getLabel(section){
            return this.options.selection[section] !== undefined ? this.options.selection[section]:section
        },
        convertIntervalsToSlots(){
            for (let i = 0; i < this.intervals.length; i++) {
                const segment = this.intervals[i]
                let end = segment.end - this.duration
                let slotStart = segment.start
                let allslots = []
                while (end >= slotStart) {
                    this.identifySlot(slotStart)
                    slotStart += this.duration
                }
            
            }
        },
        identifySlot(slotStart){
            let hour = this.getMoment(slotStart, this.currentTz).hour()
            if(hour >= 12 && hour < 18){
                this.dayParts.afternoon.push(slotStart)
            }else if(hour >= 18 && hour <= 23 ){
                this.dayParts.evening.push(slotStart)
            }else if(hour >=0){
                this.dayParts.morning.push(slotStart)
            }
        },
        timezoneDisplay(timezoneString){
            return this.getTzString.replace('[timezone]', timezoneString + ' [' + this.now.format('Z') + ']')
        },

    },
    computed: {
        getTzString(){
            return (this.options!== undefined && this.options.selection.timezone!== undefined) ? this.options.selection.timezone: ''
        },
    },


}
</script>
<style>
.btn.btn-slot {
    width: 100%;
}
.day-section{
    width: 32%;
    margin: 0 .2em;
}
.show-section{
    opacity: 1;
}
.hide-section{
    opacity: 0;
}
.timezone{
    transition: all .3s ease-in-out;
}
</style>

