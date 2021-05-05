<template>
    <div :class="getClasses">
        <label :for="name">{{ getLabel }}</label>
        <div class="d-flex">
            <input class="form-control" :id="name" type="date" v-model="updateValue" 
            :required="isRequired" :min="getMinDate" :max="getMaxDate">
        </div>
    </div>
</template>

<script>
import abstractInput from './abstractInput.vue'
export default {
    extends: abstractInput,
    compute: {
        getMinDate(){
            return ''
        },
        getMaxDate(){
            return ''
        }
    }
}   
</script>