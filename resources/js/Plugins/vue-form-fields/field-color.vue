<!-- field-color.vue -->
<template>
    <div>
      <span class="colorSq" :style="getStyle()" @click="toggle"></span>
    </div>
</template>

<script>
   import { abstractField } from "vue-form-generator"
   export default {
         mixins: [ abstractField ],
         data: () => ({
            visible: false,
         }),
         watch: {
             // whenever question changes, this function will run
             value: function (newvalue, oldvalue) {
               this.toggle();
             }
           },
         methods: {
            toggle(){
              this.visible = !this.visible;
            },
            getStyle(){
              if(this.value === undefined) this.value = '#ccc';
              if(this.value.hex !== undefined) this.value = this.value.hex;
              return 'background-color:' + this.value;
            }
         },
   };
</script>
<style>
  .colorSq{
    height: 30px;
    width: 30px;
    display: block;
    cursor: pointer;
    border: 2px solid #c3c3c3;
    border-radius: 4px;
  }
</style>