<template>
    <div :class="getClass">
        <input v-if="showInput" type="text" class="form-control" :placeholder="fieldValue" v-model="value">
        <div v-if="showInput" class="input-group-append">
            <button class="btn btn-outline-secondary" v-if="showInput" @click.prevent="saveSettings">Save</button>
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
          this.settingSave(this.fieldKey, this.value)
          this.showInput = false
      },
  }
}
</script>
<style scoped>
.vue-form-generator .form-control:not([class*=" col-"]) {
    width: 76px;
}
.vue-form-generator .btn-outline-secondary {
    max-height: 31px;
}

</style>


