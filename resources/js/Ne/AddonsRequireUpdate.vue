<template>
    <WapModal :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Please update your Wappointment addon(s) to their latest version</h4>
          <div class="border border-primary rounded p-2 text-primary">
            <ul class="mb-0">
                <li v-for="addon in show" class="my-2">
                    <img :src="getImg+'images/'+addon.key+'.png'" :alt="addon.name" class="img-fluid addon-sm mr-2">
                    <strong>{{ addon.name }}</strong> requires to be updated to at least the version <strong>{{ addon.requires_update }}</strong></li>
            </ul>
          </div>
          <div class="mt-4">
              <div class="mt-4 bg-primary p-4 rounded text-white">
                  <h4 class="text-white">In order to proceed with the update:</h4>
                    <ol>
                        <li> Go to the <a href="plugins.php" target="_blank" class="text-warning">Plugins page</a></li>
                        <li> Click the <strong>update now</strong> link if it appears (as shown in the image below)</li>
                        <li> That's it!</li>
                    </ol>
                    <div>
                        <img :src="getUpdateImg+'update_addons.png'" alt="Update link appears for your plugin" class="img-fluid rounded">
                    </div>
              </div>
              
              
              <div class="d-flex align-items-center mt-4 p-4 bg-secondary rounded">
                  <div>
                      <h5 class="mt-4">If there is no update link</h5>
                      <ol>
                        <li> Simply deactivate and delete the addon(s) from the <a href="plugins.php" target="_blank">Plugins page</a></li>
                        <li> Then go to <strong>Wappointment > Addons</strong></li>
                        <li> And click Install</li>
                        <li> The latest available version will be downloaded</li>
                    </ol>
                  </div>
                
                <div>
                    <img :src="getUpdateImg+'click_install.png'" class="img-fluid rounded" alt="How to click install">
                </div>
              </div>
            </div>
    </WapModal>
</template>
<script>
import abstractView from '../Views/Abstract'

export default {
    extends: abstractView,
    data: () => ({
        show: false
    }),
    created(){
        this.testForUpdates()
    },
    computed:{
        getImg(){
            return window.apiWappointment.apiSite + '/'
        },
        getUpdateImg(){
            return this.getImg + 'plugin/' + window.apiWappointment.version + '/'
        },
    },
    methods:{
        
        hideModal(experience){
            this.show = false
        },
        testForUpdates(){
            if(window.wappointmentAdmin.addons !== undefined){
                let addonsRequiringUpdate = []
                let addons = window.wappointmentAdmin.addons
                for (const key in addons) {
                    if (addons.hasOwnProperty(key)) {
                        if(addons[key].requires_update !== undefined){
                            addonsRequiringUpdate.push(Object.assign({key:key},addons[key]))
                        }
                        
                    }
                }
                if(addonsRequiringUpdate.length >0){
                    this.show = addonsRequiringUpdate
                }
            }
        }

    },
   
    
}
</script>
<style >
.img-fluid.addon-sm{
    max-width: 60px;
    border-radius: 2rem;
}
</style>
