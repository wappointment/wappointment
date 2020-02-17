<template>
  <div>
     <StickyBar v-if="fcIsReady">
        <div>
            <div class="d-flex flex-wrap flex-md-nowrap justify-content-between">
              <div class="d-flex">
                <a class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="prevWeek"><</a>
                <h1 class="h2 align-self-center"> {{ weekTitle }} </h1>
                <a class="btn btn-sm btn-secondary align-self-center" href="javascript:;" @click="nextWeek">></a>
                <span class="d-none d-md-block badge badge-secondary align-self-center ml-3" >Free slots {{ totalSlots }}</span>
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
            
            <div class="calendar-overflow" v-if="fullCalOption!==undefined">
              <div id="calendar">
                <FullCalendarWrapper ref="calendar" @isReady="fcIsReady=true" :config="fullCalOption" />
              </div>
            </div>

            <ControlBar v-if="viewData!==null" :progressWizard="viewData.wizard_step"></ControlBar>

            <div v-if="fcIsReady">
                <WapModal v-if="bookForAclient" :show="bookForAclient" @hide="hideModal" noscroll>
                  <h4 slot="title" class="modal-title">Choose an action</h4>
                  <h5 v-if="selectionSingleDay"> {{ startDayDisplay }} - <span class="text-muted">From {{ startTimeDisplay }} until {{ endTimeDisplay }}</span></h5>
                  <h5 v-else> {{ shortStDayDisplay }} - {{ shortEdDayDisplay }}</h5>
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
                    <AdminAppointmentBooking v-if="shownAppointmentForm" :startTime="startTime" :endTime="endTime" :realEndTime="realEndTime" :viewData="viewData"
                      :timezone="displayTimezone" @cancelled="hideModal" @confirmed="confirmedStatus" @updateEndTime="updateEndTime"/>

                    <AdminStatusBusyConfirm v-if="shownBusyConfirm" 
                    :startTime="startTime" :endTime="endTime" :timezone="displayTimezone" :viewData="viewData"  
                    @confirmed="confirmedStatus" @cancelled="hideModal"/>

                    <AdminStatusFreeConfirm v-if="shownFreeConfirm" 
                    :startTime="startTime" :endTime="endTime" :timezone="displayTimezone" :viewData="viewData"
                    @confirmed="confirmedStatus" @cancelled="hideModal"/>
                  </div>

              </WapModal>
              <WapModal v-if="showRegularAv" :show="showRegularAv" @hide="hideRegavModal" large>
                <h4 slot="title" class="modal-title">Modify your Weekly Availability</h4>
                <Regav noback></Regav>
                <button type="button" class="btn btn-secondary btn-lg mt-2" @click="hideRegavModal">Close</button>
              </WapModal>
            </div>
      </div>
    </div>
  </div>
</template>
<script>
import Regav from './Subpages/Regav'
import EventService from '../Services/V1/Event'
import StatusService from '../Services/V1/Status'
import Intervals from '../Standalone/intervals'
import Helpers from '../Standalone/helpers'
import TimeZones from '../Components/TimeZones'
import ControlBar from '../Components/ControlBar'
import FullCalendarWrapper from '../Components/FullCalendarWrapper'
import AdminAppointmentBooking from '../Components/AdminAppointmentBooking'
import AdminStatusBusyConfirm from '../Components/AdminStatusBusyConfirm'
import AdminStatusFreeConfirm from '../Components/AdminStatusFreeConfirm'
import abstractView from './Abstract'
import momenttz from '../appMoment'

import AppointmentRender from './Calendar/AppointmentRender'
let mixins_object = window.wappointmentExtends.filter('BackendCalendarMixins', {AppointmentRender:AppointmentRender})
let mixins_array = []
for (const key in mixins_object) {
  if (mixins_object.hasOwnProperty(key)) {
    mixins_array.push(mixins_object[key])
  }
}

