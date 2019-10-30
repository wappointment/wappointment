<template>
    <div class="picture-edit">
        <div @click="changePicture" class="text-center mr-2 preview-fimage">
            <div class="fimage-edit">
                <img v-if="src_image" :src="src_image" class="img-fluid rounded" width="40">
                <i v-else class="dashicons dashicons-format-image"></i>
                <span class="small text-primary" href="javascript:;">edit</span>
            </div>
        </div>
        <div v-if="edit" class="fimage-selection">
            <span class="close close-gallery" @click="close"></span>
            <div class="gallery">
                <hr>
                <WPMedias @selected="selectedFromGallery"></WPMedias>
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
            selectedId: false,
            src_image:  ''
        } 
    },
    created(){
        if(this.src)    this.src_image = this.src
    },
    methods:{
        changePicture(){
            this.edit = true
        },

        close(){
            this.edit = false
        },
        selectedFromGallery(id, element){
            this.updatedValue = id
            if(element.media_details!== undefined && element.media_details.sizes.thumbnail!== undefined){
                this.src_image = element.media_details.sizes.thumbnail.source_url
            }
        },
        selectImage(){
            this.close()
            this.selectedId = false
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
