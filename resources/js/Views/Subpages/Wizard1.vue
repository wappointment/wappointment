<template>
  <div class="container-fluid m-4">
    <h1>Welcome to Wappointment</h1>
    <p class="h6 text-muted">Let's start with a quick setup</p>
    <p class="mt-4">
       <button class="btn btn-primary btn-xl" @click="wizardStep1">Start setup</button>
    </p>
    <Notifications :messages="installationErrors" :title="mainInstallationError"></Notifications>
  </div>
</template>

<script>

import AppService from '../../Services/V1/App' // your service
import abstractview from '../Abstract'
import Notifications from '../../WP/Notifications'

export default {
  extends: abstractview,
    data: () => ({
        service: null,
        installationErrors: [],
        mainInstallationError: '',
    }),
    components: {Notifications},
    created(){
      this.service = this.$vueService(new AppService)
    },
    methods: {
      async wizardStep1Request() {
          return await this.service.call('wizard', {step:'1'})
      },

      wizardStep1() {
          this.installationErrors = []
          this.request(this.wizardStep1Request,  undefined, undefined, false,  this.redirectWizardStep1, this.failedRequest)
      },
      failedRequest(e){
        if(e.response !== undefined){
          for (const key in e.response.data.data.errors.validations) {
            if (e.response.data.data.errors.validations.hasOwnProperty(key)) {
              const errors_sub = e.response.data.data.errors.validations[key];
              for (let i = 0; i < errors_sub.length; i++) {
                const error_sing = errors_sub[i];
                this.installationErrors.push(error_sing)
              }
            }
          }
          this.mainInstallationError = e.response.data.message
        }
        this.serviceError(e)
      },

      redirectLater(){
        this.$router.push({name: 'wappointment_calendar'})
      },

      redirectWizardStep1(){
        this.$router.push({name: 'wizard2'})
      }
    }  
}
</script>
