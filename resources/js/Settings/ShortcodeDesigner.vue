<template>
    <div>
        <div class="shortcode-editor">
            <div class="shortcode-gen">
                <ShortcodeGenerator @change="updateShortCode" :title="title"/>
            </div>
            <div id="shortcode_block">
                <p class="m-0">Your shortcode: </p>
                <ClickCopy :value="shortcode" />
            </div>
        </div>
        <div class="mt-4">
            <h4>How can I use this shortcode?</h4>
            <p>Use the shortcode in your page(s) or post(s) </p>
            <VideoIframe src="https://www.youtube.com/embed/VMi2Ry-JrGA" />
        </div>
    </div>
</template>

<script>
import ClickCopy from '../Fields/ClickCopy'
import ShortcodeGenerator from './ShortcodeGenerator'
import Helpers from '../Modules/Helpers'
import VideoIframe from '../Ne/VideoIframe'
export default {
    props:{
        title: {
            type: String,
            default: 'Book now!'
        }
    },
    data: () => ({
        shortcode: '',
    }),
    mixins: [Helpers], 
    components: {ClickCopy, VideoIframe, ShortcodeGenerator}, 
    methods: {
        updateShortCode(shortcode){
            this.shortcode = shortcode
        },
    } 
}
</script>
<style>

#shortcode_block{
    border: 2px solid var(--primary);
    border-radius: .3rem;
    padding: 1rem;
    text-align: center;
}
#shortcode_block p{
    font-size: 1.4rem;
}
.shortcode-editor{
    max-width:600px;
}
.shortcode-gen{
    background-color: #f5f5f5;
    padding: .5em;
    border-radius: .4em;
}

</style>