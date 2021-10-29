<template>
    <div :class="getClasses" v-if="options.hide !== true">
        
        <label :for="options.namekey">{{ getLabel }}</label>
         <select :id="options.namekey" v-model="updateValue">
            <option disabled value="">Please make a selection</option>
            <option v-for="valuef in options.values" :value="valuef.value">{{ valuef.label }}</option>
        </select> 
    </div>
</template>

<script>
import Checkbox from './Checkbox.vue'
export default {
    extends: Checkbox,
    data: () => ({
        updateValue: '',
    }),
}   
</script>
