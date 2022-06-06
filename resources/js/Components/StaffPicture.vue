
<template>
    <div class="picture-edit">
        <div @click="changePicture" class="text-center mr-2 preview-avatar">
            <div class="avatar-edit">
                <img :src="src || gravatar" class="img-fluid wrounded" width="40" alt="avatar" />
                <span class="small text-primary" href="javascript:;">{{get_i18n('edit', 'common') }}</span>
            </div>
        </div>
        <div v-if="edit" class="avatar-selection">
            <span class="close close-gallery" @click="close"></span>
            <div class="d-flex justify-content-around">
                <button class="staff-av btn btn-secondary d-flex align-items-center" @click="revertToGravatar" :class="[isGravatar?'selected':'']">
                    <span role="img"  class="wstaff-img" :style="styleGravatar" ></span>
                    Gravatar
                </button>
                <button class="staff-av btn btn-secondary d-flex align-items-center" @click="openGallery" :class="[!isGravatar?'selected':'']">
                    <span v-if="!isGravatar" role="img"  class="dashicons dashicons-format-image mr-2" ></span>
                     Browse Media gallery
                </button>
            </div>
            <div v-if="gallery" class="gallery">
                <div class="pt-4 px-4">
                    <input type="text" v-model="search_term" @keyup.enter.prevent="refreshGallery">
                    <button class="btn btn-outline-primary" @click.prevent.stop="refreshGallery">{{ get_i18n('search','common') }}</button>
                </div>
                <hr>
                <WPMedias :search="search_term" @selected="selectedFromGallery" @confirmed="saveNewAvatar" :sizeSelect="false"></WPMedias>
                <div class="bg-white pt-3">
                    <button class="btn btn-secondary" @click="close">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import WPMedias from '../WP/Medias'
export default {
    extends: abstractView,
    components: {
        WPMedias
    }, 
    props: {
        src:'',
        gravatar: ''
    },
    data() {
        return {
            edit: false,
            gallery: false,
            selectedId: false,
            search_term: '',
            galleryShow: false,
        } 
    },
    methods:{
        changePicture(){
            this.edit = true
        },
        openGallery(){
            this.galleryOn()
        },
        close(){
            this.edit = false
            this.gallery = false
        },
        selectedFromGallery(element){
            this.selectedId = element.id
        },
        revertToGravatar(){
            this.saveGravatarRequest()
        },
        saveNewAvatar(format){
            if(!this.selectedId) return false
            this.saveNewAvatarRequest()
        },
        saveGravatarRequest(){
            this.afterSuccess(false) 
        },
        saveNewAvatarRequest(){
            this.afterSuccess(this.selectedId) 
        },
        afterSuccess(selected){
            this.close()
            this.selectedId = false
            this.$emit('changed', selected)
        },
        refreshGallery(){
            this.gallery = false
            setTimeout(this.galleryOn, 100)
        },
        galleryOn(){
            this.gallery = true
        },
    },
    computed:{
        styleGravatar(){
            return 'background-image: url("'+this.gravatar+'");'
        },
        isGravatar(){
            return this.src == this.gravatar //this.src.indexOf('gravatar.com/avatar/') !== -1
        }
    }
}
</script>
<style >
    .staff-av {
        cursor: pointer;
    }
    .avatar-selection{
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
    .preview-avatar{
        cursor: pointer;
    }
    .preview-avatar .wrounded{
        border: 1px solid #eee;
    }
    .preview-avatar:hover .wrounded{
        border: 2px solid #6664cb;
    }
    .avatar-edit{
        position:relative;
    }
    .avatar-edit span{
        position: absolute;
        display: none;
        bottom: -15px;
        left: 8px;
    }
    .avatar-edit:hover span{
        display: block;
    }
    .wstaff-img{
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background-size: cover;
        margin-right: 1rem;
    }

</style>