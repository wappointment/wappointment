<template>
    <div class="picture-edit">
        <div @click="changePicture" class="text-center preview-fimage">
            <div class="fimage-edit">
                <div v-if="hasImage" @mouseover="changeButtonOn = true" @mouseout="changeButtonOn = false">
                    <div :style="'background-image: url(' + updatedValue.src + ');'" class="img-bg d-flex justify-content-center align-items-center">
                        <div v-if="changeButtonOn" class="btn btn-secondary btn-lg">Change Picture</div>
                    </div>
                </div>
                <i v-else class="dashicons dashicons-format-image"></i>
                <span class="small text-primary" href="javascript:;">edit</span>
            </div>
        </div>
        <WapModal :show="edit" @hide="close" large>
            <div>
                <div v-if="selected_image !== null">
                    <div class="btn btn-secondary" 
                    v-for="(image_size, thumbkey) in getImagesThumb(selected_image.media_details.sizes)"  @click="changeSize(image_size)">
                        <div class="text-center">
                            <img :src="image_size.source_url" :width="setImageWidth(image_size)" />
                            <div>
                                {{ image_size.width }}px * {{ image_size.height }}px
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center" v-if="hasImage">
                    <div class="mr-4">
                        <img :src="updatedValue.src" class="img-fluid rounded" :width="preview.width">
                    </div>
                    <div>
                        <a class="btn btn-secondary btn-xs" href="javascript:;" @click="clearImage">Clear</a>
                        <a class="btn btn-primary btn-xs" href="javascript:;" @click="confirmSelectedImage">Confirm</a>
                    </div>
                </div>
                <div class="gallery" v-if="galleryShow && selected_image === null">
                    <div>
                        <input type="text" v-model="search_term">
                        <button class="btn btn-secondary" @click.prevent.stop="refreshGallery">Search</button>
                    </div>
                    <hr>
                    <WPMedias @selected="selectedFromGallery" @confirmed="confirmSelection" 
                    :search="search_term" :per_page="showing_images" :selectedImage="selectedImage"></WPMedias>
                    <div class="bg-white pt-3">
                        <button class="btn btn-secondary" @click.prevent="close">Close</button>
                    </div>
                </div>
                
            </div>
        </WapModal>
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
            changeButtonOn: false,
            isHover:false,
            edit: false,
            size: 'thumbnail',
            preview: {
                width: 40
            },
            galleryShow: true,
            search_term: '',
            showing_images: 21,
            selected_image: null,
            selected_size: null
        } 
    },
    created(){
        if([undefined,''].indexOf(this.src) === -1)    this.updatedValue.src = this.src
        this.size = this.definition.size !== undefined ? this.definition.size:this.size
        this.preview = this.definition.preview !== undefined ? this.definition.preview:this.preview
    },
    computed:{
        hasImage(){
            return this.updatedValue !== undefined && this.updatedValue.src !== undefined
        },
        selectedImage(){
            return this.updatedValue !== undefined && this.updatedValue.wp_id !== undefined ? this.updatedValue.wp_id:0
        }
    },
    methods:{
        confirmSelectedImage(){
            this.close()
        },
        getImagesThumb(sizes){
            let sizesv = Object.values(sizes)
            let keysv = Object.keys(sizes)
            for (let i = 0; i < sizesv.length; i++) {
                sizesv[i].key = keysv[i]
            }
            return sizesv.sort((a, b) => a.width > b.width ? 1 : -1)
        },
        changeSize(size){
            console.log('imagesize',size)
            this.selected_size = size
            this.updatedValue.src =size.source_url
        },
        setImageWidth(image){
            return image.width /5
        },
        refreshGallery(){
            this.galleryShow = false
            setTimeout(this.galleryOn, 100)
        },
        galleryOn(){
            this.galleryShow = true
        },
        clearImage(){
            this.updatedValue = ''
            this.selected_image = null
        },
        changePicture(){
            this.edit = true
        },

        close(){
            this.edit = false
            this.selected_image = null
        },
        confirmSelection(element, format){
            this.selectedFromGallery(element,format)
            this.close()
        },
        selectedFromGallery(element, format){
            this.selected_image = element
            let wp_image = {wp_id: element.id, src: this.updatedValue.src }
            let selectedsize = format === undefined ? this.size:format
            if(element.media_details!== undefined && element.media_details.sizes[selectedsize]!== undefined){
                wp_image.src = element.media_details.sizes[selectedsize].source_url
            }
            this.updatedValue = wp_image
            
        },

    },

}
</script>
<style >
    .img-bg {
        min-height: 100px;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center top;
        min-width: 100px;
    }

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
        background-color: #fff;
        z-index: 9;
        border-radius: .6rem;
        box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.08);
        border: 1px solid #ececec;
        padding: 2rem;
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
    }
    .preview-fimage:hover{
        border: 1px solid #6664cb;
    }
    .fimage-edit{
        position:relative;
        max-height: 100px;
        overflow: hidden;
    }
    .fimage-edit:hover {
        max-height: 100%;
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
