<template>
    <div :class="getClasses">
        <label :for="name">{{ getLabel }}</label>
        <div class="d-flex">
            <textarea class="form-control" v-model="updateValue"
            :id="name"
            :rows="rows"
            :placeholder="getLabel" :required="isRequired"></textarea>
        </div>
    </div>
</template>

<script>
import abstractInput from './abstractInput.vue'
export default {
    extends: abstractInput,
    props:{
        rows:{
            type:Number,
            default: 2
        }
    }
}   
</script>