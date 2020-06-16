<template>
    <div class="d-flex justify-content-between ddays" >
        <div v-for="(day, idday) in week" 
        :class="getClassAvailability(day,idweek, idday)" role="button"  
        @click="$emit('selectDay',day,idweek)" :data-tt="hasTooltip(day)">
            <template v-if="day > 0">
                <span v-if="noAvailability(day)"
                    class="no-avail">
                    {{ day }}
                </span>
                <span v-else>
                    {{ day }}
                </span>
            </template>
            <span v-else></span>
        </div>
    </div>
</template>

<script>
export default {
    props: ['idweek','week', 'cachedSlots', 'isDemo', 'tooltip', 'selectedDay', 'demoSelected'],
    methods: {
        getClassAvailability(day, idweek, idday){
            if(this.isDemo && this.demoSelected.day == day){
                this.demoSelected.week = idweek
            }
    
            let classes = {
                'first-day': idday === 0,
                'last-day': idday === this.week.length -1,
                'dayselected': this.isSelected(day),
                'no-avail wbtn wbtn-disabled': this.noAvailability(day),
                'wbtn-secondary wbtn wbtn-round avail': this.hasAvailability(day),
                'last-avail': this.lastAvailability(day),
                'few-avail': this.fewAvailability(day),
                'enough-avail': this.enoughAvailability(day),
                'plenty-avail': this.plentyAvailability(day),
                'hover':(this.isDemo && this.isSelected(day))
            }
            return classes
        },
        hasAvailability(daynumber){
            if(this.cachedSlots[daynumber] !== undefined) return this.cachedSlots[daynumber]
    
            return 0
        },
        hasTooltip(daynumber){
            let avail = this.hasAvailability(daynumber)
            return avail > 0 ?  this.tooltip.replace('[total_slots]', avail) : false
        },
        
        isSelected(day){
            return this.selectedDay !== false && this.selectedDay == day
        },

        noAvailability(day){
            return this.hasAvailability(day) < 1
        },

        lastAvailability(day){
            return this.hasAvailability(day) === 1
        },

        fewAvailability(day){
            let avail = this.hasAvailability(day)
            return avail > 1 && avail <= 3
        },

        enoughAvailability(day){
            let avail = this.hasAvailability(day)
            return avail > 3 && avail <= 10
        },

        plentyAvailability(day){
            let avail = this.hasAvailability(day)
            return avail > 10
        },
    }
}
</script>
<style>
.wbtn.no-avail span, 
.wbtn.avail span, 
.wbtn-secondary.wbtn-round span {
    display: block;
    line-height: 1.5em;
    height: 1.5em;
    width: 1.5em;
    padding:0;
    margin:0;
}
</style>