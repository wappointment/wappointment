<template>
  <div>
    <topPane :nextEnabled="true" @next="nextStep" @back="prevStep" :step="currentStep" :total="totalStep"></topPane>
    <div class="container-fluid" v-if="viewData">
        <div class="col-12">
            <h1 class="wp-heading-inline">Booking Widget Setup</h1>
            <p class="text-muted"><small>You can edit the style and content of the booking widget later from <strong>Wappointment > Settings > General</strong></small></p>
            <div class="d-flex">
                <div class="booking-widget-editor-wizard">
                    <Widget v-if="showWidget" :wizard="true" :params="params"></Widget>
                </div>
                <div v-if="booking_page_id === false">
                    <div>
                        <label><input type="checkbox" v-model="bookingpage"> Create a booking page</label>
                            <div class="d-flex">
                                <InputPh v-model="page.title" ph="Page title"/>
                                <div role="button" v-if="!editpagedetails" class="btn btn-link btn-xs" @click="editpagedetails=true">Edit page</div>
                            </div>
                            <div v-if="editpagedetails">
                                <InputPh v-model="page.slug" ph="Page slug"/>
                                <div>
                                    <input type="radio" id="publish" v-model="page.status" value="publish">
                                    <label for="male">Publish</label>
                                    <input type="radio" id="draft"  v-model="page.status" value="draft">
                                    <label for="female">Draft</label>
                                </div>
                            </div>
                    </div>
                    <div class="my-2">Widget's settings</div>
                    <div v-if="bookingpage" class="pl-4 small" >
                        
                        <ShortcodeGenerator @change="updateShortCode" :title="widgetDefault.button.title" :preview="false"/>
                    </div>
                </div>
                <div v-else>
                   <a :href="'post.php?post='+booking_page_id+'&action=edit'" target="_blank" class="btn btn-link btn-xs" >Edit booking page</a>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import wizardLayout from '../abstractWizardLayout'
import BookingWidgetEditor from '../../Components/BookingWidgetEditor'
import Widget from './Widget'
import ShortcodeGenerator from '../../Components/Widget/ShortcodeGenerator'
import WPPagesService from '../../Services/WP/Pages' 
export default {
    extends: wizardLayout,
    data() {
        return {
            currentStep: 3,
            totalStep: 3,
            colors: '#ffffff',
            viewName: 'wizardwidget',
            bookingpage: true,
            params: {
                autoOpen:false,
                largeVersion:false
            },
            editpagedetails:false,
            showWidget: true,
            serviceWPPages: null,
            booking_page_id: false,
            page:{
                title:'Booking page',
                slug: 'booking-page',
                status: 'publish',
                content: ''
            }
        } 
    },
    created(){
        this.serviceWPPages = this.$vueService(new WPPagesService)
    },
    components: { 
        Widget, 
        ShortcodeGenerator,
        InputPh: window.wappoGet('InputPh') 
        },
    computed: {
        widgetDefault(){
            return (this.viewData !== null && this.viewData.widget !== undefined)?  this.viewData.widget:null
        },
        getBgColor(){
            return (this.viewData !== null && this.viewData.bgcolor !== undefined)?  this.viewData.bgcolor:'#fff'
        }
    },
    methods: {
        loaded(viewData){
            this.viewData = viewData.data
            this.booking_page_id = this.viewData.booking_page_id
            if(this.viewData.booking_page){
                this.page = this.viewData.booking_page
            }
        },
        createPage() {
            this.request(this.createPageRequest,  undefined,undefined,false,  this.successCreatedPage)
        },
        async createPageRequest() {
            let params = Object.assign({},this.page)

            return await this.serviceWPPages.call('create', params)
        },
        successCreatedPage(r){

            if(r.data!==undefined && r.data.id !== undefined && r.data.id > 0){
                this.booking_page_id = r.data.id
            }
            this.saveStepToNext()
        },
        updateShortCode(shortcode, params){
            this.showWidget = false

            setTimeout(this.delayUpdate.bind(null,shortcode, params), 100);
        },
        delayUpdate(shortcode, params){
            this.page.content = shortcode
            for (const key in params) {
                if (params.hasOwnProperty(key)) {
                    this.params[key] =  params[key]
                }
            }
            this.showWidget = true
        },

        nextStep() {
            if(this.bookingpage){
                this.createPage()
            }else{
                this.saveStepToNext()
            }
        },

        saveStepToNext(){
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
<style>
.booking-widget-editor-wizard{
    width: 600px;
}
</style>

