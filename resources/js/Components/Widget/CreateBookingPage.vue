<template>
    <div class="d-flex">
        <div class="booking-widget-editor-wizard">
            <Widget v-if="showWidget" :wizard="true" :params="params"></Widget>
        </div>
        <div v-if="booking_page_id === 0">
            <div>
                <label><input type="checkbox" v-model="bookingpage"> Create a booking page</label>
                    <div v-if="bookingpage">
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
                        <div class="my-2">Widget's settings</div>
                        <div v-if="bookingpage" class="pl-4 small" >
                            <ShortcodeGenerator @change="updateShortCode" :title="widgetDefault.button.title" :preview="false"/>
                        </div>
                        <button v-if="save" class="btn btn-secondary" @click="createPage">Create Page</button>
                    </div>
            </div>
            
        </div>
        <div v-else>
            <a :href="'post.php?post='+booking_page_id+'&action=edit'" target="_blank" class="btn btn-link btn-xs" >Edit booking page</a>
        </div>
    </div>
</template>
<script>
import Widget from '../../Views/Subpages/Widget'
import abstractView from '../../Views/Abstract'
import ShortcodeGenerator from './ShortcodeGenerator'
import WPPagesService from '../../Services/WP/Pages' 
export default {
    extends: abstractView,
    props: {
        page_id: {
            type:Number,
            default: 0
        }, 
        widgetDefault: {
            type:Object,
        },
        save: {
            type:Boolean,
            default: false
        }, 
    },
    data() {
        return {
            bookingpage: false,
            showWidget: true,
            params: {
                autoOpen:false,
                largeVersion:false,
                week:false
            },
            editpagedetails:false,
            booking_page_id: 0,
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
        this.booking_page_id = this.page_id
        if(this.booking_page_id === 0){
            this.bookingpage = true
        }
    },
    components: { 
        Widget,
        ShortcodeGenerator,
        InputPh: window.wappoGet('InputPh') 
    },

    watch:{
        bookingpage(){
            this.$emit('canSave', this.bookingpage)
        }
    },

    methods: {

        createPage() {
            this.request(this.createPageRequest,  undefined,undefined,false,  this.successCreatedPage)
        },
        async createPageRequest() {
            return await this.serviceWPPages.call('create',  Object.assign({},this.page))
        },
        successCreatedPage(r){
            if(r.data!==undefined && r.data.id !== undefined && r.data.id > 0){
                this.booking_page_id = r.data.id
                this.$emit('saved', this.booking_page_id)
            }   
        },
        updateShortCode(shortcode, params){
            this.page.content = `<!-- wp:shortcode -->${shortcode}<!-- /wp:shortcode -->`
            this.showWidget = false
            setTimeout(this.delaychangeRefresh.bind(null,params), 100);
        },

        delaychangeRefresh(params){
            for (const key in params) {
                if (params.hasOwnProperty(key)) {
                    this.params[key] =  params[key]
                }
            }
            this.showWidget = true
            
        },

        
  } 

}
</script>
<style>
.booking-widget-editor-wizard{
    width: 600px;
}
</style>