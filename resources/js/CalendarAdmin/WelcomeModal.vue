<template>
    <WapModal :show="showWelcomePopup" @hide="hideWelcome">
        <h1 slot="title" class="modal-title">Yayyyy! You're done!</h1>
        <div v-if="!welcomeComplete">
            <h2 class="text-center">Congrats!</h2>
            <h5 class="text-center" v-if="passviewData.booking_page_id">You are ready to get booked</h5>
            <h5 class="text-center" v-else>You are almost set, only you need to setup your booking widget</h5>
            <div class="card d-flex flex-row justify-content-between">
            <div class="h5 my-1 d-flex align-items-center">
                <span class="dashicons dashicons-yes-alt text-success mr-2"></span> 
                <span>Availability is set, you have <strong>{{ totalSlots }} slots available</strong> this week</span>
            </div> 
            </div>
            <div class="card d-flex flex-row justify-content-between">
            <div class="h5 my-1 d-flex align-items-center">
                <span class="dashicons dashicons-yes-alt text-success mr-2"></span> 
                <span>You provide <strong>{{ passviewData.service.name }}</strong> lasting {{ selectedDuration }}min</span>
            </div> 
            </div>
            <BookingPageButton v-if="showWelcomePopup" :title="getTitleShowWelcome" :viewData="passviewData" @saved="savedPage"/>
            <div class="card d-flex flex-row justify-content-between">
            <div class=" my-1 d-flex align-items-center">
                <span class="dashicons dashicons-testimonial text-muted d-none d-sm-block"></span> 
                <div>
                <h3>One last thing!</h3>
                <p>You need to test that emails are working, either you can try to book yourself on your own or we can test it for you.</p>
                <div>
                    <InputPh v-model="getSubscribeEmail" ph="Send result to email"/>
                    <button type="button"  class="btn btn-primary align-self-start" @click.prevent.stop="sendTestBooking">Send test email to Wappointment</button>
                    <div class="ml-2 small text-muted">
                        We will generate a test booking and will reply to the confirmation email if we receive it.
                    </div>
                    <div class="ml-2 small text-muted">We will send you the result on this email : {{ getSubscribeEmail }}</div>
                </div>
                <div class="my-2">
                    <button type="button" class="btn btn-secondary btn-sm" @click.prevent.stop="sendIgnore">Ignore</button>
                </div>
                </div>
            </div> 
            </div>
        </div>
        <div v-else>
            <div class="mt-4 p-3 text-center">
            <h2>Welcome to your Calendar page!</h2>
            <img :src="getInstalledImg" class="mb-3 rounded img-fluid mr-2" alt="See how your calendar works">
            <div class="m-auto sm-box sm-text align-self-center text-left text-muted rounded bg-light p-2 pl-5">
                <p class="h6">From this page you will be able to:</p>
                <ol class="ml-0 my-3">
                    <li> Review your availability and appointments</li>
                    <li> Block times when you're busy </li>
                    <li> Add extra Bookable time </li>
                    <li> Book appointments on behalf of your clients</li>
                </ol>
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
        welcomeComplete: true,
        serviceWappointment: null,
  }),
  created(){
    this.serviceWappointment = this.$vueService(new WappointmentService)
    this.showWelcomePopup = this.passshowWelcomePopup
  },
  computed: {
    getTitleShowWelcome(){
        return this.passviewData.booking_page_id === 0? 'Booking page missing':'Your booking page has been created'
    },
    getSubscribeEmail(){
        return this.passviewData.subscribe_email[0]
    },
    getInstalledImg(){
        return window.apiWappointment.apiSite + '/plugin/' + window.apiWappointment.version + '/installed.gif'
    }
  },
  methods: {
    hideWelcome(){
        this.sendIgnore()
    },
    sendTestBooking(){
      this.request(this.sendTestBookingRequest, false, undefined,false,  this.closeAndRefreshWelcome)
    },
    async sendTestBookingRequest() {
        return await this.serviceWappointment.call('sendtestbooking', {email: this.getSubscribeEmail})
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
    
    savedPage(page_id){
      this.settingSave('booking_page', page_id)
      this.$emit('refreshInitValue')
    },
  }
}
</script>
<style>
.sm-box{
    width : 325px;
}
</style>