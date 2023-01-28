<template>
  <div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <Checkbox :element="reminder" :labels="labels" @changed="toggledPublish"></Checkbox>
      <div>
        <div>{{ reminder.subject }}<slot></slot></div>
        <div class="text-muted small">{{ reminder.label }}</div>
      </div>
    </div>
    <div class="d-flex align-items-center">
      <button v-if="canTranslate && isParent" :data-tt="get_i18n('translate', 'common')" class="btn btn-xs" @click="translateEmail"><span class="dashicons dashicons-translation"></span></button>
      <button v-if="isUnlocked && isParent" :data-tt="get_i18n('duplicate', 'common')" class="btn btn-xs" @click="duplicateReminder"><span class="dashicons dashicons-plus"></span></button>
      <button class="btn btn-xs" @click="editReminder" :data-tt="get_i18n('edit', 'common')"><span class="dashicons dashicons-edit"></span></button>
      <button v-if="isUnlocked" class="btn btn-xs" :data-tt="get_i18n('delete', 'common')" @click="deleteReminder"><span class="dashicons dashicons-trash"></span></button>
      <button v-else class="btn btn-xs disabled" disabled aria-disabled="true" :data-tt="get_i18n('only_unpublish','settings')"><span class="dashicons dashicons-trash"></span></button>
    </div>
  </div>
</template>

<script>

import Checkbox from '../Fields/Checkbox'

export default {
  components: { Checkbox},

  props:['reminder', 'canTranslate', 'child'],

  computed: {

    isPublished(){
      return this.reminder.published === true
    },
    isUnlocked() {
      return this.reminder.locked === false
    },
    isParent(){
      return this.child===undefined
    }

  },
  methods: {

    toggledPublish(){
      this.$emit('toggledPublish',this.reminder)
    },
    translateEmail(){
      this.$emit('translateEmail',this.reminder)
    },
    duplicateReminder(){
      this.$emit('duplicateReminder',this.reminder)
    },
    editReminder(){
      this.$emit('editReminder',this.reminder)
    },
    deleteReminder(){
      this.$emit('deleteReminder',this.reminder.id)
    },
   
  }  
}
</script>