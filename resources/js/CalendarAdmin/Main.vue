<template>
  <div>
     <StickyBar v-if="fcIsReady">
        <div>
            <div class="d-flex flex-wrap flex-md-nowrap justify-content-between">
              <div class="d-flex">
                <a class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="prevWeek"><</a>
                <h1 class="h2 align-self-center" @click="refreshEvents"> {{ weekTitle }} </h1>
                <a class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="nextWeek">></a>
                <FreeSlotsSelector :intervals="getThisWeekIntervals" :viewingFreeSlot="viewingFreeSlot" 
                :durations="getAllDurations" :buffer="viewData.buffer_time"
                 @getFreeSlots="getFreeSlots" @getEdition="getEdition" />
              </div>
              <div class="d-flex">
                <TimeZones v-if="viewData!==null" :timezones="viewData.timezones_list" :staffTimezone="viewData.timezone" classW="align-self-center pr-2" 
                labelDefault="View in a different timezone" :defaultTimezone="displayTimezone" @updateTimezone="updateTimezone"></TimeZones>
                <a v-if="!isToday" class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="today">This week</a>
              </div>
          </div>
        </div>
      </StickyBar>

      <div class="full-width center-content calendar-wrap full-width-layout">
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

            <ControlBar v-if="viewData!==null" :progressWizard="viewData.wizard_step"></ControlBar>

            <div v-if="fcIsReady">
                <WapModal v-if="bookForAclient" :show="bookForAclient" @hide="hideModal" noscroll>
                  <h4 slot="title" class="modal-title">Choose an action</h4>
                  <h3 class="mb-4" v-if="selectionSingleDay"> {{ startDayDisplay }} - 
                    <span class="text-muted">From {{ startTimeDisplay }} until {{ endTimeDisplay }}</span>
                    <span class="small text-muted" v-if="viewData.buffer_time > 0">includes {{ viewData.buffer_time }}min buffer</span>
                  </h3>
                  <h3 class="mb-4" v-else> {{ shortStDayDisplay }} - {{ shortEdDayDisplay }}</h3>
                  <div class="d-flex flex-column flex-md-row justify-content-between" v-if="!selectedChoice">
                    
                    <div class="btn btn-secondary mr-md-2  align-items-center" @click="confirmNewBooking" :class="{'fdisabled' :!selectionSingleDay}">
                      <div class="dashicons dashicons-admin-users"></div>
                      <div class="text-center">
                        <p class="h6 m-0">Book an appointment</p>
                        <p class="small m-0">On behalf of your client</p>
                      </div>
                    </div>

                    <div class="btn btn-secondary  mr-md-2 align-items-center" @click="confirmFree" :class="{'fdisabled' :(!selectionSingleDay || isAvailable)}">
                      <div class="dashicons dashicons-unlock txt blue"></div>
                      <div class="text-center">
                        <p class="h6 m-0">Open this time</p>
                        <p class="small m-0">Allow new bookings</p>
                      </div>
                    </div>

                    <div class="btn btn-secondary  align-items-center" @click="confirmBusy" :class="{'fdisabled' : isBusy}">
                      <div class="dashicons dashicons-lock txt red"></div>
                      <div class="text-center">
                        <p class="h6 m-0">Block this time</p>
                        <p class="small m-0">Prevent new bookings</p>
                      </div>
                    </div>

                  </div>
                  <div v-else>
                    <BehalfBooking v-if="shownAppointmentForm" :startTime="startTime" :endTime="endTime" :realEndTime="realEndTime" :viewData="viewData"
                      :timezone="displayTimezone" @cancelled="hideModal" @confirmed="confirmedStatus" @updateEndTime="updateEndTime"/>

                    <StatusBusyConfirm v-if="shownBusyConfirm" 
                    :startTime="startTime" :endTime="endTime" :timezone="displayTimezone" :viewData="viewData"  
                    @confirmed="confirmedStatus" @cancelled="hideModal"/>

                    <StatusFreeConfirm v-if="shownFreeConfirm" 
                    :startTime="startTime" :endTime="endTime" :timezone="displayTimezone" :viewData="viewData"
                    @confirmed="confirmedStatus" @cancelled="hideModal"/>
                  </div>

              </WapModal>
              <WapModal v-if="showRegularAv" :show="showRegularAv" @hide="hideRegavModal" large>
                <h4 slot="title" class="modal-title">Modify your Weekly Availability</h4>
                <Regav noback></Regav>
                <button type="button" class="btn btn-secondary btn-lg mt-2" @click="hideRegavModal">Close</button>
              </WapModal>

              <WelcomeModal v-if="showWelcomePopup" 
              @refreshEvents="refreshEvents" @refreshInitValue="refreshInitValue"
              :passviewData="viewData" :selectedDuration="selectedDuration" :passshowWelcomePopup="showWelcomePopup" :totalSlots="totalSlots" />
            </div>
      </div>
    </div>
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
  </div>
