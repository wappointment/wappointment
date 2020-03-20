
<template>
    <div class="picture-edit">
        <div @click="changePicture" class="text-center mr-2 preview-avatar">
            <div class="avatar-edit">
                <img :src="src" class="img-fluid rounded" width="40">
                <span class="small text-primary" href="javascript:;">edit</span>
            </div>
        </div>
        <div v-if="edit" class="avatar-selection">
            <span class="close close-gallery" @click="close"></span>
            <div class="d-flex justify-content-around">
                <button class="staff-av btn btn-secondary" @click="revertToGravatar" :class="[isGravatar?'selected':'']">
                    <img :src="gravatar" class="img-fluid" width="40">
                    Default Gravatar
                </button>
                <button class="staff-av btn btn-secondary" @click="openGallery" :class="[!isGravatar?'selected':'']">
                    <img v-if="!isGravatar" :src="src" class="img-fluid" width="40"> 
                     Browse Media gallery
                </button>
            </div>
            <div v-if="gallery" class="gallery">
                <hr>
                <WPMedias @selected="selectedFromGallery" @confirmed="saveNewAvatar" :sizeSelect="false"></WPMedias>
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
        } 
    },
    methods:{
        changePicture(){
            this.edit = true
        },
        openGallery(){
            this.gallery = true
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
            this.settingStaffSave('avatarId', false) 
        },
        saveNewAvatarRequest(){
            this.settingStaffSave('avatarId', this.selectedId) 
        },
        afterSuccess(result){
            this.close()
            this.selectedId = false
            this.$emit('changed')
        }
    },
    computed:{
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
    .picture-edit .staff-av img {
        border-radius: .6rem;
        margin-right: 1rem;
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
        border: 1px solid #eee;
        cursor: pointer;
        border-radius: 0.3rem;
    }
    .preview-avatar:hover{
        border: 1px solid #6664cb;
    }
    .avatar-edit{
        position:relative;
    }
    .avatar-edit span{
        position: absolute;
        display: none;
        bottom: -4px;
        left: 8px;
    }
    .avatar-edit:hover span{
        display: block;
    }
</style>