<template>
    <WapModal :show="showWelcomePopup" @hide="hideWelcome">
        <h1 slot="title" class="modal-title">{{ get_i18n( 'wizard_5_title', 'wizard') }}</h1>
        <div >
            <div class="mt-4 p-3 text-center">
                <h2>{{ get_i18n( 'wizard_5_welcome', 'wizard') }}</h2>
                <div class="d-flex">
                    <img :src="getInstalledImg" class="mb-3 rounded img-fluid mr-4" alt="See how your calendar works">
                    <div class="sm-box align-self-start text-left text-muted rounded bg-light p-2 pl-5">
                        <p class="h6">{{ get_i18n( 'wizard_5_tips', 'wizard') }}</p>
                        <ol class="ml-0 my-3">
                            <li>{{ get_i18n( 'wizard_5_tip1', 'wizard') }}</li>
                            <li>{{ get_i18n( 'wizard_5_tip2', 'wizard') }}</li>
                            <li>{{ get_i18n( 'wizard_5_tip3', 'wizard') }}</li>
                            <li>{{ get_i18n( 'wizard_5_tip4', 'wizard') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </WapModal>
</template>

<script>
import BookingPageButton from '../Settings/BookingPageButton'
import WappointmentService from '../Services/V1/Wappointment'
import abstractView from '../Views/Abstract'
export default {
    extends: abstractView,
    props: ['passviewData', 'selectedDuration', 'passshowWelcomePopup', 'totalSlots' ],
    components: { BookingPageButton, InputPh: window.wappoGet('InputPh'),},
    data: () => ({
        showWelcomePopup: false,
        serviceWappointment: null,
  }),
  created(){
    this.serviceWappointment = this.$vueService(new WappointmentService)
    this.showWelcomePopup = this.passshowWelcomePopup
  },
  computed: {
    getInstalledImg(){
        return window.apiWappointment.apiSite + '/plugin/' + window.apiWappointment.version + '/installed.gif'
    }
  },
  methods: {
    hideWelcome(){
        this.sendIgnore()
    },

    sendIgnore(){
      this.request(this.sendIgnoreRequest, false, undefined,false,  this.closeAndRefreshWelcome)
    },
    async sendIgnoreRequest() {
        return await this.serviceWappointment.call('sendignore')
    },

    closeAndRefreshWelcome(e){
      this.showWelcomePopup = false
      this.$emit('refreshEvents')
    },
    
  }
}
</script>
<style>
.sm-box{
    width : 325px;
}
</style>