</template>
<script>
import Regav from '../RegularAvailability/View'
import abstractView from '../Views/Abstract'
import TimeZones from '../Components/TimeZones'
import ControlBar from './ControlBar'
import FullCalendarWrapper from './FullCalendarWrapper'
import BehalfBooking from './BehalfBooking'
import StatusBusyConfirm from './StatusBusyConfirm'
import StatusFreeConfirm from './StatusFreeConfirm'
import SubscribeNewsletter from '../Wappointment/SubscribeNewsletter'
import momenttz from '../appMoment'

import WelcomeModal from './WelcomeModal'
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
let mixins_object = window.wappointmentExtends.filter('BackendCalendarMixins', {AppointmentRender, MixinRender, MixinBeautify, MixinSelection})
let mixins_array = [Colors]
for (const key in mixins_object) {
  if (mixins_object.hasOwnProperty(key)) {
    mixins_array.push(mixins_object[key])
  }
}

let calendar_components = window.wappointmentExtends.filter('BackendCalendarComponents', {
      SubscribeNewsletter,
      TimeZones,
      ControlBar,
      Regav,
      FullCalendarWrapper,
      BehalfBooking,
      StatusFreeConfirm,
      StatusBusyConfirm,
      FreeSlotsSelector,
      WelcomeModal,
      CalendarSettings
  })

  /**
 * TODO Review moment usage
 */

