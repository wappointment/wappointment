<template>
    <div v-if="mounted" class="wap-booking-fields">
        <div v-if="physicalSelected" class="address-service">
            <BookingAddress :service="locationObj">
                <WapImage faIcon="map-marked-alt" size="md" />
            </BookingAddress>
        </div>
        <div v-if="customFields.length > 0" v-for="fieldObject in customFields" class="wap-field">
            <div class="field-required"  v-if="fieldObject.type == 'phone'" :class="hasError(fieldObject.namekey)">
                <label :for="phoneId">{{getFieldObject(fieldObject).name}}</label>
                <PhoneInput 
                :phone="bookingFormExtended[fieldObject.namekey]"
                :countries="getPhoneCountries"
                @onInput="onInputPhone"
                :keyInput="fieldObject.namekey"
                @getId="getId"
                />
            </div>
            <template v-else>
                <component :is="getComponentType(fieldObject.type)" :name="fieldObject.namekey" 
                :error="getError(fieldObject.namekey)" :options="getFieldObject(fieldObject)" v-model="bookingFormExtended[fieldObject.namekey]" />
            </template>
        </div>
    </div>
</template>

<script>

import TextInput from './Fields/TextInput'
import Checkboxes from './Fields/Checkboxes'
import DateInput from './Fields/DateInput'
import Radios from './Fields/Radios'
import Checkbox from './Fields/Checkbox'
import Dropdown from './Fields/Dropdown'
import TextArea from './Fields/TextArea'
import BookingAddress from './Address'
import PhoneInput from './PhoneInput'
import MixinLegacy from './MixinLegacy'
import IsDemo from '../Mixins/IsDemo'
import {isEmail, isEmpty} from 'validator'
export default {
    components: window.wappointmentExtends.filter('bookingFormComponents', {
        TextInput,
        Checkboxes, 
        Radios,
        Checkbox,
        Dropdown,
        TextArea,
        BookingAddress,
        PhoneInput,
        DateInput
    }) ,
    props:['duration', 'location', 'custom_fields', 'data', 'options', 'service', 'disabledEmail', 'selectedSlot', 'schema'],
    mixins: [MixinLegacy, IsDemo],
    data: () => ({
        customFields: [],
        bookingFormExtended: {
            email: '',
        },
        errorsOnFields: {},
        phoneStatus:{},
        mounted: false,
        locationObj: null,
        phoneId: '',

    }),
    created(){
        
        if(this.isDemo){
            this.bookingFormExtended = this.options.demoData.form 
        }

        this.initForm()
    },
    mounted(){
        this.mounted = true
    },
    watch: {
        bookingFormExtended: {
            handler: function(newValue) {
                this.errorsOnFields = {}
                for (const key in newValue) {
                    if (newValue.hasOwnProperty(key)) {
                        let result = this.isFieldValid(key, newValue[key])
                        if(result !== true){
                            this.errorsOnFields[key] = result
                        }
                    }
                }

                //this.triggersDemoEvent(newValue)
                this.$emit('changed', this.bookingFormExtended, this.errorsOnFields)
            },
            deep: true
        },
    },
    computed: {
        getServiceFields(){
            let arrayInit = []
            if(this.service.options.slots !== undefined){
                arrayInit.push('slots')
            }

            return this.isLegacy ? this.legacyGetServiceFields:arrayInit.concat(this.service.options.fields)
        },
        legacyGetServiceFields(){
            let fields = ['name', 'email']
            if(this.phoneSelected || [undefined,'', false].indexOf(this.service.options.phone_required) === -1){
                fields.push('phone')
            }
            if(this.skypeSelected){
                fields.push('skype')
            }
            return fields
        },
        phoneSelected(){
            return this.locationObj.type == 2
        },
        physicalSelected(){
            return this.locationObj.type == 1
        },
        skypeSelected(){
            return this.locationObj.type == 3
        },
        getPhoneCountries(){
            return this.phoneSelected ? this.locationObj.options.countries:this.service.options.countries
        },
        forceEmail(){
            return window.apiWappointment.wp_user !== undefined && window.apiWappointment.wp_user.forceemail
        },
        componentMatches(){
            return window.wappointmentExtends.filter('bookingFormComponentsMatches', {
                'email':'TextInput',
                'input':'TextInput',
                'checkboxes':'Checkboxes',
                'radios':'Radios',
                'checkbox':'Checkbox',
                'select':'Dropdown',
                'textarea':'TextArea',
            })
        }
    },
    methods: {
        getComponentType(type){
            return this.componentMatches[type]
        },
        getFieldObject(fieldObject){
            let namekey = fieldObject.namekey=='name' ? 'fullname':fieldObject.namekey
            if(this.isDemo && this.options.form[namekey] !== undefined){
                fieldObject.name = this.options.form[namekey]
            }

            fieldObject = window.wappointmentExtends.filter('bookingFormFieldObject', fieldObject, {
                service:this.service, 
                namekey:this.options.form[namekey], 
                selectedSlot:this.selectedSlot})
            if(fieldObject.passedInitValue && this.isEmpty(String(this.bookingFormExtended[namekey]))) {
                this.bookingFormExtended[namekey] = fieldObject.passedInitValue
            }
            
            return fieldObject
        },
        initForm(){
            this.locationObj = Object.assign({},this.convertLocationLegacy(this.location))

            this.prepareSchema()
            this.initBookingForm()
            this.tryPrefill()
        },
        prepareSchema(){
            if(this.schema){
                this.customFields = [].concat(this.schema)
            }else{
                this.filterCustomFields()
            }
        },
        convertLocationLegacy(location){
            if(typeof location =='string'){
                switch (location) {
                    case 'physical':
                        return {
                            options:{
                                address: this.service.address
                            },
                            type:1
                        }
                    case 'phone':
                        return {
                            options:{
                                countries: this.service.options.countries
                            },
                            type:2
                        }
                    case 'skype':
                        return {
                            options:{},
                            type:3
                        }
                    case 'zoom':
                        return {
                            options:{},
                            type:5
                        }
                
                    default:
                        break;
                }
            }
            return location
        },
        getId(id){
            this.phoneId = id
        },
        tryPrefill(){
            if(window.apiWappointment.wp_user !== undefined && window.apiWappointment.wp_user.autofill){
                this.bookingFormExtended.email = window.apiWappointment.wp_user.email
                if(this.bookingFormExtended.name !== undefined){
                    this.bookingFormExtended.name = window.apiWappointment.wp_user.name
                }
            }
        },
        showOnlyIfEmailOrText(fieldObject){
            if(['input','email'].indexOf(fieldObject.type) === -1) return false
            if(fieldObject.type == 'email' && (this.disabledEmail !== undefined || this.forceEmail)) return false
            return true
        },

        isEmail(field){
            return isEmail(field)
        },
        isEmpty(field){
            return isEmpty(field)
        },
        isRegex(field, validationString){
            //strip the regex: prefix and strip the wrapping /
            validationString = validationString.replace('regex:','')

            if(validationString[0] == '/' && validationString[validationString.length-1] == '/') validationString = validationString.slice(1,-1)

            let regex = new RegExp(validationString)

            return regex.test(field)
        },
        isMax(field, validationString){
            let max = validationString.replace('max:','')

            return field.length <= max
        },
        isPhone(field){
            return this.phoneStatus[field]
        },
        isFieldValid(fieldKey, value){
            let fieldObject = this.getCFOptions(fieldKey)
            let validations = []
            if(fieldObject.validations !== undefined){
                validations = fieldObject.validations.split('|')
            }

            if(fieldObject.core!== undefined 
            || 
            (fieldObject.required !== undefined && fieldObject.required) 
            ||
            validations.indexOf('required') !== -1){
                return this.fieldPassValidations(fieldObject, validations, value)
            }
            return true
        },

        fieldPassValidations(fieldObject, validations, value){

            if(fieldObject.core === undefined && fieldObject.required!==true){
                return true
            }
            let field_required = 'Field is required'
            switch(fieldObject.type){

                case 'input':
                case 'textarea':
                case 'radios':
                case 'select':
                    if(this.isEmpty(String(value))){
                        return field_required
                    }
                    break;
                case 'checkboxes':
                    if(value.length === 0){
                        return field_required
                    }
                    break;

                case 'checkbox':
                    if(value !== true){
                        return field_required
                    }
                    break;
            }

            if( fieldObject.type == 'phone' && !this.isPhone(fieldObject.namekey)){
                return fieldObject.errors['is_phone']
            }

            if(( fieldObject.type == 'email' || validations.indexOf('email') !== -1) && !this.isEmail(value)){
                return fieldObject.errors['email']
            }

            for (const validationString of validations) {
                if(validationString.indexOf('regex:') !== -1 && !this.isRegex(value, validationString)){
                    return fieldObject.errors['regex']
                }

                if(validationString.indexOf('max:') !== -1 && !this.isMax(value, validationString)){
                    return fieldObject.errors['max']
                }
            }

            return true
        },
        initBookingForm(){
            let bf = {}
            for (const iterator of this.customFields) {
                bf[iterator.namekey] = ''
            }
            
            this.bookingFormExtended = Object.assign({}, bf)
            this.legacyFill()
        },

        /**
         * check whether this is still needed
         */
        legacyFill(){
            if(this.data !== undefined && Object.keys(this.data).length > 1){
                for (const key in this.bookingFormExtended) {
                    if (this.bookingFormExtended.hasOwnProperty(key) && this.data[key]!== undefined) {
                        this.bookingFormExtended[key] = this.data[key]
                    }
                }
            }
        },
        getCFOptions(namekey){
            for (const customF of this.custom_fields) {
                if(customF.namekey == namekey) {
                    if(customF.core !== undefined){
                        switch (customF.namekey) {
                            case 'name':
                                customF.name = customF.updated === true ?customF.name: this.options.form['fullname']
                                return customF
                            case 'email':
                            case 'phone':
                            case 'skype':
                            default:
                                customF.name = customF.updated === true ?customF.name:this.options.form[customF.namekey]
                                return customF

                        }
                        
                    }
                    return customF
                }
            }
        },
        insertCustomFields(){
            if(this.locationObj.options === undefined){
                return false
            }
            if(this.locationObj.options.fields === undefined || !Array.isArray(this.locationObj.options.fields)){
                this.locationObj.options.fields = []
            }
            if(this.phoneSelected && this.locationObj.options.fields.indexOf('phone') === -1){
                this.locationObj.options.fields.unshift('phone') //inser phone to the beginning
            }
            if(this.skypeSelected && this.locationObj.options.fields.indexOf('skype') === -1){
                this.locationObj.options.fields.unshift('skype') //inser skype to the beginning
            }
        },
        filterCustomFields(){
            
            let fields_src = {'src1': this.reorderFields(this.getServiceFields)}
            if(!this.isLegacy){
                this.insertCustomFields()
                fields_src.src2 = this.reorderFields(this.locationObj.options.fields) 
            }
            let customFields = []
            for (const key in fields_src) {
                if (fields_src.hasOwnProperty(key) && fields_src[key] !== undefined) {
                    for (const iterator of fields_src[key]) {
                        let cfieldslist = []
                        for (const cfield of customFields) {
                            cfieldslist.push(cfield['namekey'])
                        }
                        
                        if(cfieldslist.indexOf(iterator) === -1){
                            let cf_found = this.getCFOptions(iterator)
                            if(cf_found!== undefined){
                                customFields.push(cf_found)
                            }
                        } 
                    }
      
                }
            }
            this.customFields = customFields[0].sorting !== undefined ? customFields.sort((a,b) => a.sorting > b.sorting):customFields
        },

        reorderFields(sourceFields){
            return this.custom_fields.filter(e => sourceFields.indexOf(e.namekey) !== -1).map(el => el.namekey)
        },

        hasError(field){
            return [undefined,false].indexOf(this.errorsOnFields[field]) === -1 ? 'isInvalid':'isValid'
        },
        getError(field){
            return this.hasError(field) ? this.errorsOnFields[field]:''
        },
        onInputPhone({ number, isValid, country }, keyInput) {
            this.bookingFormExtended[keyInput] = number
            this.phoneStatus[keyInput] = isValid
        },
    }

}
</script>
<style >
.wap-booking-fields .field-required .has-error {
    color: var(--wappo-error-tx);
    font-size: .6em;
}
</style>
