<template>
  <div>
      
      <div class="reduced">
          <div v-for="(addon, addonkey) in addonsWithSettings" class="card cardb p-2 px-3 d-flex flex-row justify-content-between" @click="editAddonSettings(addonkey)">
                <span class="h5 my-1">
                  <span class="dashicons text-muted" :class="[addon.icon ? addon.icon:'dashicons-admin-generic']"></span>
                  {{ addon.name }}</span>
                <button  class="btn btn-xs btn-secondary hidden">{{ isSetupLabel(addon.setup) }}</button>
            </div>
      </div>
      <p class="m-2 font-italic small text-muted">Only some addons have settings</p>
      <WapModal v-if="activeAddon" :show="activeAddon" @hide="hideModal" large noscroll>
        <h4 slot="title" class="modal-title"> 
          <span>{{ getAddon.name }}</span>
        </h4>
        <component :is="getAddon.componentKey" @close="hideModal"></component>
      </WapModal>
  </div>
</template>

<script>

export default {
  computed:{
    isLegacy(){
        return this.service.type !== undefined
    },
  },

};
</script>