export default {
  extends: abstractView,
  mixins: mixins_array,
  name: 'calendar',
  components: calendar_components, 

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
    bookForAclient: false,
    selectedChoice: false,
    cancelbgOver: false,
    name: 'calendar',
    namekey: 'calendar',
    windowStaff: window.Staffs,
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
    daysProperties: false,
    serviceEvent: null,
    serviceStatus: null,
    openconfirm: false,
    hasBeenSetCalProps: false,
    queryParameters: undefined,
    showCalSettings: false,
    lockCalSettings: false
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
   getAllDurations(){
     return this.viewData.durations
   },
    isToday(){
      return this.firstDay!== undefined && this.lastDay !== undefined && this.firstDay.unix() < momenttz().unix() && this.lastDay.unix() > momenttz().unix()
    },
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
      return momenttz.tz(this.firstDay,this.displayTimezone).day(7)
    },
    realFirstday() {
      
      if(this.firstDay!== undefined && this.firstDay.unix() > momenttz().unix()) return this.firstDay;
      return momenttz()
    },

    displayTimezone() {
      return (this.selectedTimezone !== undefined) ? this.selectedTimezone : this.timezone
    },
    shortStDayDisplay(){
      return this.startTime.format(this.shortDayFormat+' '+this.viewData.time_format)
    },
    shortEdDayDisplay(){
      return this.endTime.format(this.shortDayFormat+' '+this.viewData.time_format)
    },
    startDayDisplay() {
      return this.startTime.format(this.viewData.date_format)
    },
    startTimeDisplay(){
      return this.formatTime(this.startTime)
    },
    endTimeDisplay(){
      return this.formatTime(this.endTime)
    },
    
    
 },
  methods: {
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
      setTimeout(this.setFullCalOptions.bind(null, this.firstDay.format()), 100);
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
    
    
    hideModal(){
        this.bookForAclient = false
        this.selectedChoice = false
    },
    hideRegavModal(){
      this.showRegularAv = false
      this.refreshEvents()
    },

    startTimeDisplayed() {
      if(this.fcIsReady) {
        if(this.getDate() !== undefined)
          return this.getDate().tz(this.selectedTimezone).day(1).hours(this.minHour)
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
        return this.getDate().tz(this.selectedTimezone).day(7).hours(this.maxHour)
      }
      return undefined
    },
    updateEndTime(newEndTime){
      this.endTime = newEndTime
    },
      updateTimezone(selectedTimezone,initSave = false){
        this.selectedTimezone = selectedTimezone
        momenttz.tz.setDefault(selectedTimezone)
        this.timezone = selectedTimezone
        
        //this.$refs.calendar.option( 'timeZone', selectedTimezone)
        this.writeHistory()

        //if(initSave === false)window.location.reload()
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
        let minutes = this.intervals.minutes + this.viewData.buffer_time
        if(hours >= 1 ) hours = (hours < 10) ? '0' + hours : hours
        else hours = '00'
        minutes = (minutes < 10) ? '0' + minutes : minutes
        
        return hours + ':' + minutes
      },
      loaded(viewData, reloaded = false){
          this.viewData = viewData.data
          if(reloaded === false){
            
            this.openedDays = this.viewData.regav

            this.intervalsCollection = new Intervals(this.viewData.availability)
            this.viewData.time_format = convertDateFormatPHPtoMoment(this.viewData.time_format)
            this.viewData.date_format = convertDateFormatPHPtoMoment(this.viewData.date_format)
            this.setMinAndMax()
            
            
            let initTimezone = (this.queryParameters !== undefined)? this.queryParameters.timezone:this.viewData.timezone
            this.timezone = initTimezone // staff timezone
            this.selectedTimezone = initTimezone // display timezone
            this.showWelcomePopup = this.viewData.showWelcome
            
            this.setInterval(this.getPref('cal_duration', this.viewData.durations[0]))
            this.minHour = this.getPref('cal_minH', 7)
            this.maxHour = this.getPref('cal_maxH', 19)
          }
          
          let defaultDate = false
          if(this.queryParameters !== undefined){
            defaultDate = this.toMoment(this.queryParameters.start.replace(' ','+')).format()
          }
          this.setFullCalOptions(defaultDate)
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
                firstDay: this.viewData.week_starts_on,
                minTime: this.minHour + ':00',
                maxTime: this.maxHour + ':00',  
                navLinks: false, 
                editable:true,
                eventLimit: true,
                events: this.loadingEvents,
                eventAllow: this.eventAllow,
                eventRender: this.eventRender
              }
              
            }
            
            if(defaultDate !== false){
              this.fullCalOption.props.defaultDate = defaultDate
            }
      },
      loadAgain(){
        this.loaded({data:Object.assign({},this.viewData)}, true)
      },
      reload(){
        this.fullCalOption = undefined
        this.fcIsReady = false
        setTimeout(this.loadAgain, 100)
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
      
      openCreateModal() {
        this.disableBgEvent = false
        this.bookForAclient = true
      },

      setMinAndMax(){
            
            for (var dayname in this.openedDays) {
                let timeBlocks = this.openedDays[dayname]

                for (let index = 0; index < timeBlocks.length; index++) {
                    let timeblock = timeBlocks[index]
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
      writeHistory(){
        if(this.firstDay !== undefined){
          window.history.pushState(
          {
            query: {
              page: 'wappointment_calendar',
              start: this.firstDay.format(),
              end: this.lastDay.format(),
              timezone: this.displayTimezone,
            }
          },
          'Calendar week ' + this.firstDay.format() + ' - ' + this.lastDay.format(),
          'admin.php?page=wappointment_calendar&start=' + this.firstDay.format() + '&end=' + this.lastDay.format() + '&timezone=' + this.displayTimezone
        )
        }
        
      },


      today(){
         this.$refs.calendar.fireMethod('changeView', 'timeGridWeek')
         this.$refs.calendar.fireMethod('today')
         this.currentView = 'timeGridWeek'
         //this.resetFirstDay()
         this.refreshEvents()
         
      },
      monthView(){
         this.$refs.calendar.fireMethod('changeView', 'month')
         this.currentView = 'month'
         this.refreshEvents()
         this.resetFirstDay()
      },
      confirmedStatus(){
        this.hideModal()
        this.refreshEvents()
      },
      refreshEvents(){
        this.hasBeenSetCalProps = false
        this.$refs.calendar.fireMethod('refetchEvents')
      },

    }
}
</script>
<style>
@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';
@import '~@fullcalendar/timegrid/main.css';


.ctrlbar{
    transition: all .3s ease-in-out;
    top: 30px;
    opacity: 0;
    position: absolute;
    margin-top: 0 !important;
    right: 10px;
    margin: 0 !important;
    padding: 4px 4px 0 4px;
  }
  .fill-event{
    height: 100%;
    width:100%;
  }
  .fc-event, .fc-bgevent{
    transition: all .3s ease-in-out;
  }
  .cal-title {
    border-radius: .5rem .5rem 0 0;
      min-height: 25px;
  }
  .fc-bgevent .fill-event{
    border-radius: .5rem;
  }
  .fc-event.past-event .fc-resizer{
    display: none;
  }
  
  .fc-event .fill-event:hover .ctrlbar, 
  .fc-bgevent .fill-event:hover .ctrlbar,
    .fc-event .fill-event.hover .ctrlbar, 
  .fc-bgevent .fill-event.hover .ctrlbar{
    top: 0 !important;
    right:0;
    opacity: 1 !important;
  }

  .fc-event-container .fc-event:hover {
    position: absolute;
    min-height: 84px;
    z-index: 3 !important;
  }
.fc-header-toolbar{
    display:none;
  }
  .fc-time-grid .fc-event {
    padding: .5rem;
  }

  .fc-axis.fc-time.fc-widget-content {
      background-color: #fff;
      opacity: 1;
  }
 .fc-time-grid-container, .fc-time-grid {
    height: 100% !important;
  }
  
  .fc-time-grid .fc-slats td {
      height: 3em;
  }
  .fc-unthemed th, .fc-unthemed td, .fc-unthemed thead, .fc-unthemed tbody, .fc-unthemed .fc-divider, .fc-unthemed .fc-row, .fc-unthemed .fc-content, .fc-unthemed .fc-popover, .fc-unthemed .fc-list-view, .fc-unthemed .fc-list-heading td{
    border: 0;
  }
  .fc-unthemed .fc-divider, .fc-unthemed .fc-popover .fc-header, .fc-unthemed .fc-list-heading td {
      background: none;
  }
  
#calendar .fc-event .btn-xs.btn-light {
  font-size: 1rem;
  color: #fff ;
  background-color: transparent;
  border: none !important;
}

#calendar .fc-event .btn-xs.btn-light:hover {
  font-size: 1rem;
  color: rgb(233, 233, 233) ;
  background-color: rgba(255,255,255,.4);
}
#calendar .fc-time-grid .fc-event{
  padding: .3rem;
}
.preview-pop .swal2-content #swal2-content{
  width: 70%;
  text-align: left;
  margin:0 auto;
}
.fc-month-view .fc-day-grid-container.fc-scroller {
    height: auto!important;
    overflow-y: auto;
}

  .fc-bgevent, .fc-event{
    box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,0);
  }
  .fc-event:hover{
    box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.3);
  }
  .fc-bgevent-skeleton .fc-bgevent,
  .fc-content-col .fc-bgevent:hover, 
  .fc-content-col .fc-bgevent.hover, 
  .fc-content-col .fc-bgevent.opening.extra.hover{
    box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.08);
     border: none;
    opacity: 1;
  }
    .fc-content-col .fc-bgevent:hover, 
  .fc-content-col .fc-bgevent.hover, 
  .fc-content-col .fc-bgevent.opening.extra.hover{
    opacity: .8 !important;
  }

  

  .fc-bgevent.debugging{
    opacity: 1;
    border-radius: .5rem;
    z-index: 1;
    background-color: #94d576;
  }




  .fc-bgevent.busy, .fc-bgevent.calendar {
      z-index: 4;
      border-radius: .5rem;
      background-color:rgb(242, 242, 242);
      border: 2px dashed #dadada;
      background-size: auto auto;
      
  }
  .past-event,
  .fc-bg .fc-day.fc-past,
  .fc-time-grid .fc-now-indicator-line,
  .fc-bgevent.busy, .fc-bgevent.calendar,
  .striped-bg{
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 20 20'%3E%3Cg %3E%3Cpolygon fill='%23ffffff' points='20 10 10 0 0 0 20 20'/%3E%3Cpolygon fill='%23ffffff' points='0 10 0 20 10 20'/%3E%3C/g%3E%3C/svg%3E") !important;
  }
  .fc-bgevent.calendar {
      z-index: 3;
  }

  .skel-past .fc-bgevent.calendar {
      display:none;
  }
  

  .fc-bgevent {
      opacity: 1;
  }

  .fc-day-header span, .fc-day-header a{
    font-weight: normal;
  }
  .fc-today span, .fc-today a{
    color: #6664cb;
  }
  .fc-past span,  .fc-past a{
    color:#ccc !important;
  }

  .fc-bg .fc-day, .fc-unthemed td.fc-day.fc-today {
    background-color: #fff;
    border: 1px solid #eaeaea;
  }
  .fc-bg .fc-day.fc-today.fc-past{
    background-color: #f9f9f9;

  }

  .fc-bg .fc-day.fc-past{
    border: 2px solid #f3f3f3;
      z-index: 3;
      border-radius: .5rem;
      background-color: #f9f9f9;
      background-size: auto auto;
  }
  .fc-bgevent.past-event{
    display: none;
  }
  .past-event{
    background-size: auto auto !important;
    opacity: .6 !important;
    background-color: rgb(240, 239, 239) !important;

  }
  .past-event .fc-bg{
    background-color: transparent !important;
     
  }
  .fc-event.past-event {
    border-color: transparent !important;
  }
  
  .past-event:hover{
    opacity: 1 !important;
  }

  .fc-time-grid .fc-bg table, .fc-time-grid .fc-content-skeleton table {
    border-collapse: separate;
    border-spacing: .3rem 0;
  }

  .fc-axis.fc-time.fc-widget-content {
      font-size: .8rem;
  }

  .fc-time-grid-event .fc-content {
     color:#fff;
  }
  .fc-allow-mouse-resize .fc-resizer.fc-end-resizer {
      width: 100%;
      height: 20px;
      left: 0;
      margin-left: 0;
      border-radius: 0 0 8px 8px;
      color: #fff;
      background-color: rgba(0,0,0,.1);
      border: none;
      text-align: center;
      z-index:10;
  }

  .fc-time-grid-event.fc-allow-mouse-resize .fc-resizer::before {
      content: "\f346";
      font-size: 20px;
      line-height: 1;
      font-family: dashicons;
      font-weight: 400;
      font-style: normal;
      text-align: center;
  }
  .fc-time-grid-event.fc-allow-mouse-resize .fc-resizer::after {
      content: "";
      display: none;
  }

  .fc-highlight-container .fc-highlight {
    background-color: transparent;
    border-color: transparent;
  }

