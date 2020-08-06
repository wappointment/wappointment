<template>
    <div>
        <input type="range" :id="id" v-model="updatedValue" :min="options.min" :max="options.max" :step="options.step">
        <label :for="id">{{ label }} ({{ updatedValue }}{{ getunit }})</label>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
export default {
    name: 'core-slider',
    props: ['options'],
    mixins: [AbstractField],
    computed:{
        getunit(){
            return this.options.unit !== undefined ? this.options.unit:''
        }
    }
}
</script>
