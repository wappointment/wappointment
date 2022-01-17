<template>
    <div :class="getClass">
        <input v-if="showInput" type="text" class="form-control" :placeholder="fieldValue" v-model="value">
        <div v-if="showInput" class="input-group-append">
            <button class="btn btn-outline-primary" v-if="showInput" @click.prevent="saveSettings">{{ get_i18n('save', 'common') }}</button>
        </div>
        <a v-else href="javascript:;" :data-tt="editLabel"
            :title="editLabel" @click="showInput=!showInput">{{ value }}</a>
    </div>
</template>

<script>
import InputPh from '../Fields/InputLabelMaterial'
import SettingsSave from '../Modules/SettingsSave'
export default {
  extends: SettingsSave,
  components: {InputPh},
  props: {
    editLabel: {
        type: String,
        default: 'Click to Edit'
    },
    fieldValue: {
        type: String,
    },
    fieldKey: {
        type: String,
    },
  },
  data() {
      return {
        showInput: false,
        value: false,
        serviceSetting: null,
      }
  },

  created(){
    this.value = this.fieldValue
  },
  computed: {
      getClass() {
          return this.showInput ? 'input-group input-group-sm':'small'
      }
  },
  methods: {
      saveSettings(){
          if(this.value==''){
              return false
          }
          this.settingSave(this.fieldKey, this.value)
          this.showInput = false
      },
  }
}
</script>



