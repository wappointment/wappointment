<template>
    <div id="buttons-block">
        <div class="d-flex my-4" >
            <button class="btn btn-secondary" :class="{selected: showWidget}" @click="showArea('widget')"><span class="dashicons dashicons-welcome-widgets-menus"></span> In my widget area</button>
            <button class="btn btn-secondary" :class="{selected: showPost}" @click="showArea('post')"><span class="dashicons dashicons-admin-post"></span> Within my Post or Pages</button>
            <button class="btn btn-secondary" :class="{selected: showTheme}" @click="showArea('theme')"><span class="dashicons dashicons-editor-code"></span> Using PHP snippet in my theme</button>
        </div>
        <div v-if="showWidget">
            <div class="mt-4">
                <VideoIframe src="https://www.youtube.com/embed/h_bWIqOmq0M" />
            </div>
        </div>
        <div v-if="showPost">
            <div  class="form-max">
                <p class="m-0">Copy the shortcode below: </p>
                <ClickCopy :value="getShortCode"></ClickCopy>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div><label><input type="checkbox" v-model="large"> Large version?</label></div>
                        <div><label><input type="checkbox" v-model="open"> Auto-Open Calendar?</label></div>
                    </div>
                    <div>
                        <img :src="previewSCimg" class="img-fluid" width="100">
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <VideoIframe src="https://www.youtube.com/embed/VMi2Ry-JrGA" />
            </div>
        </div>
        <div v-if="showTheme">
            <div class="form-max">
                <p class="m-0">PHP snippet to use in your theme: <span class="text-danger">(for experts only)</span></p>
                <ClickCopy value="<?php wappointment_booking_widget() ?>"></ClickCopy>
            </div>
        </div>
    </div>
</template>

<script>
import ClickCopy from '../Fields/ClickCopy'
import Helpers from '../Modules/Helpers'
import VideoIframe from './VideoIframe'
export default {
    mixins: [Helpers], 
    props:['title'],  
    components: {ClickCopy, VideoIframe}, 
    data: () => ({
        area: '',
        shortcode: '',
        large:false,
        open:false,
    }),
    computed: {
        previewSCimg(){
            let image = 'widget_' + (this.open ? 'cal_':'') + (this.large ? 'lg':'sm') + '.svg'
            return window.apiWappointment.resourcesUrl +'images/' + image
        },
        showWidget(){
            return this.area == 'widget'
        },
        showPost(){
            return this.area == 'post'
        },
        showTheme(){
            return this.area == 'theme'
        },
        getShortCode(){
            let shortcode = 'wap_widget title="'+this.title+'"'
            shortcode += this.large? ' large ':'' 
            shortcode += this.open? ' open ':''
            return '['+shortcode+']'
        }
    },

    methods: {
        showArea(area){
            this.area = area
        },
        selectAll(e){
          e.target.select()
        }
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
</style>