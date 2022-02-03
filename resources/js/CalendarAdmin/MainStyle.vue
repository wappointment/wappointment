<template>
    <v-style v-if="viewData!== null">
        .fc-event-container .fc-event, 
        .fc-container .fc-event {
            border-color: {{ hx_rgb(viewData.preferences.cal_appoint_col, 1) }};
            background-color: {{ hx_rgb(viewData.preferences.cal_appoint_col, 1) }};
        }
        .fc-event-container .fc-event.appointment-pending, 
        .fc-container .fc-event.appointment-pending {
            background-color: {{ hx_rgb(viewData.preferences.cal_appoint_col, .7) }};
            background-image: linear-gradient(45deg, transparent 25%, #92aaca 25%, #92aaca 50%, transparent 50%, transparent 75%, #92aaca 75%, #92aaca 100%);
            background-size: 30px 30px;
        }

        .fc-event-container .fc-event.appointment-owes, 
        .fc-container .fc-event.appointment-owes {
            border: 2px dashed #d7b637 !important;
        }

        .fc-event.past-event {
            background-color: {{ hx_rgb(viewData.preferences.cal_appoint_col, 1) }} !important;
        }

        .fc-bgevent.opening{
            opacity: 1;
            border: 2px dashed {{ hx_rgb(viewData.preferences.cal_avail_col, 1) }};
            background-color:{{ hx_rgb(viewData.preferences.cal_avail_col, 1) }};
            z-index: 1;
        }
            .fc-bgevent.opening.extra {
            background-color: {{ hx_rgb(viewData.preferences.cal_avail_col, .8) }};
            border: 2px dashed {{ hx_rgb(viewData.preferences.cal_avail_col, .8) }};
            opacity: 1;
            z-index: 2;
        }
    
    </v-style>
</template>
<script>
import Colors from '../Modules/Colors'
export default {
    props:['viewData'],
    mixins: [Colors],
}
</script>
<style>
@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';
@import '~@fullcalendar/timegrid/main.css';
@import './main.css';
</style>
