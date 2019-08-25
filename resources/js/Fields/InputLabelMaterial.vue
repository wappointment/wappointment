<template>
    <LabelMaterial :allowReset="canReset" @reseted="reseted">
        <input class="form-control" type="text" :value="value" ref="input" :placeholder="valueInit" @input="changeInput" >
    </LabelMaterial>
</template>

<script>
import LabelMaterial from './LabelMaterial'
export default {
    props: {
        value: {
            type:String
        }
        , 
        ph:{
            type:String
        }, 
        allowReset: {
            type:Boolean,
            default: false
        }
    },
    components: {LabelMaterial},
    data: () => ({
        valueInit: '',
    }),
    computed: {
        canReset(){
            return this.allowReset ===true && this.ph !== this.value
        }
    },
    created(){
       this.valueInit =  this.ph !== undefined ? this.ph:this.value
    },
    methods: {
        reseted(element){
            this.$emit('input', this.ph)

            element.value = this.ph
        },
        changeInput(e){
            return this.$emit('input', e.target.value)
        }
    }
}   
</script>