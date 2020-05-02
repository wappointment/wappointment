<template>
    <div @mouseover="showText=true" 
             @mouseout="showText=false" class="slots-panel" >
        <small class="timezone" :class="[showText?'show-tz':'hide-tz']">{{ timezoneDisplay(currentTz) }}</small>
        <div class="d-flex" v-if="ready">
            <div v-for="(slots, part) in dayParts" :class="'hello '+getSectionClass">
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
        dayParts: {},
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
        nowUnix(){
            return Date.now() /1000
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
                this.insertSlotToDayPart(slotStart, 'afternoon')
            }else if(hour >= 18 && hour <= 23 ){
                this.insertSlotToDayPart(slotStart, 'evening')
            }else if(hour >=0){
                this.insertSlotToDayPart(slotStart, 'morning')
            }
        },

        insertSlotToDayPart(slotStart, section){
            if(this.dayParts[section] === undefined ) this.dayParts[section] = []
            if(this.dayParts[section].indexOf(slotStart) === -1) this.dayParts[section].push(slotStart)
        },
        timezoneDisplay(timezoneString){
            return this.getTzString.replace('[timezone]', timezoneString + ' [' + this.now.format('Z') + ']')
        },

    },
    computed: {
        getTzString(){
            return (this.options!== undefined && this.options.selection.timezone!== undefined) ? this.options.selection.timezone: ''
        },

        getSectionClass(){
            return 'd-section ds-'+ Object.keys(this.dayParts).length
        }
    },


}
</script>
<style>
.slots-panel{
    position:relative;
    overflow: hidden;
}
.d-section {
    margin: 0 .2em;
}
.ds-1{
    width: 100%;
}
.ds-2{
    width: 48%;
}
.ds-3{
    width: 31%;
}
.btn.btn-slot {
    width: 100%;
    overflow: hidden;
}
.ds-1 .btn.btn-slot {
    width: 31%;
}

.day-part{
    display:block;
}

.timezone{
    transition: all .3s ease-in-out;
    position: absolute;
    left: 0;
    width: 100%;
    padding-bottom: .3em;
}
.show-tz{
    opacity: 1;
    top: 0 !important;
}
.hide-tz{
    opacity: 0;
    top: -45px;
}
</style>

