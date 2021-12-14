<template>
    <div class="first-avail">
        <div v-if="hasIntervals" v-html="firstAvail"></div>
        <div v-else class="werror">
            {{ options.general.noappointments }}
        </div>
    </div>
</template>
<script>
import momenttz from '../appMoment'
export default {
    props:['initIntervalsCollection', 'duration', 'timeprops', 'options'],
    computed:{
        hasIntervals(){
            return this.initIntervalsCollection !== null && this.initIntervalsCollection.intervals.length>0
        },
        firstInterval(){
           for (const interval of this.initIntervalsCollection.intervals) {
               if(interval.end - interval.start >= this.duration){
                   return interval
               }
           }
        },
        startDate(){
            return momenttz.unix(this.firstInterval.start)
        },

        formattedStartDate(){
            return this.startDate.format(this.timeprops.fullDateFormat)
        },
        firstAvail(){
            return this.options.staff_selection.firstavail.replace('[formatted_date]', '<strong>'+this.formattedStartDate+'</strong>')
        }
    },
}
</script>
<style >
.first-avail {
    font-size: 12px;
}
 .first-avail .ddays  {
    min-width: 170px;
    font-size: 12px;
} 
.first-avail .ddays .wbtn{
    font-size: .7em;
}
.wap-front .first-avail .tt-here[data-tt]::before{
    bottom: 20px;
}
.wap-front .first-avail .tt-here[data-tt]::after{
    display:none;
}
.wap-front .werror{
    color:var(--wappo-error-tx) !important;
}
</style>