let calendar_components = window.wappointmentExtends.filter('BackendCalendarComponents', {
      TimeZones,
      ControlBar,
      Regav,
      FullCalendarWrapper,
      AdminAppointmentBooking,
      AdminStatusFreeConfirm,
      AdminStatusBusyConfirm
  })
export default {
  extends: abstractView,
  mixins: mixins_array,
  name: 'calendar',
  components: calendar_components, 

  data: () => ({
    fcIsReady: false,
    observer: undefined,
    canLoadEvents: true,
    callback: undefined,
    currentView: 'timeGridWeek',
    
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
    minHour: 7,
    maxHour: 19,
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
  }),

  created(){
    this.serviceEvent = this.$vueService(new EventService)
    this.serviceStatus = this.$vueService(new StatusService)
    if(window.savedQueries!== undefined && [null,undefined].indexOf(window.savedQueries.open_confirm) === -1 && window.savedQueries.open_confirm > 0) {
      this.openconfirm = window.savedQueries.open_confirm
    }
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
      if(this.getThisWeekIntervals === 0 ) return this.getThisWeekIntervals
      return this.getThisWeekIntervals.splits(parseInt(this.viewData.service.duration)*60).totalSlots()
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
    shownAppointmentForm(){
      return this.selectedChoice === 3
    },
    shownBusyConfirm(){
      return this.selectedChoice === 2
    },
    shownFreeConfirm(){
      return this.selectedChoice === 1
    },
    selectionSingleDay(){
      return (this.startTime.day() === this.endTime.day() && 
        this.startTime.month() === this.endTime.month() && 
        this.startTime.year() === this.endTime.year())
    },
    isAvailable(){
      if(this.getThisWeekIntervals!==0) {
        for (let index = 0; index < this.getThisWeekIntervals.intervals.length; index++) {
          const element = this.getThisWeekIntervals.intervals[index]
          if(this.selectIsWithin(element)){
            return true
          }
        }
      }
      return false
    },
    

    isBusy(){
      if(this.getThisWeekIntervals!==0) {
        for (let index = 0; index < this.getThisWeekIntervals.intervals.length; index++) {
          const element = this.getThisWeekIntervals.intervals[index]
          if(
            this.selectWraps(element) ||
            this.selectIntersectsLeft(element) ||
            this.selectIntersectsRight(element) ||
            this.selectIsWithin(element)
          ){
            return false
          }

        }
      }
      
      return true
    },
 },
  methods: {
    selectIsWithin(element){
      let selStart = momenttz.tz(this.startTime.format(), this.timezone)
      let selEnd = momenttz.tz(this.endTime.format(), this.timezone)
      return selStart.unix() >= element.start 
      && selEnd.unix() <= element.end
    },
    selectWraps(element){
      return this.startTime.unix() <= element.start 
      && this.endTime.unix() >= element.end
    },
    selectIntersectsLeft(element){
      return this.startTime.unix() < element.start 
      && this.endTime.unix() > element.start 
      && this.endTime.unix() <= element.end
    },
    selectIntersectsRight(element){
      return this.startTime.unix() >= element.start 
      && this.startTime.unix() < element.end 
      && this.endTime.unix() > element.end
    },
    confirmFree(){
      if(!this.selectionSingleDay || this.isAvailable) return
      if(this.selectionSingleDay){
        this.selectedChoice = 1
      }
    },
    confirmBusy(){
      if(this.isBusy) return
      this.selectedChoice = 2
    },
    confirmNewBooking(){
      if(this.selectionSingleDay){
        this.selectedChoice = 3
      }
    },
    
    hideModal(){
        this.bookForAclient = false
        this.selectedChoice = false
    },
    hideRegavModal(){
      this.showRegularAv = false
      this.refreshEvents()
    },

    hoverIndicatorRegister(){
      let selectedTz = this.selectedTimezone 
      window.jQuery('.fc-content-today').on('mouseover','.fc-now-indicator-line', function(now, e) {
          window.jQuery('.fc-now-indicator-line .nowtime').html(now.tz(selectedTz).format('dddd HH:mm'))
          window.jQuery('.fc-now-indicator-line .nowtime').addClass('show')
      }.bind(null,  momenttz.tz(this.viewData.now,this.viewData.timezone)))

      window.jQuery('.fc-content-today').on('mouseout','.fc-now-indicator-line',() => window.jQuery('.nowtime').removeClass('show'))
    }, 
    hoverIndicatorUnregister(){

      window.jQuery('.fc-content-today').unbind('mouseover')
      window.jQuery('.fc-content-today').unbind('mouseout')
    }, 
    beforeDestroy(){
        this.hoverIndicatorUnregister()
        this.observer.disconnect()
    },
    observeNowIndicator(){
        if(this.observer !== undefined){
            this.hoverIndicatorUnregister()
            this.observer.disconnect()
        }
        this.observer = undefined
        const elements = document.querySelectorAll('.fc-content-skeleton td .fc-content-col')
        let today = false
        for (let i = 0; i < elements.length; i++) {
              if(elements[i].childNodes.length > 0){
                  for (let j = 0; j < elements[i].childNodes.length; j++) {
                      if(Array.from(elements[i].childNodes[j].classList).indexOf('fc-now-indicator-line') !== -1) {
                          today = elements[i]
                          today.classList.toggle('fc-content-today')
                      }
                      if(today !== false) break
                  }
                  
              }
            if(today !== false) break
        }
        if(today !== false){
            // config object
            const config = {
                attributes: false,
                attributeOldValue: false,
                characterData: false,
                characterDataOldValue: false,
                childList: true,
                subtree: false
            };
            // instantiating observer
            this.observer = new MutationObserver(this.observerSubscriber)

            // observing target
            this.observer.observe(today, config)
            this.hoverIndicatorRegister()
        }
    },
    observerSubscriber(mutations) {
        for (let i = 0; i < mutations.length; i++) {
          const mutation = mutations[i]
          if (mutation.addedNodes.length > 0) {
                for (let k = 0; k < mutation.addedNodes.length; k++) {
                  if( Array.from(mutation.addedNodes[k].classList).indexOf('fc-now-indicator-line')!==-1){
                    this.setTodayPastSection()
                  }
                }
            }
        }
    },
    setTodayPastSection(){
      if([0, '0', '0px'].indexOf(window.jQuery('.fc-now-indicator-line').css('top')) === -1){
        window.jQuery('.fc-now-indicator-line').css('height',window.jQuery('.fc-now-indicator-line').css('top'))
        window.jQuery('.fc-now-indicator-line').css('top', 0)
      }

      window.jQuery('<div>', {class: 'nowtime', html:''}).appendTo( window.jQuery( '.fc-now-indicator-line' ) )
        
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
      if(this.fcIsReady) {
        if(this.getDate() !== undefined)
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
        if(initSave === false)this.reload()
        
        //this.refreshEvents()
      },

      setInterval(duration){
        this.intervals.hours = parseInt(duration/ 60)
        this.intervals.minutes = duration % 60
      },
      convertInterval(){
        let hours = this.intervals.hours
        let minutes = this.intervals.minutes
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
            this.viewData.time_format = (new Helpers()).convertPHPToMomentFormat(this.viewData.time_format)
            this.viewData.date_format = (new Helpers()).convertPHPToMomentFormat(this.viewData.date_format)
            this.setMinAndMax()
            this.setInterval(this.viewData.service.duration)
            
            let initTimezone = (window.savedQueries !== undefined)? window.savedQueries.timezone:this.viewData.timezone
            this.timezone = initTimezone // staff timezone
            this.selectedTimezone = initTimezone // display timezone
          }

          if(this.loadedAfter !== undefined) this.loadedAfter()
          this.setFullCalOptions()
      },
      setFullCalOptions(){
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
                slotDuration: intervalString + ':00',
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
                editable: true,
                eventLimit: true,
                events: this.loadingEvents,
                eventAllow: this.eventAllow,
                eventRender: this.eventRender
              }
              
            }
            if(window.savedQueries !== undefined){
              this.fullCalOption.props.defaultDate = this.toMoment(window.savedQueries.start.replace(' ','+')).format()
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
      setDaysProperties(){

        if(this.daysProperties !== false) return
        let daysProperties = []
        window.jQuery('.fc-day.fc-widget-content').each(function( index ) {
          if(window.jQuery(this).hasClass('fc-past') ) daysProperties.push('fc-past')
          else daysProperties.push('')
        })
        this.daysProperties = daysProperties
      },
      setSkeletonProperties(){

        let daysProperties = this.daysProperties
        //console.log('daysProperties',daysProperties)
        window.jQuery('.fc-content-skeleton tr td').each(function( index ) {
          if(window.jQuery(this).hasClass('fc-axis')){

          }else{
            //console.log((index+1)+' '+daysProperties[index+1])
            if(daysProperties[index-1]=='fc-past') {
              window.jQuery(this).addClass('skel-past')
            }
          }
          
        })
      },
      isParentInThePast(element){
        this.setDaysProperties()
        this.setSkeletonProperties()
      },

      eventRender(renderInfo){
        let event = renderInfo.event
        let eventExt = event.extendedProps
        let element = window.jQuery(renderInfo.el)
        this.isParentInThePast(element)
         //'.fc-content-skeleton tr td'
        
          element.attr('data-id', eventExt.dbid)
          element.attr('id', 'event-'+eventExt.type+event.id)
          element.attr('data-rendering', event.rendering)

          
          if(eventExt.onlyDelete!==undefined){
            element.attr('data-only-delete', 1)
          }
          if(eventExt.past !== undefined && eventExt.past === true) {
            element.addClass('past-event')
            
            if(this.isAppointmentEvent(event.rendering)){
                element.find('.fc-time').html(this.getAppointmentHtml(event,element))
                element.append('<div class="fc-bg"></div>')
                element.click(this.cancelClick)
                element.mouseenter(this.EOver)
                element.mouseleave(this.EOut)
              }
          }else{

            if(event.rendering =='background'){
              element.mousedown(this.bgEOut)
              if(eventExt.title !== undefined){
                element.innerhtml = this.getCalendarSyncHtml(event)
              }
              
              element.click(this.cancelClick)
              element.mouseenter(this.bgEOverDelay)
              element.mouseleave(this.bgEOut)
            }else{
              if(this.isAppointmentEvent(event.rendering)){
                element.find('.fc-time').html(this.getAppointmentHtml(event,element))
                element.append('<div class="fc-bg"></div>')
                element.click(this.cancelClick)
                element.mouseenter(this.EOver)
                element.mouseleave(this.EOut)
              }
            }
          }

      },
      getCalendarSyncHtml(event){
        return '<div>'+event.extendedProps.title+'</div>'
      },
      

      EOver(event){
        if(this.disableBgEvent) return false

        this.disableSelectClick = true
        
        this.attachEvent( window.jQuery(event.currentTarget))
        
      },
      EOut(event){
        
        this.disableSelectClick = false

        if(this.disableBgEvent) return false;

        /*window.jQuery(event.target).parent().css('z-index',2)*/
        let el = window.jQuery(event.currentTarget)
        el.removeClass('hover')
        el.find('.fill-event').remove()

      },
      bgEDown(event){ 
        this.hasBeenClicked = window.jQuery(event.currentTarget).attr('data-id')
        event.stopPropagation();
      },

      modifyRegav(event){
        //this.$router.push({ name: 'regav'})
        this.showRegularAv = true;
        event.stopPropagation();
      },
      isAppointmentEvent(datarendering){
        return ['appointment-confirmed', 'appointment-pending'].indexOf(datarendering) !== -1
      },
      isAppointmentPending(datarendering){
        return 'appointment-pending' == datarendering
      },
      isAppointmentConfirmed(datarendering){
        return 'appointment-confirmed' == datarendering
      },
      attachEvent(el) {
        el.addClass('hover')

        if(el.attr('data-only-delete')!==undefined){
          let innerhtml ='<div class="fill-event">'

          if(el.attr('data-rendering')!== undefined && el.attr('data-rendering')=='background'){
            if(el.hasClass('extra')) {
              innerhtml += '<div class="crib blue">Punctual Availability</div>'
            }else{
              if(el.hasClass('busy')){
                innerhtml += '<div class="crib red">Busy</div>'
              }else{
                if(el.hasClass('recurrent')){
                  innerhtml += '<div class="crib orange">Personal Calendar recurs <span class="dashicons dashicons-update"></span></div>'
                }else{
                  innerhtml += '<div class="crib orange">Personal Calendar</div>'
                }
                
              }
              
            }
          }else{
            innerhtml += '<div class="crib yel">Appointment</div>'
          }
          
          if(!el.hasClass('past-event') || (el.hasClass('past-event') && this.isAppointmentEvent(el.attr('data-rendering')))){
            innerhtml += '<div class="d-flex justify-content-center align-items-center mx-4 ctrlbar">'
            if(this.isAppointmentConfirmed(el.attr('data-rendering'))) innerhtml += '<button class="btn btn-xs btn-light viewElement" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-visibility"></span></button>'
            if(this.isAppointmentPending(el.attr('data-rendering'))) innerhtml += '<button class="btn btn-xs btn-light confirmElement" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-yes"></span></button>'
            
            if(!el.hasClass('past-event')){
              if(this.isAppointmentEvent(el.attr('data-rendering'))) {
                innerhtml += '<button class="btn btn-xs btn-light cancelAppointment" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-dismiss"></span></button>'
              }else{
                let spanIcon = el.hasClass('calendar') ? '<span class="dashicons dashicons-controls-volumeoff"></span> ': '<span class="dashicons dashicons-trash"></span>'
                innerhtml += '<button class="btn btn-xs btn-light deleteElement" data-id="'+el.attr('data-id')+'">'+spanIcon+'</button>'
              }
                
            }
            
            innerhtml += '</div>'
          }
          innerhtml += '</div>'// endfill-event
          

          if(this.isAppointmentEvent(el.attr('data-rendering'))){
            //el.find('.fc-content').append(innerhtml)
            el.find('.fc-bg').append(innerhtml)
            el.find('.cancelAppointment').on( "click", this.cancelAppointment)
            el.find('.viewElement').on( "click", this.viewAppointment)
            el.find('.confirmElement').on( "click", this.confirmAppointment)
          } else{
            el.append(innerhtml)
            el.find('.deleteElement').on( "click", this.deleteElement)
          }
          
        }else{
          let innerhtml = '<div class="fill-event"><div class="crib grey">Weekly Availability</div>'
          innerhtml += '<div class="d-flex justify-content-center align-items-center mx-4 ctrlbar"><button class="btn btn-xs btn-light modifyRegav" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-edit"></span></button></div>'
          innerhtml += '</div>' // endfill-event
          el.append(innerhtml)
          el.find('.modifyRegav').on( "click", this.modifyRegav)
        }
        let heightTarget = el.height()

        if(el.find('.ctrlbar').length > 0) {
          let marginTopControls = Math.floor((heightTarget - el.find('.ctrlbar')[0].offsetHeight)/2)

          if(marginTopControls > 0 ){
            el.find('.ctrlbar').css('margin-top', marginTopControls+'px')
          }
        }
        
      },
      bgEOverDelay(event){
        this.cancelbgOver = setTimeout(this.bgEOver.bind('',event),200)
      },
      bgEOver(event,fsdfsd){
        let el =  window.jQuery(event.target)
        //return;
        if(!el.hasClass('fc-bgevent')) return;
        this.parentAttach = el.parent()

        if(this.disableBgEvent) return false;

        if(this.parentAttach.parent('.fc-content-col').find('.fc-highlight-container .fc-highlight').length>0) {
          this.disableBgEvent = true
          return false;
        }
        this.activeBgOverId = el.attr('data-id')
        window.jQuery('[data-id="' + this.activeBgOverId + '"]').addClass('hover')

        this.disableSelectClick = true


        this.attachEvent(el)
        
        el.css('z-index', 9)

        el.attr('data-parent-class', this.parentAttach.attr('class') )
        el.insertAfter(this.parentAttach.parent('.fc-content-col').find('.fc-highlight-container'))
        

      },
      bgEOut(event){
        window.jQuery('[data-id="' + this.activeBgOverId + '"]').removeClass('hover')
        this.activeBgOverId = false
        
        if(this.cancelbgOver) {
          clearTimeout(this.cancelbgOver)
          this.cancelbgOver = false
        }
        if(event.type=='mousedown' && window.jQuery(event.target).hasClass('btn-secondary')) {
          this.$refs.calendar.fireMethod('unselect')
          return false;
        }
        this.disableSelectClick = false

        if(this.disableBgEvent) return false;

        let el = window.jQuery(event.target)
        if(el.attr('data-parent-class') !== undefined){
          this.reAttach(el)
        }else{
          
          let newEl = el.parent()
          if(newEl.attr('class').search("fc-bgevent")!==-1){
            this.reAttach(newEl)
          }else{
            this.reAttach(newEl.parent())
          }
        }
        this.parentAttach = undefined

      },
      reAttach(el){
          el.appendTo(el.parent().find('.'+el.attr('data-parent-class')))
        
          el.removeClass('hover')
          el.find('.fill-event').remove()
          el.css('z-index', '')
          el.removeAttr('data-parent-class')
      },
      
      cancelClick(event){
          event.stopPropagation();
          return false;
      },
      bgEClick(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')

      },
      getEventById(eventId){
        for (let index = 0; index < this.events.length; index++) {
          const element = this.events[index]

          if(element.id !== undefined && element.id == eventId) return element
        }
      },
      mouseEnter(event){
        //this.viewAppointment(event)
        return false
      },
      mouseLeave(event){
        //this.viewAppointment(event)
        return false
      },
      clickEvent(event){
        this.viewAppointment(event)
        return false
      },

      deleteElement(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        this.deleteStatus(eventId)
      },
      cancelAppointment(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        this.cancelRequest(eventId)
      },

      findAppointmentById(eventId){
        let tmp = this.findEventById(eventId, 'appointment')
        return {
          start: tmp.start,
          end:tmp.end,
          extendedProps:tmp,
        }
      },

      findEventById(eventId, type = false){
        for (let index = 0; index < this.events.length; index++) {
          if(type === false && this.events[index]['type']=='appointment') {
            continue //we ignore the appointment events
          }
          if(this.events[index]['dbid']!== undefined && eventId == this.events[index]['dbid']) {
            return this.events[index]
          }
        }
      },

      viewAppointment(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        let appointment = this.findAppointmentById(eventId)

        this.$WapModalOn({
            title:'Appointment details',
            content: this.getAppointmentInfoHTML(appointment)
        })
      },

      confirmAppointment(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        this.confirmRequest(eventId)
      },

      confirmRequest(eventId){
        let appointment = this.findAppointmentById(eventId)
        this.$WapModal().confirm({
          title: 'Do you really want to confirm this appointment?',
          content: window.wappointmentExtends.filter('ConfirmPopup', this.getAppointmentInfoHTML(appointment), {appointment, viewData:this.viewData}) 
        }).then((result) => {
          if(result === true){
              this.request(this.confirmEventRequest, eventId, undefined,false,  this.refreshEvents)
          } 
        })
      },

      cancelRequest(eventId){
        this.$WapModal().confirm({
          title: 'Do you really want to cancel this appointment?',
        }).then((result) => {
          if(result === true){
            this.request(this.cancelEventRequest, eventId,undefined,false,  this.refreshEvents)
          } 
        })
      },

      deleteStatus(eventId){
        let popupData = {
          title: 'Do you really want to delete this calendar event?',
        }
        let event = this.findEventById(eventId)
        if(event.source!=''){
          popupData['title'] = 'Do you really want to mute this calendar event?'
        }
        if(event.options!==undefined && event.options.title!==undefined && event.options.title!=''){
          popupData['content'] = `<h3>${event.options.title}</h3>`
        }
        this.$WapModal().confirm(popupData).then((result) => {
          if(result === true){
              this.request(this.deleteStatusRequest, eventId,undefined,false,  this.refreshEvents)
          } 
          
        })

      },


      getEventById(eventId){
        for (let index = 0; index < this.events.length; index++) {
          const element = this.events[index];
          if(element.id==eventId){
              return element
          }
        }
      }, 
      

      selectAllow(selectInfo){
         if(this.isInThePast(selectInfo)) return false
         return true
      },

      eventDragStart(event){
        //console.log('event drag', event)
        if(event.editable !== true) return false
        this.disableBgEvent = true
      },

      eventDragStop(event ){
        if(event.editable !== true) return false
        this.disableBgEvent = false
      },

      eventPatch(info ){
        let event = info.event
        let delta =info.delta
        let revertFunc = info.revert
        

        //console.log('patch to ', event, this.toMoment(event.start))

        this.$WapModal().confirm({
          title: 'Do you really want to modify this appointment?',
          content: this.getAppointmentInfoHTML(event,delta)
        }).then((result) => {
          if(result === true){
             this.request(this.editEventRequest, {eventId: event.extendedProps.dbid, start: this.toMoment(event.start).unix(), end: this.toMoment(event.end).unix()},undefined,false,  this.refreshEvents)
          }else {
            revertFunc()
          }  
        })

      },
      toMoment(FullCaldateString, timezone = false, debug=false){

/*          if(debug){
            console.log('time is ', FullCaldateString)
           console.log('default timezone before debug',this.timezone)
          console.log('event is ', event)
          //if(this.timezone)console.log(momenttz.tz(FullCaldateString,this.timezone))
          //console.log(momenttz(FullCaldateString,this.timezone))
          console.log(momenttz.tz(FullCaldateString,this.timezone))
          console.log(momenttz.tz(FullCaldateString,this.timezone).format())
        }  */

        return momenttz.tz(FullCaldateString,this.timezone) //momenttz.tz(FullCaldateString, timezone === false ? 'UTC': timezone)
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

        if(this.isInThePast(dropLocation)) return false
        return draggedEvent.extendedProps.allowedit
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
      },

      loadingEvents(fetchInfo, successCallback, failureCallback/* , start, end, timezone, callback */){
        this.callback = successCallback
        let params = {}

        if(window.savedQueries !== undefined){
          this.timezone = window.savedQueries.timezone
          params = {start: this.toMoment(window.savedQueries.start.replace(' ','+')), end: this.toMoment(window.savedQueries.end.replace(' ','+')), timezone: this.timezone}
          this.writeHistory()
          window.savedQueries = undefined
        } else{
          params = {start: this.toMoment(fetchInfo.start), end: this.toMoment(fetchInfo.end), timezone: this.timezone}
        }

        this.firstDay = params.start

        if(this.canLoadEvents){
          this.request(this.getEventsRequest, params,undefined,false,  this.callbackInternal)
          this.canLoadEvents = false
        }
        
      },

      async getEventsRequest(params) {
          return await this.serviceEvent.call('get', {start: params.start.format(), end: params.end.format(), timezone:params.timezone, view: this.currentView})
      },
      
      async cancelEventRequest(eventId) {
          return await this.serviceEvent.call('delete', {id: eventId})
      },
      async confirmEventRequest(eventId) {
          return await this.serviceEvent.call('confirm', {id: eventId})
      },
      async deleteStatusRequest(eventId) {
          return await this.serviceStatus.call('delete', {id: eventId})
      },
      async editEventRequest(params) {
          return await this.serviceEvent.call('patch', {id: params.eventId, start: params.start, end: params.end, timezone: this.displayTimezone})
      },
      
      isLoading(isLoading){
        
        if(this.fcIsReady) {
          //this.resetFirstDay()
        }

        this.observeNowIndicator()
      },
      getDate(){
        return this.toMoment(this.$refs.calendar.fireMethod('getDate'))
      },
      resetFirstDay(){
        this.firstDay = this.getDate().startOf('week')
      },
      nextWeek(){
        this.daysProperties = false
        this.firstDay = this.$refs.calendar.next(this.lastDay)
        
        //this.resetFirstDay()
        this.writeHistory()
        
      },
      
      prevWeek(){
        this.daysProperties = false
        this.firstDay = this.$refs.calendar.prev(this.firstDay)
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
         this.refreshEvents()
         //this.resetFirstDay()
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
        this.$refs.calendar.fireMethod('refetchEvents');
      },

    }
}
</script>
<style>
/*@import '~fullcalendar/dist/fullcalendar.css';*/
@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';
@import '~@fullcalendar/timegrid/main.css';
  .calendar-overflow {
    overflow: auto;
  }
  #calendar {
    min-width: 794px;
  }
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
    min-height: 60px;
    z-index: 3 !important;
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

  h1.h5{
    margin:0 .8rem;
  }
  h1.h2{
    padding:0 .5rem;
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

  .fc-bgevent.opening{
    opacity: 1;
    border: 2px dashed #f2f2f2;
    background-color:rgb(242, 242, 242);
    z-index: 1;
  }

  .fc-bgevent.opening.extra {
      background-color: rgb(242, 242, 242);
      border: 2px dashed #dadada;
      opacity: 1;
      z-index: 2;
  }

  .fc-bgevent.busy, .fc-bgevent.calendar {
      z-index: 4;
      border-radius: 0.5rem;
      background-color:#fff;
      border: 2px dashed #dadada;
      background-size: auto auto;
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
  
  .fc-bg .fc-day.fc-past{
    border: 2px solid #f3f3f3;
      z-index: 3;
      border-radius: 0.5rem;
      background-color: #f9f9f9;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 20 20'%3E%3Cg %3E%3Cpolygon fill='%23ffffff' points='20 10 10 0 0 0 20 20'/%3E%3Cpolygon fill='%23ffffff' points='0 10 0 20 10 20'/%3E%3C/g%3E%3C/svg%3E");
      background-size: auto auto;
  }
  .fc-bgevent.past-event{
    display: none;
  }
  .past-event{
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 20 20'%3E%3Cg %3E%3Cpolygon fill='%23ffffff' points='20 10 10 0 0 0 20 20'/%3E%3Cpolygon fill='%23ffffff' points='0 10 0 20 10 20'/%3E%3C/g%3E%3C/svg%3E")  !important;
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

  .fc-event-container .fc-event, 
  .fc-container .fc-event {
    background-color: #5b447b;
    border-color: #5b447b;
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

  .fc-event-container .fc-event.appointment-pending, 
  .fc-container .fc-event.appointment-pending {
    background-color: #917ae0;
    background-image: linear-gradient(45deg, transparent 25%, #ccabf5 25%, #ccabf5 50%, transparent 50%, transparent 75%, #ccabf5 75%, #ccabf5 100%);
    background-size: 30px 30px;
    border: 2px dashed #dc49e0;
  }

  .fc-event.past-event {
    background-color: #bda7cc !important;
    cursor:default;
    background-image: none !important;
  }
  .fc-event.past-event:hover {
    background-color: #5b447b !important;
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
  border-radius: 0.5rem;
  background-color: #f9f9f9;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 20 20'%3E%3Cg %3E%3Cpolygon fill='%23ffffff' points='20 10 10 0 0 0 20 20'/%3E%3Cpolygon fill='%23ffffff' points='0 10 0 20 10 20'/%3E%3C/g%3E%3C/svg%3E");
  background-size: auto auto;
}

.btn-link {
    color: #4481c3;
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
  padding-top: 0.1rem !important;
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
  
</style>
