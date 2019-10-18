<template>
    <div class="card cardb p-2 px-3  d-flex flex-row justify-content-between" @click="$emit('click')">
        <span class="h5 my-1">
            <span v-if="is_set" class="dashicons dashicons-yes text-success" ></span> 
            <span v-else class="dashicons dashicons-no text-danger" ></span> 
            {{ label }}
        </span>
        <button  class="btn btn-xs btn-secondary hidden">{{ isSetupLabel(is_set) }}</button>
    </div>
</template>

<script>
export default {
    props: {
        is_set: {
            type: Boolean,
            default: false
        },
        label: {
            type: String,
            default: ''
        },

    },
    methods: {
      isSetupLabel(is_already_setup){
        return is_already_setup ? 'Edit' : 'Setup'
      },
    }
}   
</script>