<template>
    <div class="w-100 slider-duration">
        <div class="pl-2"v-if="[undefined,''].indexOf(label) === -1">
            {{ label}}
        </div>
        <div class="d-flex" :class="getClassWrapper">
            <range-slider
                class="slider"
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
            <input v-else @keyup.enter.prevent="updateValueInput" v-model="tempVal" type="number" size="2"/>
            <small id="emailHelp" v-if="tip" class="form-text text-muted">{{ tip }}</small>
        </div>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import RangeSlider from 'vue-range-slider'
import 'vue-range-slider/dist/vue-range-slider.css'
export default {
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
.slider {
    width: 78%;
}
.slider-duration{
    min-width: 300px;
}
.field-wrap {
    display: block;
}
</style> 