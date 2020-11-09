<template>
    <div id="buttons-block">
        <div class="d-flex my-4" >
            <button class="btn btn-secondary" :class="{selected: showPost}" @click="showArea('post')"><span class="dashicons dashicons-shortcode"></span> Using a Shortcode</button>
            <button class="btn btn-secondary" :class="{selected: showWidget}" @click="showArea('widget')"><span class="dashicons dashicons-welcome-widgets-menus"></span> Using our Widget</button>
        </div>
        <div v-if="showWidget">
            <div class="mt-4">
                <VideoIframe src="https://www.youtube.com/embed/h_bWIqOmq0M" />
            </div>
        </div>
        <div v-if="showPost">
            <h3>Use the shortcode in your page(s) or post(s) </h3>
            <div  class="d-flex align-items-center">
                
                <div class="shortcode-gen">
                    <ShortcodeGenerator @change="updateShortCode" :title="title"/>
                </div>
                
                <div class="h3 m-4 text-muted"> > </div>
                <div class="ml-4">
                    <p class="m-0">Your shortcode: </p>
                    <ClickCopy :value="shortcode"></ClickCopy>
                </div>
            </div>
            <div class="mt-4">
                <h4>How can I use this shortcode?</h4>
                <VideoIframe src="https://www.youtube.com/embed/VMi2Ry-JrGA" />
            </div>
        </div>

    </div>
</template>

<script>
import ClickCopy from '../Fields/ClickCopy'
import ShortcodeGenerator from './Widget/ShortcodeGenerator'
import Helpers from '../Modules/Helpers'
import VideoIframe from '../Ne/VideoIframe'
export default {
    mixins: [Helpers], 
    props:['title'],  
    components: {ClickCopy, VideoIframe, ShortcodeGenerator}, 
    data: () => ({
        area: '',
        shortcode: 'post',
    }),
    computed: {
        
        showWidget(){
            return this.area == 'widget'
        },
        showPost(){
            return this.area == 'post'
        },
        
    },
    
    methods: {
        updateShortCode(shortcode){
            this.shortcode = shortcode
        },
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
.shortcode-gen{
    background-color: #f5f5f5;
    padding: .5em;
    border-radius: .4em;
}
</style>