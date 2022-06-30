<template>
    <div :class="getClass">
        <div v-if="showInput" class="d-flex align-items-center">
            <textarea v-model="value"></textarea>
            <button class="btn btn-outline-primary ml-2" v-if="showInput" @click.prevent="saveSettings">{{ get_i18n('save', 'common') }}</button>
        </div>
        <div v-else class="footer-note">
            <div class="footer-note-edit" @click="showInput=!showInput">{{ footerNoteLabel }}</div>
        </div>
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
        value: '',
        serviceSetting: null,
      }
  },

  created(){
    this.value = this.fieldValue
  },
  computed: {
      footerNoteLabel(){
          return this.value.trim() !='' ? this.value:'Add Footer note'
      },
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

<style scoped>
.footer-note-edit{
    white-space: pre;
    text-align: left;
}
</style>



