<!-- fieldSlider.vue -->
<template>
    <div class="d-flex">
      <range-slider
        class="slider"
        :min="schema.min"
        :max="schema.max"
        :step="schema.step"
        v-model="value" 
        :disabled="disabled"
        :maxlength="schema.max"
        :placeholder="schema.placeholder"
        :readonly="schema.readonly">
      </range-slider> 
      <input type="text" v-model="formatedValue" readonly="readonly" size="5">
    </div>
</template>

<script>
    import RangeSlider from 'vue-range-slider'
    // you probably need to import built-in style
    import 'vue-range-slider/dist/vue-range-slider.css'
   import { abstractField } from "vue-form-generator";

   export default {
         mixins: [ abstractField ],
         components: {
           RangeSlider
         },
         computed:{
             formatedValue(){
                 return this.value + ' ' + this.schema.unit
             }
         },
         watch: {
             value: function (val) {
               if(this.schema.int === undefined )this.value = parseFloat(val).toFixed(1);
             },
           },

         mounted() {
          if(this.value === undefined || this.value == ''){
              if(this.schema.default !== undefined && this.schema.default > this.schema.min ) this.value = this.schema.default;
              else this.value = this.schema.min;
          } 
         },
   };
</script>
<style>
.slider {
  /* overwrite slider styles */
  width: 78%;
}
.field-wrap {
    display: block;
  }
</style> 