<template>
    <div v-if="mounted" class="wap-booking-fields">
        <div v-if="physicalSelected" class="address-service">
            <BookingAddress :service="location">
                <WapImage faIcon="map-marked-alt" size="md" />
            </BookingAddress>
        </div>
        <div v-for="fieldObject in customFields" class="wap-field">
            <TextInput v-if="showOnlyIfEmailOrText(fieldObject)" :name="fieldObject.namekey" 
            :error="getError(fieldObject.namekey)" :options="fieldObject" v-model="bookingFormExtended[fieldObject.namekey]" />
            <div class="field-required"  v-if="fieldObject.type == 'phone'" :class="hasError(fieldObject.namekey)">
                <label :for="phoneId">{{options.form.phone}}</label>
                <PhoneInput 
                :phone="bookingFormExtended[fieldObject.namekey]"
                :countries="getPhoneCountries"
                @onInput="onInputPhone"
                :keyInput="fieldObject.namekey"
                @getId="getId"
                />
            </div>
            <Checkboxes v-if="'checkboxes' == fieldObject.type" :name="fieldObject.namekey" 
            :error="getError(fieldObject.namekey)" :options="fieldObject" v-model="bookingFormExtended[fieldObject.namekey]" />
            <Radios v-if="'radios' == fieldObject.type" :name="fieldObject.namekey" 
            :error="getError(fieldObject.namekey)" :options="fieldObject" v-model="bookingFormExtended[fieldObject.namekey]" />
            <Checkbox v-if="'checkbox' == fieldObject.type" :name="fieldObject.namekey" 
            :error="getError(fieldObject.namekey)" :options="fieldObject" v-model="bookingFormExtended[fieldObject.namekey]" />
            <Dropdown v-if="'select' == fieldObject.type" :name="fieldObject.namekey" 
            :error="getError(fieldObject.namekey)" :options="fieldObject" v-model="bookingFormExtended[fieldObject.namekey]" />
             <TextArea v-if="'textarea' == fieldObject.type" :name="fieldObject.namekey" 
            :error="getError(fieldObject.namekey)" :options="fieldObject" v-model="bookingFormExtended[fieldObject.namekey]" />
        </div>
    </div>
</template>

<script>

import TextInput from '../BookingForm/Fields/TextInput.vue'
import Checkboxes from '../BookingForm/Fields/Checkboxes.vue'
import Radios from '../BookingForm/Fields/Radios.vue'
import Checkbox from '../BookingForm/Fields/Checkbox.vue'
import Dropdown from '../BookingForm/Fields/Dropdown.vue'
import TextArea from '../BookingForm/Fields/TextArea.vue'
import BookingAddress from './Address'
import PhoneInput from './PhoneInput'
export default {
    components: {
        TextInput,
        Checkboxes, 
        Radios,
        Checkbox,
        Dropdown,
        TextArea,
        BookingAddress,
        PhoneInput
    },
    props:['duration', 'location', 'custom_fields', 'data', 'disabledButtons', 'options', 'service', 'validators', 'disabledEmail'],
    data: () => ({
        customFields: [],
        bookingFormExtended: {
            email: '',
        },
        errorsOnFields: {},
        phoneStatus:{},
        mounted: false,
        locationObj: null,
        phoneId: ''
    }),
    created(){
        
        if(this.options !== undefined && this.options.demoData !== undefined){
            this.bookingFormExtended = this.options.demoData.form 
        }

        this.locationObj = Object.assign({},this.location)
        this.registerCustomFields()
        this.initBookingForm()
        this.tryPrefill()
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
         
                if(this.disabledButtons) {
                    this.options.eventsBus.emits('dataDemoChanged', newValue)
                } 
                this.$emit('changed', this.bookingFormExtended, this.errorsOnFields)
            },
            deep: true
        }
    },
    computed: {
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
            return this.phoneSelected ? this.location.options.countries:this.service.options.countries
        }
    },
    methods: {
        getId(id){
            this.phoneId = id
        },
        tryPrefill(){
            if(window.apiWappointment.wp_user !== undefined){
                this.bookingFormExtended.email = window.apiWappointment.wp_user.email
                if(this.bookingFormExtended.name !== undefined){
                    this.bookingFormExtended.name = window.apiWappointment.wp_user.name
                }
            }
        },
        showOnlyIfEmailOrText(fieldObject){
            if(['input','email'].indexOf(fieldObject.type) === -1) return false
            if(fieldObject.type == 'email' && this.disabledEmail !== undefined) return false
            return true
        },

        isEmail(field){
            return this.validators['isEmail'](field)
        },
        isEmpty(field){
            return this.validators['isEmpty'](field)
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
            
            let field_required = 'Field is required'
            switch(fieldObject.type){

                case 'input':
                case 'textarea':
                case 'radios':
                case 'select':
                    if(this.isEmpty(value)){
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

            for (let i = 0; i < validations.length; i++) {
                const validationString = validations[i]
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
            for (let i = 0; i < this.customFields.length; i++) {
                bf[this.customFields[i].namekey] = ''
            }
            
            this.bookingFormExtended = Object.assign({}, bf)
            if(Object.keys(this.data).length > 1){
                for (const key in this.bookingFormExtended) {
                    if (this.bookingFormExtended.hasOwnProperty(key)) {
                        if(this.data[key]!== undefined) this.bookingFormExtended[key] = this.data[key]
                    }
                }
            
            }
        },
        getCFOptions(fieldName){
            for (let i = 0; i < this.custom_fields.length; i++) {
                if(this.custom_fields[i].namekey == fieldName) {
                    let customF = this.custom_fields[i]
                    if(customF.core !== undefined){
                        switch (customF.namekey) {
                            case 'name':
                                customF.name = this.options.form['fullname']
                                return customF
                            case 'email':
                                customF.name = this.options.form[customF.namekey]
                                return customF
                            case 'phone':
                                customF.name = this.options.form[customF.namekey]
                                return customF
                            case 'skype':
                                customF.name = this.options.form[customF.namekey]
                                return customF
                        }
                        
                    }
                    return customF
                }
            }
        },
        registerCustomFields(){

            if(this.locationObj.options.fields === undefined || !Array.isArray(this.locationObj.options.fields)){
                this.locationObj.options.fields = []
            }
            if(this.phoneSelected){
                if(this.locationObj.options.fields.indexOf('phone') === -1)this.locationObj.options.fields.unshift('phone')
            }
            if(this.skypeSelected){
                if(this.locationObj.options.fields.indexOf('skype') === -1)this.locationObj.options.fields.unshift('skype')
            }
            
    
            let fields_src = {'src1':this.service.options.fields, 'src2':this.locationObj.options.fields}
            for (const key in fields_src) {
                if (fields_src.hasOwnProperty(key) && fields_src[key] !== undefined) {
                    for (let i = 0; i < fields_src[key].length; i++) {
                        let cfieldslist = []
                        for (let j = 0; j < this.customFields.length; j++) {
                             cfieldslist.push(this.customFields[j]['namekey'])
                        }
                        
                        if(cfieldslist.indexOf(fields_src[key][i]) === -1){
                            let cf_found = this.getCFOptions(fields_src[key][i])
                            if(cf_found!== undefined)this.customFields.push(cf_found)
                        } 
                         
                    }
                }
            }
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
