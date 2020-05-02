<template>
    <div >
        <div v-if="elements.length > 0" class="d-flex flex-wrap">
            <div v-for="element in elements" class="media-cell" :class="[isSelected(element)?'selected':'']" @click="selectMedia(element)">
                <MediaPreview :element="element" :selected="selectedImage"></MediaPreview>
                <div class="text-center" v-if="!sizeSelect">
                    <button v-if="isSelected(element)" class="btn btn-primary" type="button"  @click.prevent.capture.stop="$emit('confirmed', selected)" >Confirm</button>
                </div>
                <div class="text-center" v-else>
                    <div class="btn-group" v-if="isSelected(element)">
                        <button class="btn btn-primary" type="button"  @click.prevent.capture.stop="$emit('confirmed', selected)" >Confirm</button>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" @click.prevent.capture.stop="showFormat = !showFormat" type="button" id="dropdownMenuButton">
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu" :class="{'show':showFormat}" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" 
                                v-for="(image_details, image_size) in element.media_details.sizes" @click.prevent.capture.stop="$emit('confirmed', selected, image_size)" >
                                    {{ image_size }} ({{ image_details.width }}*{{ image_details.height }} )
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
        per_page: {
            type: Number,
            default: 10
        },
        search: {
            type: String,
            default: ''
        },
        selectedImage:{
            type: Number,
            default: 0
        },
        sizeSelect:{
            type: Boolean,
            default: true
        },

    },
    data() {
        return {
            serviceWPMedia: null,
            elements:[],
            selected: false,
            total:0,
            pages:0,
            page: 1,
            showFormat: false,
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
            this.selected = element
            this.$emit('selected', this.selected)
        },
        isSelected(element){
            return this.selected.id == element.id
        },
        getMedia() {
            this.request(this.getMediaRequest,  undefined,undefined,false,  this.loadedMedias)
        },
        async getMediaRequest() {
            let params = {
                media_type: this.media_type,
                per_page: this.per_page
            }
            if(this.search !='') params['search'] = this.search
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

