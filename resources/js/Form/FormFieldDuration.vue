<template>
    <div class="d-flex">
        <range-slider
            class="slider"
            :min="definition.min"
            :max="definition.max"
            :step="definition.step"
            v-model="updatedValue" 
            :maxlength="definition.max"
            :readonly="definition.readonly">
        </range-slider> 
        <input type="text" v-model="formatedValue" readonly="readonly" size="5">
        <small id="emailHelp" v-if="tip" class="form-text text-muted">{{ tip }}</small>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import RangeSlider from 'vue-range-slider'
import 'vue-range-slider/dist/vue-range-slider.css'
export default {
    components: {RangeSlider},
    mixins: [AbstractField],
    computed:{
        formatedValue(){
            return this.updatedValue + ' ' + this.definition.unit
        }
    },
    watch: {
        updatedValue: function (val) {
            if(this.definition.int === undefined )this.value = parseFloat(val).toFixed(1)
        },
    },
    mounted() {
        if(this.updatedValue === undefined || this.updatedValue == ''){
            if(this.definition.default !== undefined && this.definition.default > this.definition.min ) this.updatedValue = this.definition.default
            else this.updatedValue = this.definition.min
        } 
    },
}
</script>
<style>
.slider {
  width: 78%;
}
.field-wrap {
    display: block;
  }
</style> 