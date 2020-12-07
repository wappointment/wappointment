<template>
    <div class="d-flex align-items-center"> 
        <label class="mr-2" :for="id">{{ label }} ({{ updatedValue }}{{ getunit }})</label>
        <input type="range" :id="id" v-model="updatedValue" :min="options.min" :max="options.max" :step="options.step">
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
