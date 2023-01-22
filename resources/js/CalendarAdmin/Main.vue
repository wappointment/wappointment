<template>
  <div>
     <StickyBar v-if="fcIsReady">
        <div>
            <div class="d-flex flex-wrap flex-md-nowrap justify-content-between">
              <div class="d-flex">
                <a class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="prevWeek"><</a>
                <h1 class="h2 align-self-center" @click="refreshEvents"> {{ weekTitle }} </h1>
                <a class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="nextWeek">></a>
                
                <div class="tt-below":data-tt="rolledOverName">
                  <div class="d-flex staff-bar" v-if="viewData.staff !== undefined && viewData.legacy !== true" >
                    <div v-for="staff in viewData.staff" class="cal-staff-img" 
                    :class="{activeStaff:activeStaff.id==staff.id}" @click="changeActiveStaff(staff)">
                      <img :src="staff.avatar" @mouseover="rolledOverName=staff.name" @mouseout="rolledOverName=''" class="wstaff-img" :title="staff.name" :alt="staff.name" />
                    </div>
                  </div>
                </div>
                <FreeSlotsSelector :intervals="getThisWeekIntervals" :viewingFreeSlot="viewingFreeSlot" 
                :durations="getAllDurations" :buffer="viewData.buffer_time"
                 @getFreeSlots="getFreeSlots" @getEdition="getEdition" />
              </div>
              <div class="d-flex">
                <TimeZones v-if="viewData!==null" :timezones="viewData.timezones_list" :staffTimezone="viewData.timezone" classW="align-self-center pr-2" 
                labelDefault="View in a different timezone" :defaultTimezone="displayTimezone" @updateTimezone="updateTimezone"></TimeZones>
                <a v-if="!isToday" class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="today">{{ get_i18n('calendar_this_week', 'common') }}</a>
              </div>
          </div>
           <div v-if="rescheduleModeOn" id="follower" >
            <div v-if="canMoveReschedule">{{ get_i18n('click_for_new_start','calendar') }}</div>
            <div v-else>{{ get_i18n('confirm_new_start','calendar') }} <div>{{ formattedNewStart }} </div>
            <button class="btn btn-link" @click="resetRescheduleMode">{{ get_i18n('cancel','common') }}</button>
            <button class="btn btn-secondary" @click="rescheduleDifferentTime">{{ get_i18n('select_another_time','calendar') }}</button>
            <button class="btn btn-primary"  @click="acceptReschedule">{{ get_i18n('reschedule','common') }}</button>
            </div>
          </div>
        </div>
       
      </StickyBar>

      <div class="full-width center-content calendar-wrap full-width-layout" @mousemove="mouseMove">
        <div class="wrap">
            
            <div  v-if="fullCalOption!==undefined">
              <div id="calendar" @mouseover="showCalSettings=true" @mouseout="showCalSettings = lockCalSettings === false ? false:true" >
                <div v-if="showCalSettings">
                    <CalendarSettings :durations="getAllDurations" :duration="selectedDuration" :preferences="viewData.preferences" :pminH="minHour" :pmaxH="maxHour"
                    @expanded="lockCalSettings=true" @reduced="lockCalSettings=false" @save="savePreferences" />
                </div>
                <FullCalendarWrapper ref="calendar" @isReady="fcalReady" :config="fullCalOption" />
              </div>
            </div>

            <ControlBar v-if="viewData!==null" :progressWizard="viewData.wizard_step" />

            <div v-if="fcIsReady">
                
              <PopupActions v-if="popupActionVisible" @refreshEvents="refreshEvents" @hide="hideModal" 
              :getThisWeekIntervals="getThisWeekIntervals" :displayTimezone="displayTimezone" :activeStaff="activeStaff" 
              :momenttz="momenttz" :startTime="startTime" :endTime="endTime" :realEndTime="realEndTime" :viewData="viewData"/>

              <PopupAppointment v-if="popupAppointmentVisible" 
              @refreshEvents="refreshEvents" @rescheduleOn="rescheduleOn" @hide="hideModal" 
              :displayTimezone="displayTimezone" :activeStaff="activeStaff"
              :getThisWeekIntervals="getThisWeekIntervals" :appointment="activeAppointment" :momenttz="momenttz"  :viewData="viewData" />
              
              <WapModal v-if="showRegularAv" :show="showRegularAv" @hide="hideRegavModal" large>
                <h4 slot="title" class="modal-title">{{ get_i18n('modify_weekly_availability','calendar') }}</h4>

                  <WeeklyAvailability noback :calendar="activeStaff" :timezones_list="viewData.timezones_list" :staffs="viewData.staff"/>
                    <button type="button" class="btn btn-secondary btn-lg mt-2" @click="hideRegavModal">{{ get_i18n('close','common') }}</button>
                
              </WapModal>

              <WelcomeModal v-if="showWelcomePopup" 
              @refreshEvents="refreshEvents" @refreshInitValue="refreshInitValue"
              :passviewData="viewData" :selectedDuration="selectedDuration" :passshowWelcomePopup="showWelcomePopup" :totalSlots="totalSlots" />
            </div>
      </div>
    </div>
    <MainStyle v-if="fcIsReady" :viewData="viewData"/>
  </div>
