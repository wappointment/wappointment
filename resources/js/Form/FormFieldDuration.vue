<template>
    <div :class="className">
        <label class="pl-2"v-if="[undefined,''].indexOf(label) === -1">
            {{ label}}
        </label>
        <div class="d-flex" :class="getClassWrapper">
            <range-slider
                class="wslider"
                :min="definition.min"
                :max="definition.max"
                :step="definition.step"
                :value="updatedValue"
                @input="updateTemp" 
                @change="dragEnd" 
                :maxlength="definition.max"
                :readonly="definition.readonly">
            </range-slider> 
            <small v-if="!editableInput" data-tt="Click to edit" @click="editableInput=true">{{ formatedValue }}</small>
            <input v-else @keyup.enter.prevent.stop="updateValueInput" @focusout.prevent="updateValueInput" v-model="tempVal" type="number" size="2"/>
            <small id="emailHelp" v-if="tip" class="form-text text-muted">{{ tip }}</small>
        </div>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import RangeSlider from '../Plugins/vue-range-slider/vue-range-slider'
export default {
    name:'core-duration',
    props:{
        definition:{
            type: Object,
            default:() => {
                return {
                    min:0,
                    max:60,
                    step:5,
                    int:true,
                    unit: 'min'
                }
            }
        }
    },
    components: {RangeSlider},
    mixins: [AbstractField],
    data:() => ({
        editableInput: false,
        tempVal: '',
    }),
    computed:{
        formatedValue(){
            return this.tempVal + ' ' + (this.definition.unit !== undefined ? this.definition.unit:'')
        },
        className(){
            return this.definition.class !== undefined ? this.definition.class:'w-100'
        }
    },
    watch:{
        updatedValue(newVal, oldVal){
            this.tempVal = newVal
        }
    },

    methods: {
        updateValueInput(){
            this.editableInput = false
            this.updatedValue = this.tempVal
        },
        updateTemp(tempval){
            this.tempVal = tempval
        },
        dragEnd(newval){
            if(this.definition.int === undefined ){
                newval = parseFloat(newval).toFixed(1)
            } 
            this.updatedValue = newval
        }
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
.wrange-slider.wslider {
    width: 78%;
}
.field-wrap {
    display: block;
}

.wrange-slider {
  display: inline-block;
  padding: 0 10px;
  height: 20px;
  width: 130px;
}

.wrange-slider.disabled {
  opacity: 0.5;
}

.wrange-slider-inner {
  display: inline-block;
  position: relative;
  height: 100%;
  width: 100%;
}

.wrange-slider-rail,
.wrange-slider-fill {
  display: block;
  position: absolute;
  top: 50%;
  left: 0;
  height: 4px;
  border-radius: 2px;
  -webkit-transform: translateY(-50%);
      -ms-transform: translateY(-50%);
          transform: translateY(-50%);
}

.wrange-slider-rail {
  width: 100%;
  background-color: #e2e2e2;
}

.wrange-slider-fill {
  background-color: var(--primary);
}

.wrange-slider-knob {
  display: block;
  position: absolute;
  top: 50%;
  left: 0;
  -webkit-box-sizing: border-box;
          box-sizing: border-box;
  height: 20px;
  width: 20px;
  border: 1px solid #f5f5f5;
  border-radius: 50%;
  background-color: #fff;
  -webkit-box-shadow: 1px 1px rgba(0, 0, 0, 0.2);
          box-shadow: 1px 1px rgba(0, 0, 0, 0.2);
  -webkit-transform: translate(-50%, -50%);
      -ms-transform: translate(-50%, -50%);
          transform: translate(-50%, -50%);
  cursor: pointer;
}

.wrange-slider-hidden {
  display: none;
}

</style> 