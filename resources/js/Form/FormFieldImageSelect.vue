<template>
    <div class="picture-edit">
        <div @click="changePicture" class="text-center mr-2 preview-fimage">
            <div class="fimage-edit">
                <img v-if="hasImage" :src="updatedValue.src" class="img-fluid rounded" width="40">
                <i v-else class="dashicons dashicons-format-image"></i>
                <span class="small text-primary" href="javascript:;">edit</span>
            </div>
        </div>
        <div v-if="edit" class="fimage-selection">
            <div class="d-flex align-items-center" v-if="hasImage">
                <div class="fimage-edit" >
                    <img :src="updatedValue.src" class="img-fluid rounded" width="40">
                </div>
                <a class="btn btn-secondary btn-xs" href="javascript:;" @click="clearImage">Clear</a>
            </div>
            <span class="close close-gallery" @click="close"></span>
            <div class="gallery">
                <hr>
                <WPMedias @selected="selectedFromGallery" :selectedImage="selectedImage"></WPMedias>
                <div class="bg-white pt-3">
                    <button class="btn btn-secondary" @click.prevent="close">Close</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import AbstractField from './AbstractField'
import abstractView from '../Views/Abstract'
import WPMedias from '../WP/Medias'
export default {
    mixins: [AbstractField],
    extends: abstractView,
    components: {
        WPMedias
    }, 
    props: {
        src:'',
    },
    data() {
        return {
            edit: false,
        } 
    },
    created(){
        if(this.src)    this.updatedValue.src = this.src
    },
    computed:{
        hasImage(){
            return this.updatedValue !== undefined && this.updatedValue.src !== undefined
        },
        selectedImage(){
            return this.updatedValue !== undefined && this.updatedValue.wp_id !== undefined ? this.updatedValue.wp_id:false
        }
    },
    methods:{
        clearImage(){
            this.updatedValue = ''
        },
        changePicture(){
            this.edit = true
        },

        close(){
            this.edit = false
        },
        selectedFromGallery(id, element){
            let wp_image = {wp_id: id, src: this.updatedValue.src }
            if(element.media_details!== undefined && element.media_details.sizes.thumbnail!== undefined){
                wp_image.src = element.media_details.sizes.thumbnail.source_url
            }
            this.updatedValue = wp_image
            this.close()
        },
        selectImage(){
            this.close()
        }
    },

}
</script>
<style >
    .fimage-edit i.dashicons{
        width: 40px;
        height: 40px;
        font-size: 40px;
        color: #b9b6b6;
    }

    .picture-edit .staff-av img {
        border-radius: .6rem;
        margin-right: 1rem;
    }
    .fimage-selection{
        position: absolute;
        background-color: #fff;
        z-index: 9;
        border-radius: .6rem;
        box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.08);
        border: 1px solid #ececec;
        padding: 2rem;
        max-width: 730px;
    }
    .gallery{
            background-color: #eee;
    }
    .close.close-gallery{
        cursor:pointer;
    }
    .wapmodal-content .close.close-gallery::before, 
    .wapmodal-content .close.close-gallery::after,
    .close.close-gallery::before, 
    .close.close-gallery::after {
        height: 16px;
        width: 3px;
        position: absolute;
        background-color: #555;
        right: 30px;
        top: 10px;
    }
    .preview-fimage{
        border: 1px solid #eee;
        cursor: pointer;
        border-radius: 0.3rem;
        width: 44px;
    }
    .preview-fimage:hover{
        border: 1px solid #6664cb;
    }
    .fimage-edit{
        position:relative;
    }
    .fimage-edit span.text-primary{
        position: absolute;
        display: none;
        bottom: -4px;
        left: 8px;
    }
    .fimage-edit:hover span{
        display: block;
    }
</style>
