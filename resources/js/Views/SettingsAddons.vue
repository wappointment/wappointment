<template>
  <div >
      <div class="reduced">

          <div v-for="(addon, addonkey) in addons" class="card cardb p-2 px-3 d-flex flex-row justify-content-between" @click="editAddonSettings(addonkey)">
                <span class="h5 my-1">
                  <span class="dashicons dashicons-admin-generic text-muted"></span>
                  {{ addon.name }}</span>
                <button  class="btn btn-xs btn-secondary hidden">{{ isSetupLabel(addon.setup) }}</button>
            </div>
      </div>
      
      <WapModal v-if="activeAddon" :show="activeAddon" @hide="hideModal" large noscroll>
        <h4 slot="title" class="modal-title"> 
          <span>{{ getAddon.name }}</span>
        </h4>
        <component :is="getAddon.componentKey" @close="hideModal"></component>
      </WapModal>
  </div>
</template>

<script>
import abstractView from './Abstract'
import RequestMaker from '../Modules/RequestMaker'
export default {
  extends: abstractView,
  components: window.wappointmentExtends.filter('AddonsSettingsComponents', {}, {extends: abstractView, mixins: [RequestMaker]} ),
  data() {
    return {
      parentLoad: false,
      viewName: 'settingsaddons',
      viewData: [],
      activeAddonKey: '',
      addons: window.wappointmentExtends.filter('AddonsActive', window.wappointmentAdmin.addons )
    };
  },
  computed:{
    activeAddon(){
      return this.activeAddonKey !== ''
    },
    getAddon(){
      return this.activeAddon === true ? this.addons[this.activeAddonKey] : false
    }
  },
  methods: {
    editAddonSettings(addonkey){
      this.activeAddonKey = addonkey
    },
    hideModal(){
      this.activeAddonKey = '';
    },

  }
};
</script>
