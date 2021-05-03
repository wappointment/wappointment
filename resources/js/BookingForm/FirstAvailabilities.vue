<template>
    <div class="first-avail">
        <div class="d-flex" v-if="hasIntervals">
            <div>
                <div class="tt-here" :data-tt="getMonthYear" :title="getMonthYear">
                    <weekHeader :weekHeader="weekHeader"/>
                </div>
                <DaysOfWeek :idweek="selectedWeek" :week="reorganiseDays[selectedWeek]" :tooltip="getSlotTooltip" :selectedDay="selectedDay"
                    :demoSelected="demoSelected" :cachedSlots="cachedSlots" :isDemo="isDemo"/>  
            </div>
            <a href="javascript:;" @click.stop.self="changeWeek" class="ml-2 wbtn wbtn-secondary">></a>
        </div>
        <div v-else>
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
        }
    },
    methods:{
        autoRunOnMount(){
            this.findFirstMonthwithAvail()
            let hasAvailability = false
            let i = 0
            this.selectedWeek = 0
            while(hasAvailability === false && i < 10){
                 for (let j = 0; j < this.reorganiseDays[this.selectedWeek].length; j++) {
                    const daynumber = this.reorganiseDays[this.selectedWeek][j]
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
.wap-front .first-avail .tt-here[data-tt]::before{
    bottom:30px;
}
.wap-front .first-avail .tt-here[data-tt]::after{
    display:none;
}
</style>