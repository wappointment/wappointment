<template>
  <div v-if="dataLoaded">
      <div class="reduced">

          <div class="card p-2 px-3">

              <div class="d-flex mb-2">
                  
                  <label class="form-check-label w-100" for="buffer_time">
                  <div class="d-flex align-items-center">
                    <div class="min-label">Buffer time</div>
                    <FormFieldDuration v-model="viewData.buffer_time" model="buffer_time" @change="changed"/>
                  </div>
                  <div class="small text-muted">Time(in minutes) reserved to prepare your next appointment</div>
                  </label>

              </div>
              <div class="mt-3">
                <a v-if="viewData.front_page_type == 'page'" :href="'post.php?post='+viewData.front_page_id+'&action=edit'" target="_blank">
                  Edit Reschedule/Cancel page
                </a>
                <button v-else class="btn btn-secondary btn-sm" @click="updatePage" data-tt="Only if you don't like the default page template for cancellation and rescheduling">
                   Make Reschedule/Cancel page editable
                </button>
              </div>

              <div v-if="viewData.debug">
                <hr/>
                <div class="mt-3" >
                  <button class="btn btn-danger btn-sm" @click="startResetConfirm">
                    <span class="dashicons dashicons-image-rotate"></span> Uninstall
                  </button>
                </div>
              </div>
            </div>
      </div>
  </div>
</template>

<script>
import FormFieldDuration from '../Form/FormFieldDuration'
import abstractView from './Abstract'

export default {
  extends: abstractView,
  props:['tablabel'],
  components: {FormFieldDuration},
  data() {
    return {
      viewName: 'settingsadvanced',
    };
  },
  methods: {
    async resetInstallation() {
        return await this.service.call('freshinstall')
    },
    updatePage(){
      this.request(this.updatePageRequest,  undefined, undefined,false, this.updateViewData)
    },
    updateViewData(response){
      this.viewData = response.data
    },
    async updatePageRequest() {
        return await this.service.call('updatepage')
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

    disconnectCalendar(calendar_id){
      this.request(this.disconnectCalendarRequest, {calendar_id: calendar_id}, null ,this.disconnectCalendarSuccess, this.disconnectCalendarSuccess,this.disconnectCalendarSuccess)
    },
    async disconnectCalendarRequest(params) {
        return await this.serviceSettingStaff.call('disconnectCal', params) 
    },

    disconnectCalendarSuccess(response){
        this.viewData.calendar_url = response.data.calendar_url
        this.viewData.calendar_logs = response.data.calendar_logs
        this.savedSync()
    },

    savedSync(){
      this.hideModal()
      this.refreshInitValue()
    },

    changed(value, key) {
      this.settingSave(key, value)
    },
    redirectReset(){
         this.$WapModal()
            .request(this.sleep(4000))
          window.location = window.apiWappointment.base_admin + '?page=wappointment_calendar'
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
</style>