.fc-event-container.fc-mirror-container .fc-event{
    background-color: rgba(172, 137, 196, 0.3) !important;
    border-color: rgba(214, 179, 238, 0.3) !important;
    font-size: 1.4rem;
    text-align: center;
}

.fc-event-container.fc-mirror-container .fc-event{
    background-color: rgba(172, 137, 196, 0.3);
    border-color: rgba(214, 179, 238, 0.3);
    font-size: 1.4rem;
    text-align: center;
}

.fc-event-container.fc-mirror-container .fc-event .fc-time{
    color:#776e6e;
}

  .fc-event.past-event {
    cursor:default;
    background-image: none !important;
  }
 
  .fc-bg .fc-day, .fc-unthemed td.fc-day.fc-today, .fc-bgevent.opening, .fc-time-grid .fc-event, .fc-event{
    border-radius: 1rem;
  }

  .fc-not-allowed .fc-event .fc-bg{
    background-color:#222 ;
    border:none;
  }
  .fc-not-allowed .fc-event{
    border:none;
  }
  .fc-not-allowed .fc-event .dashicons-trash::before {
    content: "\f153";
  }


  .swal2-popup .swal2-title{
    padding: 0 1rem;
  }

.fc-slats table tbody tr .fc-axis.fc-time.fc-widget-content span {
    position: relative;
}
.fc-slats table tbody tr .fc-axis.fc-time.fc-widget-content span::after {
    content: " ";
    border-top: 1px solid #a4a4a4;
    width: .2rem;
    display: block;
    position: absolute;
    top: -16px;
    right: -14px;
}

