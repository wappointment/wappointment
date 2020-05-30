<template>
  <div>
    <topPane :nextEnabled="true" @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep"></topPane>
    <div class="container-fluid" v-if="viewData">
        <div class="col-12">
            <h1 class="wp-heading-inline">Booking Widget Setup</h1>
            <p class="text-muted"><small>You can edit the style and content of the booking widget later from 
                <strong>Wappointment > Settings > General</strong></small>
            </p>
            
            <CreateBookingPage ref="createpage" :widgetDefault="widgetDefault" :page_id="booking_page_id" 
            @canSave="canSaveTriggered" @saved="saveStepToNext"/>
        </div>
    </div>
  </div>
</template>

<script>
import wizardLayout from '../abstractWizardLayout'
import CreateBookingPage from '../../Components/Widget/CreateBookingPage' 
export default {
    extends: wizardLayout,
    data() {
        return {
            currentStep: 3,
            totalStep: 3,
            colors: '#ffffff',
            viewName: 'wizardwidget',
            canSave: false,
            booking_page_id: 0
        } 
    },

    components: { 
        CreateBookingPage 
    },
    computed: {
        widgetDefault(){
            return (this.viewData !== null && this.viewData.widget !== undefined)?  this.viewData.widget:null
        },
    },
    methods: {
        loaded(viewData){
            this.viewData = viewData.data
            this.booking_page_id = parseInt(this.viewData.booking_page_id)
            if(this.viewData.booking_page){
                this.page = this.viewData.booking_page
            }
        },
        
        nextStep() {
            if(this.canSave){
                this.$refs.createpage.createPage()
            }else{
                this.saveStepToNext()
            }
        },

        canSaveTriggered(canSave){
            this.canSave = canSave
        },

        saveStepToNext(page_id = false){
            this.booking_page_id = page_id
            this.setPrevStep()
            this.request(this.nextStepRequest, undefined,undefined,false,  this.redirectNext)
        },

        async nextStepRequest() {
            return await this.service.call('wizard', {step: this.currentStep+1, booking_page_id: this.booking_page_id})
        },

        redirectNext(){
            this.$WapModal()
            .request(this.sleep(4000))
            window.location = window.apiWappointment.base_admin + '?page=wappointment_calendar'
        },
  } 

}
</script>


