<template>
    <div class="wap-inline-elements" :class="getClasses">
        <label>{{ getLabel }}</label>
        <div class="d-flex flex-wrap">
            <div class="d-flex align-items-center inline-element" v-for="valuef in options.values">
                <input :type="type" v-model="updateValue" :value="valuef.value" :id="options.namekey+valuef.value" />
                <label :for="options.namekey+valuef.value">{{ valuef.label }}</label>
            </div>
        </div>
    </div>
</template>

<script>
import Checkbox from './Checkbox.vue'
export default {
    extends: Checkbox,
    data: () => ({
        type:'checkbox',
    }),
    created(){
        this.updateValue = []
    }
}   
</script>
<style>
.wap-booking-fields .wap-inline-elements .inline-element{
    margin-right: .5em;
}
.wap-booking-fields .wap-inline-elements .inline-element label{
    margin-left: .2em;
    margin-bottom: 0;
}
</style>