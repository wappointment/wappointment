<template>
  <div v-if="dataLoaded">
      <div class="reduced">

          <div>
              <div class="card cardb p-2 px-3" @click="goToSync">
                  <div class="d-flex flex-row justify-content-between">
                    <span class="h5 my-1">
                      <span v-if="viewData.is_calendar_sync_set">
                        <span class="dashicons dashicons-yes text-success" ></span> 
                        Personal Calendar Connected
                      </span>
                      <span v-else>
                        <span class="dashicons dashicons-no text-danger" ></span> 
                        Connect your personal calendar
                      </span>
                    </span>
                    <button  class="btn btn-xs btn-secondary hidden">{{ isSetupLabel(viewData.is_calendar_sync_set) }}</button>
                  </div>
                  <p v-if="viewData.is_calendar_sync_set" class="small text-muted m-0 ml-4">Last checked: {{ lastChecked }} | Last changed: {{ lastChanged }} | Process duration: {{ calDuration }}</p>
                  
              </div>
              <div class="card p-2 px-3">
                <div class="h5">Advanced settings</div>
                <hr/>
                <!-- <div class="d-flex mb-2">
                  <div>
                    <label for="week-starts-on" class="m-0">Week starts on</label>
                    <div class="small text-muted">In Admin Calendar view</div>
                  </div>
                  <div class="ml-4">
                      <weekDays id="week-starts-on" classN="form-control" :selected="viewData.week_starts_on" @changed="changedDay"></weekDays>
                  </div>
                </div> -->
                <div class="d-flex mb-2">
                    
                    <label class="form-check-label" for="allow-wappointment">
                        <div class="d-flex align-items-center">
                          <input type="checkbox" v-model="viewData.wappointment_allowed" id="allow-wappointment" @change="changedWappointmentAllowed()">
                          <div>Allow connection to wappointment.com</div>
                        </div>
                        <div class="small text-muted">Used to display the Addons page</div>
                    </label>

                  </div>
                <hr/>
                <div class="mt-3">
                  <button class="btn btn-secondary btn-sm" @click="startResetConfirm"><span class="dashicons dashicons-image-rotate"></span> Reset Installation</button>
                </div>
              </div>
              
          </div>
      </div>
      <WapModal v-if="showModal" :show="showModal" @hide="hideModal" large noscroll>
        <h4 slot="title" class="modal-title"> 
          <span v-if="showSync">Connect Personal calendar</span>
        </h4>

        <Sync @savedSync="savedSync" noback></Sync>
    
        <button type="button" class="btn btn-secondary btn-lg mt-2" @click="hideModal">Close</button>
      </WapModal>
  </div>
</template>

<script>
import abstractView from './Abstract'
import CalUrl from '../Modules/CalUrl'
import Sync from './Subpages/Sync'
import weekDays from "../Components/weekDays";
export default {
  extends: abstractView,
  mixins: [CalUrl],
  components: {Sync, weekDays},
  data() {
    return {
      viewName: 'settingssync',
      showSync: false,
      showModal: false
    };
  },
  methods: {
    async resetInstallation() {
        return await this.service.call('freshinstall')
    },
    startResetConfirm() {
        this.$WapModal().confirm({
          title: 'Do you really want to reset Wappointment?',
          content: 'All your data(appointments, settings, etc...) will be lost'
        }).then((result) => {
          if(result === true){
              this.request(this.resetInstallation,  undefined, this.redirectReset)
          } 
        })
    },

    hideModal(){
      this.showSync=false;
      this.showModal = false;
    },
    goToSync() {
        this.showSync=true;
        this.showModal = true;
        //this.$router.push({ name: "sync" });
    },
    savedSync(){
      this.hideModal()
      this.refreshInitValue()
    },
    redirectReset(){
        this.$WapModal()
            .request(this.sleep(4000))
          window.location = '/wp-admin/admin.php?page=wappointment_calendar'
    },
    changedDay(value){
      this.viewData['week_starts_on'] = value
      this.changed('week_starts_on')
    },
    changedWappointmentAllowed(){
      
      this.changed('wappointment_allowed')
      window.apiWappointment.allowed = this.viewData.wappointment_allowed
    },
    changed(key) {
      this.settingSave(key, this.viewData[key]);
    },
  }
};
</script>