.fc-slats table tbody tr:first-of-type td span::after{
  display: none !important;
}

.wrap {
    display: flex;
    width: 100%;
}
.fc-day-header{
    text-transform: capitalize;
}

.fc-content-skeleton .fc-now-indicator-arrow{
    border-color: #6664cb;
}

.fc-content-skeleton table .fc-now-indicator-arrow{
    border: none;
    border-top-color: #6664cb;
    border-top-style: dashed;
    border-top-width: 2px;
    z-index: 9;
}

.fc-time-grid .fc-now-indicator-line{
  border: 2px solid #f3f3f3;
  border-bottom: 2px dashed #6664cb;
  z-index: 3;
  border-radius: .5rem;
  background-color: #f9f9f9;
  background-size: auto auto;
}

.crib{
  position: absolute;
  display: inline-block;
  text-align: center;
  top:30px;
  left: 10px;
  font-size: .7rem;
  color: #f0f0f0;
  border-top-left-radius: 1rem;
  border-bottom-right-radius: 1rem;
  padding: 0 14px;
  transition: all .3s ease-in-out;
  opacity:0;
  height: 14px;
  max-width: 50%;
  padding: .3rem .8rem;
  box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.3);
}

.fc-event .fill-event:hover .crib, 
.fc-bgevent .fill-event:hover .crib,
.fc-event .fill-event.hover .crib, 
.fc-bgevent .fill-event.hover .crib{
  opacity: 1 !important;
  top: 0px;
  left: 0px;
  overflow: hidden;
  padding-left: .4rem;
  padding-right: 1.2rem;
  color: transparent;
  width: 0;
}
.crib::first-letter{
  font-size:.9rem;
  font-weight:bold;
  color:#fff;
}

