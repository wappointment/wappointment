<template>
    <div class="first-avail">
        <div v-if="hasIntervals" class="d-flex" >
            <div>
                <div class="tt-here" :data-tt="getMonthYear" :title="getMonthYear">
                    <weekHeader :weekHeader="weekHeader"/>
                </div>
                <DaysOfWeek :idweek="selectedWeek" :week="reorganiseDays[selectedWeek]" :tooltip="tooltipText" :selectedDay="selectedDay"
                    :demoSelected="demoSelected" :cachedSlots="cachedSlots" :isDemo="isDemo"/>  
            </div>
            <a href="javascript:;" @click.stop.self="changeWeek" class="ml-2 wbtn wbtn-secondary">></a>
        </div>
        <div v-else class="werror">
            {{ options.general.noappointments }}
        </div>
    </div>
</template>
<script>

import CalendarAbstract from './CalendarAbstract'
export default {
    extends: CalendarAbstract,
    computed:{
        hasIntervals(){
            return this.currentIntervals !== null && this.currentIntervals.intervals.length>0
        },
        tooltipText(){
            return this.getSlotTooltip.replace('[total_slots]','')
        }
    },
    updated: function () {
        if(this.isDemo && this.demoSelected.init == false && this.demoSelected.day !== false){
            this.$nextTick(function () {
                this.selectedDay = false
                this.demoSelected.init = true
            })
        }
    },

    methods:{
        autoRunOnMount(){
            this.findFirstMonthwithAvail()
            let hasAvailability = false
            let i = 0
            this.selectedWeek = 0
            while(hasAvailability === false && i < 10){
                for (const daynumber of this.reorganiseDays[this.selectedWeek]) {
                    if(this.cachedSlots[daynumber]>0){
                        hasAvailability= true
                    }
                }
                if(!hasAvailability){
                    this.changeWeek()
                }
                 i++
            }

        },

        changeWeek(increment = true){
            let newWeek = increment ===false ? this.selectedWeek -1:this.selectedWeek +1
            this.setWeek(newWeek)
        },
        setWeek(newWeek){
            this.selectedDay = false
            if(newWeek < 0 ){
                this.prevMonth()
                this.selectedWeek = this.reorganiseDays.length -1
            }else if(newWeek > this.reorganiseDays.length -1  ){
                this.nextMonth()
                this.selectedWeek = 0
            } else {
               this.selectedWeek = newWeek  
            }

        },

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