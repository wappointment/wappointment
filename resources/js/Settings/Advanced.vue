<template>
  <div v-if="dataLoaded">

    <BreadCrumbs v-if="crumbs.length>0" :crumbs="crumbs" @click="goTo"/>
    <component v-if="currentView !== false" :is="currentView" :key="subCompKey" 
    :subview="subview" v-bind="dynamicProps" @updateCrumb="updateCrumb" />
    
    <div class="reduced" v-else>
        <div class="card p-2 px-3">
          <div class="h5">Scheduling preferences</div>
          <hr/>
          <div>
            
            <div class="row my-2">
              <label for="approval-mode" class="col-sm-3">Approval mode</label>
              <div class="col-sm-4">
                <select class="form-control" id="approval-mode" @change="changedVD('approval_mode')" v-model="viewData.approval_mode">
                  <option value="1">Automatic</option>
                  <option value="2">Manual</option>
                </select>
              </div>
            </div>

            <div class="row mb-2">
                  <label for="date-format" class="col-sm-3 dateformatlabel"> Date format</label>
                  <div class="col-sm-9">
                    <div class="d-flex">
                      <div class="input-group input-group-sm">
                        <div class="input-group-prepend" :class="{show: toggled('date_format')}">
                          <button class="btn btn-secondary dropdown-toggle" type="button" @click="toggle('date_format')">Date</button>
                          <div class="dropdown-menu" :class="{show: toggled('date_format')}">
                            <span v-for="date in date_formats" class="dropdown-item" @click="changeDDP(date)">{{ date }} ({{ day_example(date) }})</span>
                          </div>
                        </div>
                        <input id="date-format" class="form-control" v-model="viewData.date_format" @change="changedReload('date_format')" size="5" type="text">
                      </div>

                      <div class="input-group  input-group-sm">
                        <input id="date-time-union" class="form-control" v-model="viewData.date_time_union" @change="changedReload('date_time_union')" size="3" type="text">
                      </div>

                      <div class="input-group  input-group-sm">
                        <div class="input-group-prepend" :class="{show: toggled('time_format')}">
                          <button class="btn btn-secondary dropdown-toggle" type="button" @click="toggle('time_format')">Time</button>
                          <div class="dropdown-menu" :class="{show: toggled('time_format')}">
                            <span v-for="timef in time_formats" class="dropdown-item" @click="changeDDP(timef, 'time_format')">{{ timef }} ({{ time_example(timef) }})</span>
                          </div>
                        </div>
                        <input id="time-format" class="form-control" v-model="viewData.time_format" @change="changedReload('time_format')" size="5" type="text">
                      </div>
                    </div>
                    <div class="date-preview"> <span class="font-weight-bold small">{{ viewData.today_formatted }}</span> </div>
                  </div>
            </div>
            <div class="d-flex mb-2">
              <div>
                <label for="week-starts-on" class="m-0">Week starts on</label>
                <div class="small text-muted">In Admin Calendar view</div>
              </div>
              <div class="ml-4">
                  <weekDays id="week-starts-on" classN="form-control" :selected="viewData.week_starts_on" @changed="changedDayStart"></weekDays>
              </div>
              <div class="d-flex align-items-center ml-2"> 
                <input type="checkbox" v-model="viewData.frontend_weekstart" id="frontend_weekstart" @change="changedVD('frontend_weekstart')">
                <div class="small text-muted">Apply to frontend </div>
              </div>
          </div> 
            <div class="mb-2">
              <label class="form-check-label" for="hrs-before-allowed">
                <div class="d-flex align-items-center">Appointments can be booked up to 
                  <div class="input-group-sm mx-2">
                    <input id="hrs-before-allowed" v-model="viewData.hours_before_booking_allowed" 
                    @change="changedVD('hours_before_booking_allowed')" class="form-control min-field" size="2" type="text">
                  </div> hrs before it starts</div>
              </label>
            </div>
            <div class="mb-2">
              
              <label class="form-check-label" for="allow-cancel">
                <div class="d-flex align-items-center">
                  <input type="checkbox" v-model="viewData.allow_cancellation" id="allow-cancel" @change="changedVD('allow_cancellation')">
                    Allow clients to cancel <a v-if="viewData.allow_cancellation" href="javascript:;" class="ml-2" @click="EditTextPage">edit page's text</a>
                </div>
              </label>
              <div class="d-flex align-items-center small text-muted ml-2" v-if="viewData.allow_cancellation">Let client cancel up to 
                    <div class="input-group-sm mx-2">
                      <input v-model="viewData.hours_before_cancellation_allowed"
                      @change="changedVD('hours_before_cancellation_allowed')"  class="form-control min-field" size="2" type="text">
                    </div> hrs before appointment</div>
            </div>
            <div class="mb-2">
              
              <label class="form-check-label" for="allow-reschedule">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.allow_rescheduling" id="allow-reschedule" @change="changedVD('allow_rescheduling')">
                    Allow clients to reschedule <a v-if="viewData.allow_rescheduling" href="javascript:;" class="ml-2" @click="EditTextPage">edit page's text</a>
                  </div>
              </label>
              <div class="d-flex align-items-center small text-muted ml-2" v-if="viewData.allow_rescheduling">Let client reschedule up to 
                    <div class="input-group-sm mx-2">
                      <input v-model="viewData.hours_before_rescheduling_allowed" 
                      @change="changedVD('hours_before_rescheduling_allowed')" class="form-control min-field" size="2" type="text">
                </div> hrs before appointment</div>
            </div>

            <div class="mb-2">
                  
                
                <div class="d-flex align-items-center">
                  
                    <label class="form-check-label w-100" for="video_link">
                      <div class="min-label">
                    <input type="checkbox" v-model="video_link_edit" id="video_link" >
                     Video Meeting link is accessible X minutes before the appointment starts
                     </div>
                     </label>
                    
                  <FormFieldDuration v-if="showVideoLink" v-model="viewData.video_link_shows" @change="changedVidLink"/>
                </div>
                <div class="small text-muted">If you want to restrict when your clients get access to the Video meeting url</div>
                

            </div>
            
            <div class="d-flex mb-2">
                  
                  <label class="form-check-label w-100" for="buffer_time">
                  <div class="d-flex align-items-center">
                    <div class="min-label">Buffer time</div>
                    <FormFieldDuration v-model="viewData.buffer_time"  @change="changedBuffer"/>
                  </div>
                  <div class="small text-muted">Time(in minutes) reserved to prepare your next appointment</div>
                  </label>

              </div>
              <div class="d-flex mb-2" v-if="viewData.payment_active">
                  
                  <label class="form-check-label w-100" for="buffer_time">
                  <div class="d-flex align-items-center">
                    <div class="min-label">Auto cancel pending appointment</div>
                    <FormFieldDuration v-model="viewData.clean_pending_every"  @change="changedCleaningPending"/>
                  </div>
                  <div class="small text-muted">Pending appointments are automatically cancelled after {{ viewData.clean_pending_every }} min without payment completion </div>
                  </label>

              </div>
          </div>
        </div>
        <div class="card p-2 px-3">
          <div class="h5">Edge use cases</div>
          <hr/>
          
          <div class="mb-2">
            <label class="form-check-label" for="max-active-booking" data-tt="Limit the number of active appointments a client can book with his email address">
              <div class="d-flex align-items-center">
                <input id="max-active-booking-active" 
                  v-model="maxBookings" @change="toggleMaxBookings" type="checkbox" >Limit active bookings per client 
                <div class="input-group-sm mx-2">
                  <input v-if="maxBookings" id="max-active-booking" v-model="viewData.max_active_bookings" 
                  @change="changedMaxActive" class="form-control min-field" size="2" type="text">
                  <input id="max-active-per-staff" 
                  v-model="viewData.max_active_per_staff" @change="changedVD('max_active_per_staff')" type="checkbox" >Limit per staff
                </div> </div>
            </label>
          </div>
          <div class="mb-2">
              <label class="form-check-label" for="allow-autofill" data-tt="If a user is logged in on your site, we'll prefill the booking form's fields">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.autofill" id="allow-autofill" @change="changedVD('autofill')">
                    Autofill booking form for logged in users
                  </div>
              </label>
          </div>
          <div class="mb-2">
              <label class="form-check-label" for="allow-forcemail" data-tt="If a user is logged in on your site, we'll force his account email address and hide the email field">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.forceemail" id="allow-forcemail" @change="changedVD('forceemail')">
                    Force user account's email
                  </div>
              </label>
          </div>
          <div class="mb-2">
            <label class="form-check-label" for="allow-cache">
              <div class="d-flex align-items-center" data-tt="Runs availability requests faster">
                <input type="checkbox" v-model="viewData.cache" id="allow-cache" @change="changedVD('cache')">
                Use Wappointment's cache
                <button v-if="viewData.cache" class="ml-2 btn btn-secondary btn-sm" @click="refreshCache">
                  Refresh cache
                </button>
              </div>
            </label>
          </div>
          <div class="mb-2">
              <label class="form-check-label" for="allow-staffcf" data-tt="Create fields describing your staff to be used in emails and SMS">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.allow_staff_cf" id="allow-staffcf" @change="changedVD('allow_staff_cf')">
                    Allow staff's advanced description
                  </div>
              </label>
          </div>
          <div class="mb-2">
            <label class="form-check-label" for="allow-calendar_handles_free">
              <div class="d-flex align-items-center" data-tt="Certain calendar app such as Outlook live allow you to set a time where you are free, with that option, we will set open your availability whenever we spot those.">
                <input type="checkbox" v-model="viewData.calendar_handles_free" id="allow-calendar_handles_free" @change="changedVD('calendar_handles_free')">
                Recognize FREE status in .ICS import
              </div>
            </label>
            <div v-if="viewData.calendar_handles_free" class="ml-4 mt-2">
                <label class="form-check-label" for="allow-calendar_ignores_free">
                  <div class="d-flex align-items-center" data-tt="When a FREE event is spotted, we just ignore it">
                    <input type="checkbox" v-model="viewData.calendar_ignores_free" id="allow-calendar_ignores_free" @change="changedVD('calendar_ignores_free')">
                    Ignore FREE events
                  </div>
                </label>
            </div>
          </div>

          <div class="mb-2" v-if="!wooIsActive">
            <label class="form-check-label" for="allow-invoice">
              <div class="d-flex align-items-center" data-tt="More data will be added to the receipt sent in the confirmation email">
                <input type="checkbox" v-model="viewData.invoice" id="allow-invoice" @change="changedVD('invoice')">
                Enable Invoice data
              </div>
            </label>
            <div v-if="viewData.invoice" class="ml-4 mt-2">
              Seller's data
              <textarea class="form-control"  v-model="viewData.invoice_seller" @change="changedVD('invoice_seller')" rows="2"></textarea>
            </div>
            <div v-if="viewData.invoice" class="ml-4 mt-2">
              Order nº
              <input id="hrs-before-allowed" v-model="viewData.invoice_num" @change="changedVD('invoice_num')" size="2" type="text">
            </div>
            <div v-if="viewData.invoice" class="ml-4 mt-2">
              Client's data to appear on invoice
              <FormFieldSelect :multi="true" :horizontal="true" v-model="viewData.invoice_client" :elements="viewData.custom_fields" 
            idKey="namekey" labelSearchKey="name" ph="Select data to appear on bill" @change="changedInvoiceCfield" />
            </div>
          </div>
          
          <div class="mb-2">
              <label class="form-check-label" for="allow-refreshavb" data-tt="Your staff availability gets a new open day after that time">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.allow_refreshavb" id="allow-refreshavb" @change="changedVD('allow_refreshavb')">
                    New available day is opening from 
                    <span v-if="viewData.allow_refreshavb" class="ml-1">  
                      <dayTime :selected="viewData.refreshavb_at" :timeFormat="viewData.time_format" @changed="changedRefreshAVBTime"></dayTime>
                      <small class="text-muted">{{ viewData.timezone }}</small>
                    </span>
                  </div>
              </label>
          </div>
          <div class="mb-2">
              <label class="form-check-label" for="wp-remote" data-tt="When getting errors as such 'cURL error ***'">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.wp_remote" id="wp-remote" @change="changedVD('wp_remote')">
                    Use native WP remote
                  </div>
              </label>
          </div>

          <div class="mb-2">
              <label class="form-check-label" for="jitsi-url" data-tt="Have your own private Jitsi Server? Set its URL here">
                  <div class="d-flex align-items-center">
                    Jitsi private server URL
                    <input type="text" class="ml-2" v-model="viewData.jitsi_url" id="jitsi-url" 
                    @keyup.enter.prevent="changedFromModel('jitsi_url')" 
                    @focusout="changedFromModel('jitsi_url')">
                  </div>
              </label>
          </div>

          <div class="mb-2">
              <label class="form-check-label" for="availability-fluid" data-tt="Mostly has an impact on same day bookings, if applied, we will look for the earliest available time by 5min increments">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.availability_fluid" id="availability-fluid" @change="changedVD('availability_fluid')">
                    Availability fluid
                  </div>
              </label>
          </div>

          <div class="mb-2">
              <label class="form-check-label" for="starting-times" data-tt="Offer more starting times, each 5 min, 10min, 15 min etc ..., useful for long lasting services to offer more flexibility">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.more_st" id="starting-times" @change="changedVD('more_st')">
                    Add more starting times
                    <span class="ml-2 d-flex align-items-center" v-if="viewData.more_st"><div>Start each</div> 
                    <HoursDropdown :elements="[5, 10, 15, 20, 30, 60]" :current="viewData.starting_each" :funcDisplay="funcDisplay" @selected="changeMoreSt"/>
                    </span>
                  </div>
              </label>
          </div>

          
          <div>
            <label for="roles-allowed" class="m-0">WordPress' users listed for calendars creation</label>
            <div class="small text-muted">In Wappointment > Settings > Calendars & Staff</div>
            <FormFieldSelect :multi="true" :horizontal="true" v-model="viewData.calendar_roles" :elements="viewData.all_roles" 
            idKey="key" labelSearchKey="name" ph="Select roles allowed" @change="changedRoles" />
          </div>
          <div class="mt-3">
            <a v-if="viewData.front_page_type == 'page'" :href="'post.php?post='+viewData.front_page_id+'&action=edit'" target="_blank">
              Edit Reschedule/Cancel page
            </a>
            <button v-else class="btn btn-secondary btn-sm" @click="updatePage" data-tt="Only if you don't like the default page template for cancellation and rescheduling">
                Make Reschedule/Cancel page editable
            </button>
            
          </div>
          <div class="mt-3">
            <button class="btn btn-secondary btn-sm" @click="showHealth=true" >
                Show health
            </button>
          </div>

          <div class="mt-3">
            <span v-if="viewData.manager_added">
              Manager role is available
            </span>
            <button v-else class="btn btn-secondary btn-sm" @click="addManagerRole" data-tt="Only if you need a manager without administrator capabilities in your staff">
                Add manager role
            </button>
          </div>
            <span v-if="viewData.big_prices">
              Big prices allowed
            </span>
            <button v-else class="btn btn-secondary btn-sm" @click="addBigPrices" >
                Allow Big Prices range
            </button>
          
        </div>

        <div class="card p-2 px-3">
          <div class="h5">Notifications</div>
          <hr/>
          <div class="d-flex align-items-center">
                <InputValueCards ph="Email notifications are sent to" v-model="viewData.email_notifications" @changed="changedVD('email_notifications')" /> 
          </div>
          <div class="d-flex align-items-center mt-2">
            <Checkbox :value="viewData.weekly_summary"  @changed="changedCheck('weekly_summary')"></Checkbox>
            <label class="form-check-label">
            Weekly summary <span v-if="viewData.weekly_summary">every 
              <weekDays id="weekly-summary"  :selected="viewData.weekly_summary_day" @changed="changedDay"></weekDays> at 
              <dayTime :selected="viewData.weekly_summary_time" @changed="changedTime" :timeFormat="viewData.time_format"></dayTime> 
              <small class="text-muted">{{ viewData.timezone }}</small></span>
            </label>
          </div>
          <div class="d-flex align-items-center mt-2">
            <Checkbox :value="viewData.daily_summary"  @changed="changedCheck('daily_summary')"></Checkbox>
            <label class="form-check-label" >
            Daily summary <span v-if="viewData.daily_summary"> at 
              <dayTime :selected="viewData.daily_summary_time" :timeFormat="viewData.time_format" @changed="changedDayTime"></dayTime>
              <small class="text-muted">{{ viewData.timezone }}</small></span>
            </label>
          </div>
          <div class="d-flex align-items-center mt-2">
            <Checkbox :value="viewData.notify_new_appointments"  @changed="changedCheck('notify_new_appointments')"></Checkbox>
            <label class="form-check-label">
            New Appointments 
            </label>
          </div>
          <div class="d-flex align-items-center mt-2">
            <Checkbox :value="viewData.notify_pending_appointments"  @changed="changedCheck('notify_pending_appointments')"></Checkbox>
            <label class="form-check-label">
            Pending Appointments 
            </label>
          </div>
          <div class="d-flex align-items-center mt-2">
            <Checkbox :value="viewData.notify_canceled_appointments"  @changed="changedCheck('notify_canceled_appointments')"></Checkbox>
            <label class="form-check-label">
            Cancelled Appointments 
            </label>
          </div>
          <div class="d-flex align-items-center mt-2">
            <Checkbox :value="viewData.notify_rescheduled_appointments"  @changed="changedCheck('notify_rescheduled_appointments')"></Checkbox>
            <label class="form-check-label">
            Rescheduled Appointments 
            </label>
          </div>
          <div class="mt-4">
            <hr/>
            <NotificationEmail :status="viewData.mail_status" @configureEmail="goToMailConfig"/>
          </div>
        </div>
          
        <div class="mt-3" >
          <button v-if="viewData.debug !== false" class="btn btn-danger btn-sm" @click="startResetConfirm">
            <span class="dashicons dashicons-image-rotate"></span> Uninstall
          </button>
        </div>
        <Health v-if="showHealth" />
    </div>

  </div>
