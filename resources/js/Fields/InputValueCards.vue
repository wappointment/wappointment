<template>
    <LabelMaterial extraClass="phvc" :forceFloat="true">
        <div class="form-control d-flex flex-wrap" @click.capture="focusInput">
            <ValueCard v-if="valueInit.length > 0" v-for="val in valueInit" :key="val"
                        :value="val" @discard="discardElement">{{ val }}</ValueCard>
            <input type="text" class="input-t" :value="valueInputField" ref="input" :placeholder="ph"  @focusout="changeInputValue" @keyup.enter="changeInputValue" >
        </div>
    </LabelMaterial>
</template>

<script>
import InputLabelMaterial from './InputLabelMaterial'
import ValueCard from './ValueCard'
export default {
    props:{
        phIsvalue: {
            type:Boolean,
            default: false
        },
        value: {
            type:Array
        },
    },
    extends: InputLabelMaterial,
    components:{ValueCard},
    data: () => ({
        valueInputField: '',
    }),

    methods: {
        changeInputValue(e){
            if(e.target.value.trim() == ''){
                return
            }
            this.valueInit.push(e.target.value)
            this.valueInputField = ''
            return this.sendValueChange()
        },
        discardElement(val){
            this.valueInit = this.valueInit.filter(e => e != val)
            return this.sendValueChange()
        },
        sendValueChange(){
            this.$emit('input', this.valueInit)
            return this.$emit('changed')
        },
        focusInput(){
            this.$refs.input.focus()
        }
    }
}   
</script>
<style>
.label-wrapper.phvc label {
    z-index:2;
}
.label-wrapper.phvc .input-t {
    border: 0 !important;
    box-shadow: none !important;
    padding-top: 0 !important;
}
.label-wrapper div.form-control{
    height: auto;
    padding-top: 1rem;
}
</style>