<template>
    <div v-if="mounted" class="calendarMonth">
        <div class="d-flex justify-content-between align-items-center">
            <span @click="prevMonth" class="wbtn-secondary wbtn wbtn-round wbtn-top sq" 
            role="button" :class="{'wbtn-disabled' : isCurrentMonth}" :disabled="isCurrentMonth"><span><</span></span> 
            <div>{{ getMonthYear }}</div> 
            <span @click="nextMonth" class="wbtn-secondary wbtn wbtn-round wbtn-top sq" role="button" 
            :class="{'wbtn-disabled' : isLastMonth}" :disabled="isLastMonth" ><span>></span></span>
        </div>
        <weekHeader :weekHeader="weekHeader"/>
        <transition :name="'slide-fade-side-sm-' + sideMonth">
            <div v-if="currentMonth">
                <div v-for="(week, idweek) in reorganiseDays">
                    <DaysOfWeek :idweek="idweek" :week="week" :tooltip="getSlotTooltip" :selectedDay="selectedDay"
                    :demoSelected="demoSelected" :cachedSlots="cachedSlots" :isDemo="isDemo" @selectDay="selectDay"/>
                    <transition :name="slotsAnimation">
                        <div v-if="dayWeekSelected(idweek) && selectedDay">
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
            </div>
        
        </transition>
    </div>
</template>
<script>

import CalendarAbstract from './CalendarAbstract'
export default {
    extends: CalendarAbstract,
    methods:{
        afterMonthSelected(){
            this.selectFirstDayAvail()
        },
    }
}
</script>