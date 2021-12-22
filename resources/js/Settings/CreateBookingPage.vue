<template>
    <div>
        <div class="d-flex" v-if="booking_page_id === 0">
            <div class="booking-widget-editor-wizard wrapper-widget-style" >
                <Front v-if="showWidget" classEl="wappointment_widget" :attributesEl="params" />
            </div>
            <div  class="ml-4">
                <div>
                    <label v-if="!forceCreation">
                        <input type="checkbox" v-model="bookingpage"> {{ get_i18n( 'wizard_4_create', 'wizard') }}
                    </label>
                    <div v-if="bookingpage || forceCreation">
                        <div class="d-flex">
                            <InputPh v-model="page.title" :ph="get_i18n('bwe_page_edit', 'common')"/>
                            <div role="button" v-if="!editpagedetails" class="btn btn-link btn-xs" @click="editpagedetails=true">{{ get_i18n( 'bwe_page_edit', 'common') }}</div>
                        </div>
                        <div v-if="editpagedetails">
                            <InputPh v-model="page.slug" :ph="get_i18n('bwe_page_slug', 'common')"/>
                            <div>
                                <input type="radio" id="publish" v-model="page.status" value="publish">
                                <label for="male">Publish</label>
                                <input type="radio" id="draft"  v-model="page.status" value="draft">
                                <label for="female">Draft</label>
                            </div>
                        </div>
                        <div class="my-2">{{ get_i18n( 'bwe_widget_settings', 'common') }}</div>
                        <div v-if="bookingpage || forceCreation" class="pl-4 small" >
                            <ShortcodeGenerator @change="updateShortCode" title="Book now" :preview="false" :simple="true"/>
                        </div>
                        <button v-if="save" class="btn btn-primary btn-lg btn-block" @click="createPage">{{ get_i18n( 'bwe_create_bp', 'common') }}</button>
                    </div>
                </div>
                
            </div>
        
        </div>
        <div v-else>
            <h5 class="text-primary">{{ get_i18n( 'bwe_page_bp_exist_already', 'common') }}</h5>
            <a :href="'post.php?post='+booking_page_id+'&action=edit'" target="_blank" class="btn btn-secondary"> 
                <span class="dashicons dashicons-edit"></span> {{ get_i18n( 'bwe_page_edit', 'common') }}
            </a>
            <a :href="page_link" target="_blank" class="btn btn-secondary"> 
                <span class="dashicons dashicons-calendar-alt"></span> {{ get_i18n( 'bwe_page_view', 'common') }}
            </a>
        </div>
    </div>
</template>
<script>
import Front from '../Front'
import abstractView from '../Views/Abstract'
import ShortcodeGenerator from './ShortcodeGenerator'
import WPPagesService from '../Services/WP/Pages' 
export default {
    extends: abstractView,
    props: {
        page_id: {
            type:Number,
            default: 0
        }, 
        page_link: {
            type:String,
        },
        widgetDefault: {
            type:Object,
        },
        save: {
            type:Boolean,
            default: false
        }, 
        forceCreation: {
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
                week:false,
                demoAs:true,
                center:false
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
        Front,
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
                this.settingSave('booking_page', this.booking_page_id)
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
.wrapper-widget-style{
    box-shadow: inset 0px 0px 10px 0 rgba(0,0,0,.2);
    padding: 1rem;
    border-radius: 2rem;
}
</style>