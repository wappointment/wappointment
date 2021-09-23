<template>
    <div @click="$emit('activated')" class="d-flex align-items-center">
        <input type="checkbox" :id="id" v-model="updatedValue">
        <label class="wfg-cb-label" :for="id" v-html="label"></label>
        <span v-if="tip" :data-tt="tip" >
            <span class="dashicons dashicons-info text-primary"></span>
        </span>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
export default {
    name:'core-checkbox',
    mixins: [AbstractField],
}
</script>
<style >
.wfg-cb-label{
    margin-bottom: 0;
}
</style>