<template>
    <transition>
            <div v-if="mounted">
                <transition :name="slideMonth">
                    <div v-if="currentMonth" class="calendarMonth">
                        <div class="d-flex justify-content-between align-items-center">
                            <span @click="prevMonth" class="btn btn-link" :class="{'btn-disabled' : isCurrentMonth}" :disabled="isCurrentMonth"><</span> 
                            <div>{{ getMonthYear() }}</div> 
                            <span @click="nextMonth" class="btn btn-link" :class="{'btn-disabled' : isLastMonth}" :disabled="isLastMonth" >></span>
                        </div>
                        <div class="d-flex justify-content-between ddays" >
                            <div v-for="(dayH, idy) in weekHeader">
                                {{ initial(dayH) }}
                            </div>
                        </div>
                        <div v-for="(week, idweek) in reorganiseDays">
                            <div class="d-flex justify-content-between ddays" >
                                <div v-for="(day, idday) in week" :class="{dayselected: isSelected(day)}">
                                    <template v-if="day > 0">
                                        <span v-if="noAvailability(day)"
                                            class="no-avail">
                                            {{ day }}
                                        </span>
                                        <span v-else
                                            :class="getClassAvailability(day,idweek)"
                                            :data-tt="hasTooltip(day)" @click="selectDay(day,idweek)">
                                            {{ day }}
                                        </span>
                                    </template>
                                    
                                </div>
                                
                            </div>
                            <transition name="slide-fade-sm">
                                <div class="slotsPane p-2" v-if="dayWeekSelected(idweek)">
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
                    </div>
                 </transition>
            </div>
        </transition>
</template>
<script>

import CalendarAbstract from './CalendarAbstract'
export default {
    extends: CalendarAbstract,
}
</script>