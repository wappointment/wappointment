<template>
  <div v-if="dataLoaded">
    <div class="reduced">
      <div v-for="addon in addonsWithSettings">
        <button class="btn btn-secondary btn-sm" @click="show=addon['componentKey']">Edit settings - {{addon['name']}}</button>
      </div>
      <WapModal :show="show" @hide="hidePopup" v-if="show">
          <h4 slot="title" class="modal-title">Edit addons settings</h4>
          <div class="mt-4">
              <component :is="show" />
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
      show:false,
      addons: window.wappointmentExtends.filter('AddonsActive', window.wappointmentAdmin.addons )
    };
  },
  computed:{
    addonsWithSettings(){
      return Object.values(this.addons).filter(e => e['hasAddonsSettings']===true)
    }
  },
  methods:{
    hidePopup(){
      this.show = false
    }
  }

};
</script>