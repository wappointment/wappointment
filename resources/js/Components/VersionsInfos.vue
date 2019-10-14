<template>
  <div v-if="versions">
      <SlideIn @viewed="viewed">
          <template slot="intro">
                <Count>{{changesCount}}</Count>
                <span class="text-dark">new changes</span> 
                <span class="dashicons-before dashicons-wappointment text-primary"></span> 
          </template>
          <template slot="modal-title">What's new in the latest versions</template>
          <template slot="modal-body">
             
            <div  v-for="versionObj in versions">
                <h5>Changes in version {{ versionObj.version }}</h5>
                <div v-for="change in versionObj.changes">
                    <h6>{{ change.title }}</h6>
                    <img :src="change.image" :alt="change.title">
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
export default {
    extends: abstractView,
    components:{ SlideIn, Count },
    data: () => ({
        versions: false,
    }),
    created(){
        if(window.wappointmentAdmin.updatePages.length > 0 ){
            this.versions = window.wappointmentAdmin.updatePages
        }
    },
    computed:{
        changesCount(){
            let count = 0
            for (let i = 0; i < this.versions.length; i++) {
                count += this.versions[i].changes.length
            }
            return count
        }
    },
    methods: {
        viewed(){
            this.settingStaffSave('viewed_updates', true) 
        }
    }
}
</script>