</template>

<script>
import FormFieldDuration from '../Form/FormFieldDuration'
import abstractView from '../Views/Abstract'
import momenttz from '../appMoment'
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import hasBreadcrumbs from '../Mixins/hasBreadcrumbs'
import EditCancelPage from '../Views/Subpages/EditCancelPage'
import weekDays from "../Components/weekDays"
import dayTime from "../Components/dayTime"
import Checkbox from '../Fields/Checkbox'
import LabelMaterial from '../Fields/LabelMaterial'
import MailConfig from '../Components/MailConfig'
import NotificationEmail from '../Notification/Email'
import InputValueCards from '../Fields/InputValueCards'
import FormFieldSelect from '../Form/FormFieldSelect'
import Health from '../WP/Health'
import HoursDropdown from '../Fields/ButtonMenu'
export default {
  extends: abstractView,
  props:['tablabel'],

  components: {
    FormFieldDuration, 
    EditCancelPage, 
    weekDays,
    dayTime,
    Checkbox,
    LabelMaterial,
    MailConfig,
    NotificationEmail,
    InputValueCards,
    FormFieldSelect,
    Health,
    HoursDropdown
  },
  mixins: [ hasBreadcrumbs],
  data() {
    return {
      viewName: 'settingsadvanced',
      maxBookings:false,
      showHealth:false,
      isToggled: {
        date_format : false,
        time_format : false,
      },
      date_formats : [
        'F j, Y',
        'Y-m-d',
        'm/d/Y',
        'd/m/Y'
      ],
      time_formats : [
        'g:i a',
        'g:i A',
        'H:i',
        'H\\hi',
      ],
      video_link_edit: false,
    };
  },
  created(){
    this.mainCrumbLabel = this.tablabel
  },
  watch:{
    video_link_edit(val){
      if(val === false){
        this.viewData.video_link_shows = 0
        this.changedVidLink(0)
      }
    }
  },
  computed:{
    showVideoLink(){
      return this.video_link_edit || parseInt(this.viewData.video_link_shows) > 0
    }
  },
  methods: {
    changeMoreSt(selected){
      return this.changed(selected, 'starting_each')
    },
    changedVidLink(val){
      return this.changed(val, 'video_link_shows')
    },
    changedBuffer(val){
      return this.changed(val, 'buffer_time')
    },
    changedCleaningPending(val){
      return this.changed(val, 'clean_pending_every')
    },
    funcDisplay(element){
        return this.sprintf_i18n('regav_min', 'common', element)
    },
    changedMaxActive(){
      if(this.viewData.max_active_bookings<1){
        this.maxBookings = false
      }
      this.changedVD('max_active_bookings', this.viewData.max_active_bookings>0? this.viewData.max_active_bookings:0)
    },
    toggleMaxBookings(){
      let bookings = this.maxBookings? 1:0
      this.viewData.max_active_bookings = bookings
      this.changed(bookings, 'max_active_bookings')
      
    },
    loaded(viewData){
        this.viewData = viewData.data
        this.$emit('fullyLoaded')
        this.video_link_edit = parseInt(this.viewData.video_link_shows) > 0
        this.maxBookings = parseInt(this.viewData.max_active_bookings) > 0
    },
    EditTextPage(){
      this.setCrumb('EditCancelPage', 'Edit Cancel/reschedule page', 'EditTextPage')
    },
    day_example(dformat){
      return momenttz().tz(this.viewData.timezone).format( convertDateFormatPHPtoMoment(dformat) )
    },

    time_example(tformat){
      return momenttz().tz(this.viewData.timezone).format( convertDateFormatPHPtoMoment(tformat) )
    },

    changeDDP(date, key = 'date_format'){
      this.viewData[key] = date
      this.toggle(key)
      //this.changedVD(key)
      this.changedReload(key)
    },

    changedReload(key){
      this.changedVD(key)
      this.refreshInitValue()
    },

    changedDayStart(value){
      this.viewData['week_starts_on'] = value
      this.changedVD('week_starts_on')
    },
    
    toggled(element){
      return this.isToggled[element]
    },

    toggle(element){
      this.isToggled[element] = !this.isToggled[element]
    },
    addBigPrices(){
      this.$WapModal().confirm({
          title: 'Are you sure you need this?',
          content: 'Use this option only if you have an issue setting up big price when setting your services'
        }).then((result) => {
          if(result === true){
              this.request(this.addBigPriceRequest,  undefined, undefined,false, this.updateViewData)
          } 
        })
      
    },
    async addBigPriceRequest() {
        return await this.service.call('addbigprice')
    },
    addManagerRole(){
      this.$WapModal().confirm({
          title: 'Are you sure you need this?',
          content: 'This is useful if you have a manager in the team who need to handle your Wappointment\'s settings without being an administrator'
        }).then((result) => {
          if(result === true){
              this.request(this.addManagerRoleRequest,  undefined, undefined,false, this.updateViewData)
          } 
        })
      
    },
    updateViewData(response){
      this.viewData = response.data
    },
    async addManagerRoleRequest() {
        return await this.service.call('addmanagerrole')
    },

    updatePage(){
      this.$WapModal().confirm({
          title: 'Are you sure you need this?',
          content: 'This is useful only to 1% of the users<br/>Basically instead of using a <strong>CPT</strong>(Custom Post Type) for Reschedule, Cancel and View page of your appointment you will use a standard Page'
        }).then((result) => {
          if(result === true){
              this.request(this.updatePageRequest,  undefined, undefined,false, this.updateViewData)
          } 
        })
      
    },
    updateViewData(response){
      this.viewData = response.data
    },
    async updatePageRequest() {
        return await this.service.call('updatepage')
    },
    refreshCache(){
      this.request(this.refreshCacheRequest)
    },
    async refreshCacheRequest() {
        return await this.service.call('refreshcache') 
    },
    startResetConfirm() {
        this.$WapModal().confirm({
          title: 'Do you really want to uninstall Wappointment?',
          content: 'All your data(appointments, settings, etc...) will be lost'
        }).then((result) => {
          if(result === true){
              this.request(this.resetInstallation,  undefined, undefined,false, this.redirectReset)
          } 
        })
    },
     async resetInstallation() {
        return await this.service.call('freshinstall')
    },
    changedRoles(values){
      this.changed(values, 'calendar_roles')
    },
    changedInvoiceCfield(values){
      this.changed(values, 'invoice_client')
    },
    changedVD(key){
      this.changed(this.viewData[key], key)
    },
    changed(value, key) {
      this.settingSave(key, value)
      this.viewData[key] = value
    },
    changedFromModel(key) {
      this.settingSave(key, this.viewData[key])
    },
    redirectReset(){
         this.$WapModal()
            .request(this.sleep(4000))
          window.location = window.apiWappointment.base_admin + '?page=wappointment_calendar'
    },

     goToMailConfig() {
      this.setCrumb('MailConfig', 'Mail Config', 'goToMailConfig')
    },
    mailConfigured(){
      this.viewData.mail_status = true
    },
    changedDay(value){
      this.changedDDPEXT(value, 'weekly_summary_day')
    },
    changedTime(value){
      this.changedDDPEXT(value, 'weekly_summary_time')
    },
    changedDayTime(value){
      this.changedDDPEXT(value, 'daily_summary_time')
    },
    changedRefreshAVBTime(value){
      this.changedDDPEXT(value, 'refreshavb_at')
    },

    changedDDPEXT(value, key){
      this.viewData[key] = value
      this.changedVD(key)
    },
    changedCheck(key){
      this.viewData[key] = !this.viewData[key]
       this.settingSave(key, this.viewData[key])
    },

  }
};
</script>
<style>
.min-label{
  min-width : 20%;
}
.btn-danger{
  color: #fff;
}
[data-active-page='advanced'] .card{
  max-width: none;
}

</style>