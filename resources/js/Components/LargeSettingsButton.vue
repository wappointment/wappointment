<template>
    <div class="card cardb d-flex flex-row justify-content-between" @click="$emit('click')">
        <div class="d-flex align-items-center h5 my-1">
            <div v-if="is_set" class="dashicons dashicons-yes-alt text-success mr-2" ></div> 
            <div v-else class="dashicons dashicons-dismiss text-danger mr-2" ></div> 
            <div v-if="label">{{ label }}</div>
            <div v-else><slot></slot></div>
        </div>
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
<style>
    .card{
        margin-top: .6rem;
        max-width: none;
        border-radius: .225rem;
        border-color: #e8e8e8;
        padding: .5rem !important;
    }
    .card.cardb:hover  {
        background-color:#f8f8f8;
        cursor:pointer;
    }
    .card .hidden{
        display: none;
    }
    .card:hover .hidden{
        display: block;
    }
</style>