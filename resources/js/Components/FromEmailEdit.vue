<template>
    <span>
        <span v-if="showInput" class="d-flex">
            <input class="form-control mr-2" type="text" v-model="value" @keyup.enter.prevent="saveSettings">
            <button @click.prevent="saveSettings">Save</button>
        </span>
        <span v-else>
            <a href="javascript:;" 
             @mouseover="showEdit=true" 
             @mouseout="showEdit=false" 
             :title="editLabel" class="text-muted" @click="showInput=!showInput">{{ value }}</a>
            <span v-if="showEdit" class="text-primary small">{{ editLabel }}</span>
        </span>
    </span>
    <div>
        <div v-if="showFrom" class="d-flex">
            <input class="form-control mr-2" type="text" v-model="sendconfig.from_name" >
            <input class="form-control mr-2" type="text" v-model="sendconfig.from_address" >
        </div>
        <div v-else class="d-flex align-items-center">
            <span class="m-0 mr-2">From Address: </span>
            <a href="javascript:;"  @mouseover="showEditFrom=true" 
            @mouseout="showEditFrom=false" title="Edit" class="text-dark" 
            @click="showFrom=!showFrom">{{ from_name }} <small class="text-muted"><{{from_address}}></small> </a>
            <span v-if="showEditFrom" class="text-primary small ml-2">Click to Edit</span>
        </div>
    </div>
</template>

<script>
import SettingsSave from '../Modules/SettingsSave'
export default {
  extends: SettingsSave,
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
        showEdit: false,
        value: false,
        serviceSetting: null,
      }
  },

  created(){
    this.value = this.fieldValue
  },
  methods: {
      saveSettings(){
          this.settingSave(this.fieldKey, this.value)
          this.showInput = false
      },
  }
}
</script>


