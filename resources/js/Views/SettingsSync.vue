<template>
  <div v-if="dataLoaded">
      <div class="reduced">
          <button v-if="viewData.oldcal && calendarCount == 0" class="btn btn-sm btn-link my-2" @click="importOldCal" 
          >Refresh Calendar</button>
          <button v-else class="btn btn-sm btn-outline-primary my-3" 
          :class="{'disabled':calendarCountExceeded}" @click="goToSync" 
          >Add Personal Calendar</button> <span v-if="calendarCountExceeded" class="text-danger">4 calendars syncing max allowed</span>
          <div><small>Your calendar(s) will be refreshed every 5 minutes and your availability will be updated accordingly</small></div>
            <div v-if="viewData.is_calendar_sync_set"class="card cardb p-2 px-3 mt-1 unclickable" v-for="(calendar, calendar_id) in viewData.calendar_url" >
                <div class="d-flex flex-row justify-content-between">
                  <div>
                    <div >
                      <span class="dashicons dashicons-yes text-success" ></span> 
                      Calendar {{ calendar_id}}
                      <small class="hidden ml-4 text-primary">{{ calendar}}</small>
                    </div>
                    <p class="vsmall text-muted m-0 ml-4">
                    Last checked: <span class="data-item">{{ lastChecked(calendar_id) }}</span> | 
                    Last changed: <span class="data-item">{{ lastChanged(calendar_id) }}</span> | 
                    Process duration: <span class="data-item">{{ calDuration(calendar_id) }}</span></p>
                  </div>
                  <button  class="align-self-start btn btn-xs btn-link hidden" data-tt="Disconnect Calendar" @click="disconnectCalendar(calendar_id)"><span class="dashicons dashicons-dismiss"></span></button>
                </div>                  
                
            </div>
            <button v-if="calendarCount>0" class="btn btn-sm btn-link my-2" @click="refreshManually" 
          ><span class="dashicons dashicons-update"></span> Refresh all</button>
          
      </div>
      <WapModal v-if="showModal" :show="showModal" @hide="hideModal" large noscroll>
        <h4 slot="title" class="modal-title"> 
          <span v-if="showSync">Connect Personal calendar</span>
        </h4>

        <Sync @savedSync="savedSync" @errorSaving="errorSavingCalendar" noback></Sync>
    
        <button type="button" class="btn btn-secondary btn-lg mt-2" @click="hideModal">Close</button>
      </WapModal>
  </div>
</template>

<script>
import FormFieldDuration from '../Form/FormFieldDuration'
import abstractView from './Abstract'
import CalUrl from '../Modules/CalUrl'
import Sync from './Subpages/Sync'

export default {
  extends: abstractView,
  mixins: [CalUrl],
  props:['tablabel'],
  components: {Sync, FormFieldDuration},
  data() {
    return {
      viewName: 'settingssync',
      showSync: false,
      showModal: false,
      calendarCount: 0
    };
  },
  computed: {
    calendarCountExceeded(){
      return this.calendarCount > 3
    }
  },
  methods: {
    importOldCal(){
      this.request(this.refreshCalendarsRequest, {}, undefined,false, this.importCalendarSuccess)
    },
    refreshManually(){
      this.request(this.refreshCalendarsRequest, {}, undefined,false, this.disconnectCalendarSuccess)
    },
    async refreshCalendarsRequest() {
        return await this.serviceSettingStaff.call('refreshCalendars')
    },

    hideModal(){
      this.showSync=false;
      this.showModal = false;
    },
    goToSync() {
      if(this.calendarCountExceeded) return
        this.showSync=true;
        this.showModal = true;
        //this.$router.push({ name: "sync" });
    },
    disconnectCalendar(calendar_id){
      this.$WapModal().confirm({
          title: 'Confirm calendar disconnection?',
        }).then((response) => {

            if(response === false){
                return
            }
            this.request(this.disconnectCalendarRequest, {calendar_id: calendar_id}, undefined,false, this.disconnectCalendarSuccess)
        }) 
      
    },
    async disconnectCalendarRequest(params) {
        return await this.serviceSettingStaff.call('disconnectCal', params) 
    },
    importCalendarSuccess(response){
      this.$WapModal().notifySuccess(response.data.message)
      this.hideAndRefresh()
    },
    disconnectCalendarSuccess(response){
        this.viewData.calendar_url = response.data.calendar_url
        this.viewData.calendar_logs = response.data.calendar_logs
        this.calendarCount = Object.keys(this.viewData.calendar_url).length
        this.$WapModal().notifySuccess(response.data.message)
    },
    errorSavingCalendar(error){
      this.$WapModal().notifyError(error.response.data.message)
    },
    savedSync(response){
      this.hideAndRefresh()
      this.$WapModal().notifySuccess(response.data.message)
    },
    hideAndRefresh(){
      this.hideModal()
      this.refreshInitValue()
    },
    loaded(viewData){
        this.viewData = viewData.data
        this.calendarCount = Object.keys(this.viewData.calendar_url).length
        this.$emit('fullyLoaded')
    },
    
  }
};
</script>
<style>
.wappointment-wrap p.vsmall{
  font-size:.8rem;
}
.data-item{
  border: 1px solid #d9d9d9;
  border-radius: .25rem;
  padding: .2rem;
  background-color: #f0f0f0;
}
.unclickable{
  cursor:default !important;
}
</style>