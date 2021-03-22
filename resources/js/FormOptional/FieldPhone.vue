<template>
    <div>
        <PhoneInput 
            :label="labelDefault"
            :fieldMaterial="true"
            :phone="updatedValue"
            @onInput="onInput"
            :forceFloat="updatedValue!=''"
            />
    </div>
</template>

<script>
import PhoneInput from '../BookingForm/PhoneInput'
import AbstractField from '../Form/AbstractField'
export default {
    extends: AbstractField,
    name:'opt-phone',
    components: {PhoneInput},
    props: {
        labelDefault: {
            type: String,
            default: 'Select phone'
        },
    },
    mounted(){
        this.$emit('activated')
    },
    methods: {
        updateTimezone(value) {
            this.updatedValue = value
        },
        onInput({ number, isValid, country }) {
            this.updatedValue = number
        },
    }
}
</script>
<style >
.phone-field-wrap input.tel,
.phone-field-wrap input.tel:focus{
    border:none;
    box-shadow: none;
}
</style>
