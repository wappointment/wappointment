<template>
  <div v-if="has_version_to_show">
      <SlideIn @viewed="viewed" @closed="closed" :show_already="show_already">
          <template slot="intro">
                <Count>{{changesCount}}</Count>
                <span class="text-dark">new features</span> 
                <span class="dashicons-before dashicons-wappointment text-primary"></span> 
          </template>
          <template slot="modal-title">What's new in the latest versions</template>
          <template slot="modal-body">
             
            <div  v-for="versionObj in versions">
                <h3>Updates in version {{ versionObj.version }}</h3>
                <div class="my-3" v-for="change in versionObj.changes">
                    <h6>{{ change.title }}</h6>
                    <div class="d-flex flex-wrap">
                        <div v-for="(image, idx) in change.images" class="mr-2 mb-2 img-thumbnail">
                            <img :src="getImageLink(image.src)" :class="{'img-no-caption':hasCaption(image,change.images.length)}" :alt="image.alt">
                            <div v-if="hasCaption(image,change.images.length)" class="img-caption">{{ idx+1 }} - {{ image.alt }}</div>
                        </div>
                    </div>
                </div>
            </div>
          </template>
      </SlideIn>
  </div>
</template>
<script>
import abstractView from '../Views/Abstract'
import SlideIn from './SlideIn'
import Count from './Count'
import Helpers from '../Modules/Helpers'
export default {
    extends: abstractView,
    mixins:[Helpers],
    components:{ SlideIn, Count },
    props:{
        manual_show:{
            type:Boolean,
            default:false
        }
    },
    data: () => ({
        versions: false,
    }),
    created(){
        if(this.manual_show){
            this.viewName = 'all_versions_changes'
            this.refreshInitValue()
        }else{
            if(window.wappointmentAdmin.updatePages.length > 0 ){
                this.versions = window.wappointmentAdmin.updatePages
            }
        }
        
    },
    computed:{
        show_already(){
            return this.manual_show && this.versions && this.versions.length > 0
        },
        has_version_to_show(){
            if(this.manual_show && this.versions) return true
            else{
                return this.versions
            }
        },
        changesCount(){
            let count = 0
            for (let i = 0; i < this.versions.length; i++) {
                count += this.versions[i].changes.length
            }
            return count
        }
    },
    methods: {
        hasCaption(image, countImages){
            return image.alt !== undefined && countImages > 1
        },
        closed(){
            this.$emit('closed')
        },
        getImageLink(image){
            return this.pathAssets('images/'+image)
        },
        loaded(viewData){
            this.viewData = viewData.data
            this.versions = viewData.data.versions
        },
        viewed(){
            this.settingStaffSave('viewed_updates', true) 
        }
    }
}
</script>
<style >
.img-thumbnail{
    border: 1px solid #d3d3d3;
    border-radius: .4rem;
    box-shadow: 0 .01rem .6rem 0 rgba(197, 37, 211, 0.15);
}

.img-thumbnail img{
    border-radius: .4rem;
}
.img-thumbnail img.img-no-caption {
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 0;
}
.img-caption{
    text-align: center;
    color: #8f8391;
}
</style>