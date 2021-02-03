<template>
    <div>
        <template v-if="isImage">
            <img :class="getClass" :src="getSelectedUrl" :height="sizePreview"
            :alt="getTitle" :title="getTitle" >
        </template>
        <template v-else>
            media preview impossible
        </template>
    </div>
</template>

<script>


export default {

    props: {
        element:{
            type: Object,
        },
        format: {
            type: String,
            default: 'thumbnail'
        },
        size: {
            type: String,
            default: '150'
        },
        thumbnail:{
            type:Boolean,
            default: true
        },
        selected:{
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            sizePreview: null,
        } 
    },
    created(){
        if(this.thumbnail) this.sizePreview = '100'
        else this.sizePreview = this.size
    },
    methods:{
        getFormatObject(){
            return this.element.media_details.sizes[this.format] !== undefined ? this.element.media_details.sizes[this.format]:this.element.media_details.sizes.full
        },
    },
    computed:{
        getClass(){
            let classString = this.isSelected ? ' selected ':''
            classString += this.thumbnail === true ? ' rounded img-fluid ':''
            return classString
        },
        getTitle(){
            return this.element.media_details.image_meta !== undefined ? this.element.media_details.image_meta.title:this.element.slug
        },
        getSelectedUrl(){
            return this.element.media_details.sizes !== undefined ? this.getFormatObject().source_url :this.element.source_url
        },
        
        isImage(){
            return this.element.media_type == 'image'
        },
        isSelected(){
            return this.selected == this.element.id
        }
    }
}
</script>