</template>
<style>
#follower{
    border-radius: 1rem;
  box-shadow: 0 1.2rem 1rem 0 rgba(0,0,0,0.1);
  margin: 0 auto;
  width: 450px;
  text-align: center;
  position: absolute;
  border: 2px solid var(--warning);
  background-color: white;
  color: #797676;
  font-size: 1.1rem;
  font-weight: bold;
  left: 36%;
  padding: 0.4rem;
  top:4px;
}
</style>
<script>
import WeeklyAvailability from '../RegularAvailability/View'
import abstractView from '../Views/Abstract'
import TimeZones from '../Components/TimeZones'
import ControlBar from './ControlBar'
import FullCalendarWrapper from './FullCalendarWrapper'
import SubscribeNewsletter from '../Wappointment/SubscribeNewsletter'
import momenttz from '../appMoment'
import luxonApp from '../appLuxon'
import WelcomeModal from './WelcomeModal'
import PopupActions from './PopupActions'
import PopupAppointment from './PopupAppointment'
import AppointmentRender from './AppointmentRender'
import FreeSlotsSelector from './FreeSlotsSelector'
import CalendarSettings from './CalendarSettings'
import MixinRender from './MixinRender'
import MixinBeautify from './MixinBeautify'
import MixinSelection from './MixinSelection'

import EventService from '../Services/V1/Event'
import StatusService from '../Services/V1/Status'

import Intervals from '../Standalone/intervals'
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import Colors from '../Modules/Colors'
const MainStyle = () => import(/* webpackChunkName: "MainStyle" */ './MainStyle')

let mixins_object = window.wappointmentExtends.filter('BackendCalendarMixins', {AppointmentRender, MixinRender, MixinBeautify, MixinSelection})
let mixins_array = [Colors]
for (const key in mixins_object) {
  if (mixins_object.hasOwnProperty(key)) {
    mixins_array.push(mixins_object[key])
  }
}

  /**
 * TODO Review moment usage
 */

