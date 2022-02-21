<template>
    <div >
        <VueTelInput placeholder="" 
        v-model="phoneval"
        @onInput="onInput" 
        :hasLabel="label"
        :classField="className"
        :fieldMaterial="fieldMaterial"
        :id="generatedId"
        :onlyCountries="countries"></VueTelInput>
        <CountryStyle/>
    </div>
    
</template>

<script>

const VueTelInput = () => import(/* webpackChunkName: "VueTelInput" */ '../Plugins/vue-tel-input/index.js')
const CountryStyle = () => import(/* webpackChunkName: "style-flag" */ '../Components/CountryStyle')
export default {
    mounted(){
        this.phoneval = this.phone
    },
    components: {VueTelInput, CountryStyle}, 
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
        keyInput: {
            type:String,
            default: ''
        },
        countries: {
            type: Array,
            default: () => [],
        },
        fieldMaterial:{
            type: Boolean,
            default:false
        }
    },
    data: () => ({
        phoneval: '',
        generatedId: "telinput-"+(new Date()).getTime()
    }),
    created(){
        this.$emit('getId', this.generatedId)
    },
    methods: {
        onInput(data){
            this.$emit('onInput',data, this.keyInput)
        }
    },
}
</script>
<style>
.wap-booking-fields .isInvalid .search.flex-fill.show {
    border-right: none !important;
    box-shadow: none;
}
</style>