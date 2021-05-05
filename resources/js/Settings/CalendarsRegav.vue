<template>
    <div>
        <div class="d-flex">
            <div v-for="(hours,day) in getUsedDays(calendar.regav)" class="dayLabel mr-2">
                <div :data-tt="getDayLabel(day)">{{ getDayLabel(day)[0] }}</div>
                <div class="d-flex">
                    <div class="available-slot mr-1 tt-below" :class="{'mx-1':idhx <1}" v-for="(hour, idhx) in hours" :data-tt="convertHours(hour)"></div>
                </div>
            </div>
        </div>
        <a v-if="canEdit" class="small" href="javascript:;" @click="$emit('edit', calendar)">edit</a>
    </div>
</template>

<script>

import Connections from '../RegularAvailability/Connections'
import ConnectionsMixins from '../RegularAvailability/ConnectionsMixins'
import abstractView from '../Views/Abstract'
export default {
    extends: abstractView,
    components: { Connections },
    mixins: [ConnectionsMixins],
    props: {
        calendar:{
            type: Object,
        },
        canEdit:{
            type:Boolean,
            default:true
        }
    },
    methods: {
        convertHours(hours){
            return this.convertHour(hours[0])+' - '+this.convertHour(hours[1])
        },
        convertHour(hour){
            let modulo = hour%60
            return Math.floor(hour  /60)+'h'+(modulo>0 ? modulo:'')
        },
        getDayLabel(daykey){
            return daykey
        },
        getUsedDays(regav){
            return Object.keys(regav).filter((e,idx) => idx!== 'precise' && e.length > 0)
            .reduce((obj, key) => {
                if(regav[key].length > 0){
                    obj[key]=regav[key]
                }
                return obj
            }, {});
        },
    }
}   
</script>
<style>

.dayLabel {
    text-transform: capitalize;
    text-align: center;
}
.available-slot{
    background-color: #ccc;
    width: .3rem;
    height: 1rem;
}
.available-slot:hover{
    background-color: #37792a;
}

.available-slot.tt-below[data-tt]::before{
    top:auto;
    bottom: -200%;
}
.available-slot.tt-below[data-tt]::after{
    top:auto;
    bottom: 20%;
}
</style>