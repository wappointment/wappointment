<template>
    <div :class="getClasses">
        <input :type="type" v-model="updateValue" :id="options.namekey" />
        <label :for="options.namekey">{{ getLabel }}</label>
    </div>
</template>

<script>
import abstractInput from './abstractInput.vue'
export default {
    extends: abstractInput,
    data: () => ({
        type:'checkbox',
        updateValue: false,
    }),

    computed: {
        getClasses(){
            let classes = {}
            if(this.isRequired) classes['multi-required'] = true
            classes[this.hasError ? 'isInvalid':'isValid'] = true
            return classes
        },
    },

}   
</script>
<style>
.wap-booking-fields .multi-required > label::after {
    content: " *";
    color: rgba(237,117,117,1);
}
</style>