<template>
    <div>
        <CalendarWeek v-if="isWeekView" @selectSlot="selectSlot" :options="options" :service="service" :initIntervalsCollection="initIntervalsCollection"
                    :timeprops="timeprops" :staffs="staffs" :duration="duration" :viewData="viewData"  />
        <CalendarMonth v-else @selectSlot="selectSlot" :options="options" :service="service" :initIntervalsCollection="initIntervalsCollection"
                    :timeprops="timeprops" :staffs="staffs" :duration="duration" :viewData="viewData"  />
    </div>
</template>

<script>

import CalendarMonth from './CalendarMonth'
import CalendarWeek from './CalendarWeek'

export default {
    props: ['options','service','initIntervalsCollection', 'timeprops', 'staffs','duration', 'viewData','rescheduling'],
    components: {
        CalendarMonth, CalendarWeek
    }, 
    computed: {
        isWeekView(){
            return [undefined,false].indexOf(this.options.selection.check_viewweek) === -1
        }
    },
    methods: {
        selectSlot(slot){
            this.$emit('selectSlot', this.rescheduling?'RescheduleConfirm':'BookingFormInputs', {selectedSlot: slot})
        },
    }

}
</script>


