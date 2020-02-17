<template>
    <div >
        <div v-if="elements.length > 0" class="d-flex flex-wrap">
            <div v-for="element in elements" class="media-cell" :class="[isSelected(element)?'selected':'']" @click="selectMedia(element)">
                <MediaPreview :element="element" :selected="selectedImage"></MediaPreview>
            </div>
        </div>
        <div v-else>
            We cannot find any image in your site's medias <a target="_blank" :href="getUploadLink">Upload media</a>
        </div>
        <div  class="d-flex justify-content-center">
            <template v-for="p in pages">
                <span v-if="p == page">{{ p }}</span>
                <a v-else href="javascript:;" @click="changePage(p)">{{ p }}</a>
                <span v-if="p!=pages"> - </span>
            </template>
        </div>
    </div>
</template>

<script>

import abstractView from '../Views/Abstract'
import WPMediaService from '../Services/WP/Media' 
import MediaPreview from '../WP/MediaPreview'
export default {
    extends: abstractView,
    components:{ MediaPreview },
    props: {
        media_type:{
            type: String,
            default: 'image'
        },
        selectedImage:{
            type: Number,
            default: 0
        }

    },
    data() {
        return {
            serviceWPMedia: null,
            elements:[],
            selected: false,
            total:0,
            pages:0,
            page: 1
        } 
    },
    created(){
        this.serviceWPMedia = this.$vueService(new WPMediaService)
        this.getMedia()
    },
    computed: {
        getUploadLink(){
            return window.apiWappointment.base_admin.replace('admin.php','upload.php')
        }
    },
    methods: {
        changePage(p){
            this.page = p
            this.getMedia()
        },
        selectMedia(element){
            this.selected = element.id
            this.$emit('selected', this.selected, element)
        },
        isSelected(element){
            return this.selected == element.id
        },
        getMedia() {
            this.request(this.getMediaRequest,  undefined,undefined,false,  this.loadedMedias)
        },
        async getMediaRequest() {
            let params = {media_type: this.media_type}
            if(this.page) params['page'] = this.page
            return await this.serviceWPMedia.call('get', params)
        },
        loadedMedias(r){
            //console.log('r',r)
            this.total = parseInt(r.headers['x-wp-total'])

            this.pages = parseInt(r.headers['x-wp-totalpages'])
            this.elements = r.data
        },

    }  
}
</script>
<style >
    .media-cell{
        margin: 1rem;
        cursor: pointer;
        border-radius: .3rem;
    }
    .media-cell:hover{
        box-shadow: 0 0 .7rem 0 rgba(30, 109, 138, 0.4);
    }
    .media-cell.selected{
        box-shadow: 0 0 .4rem 0 rgb(30, 109, 138);
    }
</style>

