<template>
    <div v-if="currentMonth" class="calendarMonth">
        <div class="d-flex justify-content-between align-items-center">
            <span @click="prevWeek" class="wbtn-secondary wbtn wbtn-round wbtn-top sq" role="button" 
            :class="{'wbtn-disabled' : isCurrentWeek}" :disabled="isCurrentWeek"><span><</span></span> 
            <div>{{ getMonthYear}}</div> 
            <span @click="nextWeek" class="wbtn-secondary wbtn wbtn-round wbtn-top sq" role="button" 
            :class="{'wbtn-disabled' : isLastWeek}" :disabled="isLastWeek" ><span>></span></span>
        </div>
        <weekHeader :weekHeader="weekHeader"/>
        <transition :name="'slide-fade-side-sm-' + sideWeek">
            <div v-if="!changingWeek" >
                <DaysOfWeek :idweek="selectedWeek" :week="reorganiseDays[selectedWeek]" :tooltip="getSlotTooltip" :selectedDay="selectedDay"
                :demoSelected="demoSelected" :cachedSlots="cachedSlots" :isDemo="isDemo" @selectDay="selectDay"/>

                <transition :name="slotsAnimation">
                    <div v-if="dayWeekSelected(selectedWeek) && selectedDay">
                        <div class="timezone">{{ timezoneDisplay(currentTz) }}</div>
                        <div class="slotsPane p-2" >
                            <DaySlots 
                            :intervals="availableIntervals.intervals" 
                            :duration="realSlotDuration()" 
                            :currentTz="currentTz"
                            :time_format="time_format"
                            :options="options"
                            :now="now"
                            :viewData="viewData"
                            @selected="selectSlot" />
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </div>
</template>
<script>

import CalendarAbstract from './CalendarAbstract'
export default {
    extends: CalendarAbstract,
    data: () => ({
        prevMth: false,
        sideWeek: 'left',
        changingWeek: false
    }),
    methods:{
        autoRunOnMount(){
            this.findFirstMonthwithAvail()
            let i= 0
            while(this.availableIntervals.intervals === undefined && i < 10){
                this.changeWeek(true, true)
                i++
            }
            
        },
         changeWeek(increment = true, auto = false){
            this.sideWeek = increment? 'left': 'right'
            this.changingWeek = true
            let newWeek = increment ===false ? this.selectedWeek -1:this.selectedWeek +1
  
            auto ? this.setWeek(newWeek):setTimeout(this.setWeek.bind('',newWeek ), 600)
        },
        setWeek(newWeek){
            this.selectedDay = false
            this.changingWeek = false
            if(newWeek < 0 ){
                this.prevMonth()
                this.prevMth = true
            }else if(newWeek > this.reorganiseDays.length -1  ){
                this.nextMonth()
                this.prevMth = false
            } else {
               this.selectedWeek = newWeek  
               this.selectFirstDayAvailOfWeek()
            }

        },
        cleanWeek(){
            //if the last week is empty we pop it
            if(this.reorganiseDays[this.reorganiseDays.length -1].reduce((sumvalue, current) => sumvalue + current) === 0)
            {
                this.reorganiseDays.pop()
            }
        },
        afterMonthSelected(){
            //clean inexisting final week
            this.cleanWeek()
            this.selectedWeek = this.prevMth? this.reorganiseDays.length -1 : 0

            this.demoSelected.week = this.selectedWeek
            this.selectFirstDayAvailOfWeek()
        },
        selectFirstDayAvailOfWeek(){
            let currentWeek = this.reorganiseDays[this.selectedWeek]
            for (let i = 0; i < currentWeek.length; i++) {
                const day = currentWeek[i]
                if(this.hasAvailability(day)){
                    return this.selectDay(day,this.selectedWeek, false)
                }
            }
            
        },
        autoSkipToWeekWithAvailability(){
            this.sideWeek=='right' ? this.nextWeek():this.prevWeek()
        },


       
        nextWeek(){
            if(this.isLastWeek === true ) return false

            this.triggerWeekChange()
            
        },
        prevWeek(){
            if(this.isCurrentWeek === true) return false
            this.triggerWeekChange( false)
        },

        triggerWeekChange(next = true){

            let timeout = 0
            //we only close the drawer if there are intervals
            if(this.availableIntervals.intervals !== undefined){
                this.resetDaySelection() // close drawer
                timeout = 600
            }
            
            setTimeout(this.changeWeek.bind('',next ), timeout)
        },
        
    },
    computed: {
        isCurrentWeek(){
            return this.isCurrentMonth === true && this.todayWeek === this.selectedWeek
        },
        todayWeek() {
            return parseInt(Math.ceil(this.now.date() / 7) - 1)
        },
        isLastWeek(){
            return this.isLastMonth && this.selectedWeek == this.reorganiseDays.length -1
        },
    }
}
</script>