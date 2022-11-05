<template>
  <div v-if="dataLoaded">
    
    <div class="reduced">
        <div class="card p-2 px-3">
          <div class="h5">Scheduling preferences</div>
          <hr/>
          
        </div>

    </div>

  </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import Checkbox from '../Fields/Checkbox'
import LabelMaterial from '../Fields/LabelMaterial'
import InputValueCards from '../Fields/InputValueCards'
import FormFieldSelect from '../Form/FormFieldSelect'
export default {
  extends: abstractView,
  props:['tablabel'],

  components: {
    Checkbox,
    LabelMaterial,
    InputValueCards,
    FormFieldSelect,
  },
  data() {
    return {
      viewName: 'settingsaddons',
    };
  },

};
</script>