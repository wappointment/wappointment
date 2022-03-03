<template>
    <div :class="getClasses" v-if="options.hide !== true">
        
        <label :for="options.namekey">{{ getLabel }}</label>
         <select :id="options.namekey" v-model="updateValue">
            <option v-if="defaultValueAllowed" disabled value="">{{ options.defaultlabel }}</option>
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

    created(){
        if(!this.defaultValueAllowed && this.updateValue == ''){
            this.updateValue = this.options.values[0].value
        }
    },
    computed:{
        defaultValueAllowed(){
            return [true].indexOf(this.options.nodefault) === -1
        },
        
    },
}   
</script>
