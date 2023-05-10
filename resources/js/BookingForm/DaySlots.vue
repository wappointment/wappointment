<template>
    <div class="slots-panel">
        <div class="d-flex" v-if="ready">
            <div v-for="(slots, part) in dayParts" :class="'hello '+getSectionClass">
                <small class="day-part">{{getLabel(part)}}</small>
                <template v-for="(slot, slid) in slots" >
                <BookingButton @click="$emit('selected', slot)" 
                className="wbtn wbtn-primary wbtn-slot" :key="'button-'+slot.start" :options="options" >
                    <span>{{ getDateTime().fromSeconds(slot.start).setLocale(browserLocale).toLocaleString(timeFormatLuxon) }}</span>
                    <span v-if="slot.left" class="sleft">{{options.selection.slots_left.replace('[slots_left]', slot.left) }}</span>
                </BookingButton>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import BookingButton from './BookingButton'
import Dates from '../Modules/Dates'
import { DateTime } from "luxon";
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
        },
        viewData:{
            type: Object
        },
    },
    data: () => ({
        ready: false,
        dayParts: {},
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
        getDateTime(){
            return DateTime
        },
        getLabel(section){
            return this.options.selection[section] !== undefined ? this.options.selection[section]:section
        },
        nowUnix(){
            return Date.now() /1000
        },
        convertIntervalsToSlots(){
            for (const segment of this.intervals) {
                if(segment.left !== undefined){
                    this.identifySlot(segment)
                    continue;
                }
                let end = segment.end - this.duration
                let slotStart = segment.start
                while (end >= slotStart) {
                    this.identifySlot({start:slotStart})
                    slotStart += this.incrementType()
                }
            }
        },
        incrementType(){
            return this.viewData.more_st ? this.viewData.starting_each*60:this.duration
        },
        identifySlot(segment){
            let hour = DateTime.fromSeconds(segment.start).toFormat("H")

            if(hour >= 12 && hour < 18){
                this.insertSlotToDayPart(segment, 'afternoon')
            }else if(hour >= 18 && hour <= 23 ){
                this.insertSlotToDayPart(segment, 'evening')
            }else if(hour >=0){
                this.insertSlotToDayPart(segment, 'morning')
            }
        },

        insertSlotToDayPart(segment, section){
            if(this.dayParts[section] === undefined ) {
                this.dayParts[section] = []
            }
            this.dayParts[section].push(segment)
        },
        

    },
    computed: {
        browserLocale(){
            return (navigator.language !== null && navigator.language !== undefined) ? navigator.language : 'en-US'
        },
        timeFormatLuxon(){
            return DateTime.TIME_SIMPLE
        },
        getSectionClass(){
            return 'd-section ds-'+ Object.keys(this.dayParts).length
        }
    },


}
</script>
<style>
.slots-panel{
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
.wbtn span{
    display:block;
}
.wbtn .sleft {
    color: #f2f2f2;
    font-size: .7em;
}
.wbtn.wbtn-slot {
    width: 100%;
    overflow: hidden;
}
.wbtn.wbtn-slot,
.wbtn.wbtn-slot:not(:disabled):not(.disabled):active, 
.wbtn.wbtn-slot:not(:disabled):not(.disabled).active{
    margin: 0.2em;
}

.ds-1 .wbtn.wbtn-slot {
    width: 31%;
}

.day-part{
    display: block;
    text-align: center;
    font-size: .75em;
    min-height: 28px;
}

.timezone{
    width: 100%;
}

</style>

