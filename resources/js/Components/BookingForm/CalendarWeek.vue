<template>
    
        <div v-if="currentMonth" class="calendarMonth">
            <div class="d-flex justify-content-between align-items-center">
                <span @click="prevWeek" class="btn-secondary btn btn-round" :class="{'btn-disabled' : isCurrentWeek}" :disabled="isCurrentWeek"><</span> 
                <div>{{ getMonthYear()}}</div> 
                <span @click="nextWeek" class="btn-secondary btn btn-round" :class="{'btn-disabled' : isLastWeek}" :disabled="isLastWeek" >></span>
            </div>
            <div class="d-flex justify-content-between ddays" >
                <div v-for="(dayH, idy) in weekHeader">
                    {{ initial(dayH) }}
                </div>
            </div>
            <transition :name="slideWeek">
                <div v-if="!changingWeek" >
                    <div class="d-flex justify-content-between ddays" >
                        <div v-for="(day, idday) in reorganiseDays[selectedWeek]" :class="{dayselected: isSelected(day)}">
                            <template v-if="day > 0">
                                <span v-if="noAvailability(day)"
                                    class="no-avail">
                                    {{ day }}
                                </span>
                                <span v-else
                                    :class="getClassAvailability(day,selectedWeek)"
                                    :data-tt="hasTooltip(day)" @click="selectDay(day,selectedWeek)">
                                    {{ day }}
                                </span>
                            </template>
                            
                        </div>
                        
                    </div>
                    <transition name="slide-fade-sm">
                        <div class="slotsPane p-2" v-if="dayWeekSelected(selectedWeek) && selectedDay">
                            <DaySlots 
                            :intervals="availableIntervals.intervals" 
                            :duration="realSlotDuration()" 
                            :currentTz="currentTz"
                            :time_format="time_format"
                            :options="options"
                            :now="now"
                            @selected="selectSlot" />
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
        sideWeek: 'right',
        changingWeek: false
    }),
    methods:{
        setWeek(newWeek, delay=true){
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
        afterMonthSelected(){
            if(this.reorganiseDays[this.reorganiseDays.length -1].reduce(
                (accumulateur, valeurCourante) => accumulateur + valeurCourante) === 0)
                {
                this.reorganiseDays.pop()
            }
            if(this.prevMth){
                this.selectedWeek = this.reorganiseDays.length -1
            }else{
                this.selectedWeek = 0
            }
            this.selectFirstDayAvailOfWeek()
        },
        selectFirstDayAvailOfWeek(){
            let currentWeek = this.reorganiseDays[this.selectedWeek]
            for (let i = 0; i < currentWeek.length; i++) {
                const day = currentWeek[i]
                if(!this.noAvailability(day)){
                    return this.selectDay(day,this.selectedWeek)
                }
            }
            //this.autoSkipToWeekWithAvailability()
        },
       /*  autoSkipToWeekWithAvailability(){
            this.sideWeek=='right' ? this.nextWeek():this.prevWeek()
        } */


        changeWeek(increment = true, animate = false){
            this.changingWeek = true
            let newWeek = increment ===false ? this.selectedWeek -1:this.selectedWeek +1
            if(animate) {
                setTimeout(this.setWeek.bind('',newWeek ), 100)
            }else{
                this.setWeek(newWeek, false)
            }
        },
        nextWeek(){
            if(this.isLastWeek === true ) return false
            this.sideWeek = 'right'
            this.changeWeek(true, true)
        },
        prevWeek(){
            if(this.isCurrentWeek === true) return false
            this.sideWeek = 'left'
            this.changeWeek(false, true)
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
        slideWeek(){
            return 'slide-fade-side-sm-' + this.sideWeek
        },
    }
}
</script>