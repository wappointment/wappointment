<template>
  <div class="container m-4">
    <h1>{{ get_i18n( 'wizard_1_title', 'wizard') }}</h1>
    <div class="d-flex align-items-center welcome-wrapper">
      <div class="mr-4">
        <h3>{{ get_i18n( 'wizard_1_lets', 'wizard') }}</h3>
        <div v-if="suggest_ugly_links" class="bg-danger p-4 rounded">
          <div class="h5 text-white">Your site has permalinks "ON" but your server is not properly configured to handle them. </div>
          <div class="h6 text-white">You should configure either your server or switch to ugly permalinks. <a href="https://wordpress.org/support/article/using-permalinks/" target="_blank">Read about permalinks</a></div>
          <div class="d-flex p-2">
            <a href="options-permalink.php" target="_blank" class="btn btn-secondary mr-2">Change permalinks configuration</a>
            <a href="admin.php?page=wappointment_calendar&wappo_ugly_permalinks=1#/wizard1" class="btn btn-primary" >Continue With Wappointment Anyway</a>
          </div>
          
        </div>
        <div v-else>
            <p class="mt-4">
            <button class="btn btn-primary btn-lg btn-block" @click="wizardStep1">{{ get_i18n( 'wizard_1_start', 'wizard') }}</button>
          </p>
          <div class="small">
            <div>{{ get_i18n( 'wizard_1_ask_help1', 'wizard') }}</div>
            <div>{{ get_i18n( 'wizard_1_ask_help2', 'wizard') }}<a href="https://wappointment.com/support" target="_blank">https://wappointment.com/support</a></div>
          </div>
        </div>
      </div>
      <img :src="getWelcomeImg" class="img-fluid" :alt="get_i18n( 'wizard_1_title', 'wizard')">
    </div>
    <Notifications v-if="installationErrors.length > 0" :messages="installationErrors" :title="mainInstallationError"></Notifications>
    
  </div>
</template>

<script>

import AppService from '../../Services/V1/App' // your service
import abstractview from '../Abstract'
const Notifications = () => import(/* webpackChunkName: "wappo-notif" */ '../../WP/Notifications')

export default {
  extends: abstractview,
    data: () => ({
        service: null,
        installationErrors: [],
        mainInstallationError: '',
        suggest_ugly_links: false
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
        
        if(e.response.status === 404){
          // pretty permalinks configure in WP but are not well configured on the server
          // suggest switching to ugly ones
          this.suggest_ugly_links = true
        }
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
    },
    computed: {
      getWelcomeImg(){
        return window.apiWappointment.apiSite + '/plugin/' + window.apiWappointment.version + '/welcome.gif'
      }
    },
}
</script>
<style >
.welcome-wrapper{
  width: 480px;
  background: #f1ebff;
  border-radius: .25rem;
  padding: 1rem;
}
</style>