export default {
  extends: abstractView,
  mixins: mixins_array,
  name: 'calendar',
  components: {
      SubscribeNewsletter,
      TimeZones,
      ControlBar,
      WeeklyAvailability,
      FullCalendarWrapper,
      FreeSlotsSelector,
      WelcomeModal,
      CalendarSettings,
      PopupActions,
      PopupAppointment,
      MainStyle
  }, 
  data: () => ({
    momenttz: momenttz,
    selectedDuration: false,
    fcIsReady: false,
    observer: undefined,
    canLoadEvents: true,
    callback: undefined,
    viewingFreeSlot: false,
    currentView: 'timeGridWeek',
    showWelcomePopup: false,
    showRegularAv: false,
    clientSelected: false,
    viewName: 'calendar',
    firstDay: undefined,
    cancelbgOver: false,
    name: 'calendar',
    namekey: 'calendar',
    windowStaff: window.Staffs, //TODO review and remove
    modifiedEvent: [],
    selectedTimezone: undefined,
    timezone: undefined,
    staffs: [],
    disableSelectClick: false,
    disableBgEvent: false,
    startTime:0,
    endTime:0,
    realEndTime:0,
    intervalsCollection: null,
    fullCalOption: undefined,
    openedDays: [],
    minHour: null,
    maxHour: null,
    events: [],
    intervals: {
      hours: 0,
      minutes: 0
    },
    activeBgOverId: false,
    shortDayFormat: 'Do MMM YY',
    headerDayFormat: { weekday: 'short', month: 'numeric', day: 'numeric', omitCommas: true },
    daysProperties: false,
    serviceEvent: null,
    serviceStatus: null,
    openconfirm: false,
    hasBeenSetCalProps: false,
    queryParameters: undefined,
    showCalSettings: false,
    lockCalSettings: false,
    activeStaff: null,
    rolledOverName: '',
    popupActionVisible:false,
    popupAppointmentVisible: false,
    activeAppointment: null,
    rescheduleModeOn: false,
    moveCursor:false,
    canMoveReschedule: true,
    newStartDate:null,
    tempRescheduleEvent: null
  }),

  created(){
    this.serviceEvent = this.$vueService(new EventService)
    this.serviceStatus = this.$vueService(new StatusService)
    
    this.checkQueryParams()
    
  },

 watch: {
    events: {
      handler: function(newValue) {
        if(this.openconfirm && newValue.length > 1){
          this.confirmRequest(this.openconfirm)
          this.openconfirm = false
        }
      },
    },
  },
 
 computed: {
   formattedNewStart(){
     return this.newStartDate.format(this.viewData.date_format+' '+this.viewData.time_format)
   },
   activeAvailability(){
     return this.activeStaff.availability
   },
   activeDotcom(){
     return this.viewData.legacy !== true ? this.activeStaff.options.dotcom:this.viewData.is_dotcom_connected
   },
   isDotComConnected(){
     return [undefined, false].indexOf(this.activeDotcom) === -1
   },
   dotComServices(){
     return this.isDotComConnected ? this.activeDotcom.services:[]
   },
    
   activeRegav(){
     return this.viewData.legacy !== true ? this.activeStaff.options.regav:this.viewData.regav
   },
   getAllDurations(){
     return this.viewData.durations
   },
    isToday(){
      return this.firstDay!== undefined && this.lastDay !== undefined && this.firstDay.unix() < momenttz().unix() && this.lastDay.unix() > momenttz().unix()
    },
    //TODO review and remove
    staffExceptOwner() {
      return this.windowStaff.filter((staff) => {
        if(staff.id !== this.modifiedEvent.staff_id) return staff
      });
    },
    weekTitle() {
      return (this.firstDay!== undefined) ? this.firstDay.format(this.shortDayFormat) + ' - ' + this.lastDay.format(this.shortDayFormat) : ''
    },
    weekTitleShort() {
      return (this.firstDay!== undefined) ? this.firstDay.format('Do MMM') + ' - ' + this.lastDay.format('Do MMM') : ''
    },

    getThisWeekIntervals() {
      let start = this.nowTime()
      if(start === undefined) return 0
      let timeDisplayStart = this.startTimeDisplayed()

      if(!start.isSameOrAfter(timeDisplayStart)){
        start = timeDisplayStart
      }
      let end = this.endTimeDisplayed()
      if(this.intervalsCollection === null || end === undefined || start === undefined) return 0
      return this.intervalsCollection.get(start, end)

    },
    totalSlots() {
      return this.getThisWeekIntervals === 0 ? 0:this.getThisWeekIntervals.splits(parseInt(this.selectedDuration)*60).totalSlots()
    },
    lastDay() {
      return momenttz.tz(this.firstDay,this.displayTimezone).add(6, 'day')
    },
    realFirstday() {
      
      if(this.firstDay!== undefined && this.firstDay.unix() > momenttz().unix()) return this.firstDay;
      return momenttz()
    },

    displayTimezone() {
      return (this.selectedTimezone !== undefined) ? this.selectedTimezone : this.timezone
    },
 },
  methods: {
    async initValueRequest() {
        return await this.serviceViewData.call('calendar')
    },
    hasDotcom(){
      return this.isDotComConnected
    },
    changeActiveStaff(staff){
      this.activeStaff = staff
      if(this.queryParameters !== undefined){
        this.queryParameters.timezone = this.activeStaff.options.timezone
      }
      
      this.reload(true)
    },
    getPref(pref = 'cal_duration', defaultVal= ''){
      return [undefined,false, null,''].indexOf(this.viewData.preferences[pref]) === -1 ? this.viewData.preferences[pref]: defaultVal
    },
    checkQueryParams(){
      if(window.savedQueries!== undefined ) {
        this.queryParameters = window.savedQueries
        if([null,undefined].indexOf(window.savedQueries.open_confirm) === -1 && window.savedQueries.open_confirm > 0){
          this.openconfirm = window.savedQueries.open_confirm
        }
        
      }
    },
    resizeSlots(duration){
      this.setInterval(duration)

      this.reloadAfterChange()
    },
    reloadAfterChange(){
      this.fullCalOption = undefined
      setTimeout(this.setFullCalOptions.bind(null, this.firstDay.format()), 100)
    },
    savePreferences(preferences){
      this.minHour = preferences.minH
      this.maxHour = preferences.maxH
      this.viewData.preferences = preferences
      this.resizeSlots(preferences.interval)
    },

    async getEventsRequest(params) {
        let p = {
          start: params.start.format(), 
          end: params.end.format(), 
          timezone:params.timezone, 
          view: this.currentView, 
        }
        if(this.activeStaff.id!==undefined){
          p.staff_id = this.activeStaff.id
        }
        if(this.viewingFreeSlot){
          p.viewingFreeSlot = true
        }
          return await this.serviceEvent.call('list', p, {
            'cal-maxH': this.maxHour,
            'cal-minH': this.minHour,
            'cal-duration': this.selectedDuration,
            'cal-appoint-col': this.viewData.preferences.cal_appoint_col,
            'cal-avail-col': this.viewData.preferences.cal_avail_col,
          })
      },
    
    fcalReady(){
      this.fcIsReady=true
    },
    getFreeSlots(){
      this.viewingFreeSlot = true
      this.refreshEvents()
    },
    getEdition(){
      this.viewingFreeSlot = false
      this.refreshEvents()
    },
    
    hideRegavModal(){
      this.showRegularAv = false
      this.refreshEvents()
    },

    startTimeDisplayed() {
      if(this.fcIsReady) {
        if(this.getDate() !== undefined)
          return this.getDate().tz(this.selectedTimezone).day(this.firstDay).hours(this.minHour)
      }
      return undefined
    },
    nowTime() {
      if(this.viewData!==null && this.viewData.now!==undefined) {
        return momenttz.tz(this.viewData.now,this.viewData.timezone)
      }
      return undefined
    },
    endTimeDisplayed() {
      if(this.fcIsReady && this.getDate() !== undefined) {
        
        return this.getDate().tz(this.selectedTimezone).add(6, 'day').hours(this.maxHour)
        //return this.getDate().tz(this.selectedTimezone).day(7).hours(this.maxHour)
      }
      return undefined
    },
      updateTimezone(selectedTimezone,initSave = false){
        this.selectedTimezone = selectedTimezone
        momenttz.tz.setDefault(selectedTimezone)
        this.timezone = selectedTimezone
        
        //this.$refs.calendar.option( 'timeZone', selectedTimezone)
        this.writeHistory()

        if(initSave === false) {
          this.reload()
        }
        
        //this.refreshEvents()
      },

      setInterval(duration){
        this.selectedDuration = duration
        this.intervals.hours = parseInt(this.selectedDuration/ 60)
        this.intervals.minutes = this.selectedDuration % 60
      },
      convertInterval(){
        let hours = this.intervals.hours
        let minutes = this.intervals.minutes //+ this.viewData.buffer_time
        if(hours >= 1 ) hours = (hours < 10) ? '0' + hours : hours
        else hours = '00'
        minutes = (minutes < 10) ? '0' + minutes : minutes
        
        return hours + ':' + minutes
      },

      getStaffTz(){
        return this.viewData.legacy !== true ? this.activeStaff.options.timezone:this.activeStaff.t
      },
      loaded(viewData, reloaded = false, staffChange=false){
          this.viewData = viewData.data
          
          if(this.viewData.legacy === true){
            this.activeStaff = this.viewData.staff
          }else{
            if(staffChange === false ){
              if(this.queryParameters !== undefined && this.queryParameters.staff !== undefined){
                let staffid = this.queryParameters.staff
                this.activeStaff = this.viewData.staff.find(e => e.id ==staffid)
              }else{
                this.activeStaff = this.viewData.staff[0]
              }
              
            }

          }
          
          this.openedDays = this.activeRegav
          this.intervalsCollection = new Intervals(this.activeAvailability)
          let initTimezone = (this.queryParameters !== undefined)? this.queryParameters.timezone:this.getStaffTz()
          this.timezone = initTimezone // staff timezone
          this.selectedTimezone = initTimezone // display timezone

          if(reloaded === false){
            
            this.viewData.time_format = convertDateFormatPHPtoMoment(this.viewData.time_format)
            this.viewData.date_format = convertDateFormatPHPtoMoment(this.viewData.date_format)
            this.setMinAndMax()
            
            this.showWelcomePopup = this.viewData.showWelcome
            
            this.setInterval(this.getPref('cal_duration', this.viewData.durations[0]))
            let min_max = this.getMinMaxHourFromRegav()

            this.minHour = min_max.min < this.getPref('cal_minH')? min_max.min:this.getPref('cal_minH')
            this.maxHour = min_max.max > this.getPref('cal_maxH')? min_max.max:this.getPref('cal_maxH')
            if(this.maxHour > 24) {
              this.maxHour = 24
            }
          }
          
          let defaultDate = false
          if(this.queryParameters !== undefined){
            defaultDate = this.toMoment(this.queryParameters.start.replace(' ','+')).format()
          }
          this.setFullCalOptions(defaultDate)

      },


      getMinMaxHourFromRegav(){
        let min = 7
        let max = 19
        let min_max = this.getMinMaxRegav()
        if(this.openedDays.precise !== undefined){
          min_max.min = Math.floor(min_max.min/60)
          min_max.max = Math.ceil(min_max.max/60)
        }
        return {
          min:  min_max.min < min ? min_max.min:min, 
          max: min_max.max > max ? min_max.max:max, 
        }
      },
      getMinMaxRegav(){
        let min = false
        let max = false
        for (const key in this.openedDays) {
          if (this.openedDays.hasOwnProperty(key)) {
            const daysSlots = this.openedDays[key]
            if(Array.isArray(daysSlots) && daysSlots.length > 0){
              for (const iterator of daysSlots) {
                min = iterator[0] < min || min === false ? iterator[0]:min
                max = iterator[1] > max || max === false ? iterator[1]:max
              }
            }
            
          }
        }
        return {min:min, max:max}
      },
      
      setFullCalOptions(defaultDate = false){
          const intervalString = this.convertInterval()
          
          this.fullCalOption = {
              events: {
                select: this.selectMethod,
                loading: this.isLoading,
                eventClick: this.clickEvent,
                eventMouseEnter: this.mouseEnter,
                eventMouseLeave: this.mouseLeave,
                eventDragStart: this.eventDragStart,
                eventDragStop: this.eventDragStop,
                eventDrop: this.eventPatch,
                eventResize: this.eventPatch,
                dateClick: this.dateClick
              },
              props: {
                header: {
                  left: '',
                  center: '',
                  right: ''
                },
                slotLabelInterval: intervalString,
                slotDuration: intervalString,
                showNonCurrentDates: true,
                nowIndicator: true,
                now: momenttz.tz(this.viewData.now,this.viewData.timezone).tz(this.timezone).format(),
                weekends: true,
                allDaySlot: false,
                locale: window.waplocale,
                timeZone: this.displayTimezone,
                slotLabelFormat: this.viewData.time_format,
                eventTimeFormat: this.viewData.time_format,
                eventDurationEditable:  true,
                eventStartEditable: true, 
                selectable: true,
                selectMirror: true,
                selectMinDistance: 10,
                selectAllow: this.selectAllow,
                defaultView: 'timeGridWeek',
                columnHeaderFormat: this.headerDayFormat,
                firstDay: this.viewData.week_starts_on,
                minTime: this.minHour + ':00',
                maxTime: this.maxHour + ':00',  
                navLinks: false, 
                editable:true,
                eventLimit: true,
                events: this.loadingEvents,
                eventAllow: this.eventAllow,
                eventRender: this.eventRender,
                
              }
              
            }
            
            if(defaultDate !== false){
              this.fullCalOption.props.defaultDate = defaultDate
            }
      },
      rescheduleOn(){
        this.rescheduleModeOn = true
      },
      dateClick(info){
        if(!this.canMoveReschedule){
          return
        }
        /* console.log(this.toMoment(info.dateStr), this.toMoment(info.dateStr).unix()) */
        this.canMoveReschedule = false
        this.newStartDate = this.toMoment(info.dateStr)
        let duration = this.toMoment(this.activeAppointment.end).unix() - this.toMoment(this.activeAppointment.start).unix()
        this.tempRescheduleEvent = this.$refs.calendar.getApi.addEvent(
          { // this object will be "parsed" into an Event Object
            title: 'confirm new event', // a property!
            start: this.toMoment(info.dateStr).format(), // a property!
            end: this.toMoment(info.dateStr).add(duration,'seconds').format() // a property! ** see important note below about 'end' **
          }
        )
        
/*         if(this.rescheduleModeOn){
          console.log('rescheduling and listening for reschedule events')
        } */
      },

      rescheduleDifferentTime(){
        this.canMoveReschedule = true
        if(this.tempRescheduleEvent !== null){
          this.tempRescheduleEvent.remove()
        }
      },
      acceptReschedule(){
        //console.log('this.activeAppointment',this.activeAppointment)
        let duration = this.toMoment(this.activeAppointment.end).unix() - this.toMoment(this.activeAppointment.start).unix()
        
        this.request(this.editEventRequest, {
          eventId: this.activeAppointment.extendedProps.dbid, 
          start: this.newStartDate.unix(), 
          end: this.newStartDate.unix()+duration
          },
          undefined,false,  this.resetRescheduleMode)
      },

      resetRescheduleMode(){
        if(this.tempRescheduleEvent !== null){
          this.tempRescheduleEvent.remove()
        }
        this.activeAppointment = null
          this.rescheduleModeOn = false
          this.moveCursor =false
          this.canMoveReschedule = true
          this.newStartDate = null
          this.refreshEvents()
      },
      mouseMove(event) {
        if(this.rescheduleModeOn ){
          /* let coordinates = {x:event.clientX, y:event.clientY}
          if(!this.moveCursor || this.distanceUpdate(coordinates)){
            
            
            this.moveCursor = coordinates
          } */
          if(this.canMoveReschedule){
            /* let xp = 40, yp = 40;
            // change 12 to alter damping higher is slower
              xp += (event.pageX - xp) 
              yp += (event.pageY - yp) 
              document.getElementById("follower").style.left = event.pageX+"px"
              document.getElementById("follower").style.top = event.pageY +"px" */
          }
          
          
        }
      },

      distanceUpdate(coordinates){
        let newdist = this.getDistance(coordinates.x, coordinates.y,this.moveCursor.x, this.moveCursor.y)
        return newdist > 50;
      },

      getDistance(x1, y1, x2, y2){
        let y = x2 - x1;
        let x = y2 - y1;

        return Math.sqrt(x * x + y * y);
      },

      loadAgain(staffChange){
        this.loaded({data:Object.assign({},this.viewData)}, true, staffChange)
      },
      reload(staffChange = false){
        this.fullCalOption = undefined
        this.fcIsReady = false
        setTimeout(this.loadAgain.bind(null,staffChange), 100)
      },

      toMoment(FullCaldateString, timezone = false, debug=false){
        return momenttz.tz(FullCaldateString,this.timezone) 
      },
      isInThePast(event){

        if(momenttz.tz(this.viewData.now,this.viewData.timezone).unix() > this.toMoment(event.start).unix() 
          ||
           momenttz.tz(this.viewData.now,this.viewData.timezone).unix() > this.toMoment(event.end).unix()
        ){
          return true
        }
        return false
      },

      eventAllow(dropLocation, draggedEvent){
        return this.isInThePast(dropLocation)? false:draggedEvent.extendedProps.allowedit
      },

      selectMethod(selectInfo) {
          
            this.startTime = this.toMoment(selectInfo.startStr)
            this.realEndTime = this.toMoment(selectInfo.endStr)
            this.endTime = this.toMoment(selectInfo.endStr)

            /* this.startTime = momenttz.tz(start.format(), this.timezone)
            this.endTime = momenttz.tz(end.format(), this.timezone) */
            this.openModalFreeTime();
      },

      openModalFreeTime() {
        this.openCreateModal();
      },

      formatTime(myMoment, format = false){
        if(format === false) format = this.viewData.time_format
        return myMoment.format(format)
      },
      
      hideModal(){
        this.popupActionVisible = false
        this.popupAppointmentVisible = false
      },
      openCreateModal() {
        this.disableBgEvent = false
        this.popupActionVisible = true
      },
      openAppointmentModal(appointment) {
        this.disableBgEvent = false
        this.popupAppointmentVisible = true
        this.activeAppointment = appointment
      },

      setMinAndMax(){
            
            for (var dayname in this.openedDays) {
                let timeBlocks = this.openedDays[dayname]
                if(Array.isArray(timeBlocks)){
                    for (const timeblock of timeBlocks) {
                  
                      if(this.minHour === false){
                        this.minHour = timeblock[0]
                        this.maxHour = timeblock[1]
                      }
                      if(timeblock[0] < this.minHour){
                          if(timeblock[0]>0) this.minHour = timeblock[0] - 1
                          else this.minHour = 0
                      } 
                      if(timeblock[1] > this.maxHour){
                          if(timeblock[1]<24) this.maxHour = timeblock[1] + 1
                          else this.maxHour = 24
                      }
                  }
                }
                
            }
            if( this.minHour > 0 ) this.minHour --
            if( this.maxHour < 24 ) this.maxHour ++
        },

      callbackInternal(a){
        this.events = a.data.events
  
        for (const key in this.events) {
          
          if (this.events.hasOwnProperty(key)) {

            const element = this.events[key]

            this.events[key].dbid = this.events[key].delId
            this.events[key].id = this.events[key].type+'-'+this.events[key].id
            
            if( momenttz.tz(this.viewData.now,this.viewData.timezone).unix() > momenttz.tz(this.events[key].end, this.selectedTimezone).unix() ) {
              this.events[key].allowedit = false
              this.events[key].past = true
            }else{
              this.events[key].allowedit = true
              this.events[key].past = false
            }
            this.events[key] = Object.assign({},this.events[key])
          }
        }
        this.$refs.calendar.option('now', a.data.now)

        this.intervalsCollection = null

        this.intervalsCollection = new Intervals(a.data.availability)
        
        //this.option
        this.callback(this.events)
        this.canLoadEvents = true
        this.$emit('fullyLoaded')
      },

      loadingEvents(fetchInfo, successCallback, failureCallback/* , start, end, timezone, callback */){
        this.callback = successCallback
        let params = {}

        if(this.queryParameters !== undefined){
          this.timezone = this.queryParameters.timezone
          params = {
            start: this.toMoment(this.queryParameters.start.replace(' ','+')), 
            end: this.toMoment(this.queryParameters.end.replace(' ','+')), 
            timezone: this.timezone
          }
          this.writeHistory()
          
        } else{
          params = {start: this.toMoment(fetchInfo.start), end: this.toMoment(fetchInfo.end), timezone: this.timezone}
        }

        this.firstDay = params.start

        if(this.canLoadEvents){
          this.request(this.getEventsRequest, params,undefined,false,  this.callbackInternal)
          this.canLoadEvents = false
        }
      },

      isLoading(isLoading){
        this.observeNowIndicator()
      },

      getDate(){
        return this.toMoment(this.$refs.calendar.fireMethod('getDate'))
      },
      resetFirstDay(){
        this.firstDay = this.getDate().startOf('week')
      },
      nextWeek(){
        this.queryParameters = undefined
        this.daysProperties = false
        this.firstDay = this.$refs.calendar.next(this.lastDay)
        this.hasBeenSetCalProps = false
        //this.resetFirstDay()
        this.writeHistory()
        
      },
      
      prevWeek(){
        this.queryParameters = undefined
        this.daysProperties = false
        this.firstDay = this.$refs.calendar.prev(this.firstDay)
        this.hasBeenSetCalProps = false
        //this.resetFirstDay()
        this.writeHistory()
      },
      writeHistory(clear = false){
        if(this.firstDay !== undefined){
          this.queryParameters = {
              page: 'wappointment_calendar',
              start: this.firstDay.format(),
              end: this.firstDay.clone().add(7, 'days').format(),
              timezone: this.displayTimezone,
              staff: this.activeStaff.id,
            }
          window.history.pushState(
          {
            query: Object.assign({},this.queryParameters)
          },
          'Calendar week ' + this.firstDay.format() + ' - ' + this.lastDay.format(),
          'admin.php?page=wappointment_calendar'+ (clear === false ? '&start=' + this.firstDay.format() + '&end=' + this.lastDay.format() + '&timezone=' + this.displayTimezone + '&staff='+this.activeStaff.id:'')
        )
        }
        
      },



      today(){
        this.queryParameters = undefined
        this.daysProperties = false
        
        this.firstDay = undefined
        this.hasBeenSetCalProps = false
        this.$refs.calendar.fireMethod('today')
        //this.resetFirstDay()
        this.refreshEvents()
        this.writeHistory(true)
         
      },
      monthView(){
         this.$refs.calendar.fireMethod('changeView', 'month')
         this.currentView = 'month'
         this.refreshEvents()
         this.resetFirstDay()
      },
      
      refreshEvents(){
        this.hasBeenSetCalProps = false
        this.$refs.calendar.refresh()
      },

    }
}
</script>
