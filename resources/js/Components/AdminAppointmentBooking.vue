<template>
    <div id="wrapperAdmin">
        <StyleGenerator :options="viewData.widget" wrapper="wrapperAdmin"></StyleGenerator>
        <div class="mb-2">
        <h3>Book an appointment for your client</h3>
        <div>
            <AppointmentTypeSelection :service="viewData.service" :preselect="selectedAppointmentType" @selected="selectingAppointmentType"></AppointmentTypeSelection>
        </div>
        <div v-if="selectedAppointmentType">
            <div v-if="clientSelected">
                <div class="d-flex align-items-center">
                    <div class="mr-2">
                    <img class="rounded-circle" :src="clientSelected.avatar" :title="clientSelected.name">
                    </div>
                    <div>
                    <h6 class="m-0">{{ clientSelected.name }}</h6>
                    <small>{{ clientSelected.email }}</small>
                    </div>
                </div>
                <small class="btn btn-link btn-sm" role="button" @click="clearClientSelection">Change client</small>
            </div>
            <div v-else>
                <div class="mb-3">
                    <div class="input-group input-group-lg" >
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroup-sizing-lg">Email</span>
                    </div>
                    <input type="text" class="form-control" id="bookingemail" :class="hasError('email')" v-model="bookingForm.email" @focus="canShowDropdown" @blur="clearDropdownDelay">
                    </div>
                    <div>
                        <div class="dd-search-results" v-if="showDropdown" >
                        <div v-if="clientsResults.length>0">
                            <div class="btn btn-light d-flex align-items-center" role="button" v-for="client in clientsResults" @click="selectClient(client)">
                                <div class="mr-2">
                                <img class="rounded-circle" :src="client.avatar" :title="client.name">
                                </div>
                                <div>
                                <h6 class="m-0 text-left">{{ client.name }}</h6>
                                <small>{{ client.email }}</small>
                                </div>
                            </div>
                        </div>
                        <div v-if="clientSearching">
                            Loading ...
                        </div>
                        </div>  
                    </div>
                </div>
                <div class="input-group mb-3" >
                    <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroup-sizing-default">Name</span>
                    </div>
                    <input class="form-control" id="bookingname" type="text" :class="hasError('name')" v-model="bookingForm.name">
                </div>

                <div class="input-group mb-3" v-if="phoneSelected" >
                    <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroup-sizing-default">Phone</span>
                    </div>
                    <PhoneInput 
                    :phone="bookingForm.phone"
                    @onInput="onInput"
                    :className="hasError('phone')+ ' form-control'"
                    :countries="preferredCountries" 
                    ></PhoneInput>
                </div>

                <div class="input-group mb-3" v-if="skypeSelected" >
                    <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroup-sizing-default">Skype</span>
                    </div>
                    <input class="form-control" id="bookingskype" type="text" :class="hasError('skype')" v-model="bookingForm.skype">
                </div>

            </div>

        </div>
        </div>
        <div>
            <button type="button" class="btn btn-secondary btn-lg" @click="$emit('cancelled')">Cancel</button>
            <button type="button" class="btn btn-primary btn-lg" :class="{'disabled': !readyToBook}" @click="confirmNewBookingRequest">Confirm Booking</button>
        </div>
    </div>
