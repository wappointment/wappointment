<template>
    <transition name="slide-fade">
        <div v-if="mounted">
            <div class="form-summary text-center" v-if="isCompactHeader">
                <div class="my-2">
                    <div v-if="appointment_starts_at">
                        <div class="wselected wclosable wmy-4 d-flex align-items-center d-flex-inline">
                            <WapImage :faIcon="['far','clock']" size="auto" />
                            <span class="welementname wml-2">{{ appointment_starts_at }}</span>
                            <span class="wclose" @click="back" ></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wappointment-errors" v-if="errors.length > 0">
                <div v-for="error in errors">
                    {{ error }}
                </div>
            </div>
            <AppointmentTypeSelection v-if="legacyServiceHasTypes" 
            :options="options" 
            :typeSelected="selection" 
            :typesAllowed="service.type"
            @selectType="selectType" />
            <transition name="slide-fade">
                <div v-if="showFormInputs">
                    <FieldsGenerated @changed="changedBF" @dataDemoChanged="dataDemoChanged" 
                    :validators="validators" :custom_fields="custom_fields" 
                    :service="service" :location="location" :data="data" 
                    :options="options" />
                
                    <div v-if="termsIsOn" class="wap-terms" v-html="getTerms"></div>
                </div>
            </transition>
            <div class="d-flex wbtn-confirm">
                <div class="mr-2"><span class="wbtn-secondary wbtn" @click="back">{{options.form.back}}</span></div>
                <span v-if="canSubmit" class="wbtn-primary wbtn flex-fill mr-0" @click="confirmSwitch">{{options.form.confirm}}</span>
                <span v-else class="wbtn-primary wbtn wbtn-disabled flex-fill mr-0" disabled>{{options.form.confirm}}</span>
            </div>
            <CountryStyle/>
        </div>
    </transition>
</template>

<script>

