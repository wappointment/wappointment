<template>
    <div class="slots-panel">
        <div class="d-flex" v-if="ready">
            <div v-for="(slots, part) in dayParts" :class="'hello '+getSectionClass">
                <small class="day-part">{{getLabel(part)}}</small>
                <BookingButton @click="$emit('selected', slot)" 
                v-for="(slot, slid) in slots" 
                className="wbtn wbtn-primary wbtn-slot" :key="slot" :options="options" >
                    {{ getMoment(slot, currentTz).format(time_format) }}
                </BookingButton>
            </div>
        </div>
    </div>
</template>

<script>
import BookingButton from './BookingButton'
import Dates from '../Modules/Dates'
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
        

    },
    computed: {

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
}

.timezone{
    width: 100%;
}

</style>

