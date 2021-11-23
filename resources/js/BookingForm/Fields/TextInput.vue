<template>
    <div :class="getClasses">
        <label :for="name">{{ getLabel }}</label>
        <div class="d-flex">
            <input class="form-control" :id="name" type="text" v-model="updateValue" 
            :required="isRequired">
        </div>
        <div v-if="hasError" class="has-error"> {{ this.error }}</div>
    </div>
</template>

<script>
import abstractInput from './abstractInput.vue'
export default {
    extends: abstractInput,
}   
</script>