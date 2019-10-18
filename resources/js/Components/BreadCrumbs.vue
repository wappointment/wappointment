<template>
    <div class="breadcrumbs d-flex p-2 my-2 rounded bg-light">
        <div v-for="(crumb,index) in crumbs">
            <a v-if="crumb.disabled === undefined" href="javascript:;" @click="clickCrumb(crumb)"> {{ crumb.label }} </a> 
            <span v-else> {{ crumb.label }} </span>
            <span v-if="index != Object.keys(crumbs).length - 1"> > </span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        crumbs: {
            type: Array,
            default: []
        },

    },
    methods: {
      clickCrumb(crumb){
        return this.$emit('click', crumb)
      },
    }
}   
</script>