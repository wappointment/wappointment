<template>
    <div>
        <label @click="changePicture" style="cursor:pointer" v-if="[undefined,''].indexOf(label) === -1">
            {{ label}}
        </label>
        <div class="picture-edit">
            <div @click="changePicture" class="text-center clickable" role="button" :class="{'preview-selected':hasImage, 'preview-fimage':parseInt(preview.width)  < 80}">
                <div class="fimage-edit">
                    <div v-if="hasImage" >
                        <div :style="getImageStyle" class="img-bg d-flex justify-content-center align-items-center"></div>
                    </div>
                    <i v-else class="dashicons dashicons-format-image"></i>
                    <span class="small text-primary" href="javascript:;">{{ hasImage ? get_i18n('edit','common'):get_i18n('add','common')}}</span>
                </div>
            </div>
            <WapModal :show="edit" @hide="close" large>
                <h4 slot="title" class="modal-title">{{ get_i18n('select_image','common') }}</h4>
                <div>
                    <div v-if="selected_image !== null && selected_image.media_details.sizes !== undefined">
                        <div v-for="(image_size, thumbkey) in getImagesThumb(selected_image.media_details.sizes)"
                        class="btn btn-secondary mr-2 btn-cell" :class="{selected: isSelectedSize(image_size.key)}"
                        @click="changeSize(image_size)">
                            <div class="text-center">
                                <img :src="image_size.source_url" :width="setImageWidth(image_size)" />
                                <div>
                                    {{ image_size.width }}px * {{ image_size.height }}px
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-2" v-if="hasImage">
                        <div class="mr-4">
                            <img :src="wp_image.src" class="img-fluid rounded" :width="preview.width" />
                        </div>
                        <div>
                            <a class="btn btn-secondary" href="javascript:;" @click.prevent.stop="clearImage">{{ get_i18n('clear','common') }}</a>
                            <a class="btn btn-primary" href="javascript:;" @click.prevent.stop="confirmSelectedImage">{{ get_i18n('confirm','common') }}</a>
                        </div>
                    </div>
                    <div class="gallery" v-if="galleryShow && selected_image === null">
                        <div class="pt-4 px-4">
                            <input type="text" v-model="search_term" @keyup.enter.prevent="refreshGallery">
                            <button class="btn btn-outline-primary" @click.prevent.stop="refreshGallery">{{ get_i18n('search','common') }}</button>
                        </div>
                        <hr>
                        <WPMedias v-if="edit" @selected="selectedFromGallery" @confirmed="confirmSelection" 
                        :search="search_term" :per_page="showing_images" :selectedImage="selectedImage"></WPMedias>
                        <div class="bg-white pt-3">
                            <button class="btn btn-secondary" @click.prevent.stop="close">{{ get_i18n('close','common') }}</button>
                        </div>
                    </div>
                    
                </div>
            </WapModal>
        </div>
    </div>

</template>

<script>
import AbstractField from '../Form/AbstractField'
import abstractView from '../Views/Abstract'
import WPMedias from '../WP/Medias'
export default {
    name:'opt-imageselect',
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
            isHover: false,
            edit: false,
            size: 'thumbnail',
            preview: {
                width: 40
            },
            galleryShow: false,
            search_term: '',
            showing_images: 21,
            selected_image: null,
            selected_size: null,
            wp_image: {},
            rounded: false
        } 
    },
    created(){
        if([undefined,''].indexOf(this.src) === -1) this.wp_image.src = this.src
        if(Object.keys(this.wp_image).length == 0) this.wp_image = Object.assign({},this.value)
        this.size = this.definition.size !== undefined ? this.definition.size:this.size
        this.preview = this.definition.preview !== undefined ? this.definition.preview:this.preview
    },
    computed:{
        
        hasImage(){
            return this.wp_image !== undefined && this.wp_image.src !== undefined
        },
        selectedImage(){
            return this.wp_image !== undefined && this.wp_image.wp_id !== undefined ? this.wp_image.wp_id:0
        },
        getImageStyle(){
            let style = 'background-image: url(' + this.wp_image.src + ');'
            if(this.definition.thumb_size !== undefined){
                style += 'min-width:'+this.definition.thumb_size+';'
                style += 'min-height:'+this.definition.thumb_size+';'
            }
            return style
        }
    },
    methods:{
        isSelectedSize(testedKey){          
            return this.selected_size !== null && this.selected_size.key !== undefined && this.selected_size.key == testedKey
        },
        confirmSelectedImage(){
            this.close()
            this.updatedValue = this.wp_image
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
            this.selected_size = size
            this.wp_image.src = size.source_url
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
            this.wp_image = {}
            this.selected_image = null
            this.confirmSelectedImage()
        },
        changePicture(){
            this.edit = true
            this.galleryOn()
        },

        close(){
            this.selected_image = null
            this.edit = false
            
        },
        confirmSelection(element, format){
            this.selectedFromGallery(element,format)
            this.close()
        },
        selectedFromGallery(element, format){
            this.selected_image = element
            this.wp_image = {wp_id: element.id, src: this.wp_image.src }
            let selectedsize = format === undefined ? this.size:format

            if(element.media_details.sizes!== undefined && element.media_details.sizes[selectedsize]!== undefined){
                this.wp_image.src = element.media_details.sizes[selectedsize].source_url
            }else{
                this.wp_image.src = element.source_url
            }
            
        },

    },

}
</script>
<style >
    .img-bg {
        min-height: 100px;
        background-size: auto;
        background-repeat: no-repeat;
        background-position: center top;
        min-width: 100px;
    }
    .fieldthumb .img-bg{
        min-height: 50px;
        background-size: cover;
        min-width: 50px;
    }
    .fimage-edit {
        position: relative;
    }
    .fimage-edit i.dashicons{
        width: 50px;
        height: 50px;
        font-size: 40px;
        color: #b9b6b6;
        padding-top: 5px;
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
        border-radius: .2rem;
        overflow: hidden;
        border-radius: 50%;
    }

    .preview-fimage:hover{
        border: 1px solid #6664cb;
    }

    .fimage-edit span.text-primary{
        position: absolute;
        display: none;
        bottom: -4px;
        left: 8px;
    }
    .fimage-edit:hover span{
        display: block;
        width: 100%;
        height: 18px;
        position: absolute;
        left: 0;
        background: rgba(97, 134, 204, 0.6);
        bottom: 0;
        color: #fff !important;
        font-weight: bold;
    }
</style>