import AppointmentTypeSelection from './AppointmentTypeSelection'
import AbstractFront from './AbstractFront'
import {isEmail, isEmpty} from 'validator'
const CountryStyle = () => import(/* webpackChunkName: "style-flag" */ '../Components/CountryStyle')
import MixinTypeSelected from './MixinTypeSelected'
import WappoServiceBooking from '../Services/V1/BookingN'
import FieldsGenerated from './FieldsGenerated'
import FormMixinLegacy from './FormMixinLegacy'
import MixinLegacy from './MixinLegacy'
import BookingAddress from './Address'
import PhoneInput from './PhoneInput'
import IsDemo from '../Mixins/IsDemo'
import HasPaidService from '../Mixins/HasPaidService'
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    extends: AbstractFront,
    mixins: [ MixinTypeSelected, FormMixinLegacy,MixinLegacy, IsDemo, CanFormatPrice, HasPaidService],
    props: ['service', 'selectedSlot', 'options', 'errors', 'data', 
    'timeprops', 'relations', 'appointment_starts_at',
    'duration', 'location', 'custom_fields', 'staffs','selectedStaff','selectedPackage','selectedVariation'],
    components: {
        BookingAddress,
        PhoneInput,
        CountryStyle,
        FieldsGenerated,
        AppointmentTypeSelection
    }, 
    data: () => ({
        phoneId:'',
        phoneValid: false,
        errorsOnFields: {},
        mounted: false,
        bookingFormExtended: null,
        canDisplayInputs: false,
        staff: null
    }),

    created(){
        
        this.serviceAppointmentService = this.$vueService(new WappoServiceBooking)
        if(this.isLegacy){
            if(this.service.type.length == 1 || this.disabledButtons){
                this.selectType(this.service.type[0])
            }
        }else{
            this.setCanDisplay(this.location)
            this.setStaff()
        }
    },

    mounted(){
        this.mounted = true
    },

    computed: {
        isCompactHeader(){
            return this.options.general === undefined || [undefined, false].indexOf(this.options.general.check_header_compact_mode) === -1
        },
        getTerms(){
            return this.cleanString(this.options.form.terms).replace('[link]', '<a href="'+this.cleanString(this.options.form.terms_link)+'" target="_blank">').replace('[/link]', '</a>')
        },
        termsIsOn(){
            return this.options.form.check_terms === true
        },
        
        requirePhoneInput(){
            return this.phoneSelected || [undefined,false,''].indexOf(this.service.options.phone_required) === -1 
        },

        skypeValid(){
            return /^[a-zA-Z][a-zA-Z0-9.\-_]{5,31}$/.test(this.bookingForm.skype)
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
        },
        canSubmit(){
            return  Object.keys(this.errorsOnFields).length < 1
        },
        
        legacyServiceHasTypes(){
            return this.isLegacy && this.service.type.length > 1
        },

        showFormInputs(){
            return this.canDisplayInputs && (!this.legacyServiceHasTypes || (this.legacyServiceHasTypes && typeof this.location == 'string' && this.location !== ''))
        }
    },
    methods: {
        setStaff(staff_key = false){
            this.staff = this.selectedStaff
        },

        getId(id){
            this.phoneId = id
        },

        tryPrefill(){
            if(window.apiWappointment.wp_user !== undefined && window.apiWappointment.wp_user.autofill){
                this.bookingForm.email = window.apiWappointment.wp_user.email
                this.bookingForm.name = window.apiWappointment.wp_user.name
            }
        },
    
        selectDefaultType(){
            this.selection = this.service.type[0]
        },
        
        selectType(type){
            this.selection = type
            let bookingForm =  Object.assign ({}, this.bookingForm)
            this.bookingForm = {}
            this.bookingForm = bookingForm
            this.bookingForm.type = type
            this.dataDemoChanged(this.bookingForm)
            this.$emit('selectedLocation', type)
            this.canDisplayInputs = false
            setTimeout(this.setCanDisplay, 100);
        },

        setCanDisplay(){
            this.canDisplayInputs = true
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

        changedBF(newval, errors){
            this.bookingFormExtended = newval
            this.errorsOnFields = errors
            this.dataDemoChanged(newval)
        },

        dataDemoChanged(newValue){
            if(this.disabledButtons) {
                newValue.type = this.selection
                this.options.eventsBus.emits('dataDemoChanged', newValue)
            } 
        },

        back(){

            if(this.triggersDemoEvent('selection')){
                return
            }
            this.$emit('back', this.relations.prev,{selectedSlot:false})
        },

        confirmSwitch(){
            return this.isLegacy ? this.confirmLegacy():this.confirm()
        },

        confirm(){
            if(this.triggersDemoEvent('confirmation')){
                return
            }
            let data = this.bookingFormExtended
            data.time = this.selectedSlot
            data.ctz = this.timeprops.ctz
            data.service = this.service.id
            data.location = this.location.id
            data.duration = this.duration
            data.staff_id = this.staff.id
            if(this.selectedPackage){
                data.package_id = this.selectedPackage.id
                data.package_price_id = this.selectedVariation.price_id
            }
            
            //turns loading mode on in parent
            this.$emit('loading', {loading:true, dataSent: data})
            //create request
            this.saveBookingRequest(data)
            .then(this.appointmentBooked)
            .catch(this.appointmentBookingError)
        },

        async saveBookingRequest(data) {
            return await this.serviceAppointmentService.call('save', data)
        }, 

        appointmentBooked(result){
            if(result.data.result !== undefined){
                let data ={
                    appointmentSavedData: result.data.appointment,
                    order: result.data.order,
                    isApprovalManual: result.data.status == 0, 
                    appointmentSaved: true, 
                    appointmentKey: result.data.appointment.edit_key, 
                    loading: false
                }
                console.log('data',data)
                this.$emit('confirmed', 
                this.mustPay ? 'BookingPaymentStep' :this.getAddonNextScreen(result.data.result), 
                data
                )
            }else{
                this.$emit('loading', {loading:false})
                this.appointmentBookingError({message: 'Error in booking request response'})
            }
        },

        getAddonNextScreen(result){
            return window.wappointmentExtends.filter('AppointmentBookedNextScreen', this.relations.next, {result:result, service: this.service} )
        },

        appointmentBookingError(error){
            this.$emit('serviceError',error)
        },
    }

}
</script>
<style>

.wap-front .wap-booking-fields label{
    color: var(--wappo-body-tx);
}
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

.wap-booking-fields label{
    margin-bottom: 0;
    font-size: .9em;
}

.wap-terms{
    font-size: .7em;
    text-align: left;
    line-height: 1.4;
    margin-bottom: .3em;
}

.wap-front .wrounded{
  border-radius : 50%;
}
.wap-front .wshadow{
  box-shadow: inset 0px 8px 10px 0 rgba(0,0,0,.08);
}
.wap-front .form-summary .wselected {
    display: inline-flex !important;
    font-size: .8em;
}

</style>
