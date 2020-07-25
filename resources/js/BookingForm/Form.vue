<template>
    <transition name="slide-fade">
        <div v-if="mounted" class="max400">
            <div class="text-center">
                <div class=" my-2">
                    <div><strong>{{options.form.header}}</strong></div>
                    <div v-if="appointment_starts_at"><strong>{{ appointment_starts_at }}</strong></div>
                </div>
                <div class="wappointment-errors" v-if="errors.length > 0">
                    <div v-for="error in errors">
                        {{ error }}
                    </div>
                </div>
                
                <div v-if="serviceHasTypes">
                    <div v-if="allowedType('physical')" @click="selectType('physical')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: physicalSelected}">
                        <FontAwesomeIcon icon="map-marked-alt" size="lg"/>
                        <div>{{options.form.inperson}}</div>
                    </div>
                    <div v-if="allowedType('phone')" @click="selectType('phone')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: phoneSelected}">
                        <FontAwesomeIcon icon="phone" size="lg"/>
                        <div>{{options.form.byphone}}</div>
                    </div>
                    <div v-if="allowedType('skype')" @click="selectType('skype')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: skypeSelected}">
                        <FontAwesomeIcon :icon="['fab', 'skype']" size="lg"/>
                        <div>{{options.form.byskype}}</div>
                    </div>
                </div>

            </div>
            <transition name="slide-fade">
                <div v-if="selectedServiceType">
                    <div class="wap-booking-fields">
                        <div v-if="physicalSelected" class="address-service">
                            <BookingAddress :service="service">
                                <FontAwesomeIcon icon="map-marked-alt" size="lg"/>
                            </BookingAddress>
                        </div>
                        <div class="wap-field field-required" :class="hasError('name')">
                            <label for="name">{{options.form.fullname}}</label>
                            <p class="d-flex">
                                <input class="form-control" id="name" type="text" autocomplete="name" v-model="bookingForm.name" :required="true">
                            </p>
                        </div>
                        <div class="wap-field field-required" :class="hasError('email')">
                            <label for="email">{{options.form.email}}</label>
                            <p class="d-flex">
                                <input type="email" id="email" class="form-control" autocomplete="email" v-model="bookingForm.email" :required="true">
                            </p>
                        </div>
                        <div v-if="requirePhoneInput" class="wap-field field-required" :class="hasError('phone')">
                            <PhoneInput 
                            :label="options.form.phone"
                            :phone="bookingForm.phone"
                            :countries="service.options.countries"
                            @onInput="onInput" 
                            ></PhoneInput>
                        </div>
                        <div v-if="skypeSelected" class="wap-field field-required" :class="hasError('skype')">
                            <label for="skype">{{options.form.skype}}</label>
                            <p class="d-flex">
                                <input class="form-control" type="text" id="skype" v-model="bookingForm.skype">
                            </p>
                        </div>

                        <div v-if="termsIsOn" class="wap-terms" v-html="getTerms"></div>
                    </div>
                </div>
            </transition>
            <div class="d-flex wbtn-confirm my-2">
                <span class="wbtn-secondary wbtn" role="button" @click="back">{{options.form.back}}</span>
                <span v-if="canSubmit" class="wbtn-primary wbtn flex-fill m-0" role="button" @click="confirm">{{options.form.confirm}}</span>
                <span v-else class="wbtn-primary wbtn wbtn-disabled flex-fill m-0" role="button" disabled>{{options.form.confirm}}</span>
            </div>
            <CountryStyle/>
        </div>
    </transition>
</template>

<script>
import abstractFront from '../Views/abstractFront'
import BookingAddress from './Address'
import PhoneInput from './PhoneInput'
import Strip from '../Helpers/Strip'

import {isEmail, isEmpty} from 'validator'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkedAlt, faPhone} from '@fortawesome/free-solid-svg-icons'
import { faSkype} from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faMapMarkedAlt, faPhone, faSkype)
const CountryStyle = () => import(/* webpackChunkName: "style-flag" */ '../Components/CountryStyle')