</template>
<script>
import AppointmentTypeSelection from './AppointmentTypeSelection'
import ClientService from '../Services/V1/Client'
import RequestMaker from '../Modules/RequestMaker'
import PhoneInput from './BookingForm/PhoneInput'
import {isEmail, isEmpty} from 'validator'
import FormInputs from './BookingForm/Form'
import StyleGenerator from './StyleGenerator'
export default {
    props: ['viewData','startTime', "endTime", "realEndTime"],
    mixins:[RequestMaker],
    components: {AppointmentTypeSelection,PhoneInput, FormInputs, StyleGenerator},
    data: () => ({
        clientSearching:false,
        clientsResults: [],
        bookingForm: {
            email: '',
            phone: '',
            skype: '',
            name: '',
        },
        clientSelected:false,
        clientid: false,
        serviceClient: null,
        showDropdown: false,
        selectedAppointmentType: false,
        phoneValid: false,
        errorsOnFields: {},
        prevEmail: '',
    }),
    created(){
 
        this.serviceClient = this.$vueService(new ClientService)
    },
    watch: {
      bookingForm: {
          handler: function(newValue){
            this.changedFormValue(newValue)
          },
          deep: true
      },

    },
    computed: {
        selectedDuration(){
            return this.service.duration
        },
        isToday(){
            return this.firstDay!== undefined && this.lastDay !== undefined && this.firstDay.unix() < momenttz().unix() && this.lastDay.unix() > momenttz().unix()
        },
        preferredCountries(){
        return this.viewData.preferredCountries
        },
        readyToBook(){
        return this.bookingForm.clientid !== false || (Object.keys(this.errorsOnFields).length < 1 && isEmail(this.bookingForm.email))
        },
        skypeValid(){
            return /^[a-zA-Z][a-zA-Z0-9.\-_]{5,31}$/.test(this.bookingForm.skype)
        },
        phoneSelected(){
            return this.selectedAppointmentType == 'phone'
        },
        skypeSelected(){
            return this.selectedAppointmentType == 'skype'
        },
        validators(){
            return {
                'isEmail': isEmail,
                'isEmpty': isEmpty,
            }
        } 
    },
    
    methods:{
        changedFormValue(newValue) {
            this.errorsOnFields = {}
            if(newValue.email!== undefined && newValue.email.length > 4 
            && newValue.email.indexOf('@')!== -1 && this.prevEmail != newValue.email){
                this.searchClient(newValue.email)
                this.prevEmail = newValue.email
            }

            if(newValue.name!== undefined && isEmpty(newValue.name) ) this.errorsOnFields.name = true
            if(newValue.email!== undefined && isEmpty(newValue.email) || !isEmail(newValue.email)) this.errorsOnFields.email = true
            if(newValue.phone!== undefined && this.phoneSelected && (isEmpty(newValue.phone) || !this.phoneValid)) this.errorsOnFields.phone = true
            if(newValue.skype!== undefined && this.skypeSelected && (isEmpty(newValue.skype) || !this.skypeValid)) this.errorsOnFields.skype = true

        },
        
        hasError(field){
            if(this.bookingForm[field] === '') return ''
            if(this.errorsOnFields[field] !== undefined && this.errorsOnFields[field]===true) return 'is-invalid'
            return 'is-valid'
        },
        
        canShowDropdown(){
            if(this.clientsResults.length > 0){
                this.showDropdown = true
            }
        },
        clearDropdownDelay(){
            setTimeout(this.clearDropDown, 100);
        },
        clearDropDown(){
            this.showDropdown = false
        },
        onInput({ number, isValid, country }) {
            this.bookingForm.phone = number
            this.phoneValid = isValid
        },
        selectingAppointmentType(type){
            this.bookingForm.type=type
            
            this.selectedAppointmentType = type
            this.changedFormValue(this.bookingForm)
        },
        clearClientSelection(){
            this.bookingForm.clientid = false
            this.clientSelected = false
        },
        selectClient(client){
            this.bookingForm.clientid = client.id
            this.clientSelected = client
            this.bookingForm.email = ''
            this.showDropDown = false
        },
        searchClient(){
            if(!this.clientSearching){
                this.clientSearching = true
                this.showDropdown = true
                this.clientsResults = []
                this.searchClientRequest(this.bookingForm.email).then(
                function(result){
                    return this.clientsFound(result)
                }.bind(this),
                function(err){
                    return this.clientsError(err)
                }.bind(this))
            }
        },
        async searchClientRequest(email) {
            return await this.serviceClient.call('search', {email: email})
        },
        clientsFound(result){
            this.clientSearching = false
            if(result.data!== undefined && result.data.length > 0){
                this.clientsResults = result.data
            }
            
        },
        clientsError(){
            this.clientSearching = false
        },
        confirmNewBookingRequest(){
            if(this.readyToBook) {
                this.request(this.bookingRequest,{start:this.startTime, end:this.endTime},  undefined,false, this.refreshEvents)
            }
        
        },
        refreshEvents(){
            this.$emit('confirmed')
        },
        async bookingRequest(params) {
          return await this.serviceClient.call('book', Object.assign({ start: params.start.unix(), end: params.end.unix(), timezone: this.timezone}, this.bookingForm))
        },
    }
}
</script>