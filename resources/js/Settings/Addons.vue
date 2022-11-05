<template>
  <div v-if="dataLoaded">
    
    <div class="reduced">
      <WapModal :show="show" @hide="hidePopup" v-if="show">
          <h4 slot="title" class="modal-title">Edit addons settings</h4>
          <div class="mt-4">
              <component is="WappointmentMailChimpSettings" />
          </div>
      </WapModal>
    </div>

  </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import Checkbox from '../Fields/Checkbox'
import LabelMaterial from '../Fields/LabelMaterial'
import InputValueCards from '../Fields/InputValueCards'
import FormFieldSelect from '../Form/FormFieldSelect'
import RequestMaker from '../Modules/RequestMaker'
export default {
  extends: abstractView,
  props:['tablabel'],
  components: window.wappointmentExtends.filter('AddonsSettingsComponents', {
    Checkbox,
    LabelMaterial,
    InputValueCards,
    FormFieldSelect,
  }, {extends: abstractView, mixins: [RequestMaker]} ),
  data() {
    return {
      viewName: 'settingsaddons',
    };
  },

};
</script>