export default {
    extends: abstractFront,
    mixins: [ Strip],
    props: ['service','selectedSlot', 'options', 'errors', 'data', 'timeprops', 'relations', 'appointment_starts_at'],
    components: {
        BookingAddress,
        PhoneInput,
        FontAwesomeIcon,
        CountryStyle
    }, 
    data: () => ({
        bookingForm: {
            email: '',
            phone: '',
            skype: '',
            name: '',
        },
        phoneValid: false,
        errorsOnFields: {},
        selectedServiceType: false,
        mounted: false,
        disabledButtons: false,
    }),
    watch: {
        bookingForm: {
            handler: function(newValue) {
                this.errorsOnFields = {}

                if(isEmpty(newValue.name) ) this.errorsOnFields.name = true
                if(isEmpty(newValue.email) || !isEmail(newValue.email)) this.errorsOnFields.email = true
                if(this.requirePhoneInput && (isEmpty(newValue.phone) || !this.phoneValid)) this.errorsOnFields.phone = true
                if(this.skypeSelected && (isEmpty(newValue.skype) || !this.skypeValid)) this.errorsOnFields.skype = true
                if(this.disabledButtons) {
                    this.options.eventsBus.emits('dataDemoChanged', newValue)
                } 
            },
            deep: true
        }
    },
    created(){

        if(this.options.demoData !== undefined){
            this.bookingForm = this.options.demoData.form 
            this.selectedServiceType = this.bookingForm.type
            this.disabledButtons = true
        }
    },
    mounted(){
        if(this.service !== false && !this.serviceHasTypes) {
            this.selectDefaultType()
        }
        this.mounted = true
        if(Object.keys(this.data).length > 1){
            this.bookingForm = Object.assign({},this.data)
            if(this.bookingForm.type!==undefined)this.selectedServiceType = this.bookingForm.type
        }

    },
    computed: {
        getTerms(){
            return this.strip(this.options.form.terms).replace('[link]', '<a href="'+this.options.form.terms_link+'" target="_blank">').replace('[/link]', '</a>')
        },
        termsIsOn(){
            return this.options.form.check_terms === true
        },
        canSubmit(){
            return this.selectedServiceType && Object.keys(this.errorsOnFields).length < 1 && !this.dataEmpty
        },
        requirePhoneInput(){
            return this.phoneSelected || [undefined,false,''].indexOf(this.service.options.phone_required) === -1 
        },
        phoneSelected(){
            return this.selectedServiceType == 'phone'
        },
        physicalSelected(){
            return this.selectedServiceType == 'physical'
        },
        skypeSelected(){
            return this.selectedServiceType == 'skype'
        },
        skypeValid(){
            return /^[a-zA-Z][a-zA-Z0-9.\-_]{5,31}$/.test(this.bookingForm.skype)
        },
        serviceHasTypes(){
            return this.service.type.length > 1 
        },
        dataEmpty(){
            for (const key in this.bookingForm) {
                if (this.bookingForm.hasOwnProperty(key)) {
                    if(this.bookingForm[key]!== '') return false
                }
            }
            return true
        },
        validators(){
            return {
                'isEmail': isEmail,
                'isEmpty': isEmpty,
            }
        }
    },
    methods: {

        back(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'selection')
              return
            } 
            
            this.$emit('back', this.relations.prev,{selectedSlot:false})
        },
        
        confirm(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'confirmation')
              return
            } 
            let data = this.bookingForm
            data.time = this.selectedSlot
            data.type = this.selectedServiceType
            data.ctz = this.timeprops.ctz
            //turns loading mode on in parent
            this.$emit('loading', {loading:true, dataSent: data})
            //create request
            this.saveBookingRequest(data)
            .then(this.appointmentBooked)
            .catch(this.appointmentBookingError)
        },
        

        async saveBookingRequest(data) {
            return await this.serviceBooking.call('save', data)
        }, 

        appointmentBooked(result){
            let relationnext = window.wappointmentExtends.filter('AppointmentBookedNextScreen', this.relations.next, 
            {result:result, service: this.service} )
            
            this.$emit('confirmed', relationnext , {
                appointmentSavedData:result.data.appointment, 
                isApprovalManual:(result.data.status == 0), 
                appointmentSaved: true, 
                appointmentKey: result.data.appointment.edit_key, 
                loading: false
            })
        },

        appointmentBookingError(error){
            this.$emit('serviceError',error)
        },

        
        selectDefaultType(){
            this.selectedServiceType = this.service.type[0]
        },
        
        allowedType(type){
            return this.service.type.indexOf(type) !== -1
        },
        selectType(type){
            this.selectedServiceType = type
            let bookingForm =  Object.assign ({}, this.bookingForm)
            this.bookingForm = {}
            this.bookingForm = bookingForm
            this.bookingForm.type = type
        },

        hasError(field){
            if(this.bookingForm[field] === '') return ''
            if(this.errorsOnFields[field] !== undefined && this.errorsOnFields[field]===true) return 'isInvalid'
            return 'isValid'
        },
        onInput({ number, isValid, country }) {
            this.bookingForm.phone = number
            this.phoneValid = isValid
        },
    }

}
</script>
<style>

.wap-front .phone-field .dropdown ul {
    position: initial;
    max-width: 266px;
    min-width: 224px;
    margin-top: 12px;
    overflow-y: scroll !important;
    overflow: hidden;
}

.wap-front .phone-field .dropdown {
    border-radius: .2em;
}

.wap-front .phone-field .dropdown.open + input {
    display:none;
}

.wap-front .address-service{
    border-radius: .2em;
    padding: .3em;
    margin: auto;
    text-align: left;
    margin-bottom: .6rem;
}

.wap-front .address-service .icon-address{
    padding: 0 .3em;
}

.wap-front .address-service address{
    margin-left: .6em;
}

.wap-booking-fields {
    text-align: left;
    margin: .5em 0;
}

.wap-booking-fields label{
    margin-bottom: 0;
    font-size: .9em;
}

.wap-terms{
    font-size: .7em;
    text-align: left;
    line-height: 1.4;
}

</style>
