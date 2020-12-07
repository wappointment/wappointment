
<template>
    <div class="calendar-settings sm-text" @mouseover="showText = true" @mouseout="showText = false">
        
        <div class="p-3" v-if="showPreferences">
            <h3 class="d-flex align-items-center">
                <span class="dash-h3 dashicons dashicons-admin-settings"></span> <span >Edit preferences</span></h3>
            <div class="d-flex align-items-center mb-2">
                <div>Hours shown from: </div>
                <div class="dropdown d-flex align-self-center tt-below" :class="{'show': toggleMinH}" >
                    <button class="btn btn-secondary dropdown-toggle btn-xs" type="button" @click="toggleMinH=!toggleMinH">
                        {{minHour}}h </span>
                    </button>
                    <div class="dropdown-menu" :class="{'show': toggleMinH}">
                        <a class="dropdown-item" href="javascript:;" v-for="durationI in hoursAvail" @click="changeMinHour(durationI)"> {{durationI}}h </a>
                    </div>
                </div>
                <div>Until</div>
                <div class="dropdown d-flex align-self-center tt-below" :class="{'show': toggleMaxH}" >
                    <button class="btn btn-secondary dropdown-toggle btn-xs" type="button" @click="toggleMaxH=!toggleMaxH">
                        {{maxHour}}h </span>
                    </button>
                    <div class="dropdown-menu" :class="{'show': toggleMaxH}">
                        <a class="dropdown-item" href="javascript:;" v-for="durationI in hoursAvail" @click="changeMaxHour(durationI)"> {{durationI}}h </a>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center mb-2">
                <div>One interval equals</div>
                <div class="dropdown d-flex align-self-center tt-below" :class="{'show': toggle}" >
                    <button class="btn btn-secondary dropdown-toggle btn-xs" type="button" @click="toggle=!toggle">
                        {{selectedDuration}}min </span>
                    </button>
                    <div class="dropdown-menu" :class="{'show': toggle}">
                        <a class="dropdown-item" href="javascript:;" v-for="durationI in baseDurations" @click="selectDuration(durationI)"> {{durationI}}min </a>
                    </div>
                </div>
            </div>
            <div>
                <div>Colors</div>
                <div class="d-flex align-items-center mb-2">
                    <div class="mr-2"><ColorPicker v-model="cal_avail_col" label="Available"></ColorPicker></div>
                    <div class="mr-2"><ColorPicker v-model="cal_appoint_col" label="Appointment"></ColorPicker></div>
                </div>
            </div>
            
            <button type="button" @click.prevent.stop="hidePref" class="btn btn-secondary btn-sm">Close</button> 
            <button type="button" @click.prevent.stop="savePreferences" class="btn btn-outline-primary btn-sm">Save</button>
        </div>
        <div v-else class="btn btn-link btn-xs" role="button" @click="showPref"><span class="dashicons dashicons-admin-settings"></span> <span v-if="showText">Edit preferences</span></div>
            <v-style >
            .fc-event-container .fc-event, 
            .fc-container .fc-event {
                border-color: {{ hx_rgb(cal_appoint_col, 1) }} !important;
                background-color: {{ hx_rgb(cal_appoint_col, 1) }}!important;
            }
            .fc-event-container .fc-event.appointment-pending, 
            .fc-container .fc-event.appointment-pending {
                background-color: {{ hx_rgb(cal_appoint_col, .7) }}!important;

            }

            .fc-event.past-event {
                background-color: {{ hx_rgb(cal_appoint_col, 1) }} !important;
            }


            .fc-bgevent.opening{
                border: 2px dashed {{ hx_rgb(cal_avail_col, 1) }}!important;
                background-color:{{ hx_rgb(cal_avail_col, 1) }}!important;
            }
                .fc-bgevent.opening.extra {
                background-color: {{ hx_rgb(cal_avail_col, .8) }}!important;
                border: 2px dashed {{ hx_rgb(cal_avail_col, .8) }}!important;

            }
        
        </v-style>
    </div>
</template>

<script>
import Colors from '../Modules/Colors'
import ColorPicker from '../Components/ColorPicker'
export default {
    mixins:[Colors],
    props: [ 'durations', 'duration', 'pminH','pmaxH', 'preferences'],
    data: () => ({
        showPreferences: false,
        toggle: false,
        toggleMinH: false,
        toggleMaxH: false,
        hoursAvail:[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24],
        baseDurations: [10, 15, 20, 30, 60],
        minHour: 6,
        maxHour: 19,
        selectedDuration: 0,
        showText: false,
        cal_avail_col: '',
        cal_appoint_col: ''
    }),
    components: {ColorPicker},
    created(){
        for (let i = 0; i < this.durations.length; i++) {
            if(this.baseDurations.indexOf(this.durations[i]) === -1){
                this.baseDurations.push(this.durations[i])
            }
        }
        this.selectedDuration = this.duration
        this.minHour = this.pminH
        this.maxHour = this.pmaxH
        if([undefined, null].indexOf(this.preferences.cal_avail_col ) === -1){
            this.cal_avail_col = this.preferences.cal_avail_col
        }
        if([undefined, null].indexOf(this.preferences.cal_avail_col) === -1){
            this.cal_appoint_col = this.preferences.cal_appoint_col
        }
        
        
    },
    methods:{
        showPref(){
            this.showPreferences=true 
            this.$emit('expanded')
        },
        hidePref(){
            this.showPreferences=false 
            this.$emit('reduced')
        },
        savePreferences(){
           
            this.$emit('save', {
                interval: this.selectedDuration,
                minH: this.minHour,
                maxH: this.maxHour,
                cal_avail_col: this.cal_avail_col,
                cal_appoint_col: this.cal_appoint_col
            })

        },
        selectDuration(duration){
            this.selectedDuration = duration
            
            this.toggle = false
        },
        changeMinHour(minH){
            this.minHour = minH
            this.toggleMinH = false
        },
        changeMaxHour(maxH){
            this.maxHour = maxH
            this.toggleMaxH = false
        }
    }
}
</script>

<style>
  #calendar .calendar-settings{
    position: absolute;
    z-index: 2;
    background-color: #f2f2f2;
    border-radius: .5rem;
    top: -10px;
    box-shadow:0px 4px 10px -10px rgba(0,0,0,.9);
  }
  .dash-h3{
    font-size: 2rem;
    width: 32px;
    height: 32px;
  }
  .calendar-settings .dropdown-menu.show{
    overflow: scroll;
    max-height: 300px;
  }
  .calendar-settings .dropdown-item {
    padding: .1rem .4rem;
    font-size: .8em;
}
</style>