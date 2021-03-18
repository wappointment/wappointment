<template>
    <WapModal :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Please update your Wappointment addon(s) to their latest version</h4>
          <div class="bg-danger rounded p-2 text-white">
            <ol>
                <li v-for="addon in show" class="my-2"><strong>{{ addon.name }}</strong> requires to be updated to at least the version <strong>{{ addon.requires_update }}</strong></li>
            </ol>
          </div>
          <div class="mt-4">
              <h4>In order to proceed with the update:</h4>
              <ol>
                  <li> Go to the <a href="plugins.php" target="_blank">Plugins page</a></li>
                  <li> Click the update link if it appears</li>
                  <li> That's it!</li>
              </ol>
              <h4>In case the update link doesn't appear:</li></h4>
              <ol>
                  <li> Simply deactivate and delete the addon(s)</li>
                  <li> Then go to <strong>Wappointment > Addons</strong></li>
                  <li> And click Install</li>
                  <li> The latest available version will be downloaded</li>
              </ol>
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
                            addonsRequiringUpdate.push(addons[key])
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

