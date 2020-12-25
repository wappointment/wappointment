<template>
  <div v-if="dataLoaded">

    <BreadCrumbs v-if="crumbs.length>0" :crumbs="crumbs" @click="goTo"/>
    <component v-if="currentView !== false" :is="currentView" :key="subCompKey" 
    :subview="subview" v-bind="dynamicProps" @updateCrumb="updateCrumb"></component>
    
    <div class="reduced" v-else>
        <LargeButton @click="goToRegav" label="Weekly availability" :is_set="viewData.is_availability_set" ></LargeButton>

        <LargeButton @click="goToDotCom" :is_set="is_dotcom_connected !== false" >
          <div class="d-flex align-items-center" v-if="is_dotcom_connected !== false">
            Connected to
            <div v-if="servicesConnected.length > 0" class="d-flex">
              <div class="ml-2 slot"  :data-tt="serviceDescription(servicekey)" v-for="servicekey in servicesConnected" >
                <img :src="getServiceImage(servicekey)" />
                {{ serviceLabel(servicekey) }}
              </div>
            </div>
          </div> 
          
          <div class="d-flex align-items-center"  v-else>
            Connect to <div class="d-flex" v-if="servicesNotConnected.length > 0">
                        <span class="ml-2 slot"  :data-tt="serviceDescription(servicekey)" v-for="servicekey in servicesNotConnected" >
                          <img :src="getServiceImage(servicekey)" />
                          {{ serviceLabel(servicekey) }}
                        </span>
                      </div>
          </div>
          
        </LargeButton>
        <WapModal v-if="dotcomOpen" :show="dotcomOpen" marge @hide="dotcomOpen = false">
          <h4 slot="title" class="modal-title"> 
            Connect to Zoom, Google Calendar etc...
          </h4>
          <div class="wrapper-dotcom p-4">
            <div class="d-flex justify-content-center align-items-center flex-wrap flex-xl-nowrap">
              <div >
                <h3 class="d-flex align-items-center">
                  <span role="img" class="wstaff-img" :style="styleGravatar"></span>
                  <span>{{ viewData.activeStaffName }}</span>
                </h3>
                <div v-if="is_dotcom_connected">
                  <a class="small" href="javascript:;" @click="refresh" data-tt="Refresh service status">refresh</a> - <a data-tt="Disconnect from Wappointment.com" class="small text-danger" href="javascript:;" @click="disconnectWappo">disconnect</a>
                  
                  <div class="wservices-list text-muted smal">
                    <div class="d-flex">
                      <div class="mr-2 label-title">Enabled services: </div>
                      <div v-if="servicesConnected.length > 0">
                        <div class="slot"  :data-tt="serviceDescription(servicekey)" v-for="servicekey in servicesConnected" >
                          <img :src="getServiceImage(servicekey)" />
                          {{ serviceLabel(servicekey) }}
                        </div>
                      </div>
                    </div>
                    <div class="d-flex text-muted" v-if="servicesNotConnected">
                      <div class="mr-2 label-title">Disabled services: </div>
                      <div v-if="servicesNotConnected.length > 0">
                        <div class="slot disabled" :data-tt="serviceDescription(servicekey)" v-for="servicekey in servicesNotConnected" >
                          <img :src="getServiceImage(servicekey)" />
                          {{ serviceLabel(servicekey) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="small"><a :href="addMoreService" target="_blank" data-tt="Enable new services from your account page on Wappointment.com">Enable/Disable services</a></div>
                </div>
                <div v-else>
                  <div class="mb-3">
                    <InputPh v-model="account_key" ph="Enter account code" /> 
                  </div>
                  <button class="btn btn-primary btn-block btn-lg mb-2" @click="connectToWappo">Connect Account</button>
                  <div class="text-muted">Don't have an account yet? <a :href="createAccount" target="_blank">Create your free account</a></div>
                </div>
              </div>

              <div v-if="!is_dotcom_connected" class="create-account ml-xl-4">
                <h2>Automate your appointments' process</h2>
                <ul>
                  <li>Connect your favourite tools in seconds and automatically:</li>
                  <li>
                    <ol>
                      <li>Create <strong ><img :src="getServiceImage('zoom')" /> Zoom</strong> and <strong ><img :src="getServiceImage('googlemeet')" /> Google Meet</strong> meetings</li>
                      <li>Save appointments in <strong ><img :src="getServiceImage('google')" /> Google Calendar</strong></li>
                      <li>and soon more to come ...</li>
                    </ol>
                  </li>
                  
                  <li>Simply <a :href="createAccount" target="_blank">create an account</a>, <strong>it's free</strong>! </li>
                </ul>
              </div>
            </div>
            
          </div>
        </WapModal>

        <LargeButton @click="goToService" :label="serviceBtnLabel" :is_set="viewData.is_service_set" ></LargeButton>

        <LargeButton @click="goToWidgetSetup" label="Booking Widget Editor" :is_set="viewData.is_widget_set" ></LargeButton>

        <BookingPageButton :viewData="viewData" @saved="savedPage"/>
        
        <div class="card p-2 px-3">
          <div class="h5">Scheduling preferences</div>
          <hr/>
          <div>
            
            <div class="row my-2">
              <label for="approval-mode" class="col-sm-3">Approval mode</label>
              <div class="col-sm-4">
                <select class="form-control" id="approval-mode" @change="changed('approval_mode')" v-model="viewData.approval_mode">
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
                        <input id="time-format" class="form-control" v-model="viewData.time_format" @change="changed('time_format')" size="5" type="text">
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
                <weekDays id="week-starts-on" classN="form-control" :selected="viewData.week_starts_on" @changed="changedDay"></weekDays>
            </div>
          </div> 
            <div class="mb-2">
              <label class="form-check-label" for="hrs-before-allowed">
                <div class="d-flex align-items-center">Appointments can be booked up to 
                  <div class="input-group-sm mx-2">
                    <input id="hrs-before-allowed" v-model="viewData.hours_before_booking_allowed" 
                    @change="changed('hours_before_booking_allowed')" class="form-control min-field" size="2" type="text">
                  </div> hrs before it starts</div>
              </label>
            </div>
            <div class="mb-2">
              
              <label class="form-check-label" for="allow-cancel">
                <div class="d-flex align-items-center">
                  <input type="checkbox" v-model="viewData.allow_cancellation" id="allow-cancel" @change="changed('allow_cancellation')">
                    Allow clients to cancel <a v-if="viewData.allow_cancellation" href="javascript:;" class="ml-2" @click="EditTextPage">edit page's text</a>
                </div>
              </label>
              <div class="d-flex align-items-center small text-muted ml-2" v-if="viewData.allow_cancellation">Let client cancel up to 
                    <div class="input-group-sm mx-2">
                      <input v-model="viewData.hours_before_cancellation_allowed"
                      @change="changed('hours_before_cancellation_allowed')"  class="form-control min-field" size="2" type="text">
                    </div> hrs before appointment</div>
            </div>
            <div class="mb-2">
              
              <label class="form-check-label" for="allow-reschedule">
                  <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="viewData.allow_rescheduling" id="allow-reschedule" @change="changed('allow_rescheduling')">
                    Allow clients to reschedule <a v-if="viewData.allow_rescheduling" href="javascript:;" class="ml-2" @click="EditTextPage">edit page's text</a>
                  </div>
              </label>
              <div class="d-flex align-items-center small text-muted ml-2" v-if="viewData.allow_rescheduling">Let client reschedule up to 
                    <div class="input-group-sm mx-2">
                      <input v-model="viewData.hours_before_rescheduling_allowed" 
                      @change="changed('hours_before_rescheduling_allowed')" class="form-control min-field" size="2" type="text">
                </div> hrs before appointment</div>
            </div>
          </div>
        </div>
    </div>

  </div>
</template>

<script>
import abstractView from './Abstract'
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import momenttz from '../appMoment'
import Regav from '../RegularAvailability/View'
import Service from './Subpages/Service'
import Widget from './Subpages/Widget'
import EditCancelPage from './Subpages/EditCancelPage'
import LargeButton from '../Components/LargeSettingsButton'
import hasBreadcrumbs from '../Mixins/hasBreadcrumbs'
import weekDays from "../Components/weekDays"
import RequestMaker from '../Modules/RequestMaker'
import AbstractListing from './AbstractListing'
import DurationCell from '../BookingForm/DurationCell'
import BookingPageButton from '../Components/Widget/BookingPageButton'

export default {
  extends: abstractView,
  mixins: [ hasBreadcrumbs],
  components: window.wappointmentExtends.filter('SettingsGeneralComponents', { 
     BookingPageButton, Service, Regav, Widget , EditCancelPage, LargeButton, AbstractListing, DurationCell, weekDays
    }, 
    {
      extends: abstractView, 
      mixins: [RequestMaker]
    } ),
  props:['tablabel'],
  created(){
    this.mainCrumbLabel = this.tablabel
  },
  data() {
    return {
      viewName: 'settingsgeneral',
      serviceBtnLabel: window.wappointmentExtends.filter('serviceBtnLabel', 'Service setup' ),
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
      dotcomOpen: false,
      account_key: '',
      is_dotcom_connected: false,
    };
  },
  computed: {
    resourcesUrl(){
      return window.apiWappointment.resourcesUrl+'images/'
    },

    styleGravatar(){
        return 'background-image: url("'+this.viewData.gravatar+'");'
    },
    createAccount(){
      return window.apiWappointment.apiSite + '/register'
    },
    addMoreService(){
      return window.apiWappointment.apiSite + '/client/account'
    },
    servicesNotConnected(){
      let servicesNC = ['zoom','google', 'googlemeet']
      if(this.is_dotcom_connected){
        for (let i = 0; i < this.is_dotcom_connected.services.length; i++) {
          const service = this.is_dotcom_connected.services[i]
          servicesNC = servicesNC.filter(e => e.indexOf(service) === -1)
        }
      }
      return servicesNC
    },
    servicesConnected(){
      let servicesC = []
      if(this.is_dotcom_connected){
        for (let i = 0; i < this.is_dotcom_connected.services.length; i++) {
          const service = this.is_dotcom_connected.services[i]
          servicesC.push(service)
          if(service == 'google'){
            servicesC.push('googlemeet')
          }
        }
      }
      return servicesC
    }
  },
  methods: {
    getServiceImage(service){
      switch (service) {
        case 'zoom':
          return this.resourcesUrl+'zoom.png'
        case 'google':
          return this.resourcesUrl+'google-calendar.png'
        case 'googlemeet':
          return this.resourcesUrl+'google-meet.png'
      }
    },
    serviceDescription(service){
      switch (service) {
        case 'zoom':
          return 'Automatically creates Zoom meeting for your new Video Meetings and save the meeting link in Wappointment'
        case 'google':
          return 'Automatically save new appointments to a secondary calendar in your Google Calendar account'
        case 'googlemeet':
          return 'Automatically generates Google Meet meetings for your new Video Meetings and save the meeting link in Wappointment'
      }
    },
    
    serviceLabel(serviceKey){
      switch (serviceKey) {
        case 'zoom':
          return 'Zoom'
        case 'google':
          return 'Google Calendar'
        case 'googlemeet':
          return 'Google Meet'
      }
    },
    loaded(viewData){
          this.viewData = viewData.data
          this.is_dotcom_connected = viewData.data.is_dotcom_connected
          this.autoSelectSection()
          this.$emit('fullyLoaded')
      },
    autoSelectSection(){
      switch (this.$route.name) {
        case 'general_regav':
          return this.goToRegav(false)
        case 'general_zoom_account':
          return this.is_dotcom_connected === false ? this.goToDotCom():false
        default:
          break;
      }
    },
    savedPage(page_id){
      this.settingSave('booking_page', page_id)
      this.refreshInitValue()
    },

    goToDotCom(){
      this.dotcomOpen = true
    },

    goToRegav(click = true) {
      this.setCrumb('Regav', 'Weekly Availaibility', 'goToRegav')
      if(click){
        this.$router.push({name: 'general_regav'})
      }
    },

    goToService() {
      this.setCrumb('Service', this.serviceBtnLabel, 'goToService')
    },
    goToWidgetSetup() {
      this.setCrumb('Widget', 'Booking Widget setup', 'goToWidgetSetup')
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
      this.changedReload(key)
    },

    changedReload(key){
      this.changed(key)
      this.refreshInitValue()
    },

    changedDay(value){
      this.viewData['week_starts_on'] = value
      this.changed('week_starts_on')
    },
    
    toggled(element){
      return this.isToggled[element]
    },

    toggle(element){
      this.isToggled[element] = !this.isToggled[element]
    },

    changed(key) {
      this.settingSave(key, this.viewData[key])
    },

    refresh() {
        this.request(this.refreshWappoRequest,{},null, false,this.successRefreshed)
    },

    async refreshWappoRequest() {
        return await this.service.call('refreshdotcom')
    },
    successRefreshed(response){
        this.is_dotcom_connected = response.data.data.dotcom
    },
    successConnected(response){
        this.is_dotcom_connected = response.data.data.dotcom
    },

    connectToWappo() {
        this.request(this.connectToWappoRequest,{account_key: this.account_key},null, false,this.successConnected)
    },

    async connectToWappoRequest(params) {
        return await this.service.call('connectdotcom', params)
    },
    
    successConnected(response){
        this.is_dotcom_connected = response.data.data
        this.$WapModal().notifySuccess(response.data.message,1)
        window.location.reload()
    },

    disconnectWappo(){
        this.request(this.disconnectToWappoRequest,{},null,false, this.successDisconnected)
    },

    async disconnectToWappoRequest(params) {
        return await this.service.call('disconnectdotcom', params)
    },
    
    successDisconnected(response){
        this.is_dotcom_connected = false
        this.$WapModal().notifySuccess(response.data.message,1)
        window.location.reload()
    },
   
  }
};
</script>
<style>
.dateformatlabel{
  line-height: 1rem;
}
.dropdown-toggle .dropdown-menu {
    position: absolute;
    top: 28px;
    left: 14px;
}
.date-preview {
  background-color: #f2f2f2;
  font-size: 1.1rem;
  color: #7a7575;
  text-align: center;
  border-radius: 0 0 .2rem .2rem;
}
.form-control.min-field {
  width:36px;
}

/*hack for the sticky to work*/
.wapmodal-body.biggerPop {
    min-height: 800px;
    position: relative;
}
.slot {
    border-radius: .3rem;
    font-size: 13px;
}
.slot img{
  max-height: 20px;
}
.slot.disabled img {
    filter:grayscale(1);
}
.slot.disabled:hover img {
    filter:grayscale(0);
}
.btn-lg.btn-block{
  font-weight: bold;
}
.wrapper-dotcom{
  border-radius: .4rem;
  max-width: 850px;
  margin: 0 auto;
}
.create-account {
	background: #d6d5ff;
	padding: .8em;
	border-radius: .4em;
	min-width: 380px;
  max-width: 480px;
}
.zoom-color{
  color: #2d8cff;
}
.google-color {
  color: #3d76bb;
}
.reduced .wapmodal-content.marge {
    margin-right: auto;
    max-width: 960px;
    width: 60%;
}
.wservices-list {
	background-color: #f0f0f0;
	border-radius: .6em;
  padding: 1rem;
  width:300px;
}
.wservices-list .label-title{
  width:116px;
  font-size: .9rem;
}
.slot[data-tt]::before{
  min-width: 300px;
}
.slot[data-tt]::before,
.slot[data-tt]::after{
  bottom: 140%;
  left: 10px;
}
.wservices-list .slot {
    margin-bottom: .9em;
}
</style>
