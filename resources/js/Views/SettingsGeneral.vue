<template>
  <div>
    <div  v-if="dataLoaded">
      <div class="reduced">

          <div>
              <div class="card cardb p-2 px-3  d-flex flex-row justify-content-between" @click="goToRegav">
                  <span class="h5 my-1">
                    <span v-if="viewData.is_availability_set" class="dashicons dashicons-yes text-success" ></span> 
                    <span v-else class="dashicons dashicons-no text-danger" ></span> 
                    Weekly availability</span>
                  <button  class="btn btn-xs btn-secondary hidden">{{ isSetupLabel(viewData.is_availability_set) }}</button>
              </div>
              <div class="card cardb p-2 px-3  d-flex flex-row justify-content-between"  @click="goToService">
                <span class="h5 my-1">
                  <span v-if="viewData.is_service_set" class="dashicons dashicons-yes text-success" ></span> 
                    <span v-else class="dashicons dashicons-no text-danger" ></span>
                   Service setup</span>
                <button class="btn btn-xs btn-secondary hidden">{{ isSetupLabel(viewData.is_service_set) }}</button>
              </div>
              <div class="card cardb p-2 px-3  d-flex flex-row justify-content-between"  @click="goToWidgetSetup">
                <span class="h5 my-1">
                  <span v-if="viewData.is_widget_set" class="dashicons dashicons-yes text-success" ></span> 
                    <span v-else class="dashicons dashicons-no text-danger" ></span>
                   Booking Widget setup</span>
                <button class="btn btn-xs btn-secondary hidden">{{ isSetupLabel(viewData.is_widget_set) }}</button>
              </div>
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
                              <input id="date-format" class="form-control" v-model="viewData.date_format" @change="changed('date_format')" size="5" type="text">
                            </div>

                            <div class="input-group  input-group-sm">
                              <input id="date-time-union" class="form-control" v-model="viewData.date_time_union" @change="changed('date_time_union')" size="3" type="text">
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
                          <div class="date-preview"> <span class="font-weight-bold small">{{ date_example }}</span> </div>
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
      
    </div>
    <WapModal v-if="showModal" :show="showModal" @hide="hideModal" :classExtra="getExtraClass" large noscroll>
      <h4 slot="title" class="modal-title"> 
        <span v-if="showRegav">Modify your Weekly Availability</span>
        <span v-if="showService">Service Edit</span>
        <span v-if="showWidget">Booking Widget setup</span>
        <span v-if="showEditPage">Edit Cancel/Reschedule page</span>
      </h4>

      <Regav v-if="showRegav" ></Regav>
      <Service v-if="showService" @close="hideModal"></Service>
      <div v-if="showWidget" class="mt-4">
        <Widget></Widget>
      </div>
      <div v-if="showEditPage" class="mt-4">
        <EditCancelPage></EditCancelPage>
      </div>
      <button v-if="!showService && showRegav" type="button" class="btn btn-secondary btn-lg mt-2" @click="hideModal">Close</button>
    </WapModal>
  </div>
</template>

<script>
import abstractView from './Abstract'

import Helpers from '../Standalone/helpers'
import momenttz from '../appMoment'
import Regav from './Subpages/Regav'
import Service from './Subpages/Service'
import Widget from './Subpages/Widget'
import EditCancelPage from './Subpages/EditCancelPage'
export default {
  extends: abstractView,
  components: { Service, Regav,Widget , EditCancelPage},
  data() {
    return {
      viewName: 'settingsgeneral',
      showModal: false,
      showRegav: false,
      showService: false,
      showWidget: false,
      showEditPage: false,
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
    };
  },
  computed:{
    getExtraClass(){
      return this.showWidget ? 'biggerPop':''
    },
    date_example(){
      return momenttz().tz(this.viewData.timezone).format(
        (new Helpers()).convertPHPToMomentFormat(this.viewData.date_format + '['+this.viewData.date_time_union+']' + this.viewData.time_format))
    }
  },
  methods: {
    
    hideModal(){
      this.showModal = false
      this.showRegav = false
      this.showService = false
      this.showWidget = false
      this.showEditPage = false
    },
    goToRegav() {
      this.showModal = true
      this.showRegav = true
    },
    goToService() {
      this.showModal = true
      this.showService = true
    },
    goToWidgetSetup() {
      this.showModal = true
      this.showWidget = true //widgetDefault!==null
    },
    EditTextPage(){
      this.showModal = true
      this.showEditPage = true
    },
    
    day_example(dformat){
      return momenttz().tz(this.viewData.timezone).format( (new Helpers()).convertPHPToMomentFormat(dformat) )
    },
    time_example(tformat){
      return momenttz().tz(this.viewData.timezone).format( (new Helpers()).convertPHPToMomentFormat(tformat) )
    },
    changeDDP(date, key = 'date_format'){
      this.viewData[key] = date
      this.toggle(key)
      this.changed(key)
    },
    
    toggled(element){
      return this.isToggled[element]
    },
    toggle(element){
      this.isToggled[element] = !this.isToggled[element]
    },
    changed(key) {
      this.settingSave(key, this.viewData[key]);
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
</style>