.fc-event .fill-event:hover .crib:hover, 
.fc-bgevent .fill-event:hover .crib:hover,
.fc-event .fill-event.hover .crib:hover, 
.fc-bgevent .fill-event.hover .crib:hover{
  overflow: none !important;
  width: 100% !important;
  color: #fff !important;
  height: auto !important;
}

.crib.grey{background: #999;}
.crib.blue{background: #4481c3;}
.crib.red{background: #d13f3f;}
.crib.yel{background: #d1c53f;}
.crib.orange{background: #d1683f;}

.txt.grey{color: #999;}
.txt.blue{color: #4481c3;}
.txt.red{color: #d13f3f;}
.txt.yel{color: #d1c53f;}
.txt.orange{color: #d1683f;}

.fc-event .fc-bg {
    z-index: 1;
    background: rgba(0, 0, 0, 0.01);
    opacity: .25;
    border-radius: 1rem;
}
.fc-event:hover .fc-bg {
    z-index: 6;
    opacity: 1;
}

  .calendar-overflow {
    overflow: auto;
  }
  #calendar {
    min-width: 794px;
    position: relative;
  }

  .btn-default {
      background-color: #f3f3f3;
  }
  .btn:hover {
      text-decoration: none !important;
  }
  .appointment-title:hover {
    text-decoration: underline;
    cursor: pointer;
  }
  .alert.top-right {
      z-index: 9002;
  }
  .calendar-wrap{
    position: relative;
    margin: 0 10px 0 0;
    font-size: 15px;
  }


  h1.h5{
    margin:0 .8rem;
  }
  h1.h2{
    padding:0 .5rem;
  }
  
  

.btn-link {
    color: #4481c3;
}



.flex-md-row .dashicons{
  font-size: 2rem;
  height: 2rem;
  width: 2rem;
}
 #event-undefinedundefined .fc-bg {
  background: #d4d4d4;
  opacity: .7;
}
 #event-undefinedundefined  {
  background: none;
  border: none;
}
 #event-undefinedundefined .fc-time  {
  color: #000;
  font-size: 1.1rem;
}
.nowtime{
  bottom: 0;
  color: #6664cb;
  text-align: center;
  position: absolute;
  width: 100%;
  display: none;
}
.nowtime.show{
  display: block;
}


#calendar .btn-xs .dashicons {
  width: 1rem;
  height: 1rem;
  font-size: 1rem;
}

#calendar .btn-xs.btn-light {
  font-size: 1rem;
  border-radius: 8rem !important;
  padding-top: .1rem !important;
}


.fdisabled{
  cursor: not-allowed !important;
  color: #c8c8c8 !important;  
}

.fdisabled .dashicons.txt{
  color:#c8c8c8 !important;
}

.dd-search-results{
  position: absolute;
  background-color: #f8f9fa;
  padding: 5px;
  box-shadow: 4px 7px 8px 2px rgba(0,0,0,.1);
  z-index: 5;
}

.calendar-wrap .fc-day-header span, .calendar-wrap .fc-day-header a {
    font-weight: normal;
    color: #777676;
}
.svg-inline--fa {
    width: 1.125em;
}
.buffer{
  border: 1px dashed rgb(147, 144, 144);
  background-color: rgb(255, 255, 255);
  border-radius: 0 0 1rem 1rem;
  text-align: center;
  margin-top: .2rem;
  position: absolute;
  bottom: -1px;
  width: 100%;
  left: -1px;
  color: #6d6d6d;
  z-index:2;
}
.cal-title{
  font-size: .8rem;
  padding: .2rem;
  padding-left: 2rem;
  background-color: #d1683f;
  color: #fff;
}


.calendar-wrap .card {
  padding: .5em 1em;
  max-width: 100%;
  border-radius: 2rem;
}

.calendar-wrap .dashicons-testimonial{
  font-size: 5rem;
  width: 8rem;
  height: 100%;
  padding: 3rem 0;
  border-radius: 1rem;
  margin: 0 1rem;
}

.calendar-wrap .welcome-list li{
  margin-bottom:0;
}
  
</style>
