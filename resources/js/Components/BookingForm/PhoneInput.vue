<template>
    <div>
        <label v-if="label" :for="generatedId">{{ label }}</label>
        <VueTelInput placeholder="" 
        v-model="phoneval"
        @onInput="onInput" 
        :classField="className"
        :id="generatedId"
        :onlyCountries="countries"></VueTelInput>
    </div>
    
</template>

<script>
import VueTelInput from '../../Plugins/vue-tel-input/index.js'
export default {
    mounted(){
        this.phoneval = this.phone
    },
    components: {
        VueTelInput,
    }, 
    props: {
        phone: {
            type:String
        },
        className: {
            type:String,
            default: ''
        },
        label: {
            type:String
        },
        countries: {
            type: Array,
            default: () => [],
        },
    },
    data: () => ({
        phoneval: '',
        generatedId: "telinput-"+(new Date()).getTime()
    }),
    methods: {
        onInput(data){
            this.$emit('onInput',data)
        }
    },
}
</script>
