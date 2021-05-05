<template>
    <div id="buttons-block">
        <div class="text-muted mt-4">Insert the booking form in a new page, within an existing page or within a widget area</div>
        <div class="d-flex my-4" >
            <button class="btn btn-secondary btn-cell btn-xs ml-0 mr-2" :class="{selected: showPage}" @click="showArea('page')">
                <span class="dashicons dashicons-welcome-add-page mr-1"></span> Create a new page
            </button>
            <button class="btn btn-secondary btn-cell btn-xs ml-0 mr-2" :class="{selected: showPost}" @click="showArea('post')">
                <span class="dashicons dashicons-shortcode mr-1"></span> Using a Shortcode
            </button>
            <button class="btn btn-secondary btn-cell btn-xs ml-0 mr-2" :class="{selected: showWidget}" @click="showArea('widget')">
                <span class="dashicons dashicons-welcome-widgets-menus mr-1"></span> Using our Widget
            </button>
        </div>
        <div v-if="showPage">
            <div class="mt-4">
                <CreateBookingPage ref="createpage" :forceCreation="true" :save="true" 
                :widgetDefault="widgetDefault" :page_id="booking_page_id" :page_link="page_link" />
            </div>
        </div>
        <div v-if="showPost">
            <ShortcodeDesigner :title="title" :simple="true" />
        </div>
        <div v-if="showWidget">
            <div class="mt-4">
                <VideoIframe src="https://www.youtube.com/embed/h_bWIqOmq0M" />
            </div>
        </div>

    </div>
</template>

<script>
import Helpers from '../Modules/Helpers'
import VideoIframe from '../Ne/VideoIframe'
import CreateBookingPage from '../Settings/CreateBookingPage' 
import ShortcodeDesigner from '../Settings/ShortcodeDesigner' 
export default {
    mixins: [Helpers], 
    props:['title','page_id', 'page_link'],  
    components: {VideoIframe, CreateBookingPage, ShortcodeDesigner}, 
    data: () => ({
        area: 'page',
        booking_page_id: 0,
        widgetDefault:{
            button:{
                title: 'booking-page'
            }
        }
    }),
    created(){
        this.booking_page_id = this.page_id
    },
    computed: {

        showWidget(){
            return this.area == 'widget'
        },
        showPost(){
            return this.area == 'post'
        },
        showPage(){
            return this.area == 'page'
        },
    },
    
    methods: {
        showArea(area){
            this.area = area
        },
        selectAll(e){
          e.target.select()
        },

  } 
}
</script>
<style>
#buttons-block textarea{
    resize: none;
}
.form-max{
    max-width: 350px;
}
#buttons-block .btn-secondary.btn-cell .dashicons{
    position: initial;
}
#buttons-block .btn-secondary.btn-cell{
    display: flex;
    align-items: center;
}

</style>