<template>
    <div id="wrapperAdmin">
        <StyleGenerator :options="viewData.widget" wrapper="wrapperAdmin"></StyleGenerator>
        <div class="mb-2">
            <h3>Book an appointment for your client</h3>
            <div v-if="service" class="selected-service d-flex">
                <span class="d-flex align-items-center" 
                :class="[hasMoreThanOneService ? 'wbtn wbtn-cell wbtn-secondary wbtn-service':'wbtn wbtn-cell btn-outline-secondary disabled text-muted']" data-tt="Change service" @click.stop.prevent="changeService">
                    <WapImage v-if="serviceHasIcon" :element="service" :desc="service.name" size="md" /> 
                    <span class="ml-2">{{ service.name }}</span>
                </span> 
                <span class=" d-flex align-items-center wbtn wbtn-cell wbtn-secondary wbtn-service" 
                 v-if="duration" :data-tt="hasMoreThanOneDuration ? 'Change duration':false" @click.stop.prevent="changeDuration">
                    {{ duration }}min
                </span> 
                <span class="d-flex align-items-center" 
                :class="[hasMoreThanOneLocation ? 'wbtn wbtn-cell wbtn-secondary wbtn-service':'wbtn wbtn-cell btn-outline-secondary disabled text-muted']" v-if="location" :data-tt="hasMoreThanOneLocation ? 'Change location':false" @click.stop.prevent="changeLocation">
                    <WapImage :element="location" :desc="location.name" size="md" /> 
                    <span class="ml-2">{{ location.name }}</span>
                </span>
            </div>
            <div v-if="!service && hasMoreThanOneService">
                <ServiceSelection @serviceSelected="serviceSelected" :options="viewData.widget" :services="services" :admin="true"/>
            </div>
            <div v-if="service && !duration" class="p-2">
                <DurationSelection @durationSelected="durationSelected" :service="service" :options="viewData.widget" />
                <div>
                    Your calendar selection : 
                    <span class="text-primary" v-if="duration !== durationSelectedFC" data-tt="Change duration" @click.stop.prevent="backToOriginalDuration">{{ durationSelectedFC }}min</span> 
                </div>
            </div>
            
            <div v-if="service && duration && !location" class="p-2">
                <LocationSelection v-if="hasMoreThanOneLocation" @locationSelected="locationSelected" :service="service" :options="viewData.widget" />
            </div>
            
            <div v-if="allSelected">
                <div v-if="clientSelected">
                    <div class="d-flex align-items-center">
                        <div class="mr-2">
                            <img class="rounded-circle" :src="clientSelected.avatar" :title="clientSelected.name" />
                        </div>
                        <div>
                            <h6 class="m-0">{{ clientSelected.name }}</h6>
                            <small>{{ clientSelected.email }}</small>
                        </div>
                    </div>
                    <a class="text-primary" href="javascript:;" @click="clearClientSelection">Change client</a>
                </div>
                <div v-else>
                    <div class="mb-3">
                        <div>
                            <div class="field-required ddd " :class="hasError('email')">
                                <label for="bookingemail">Email:</label> 
                                <p class="d-flex">
                                    <input id="bookingemail" type="text" required="required" class="form-control" 
                                    v-model="bookingForm.email" @focus="canShowDropdown" @blur="clearDropdownDelay">
                                </p>
                            </div> 
                        </div>

                        <div>
                            <div class="dd-search-results" v-if="showDropdown" >
                                <div v-if="clientsResults.length>0">
                                    <div class="btn btn-light d-flex align-items-center" v-for="client in clientsResults" @click="selectClient(client)">
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
                    <div :class="[hasErrorEmail?'hide-cf':'']">
                        <FieldsGenerated @changed="changedBF" :disabledEmail="true"
                        :validators="validators" :custom_fields="viewData.custom_fields" 
                        :service="service" :location="location" :data="bookingForm" 
                        :options="viewData.widget" />

                        <div v-if="formHasErrors" class="error">
                            <div v-for="(error,namekeyidx) in errorsOnFields">
                                {{ getFieldLabel(namekeyidx) }} : {{ error }}
                            </div>
                        </div>
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
import {isEmail, isEmpty} from 'validator'
import PhoneInput from '../BookingForm/PhoneInput'
import FormInputs from '../BookingForm/Form'
import StyleGenerator from '../Components/StyleGenerator'
import ServiceSelection from '../BookingForm/ServiceSelection'
import DurationSelection from '../BookingForm/DurationSelection'
import LocationSelection from '../BookingForm/LocationSelection'
import FieldsGenerated from '../BookingForm/FieldsGenerated'
import WappoServiceBooking from '../Services/V1/BookingN'
export default {
    props: ['viewData','startTime', "endTime", "realEndTime", 'activeStaff'],
    mixins:[RequestMaker],
    components: {AppointmentTypeSelection, PhoneInput, FormInputs, StyleGenerator, ServiceSelection, DurationSelection, LocationSelection, FieldsGenerated},
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
        service: false,
        duration:false,
        durationSelectedFC:false,
        location:false,
        services: [],
        serviceBooking: null,
        endTimeParam: false,
    }),
    created(){
        this.serviceClient = this.$vueService(new ClientService)
        this.bookingForm = {email: ''}
        
        this.serviceBooking = this.$vueService(new WappoServiceBooking)

        this.services = this.activeStaff.services
        if(!this.hasMoreThanOneService) {
            this.serviceSelected('ignore',{service: this.services[0]})
        }
        this.durationSelectedFC = (this.realEndTime.unix() - this.startTime.unix())/60
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
        serviceHasIcon(){
            return this.service.options.icon != ''
        },
        locationHasIcon(){
            return this.location.options.icon != ''
        },
        isToday(){
            return this.firstDay!== undefined && this.lastDay !== undefined && this.firstDay.unix() < momenttz().unix() && this.lastDay.unix() > momenttz().unix()
        },

        preferredCountries(){
            return this.viewData.preferredCountries
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
        },

        hasMoreThanOneService(){
            return this.services.length > 1
        },

        hasMoreThanOneDuration(){
            return this.service && this.service.options.durations.length > 1
        },

        hasMoreThanOneLocation(){
            return this.service && this.service.locations !==undefined && this.service.locations.length > 1
        },

        allSelected(){
            return this.service && this.duration && this.location
        },

        readyToBook(){
            return this.allSelected && (
                (this.bookingForm.clientid !== undefined && [false,undefined].indexOf(this.bookingForm.clientid) === -1)  || 
                (Object.keys(this.errorsOnFields).length < 1 && this.validators['isEmail'](this.bookingForm.email))
            )
        },

        formHasErrors(){
            return Object.keys(this.errorsOnFields).length > 0
        },

        hasErrorEmail(){
            return [undefined,false].indexOf(this.errorsOnFields['email']) === -1
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
    
        
        canShowDropdown(){
            if(this.clientsResults.length > 0){
                this.showDropdown = true
            }
        },
        clearDropdownDelay(){
            setTimeout(this.clearDropDown, 100)
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
       
        refreshEvents(){
            this.$emit('confirmed')
        },

        getFieldLabel(namekey){
            for (let i = 0; i < this.viewData.custom_fields.length; i++) {
                const element = this.viewData.custom_fields[i]
                if(element['namekey'] == namekey) return element.name
            }
        },
        
        hasError(field){
            return [undefined,false].indexOf(this.errorsOnFields[field]) === -1 ? 'isInvalid':'isValid'
        },
        backToOriginalDuration(){
            this.setDuration(this.durationSelectedFC)
        },
        realSlotDuration(duration){
            return parseInt(duration) + parseInt(this.viewData.buffer_time) 
        },
        setDuration(duration){
            if(duration === false){
                this.duration = false
            }else{
                let start = this.startTime.clone()
                start.add(this.realSlotDuration(duration),'minutes')
                this.$emit('updateEndTime', start)
                this.endTimeParam = start
                this.duration = duration
            }
            
        },
        selectClient(client){
            this.bookingForm.clientid = client.id
            this.clientSelected = client
            this.bookingForm.email = ''
            this.showDropDown = false
        },
        changedBF(newValue, errors){
            this.bookingForm = newValue
            this.errorsOnFields = errors
        },
        changeService(){
            if(this.hasMoreThanOneService){
                this.service = false
                this.changeDuration()
                this.changeLocation()
            }
        },
        changeLocation(){
            this.location = this.hasMoreThanOneLocation || this.service === false ? false:this.service.locations[0]
        },
        changeDuration(){
            this.duration = false
        },
        durationSelected(screen, data){
            this.setDuration(data.duration)
        },
        locationSelected(screen, data){
            this.location = data.location
        },
        serviceSelected(screen, data){
            this.service = data.service
            this.duration = false
            if(data.duration !== undefined){
                this.duration = this.setDuration(data.duration)
            }
            this.changeLocation()
        },
         confirmNewBookingRequest(){
            if(this.readyToBook) {
                this.request(this.bookingRequest,{start:this.startTime, end:this.endTime},  undefined,false, this.refreshEvents)
            }
        },
        async bookingRequest(params) {

          return await this.serviceBooking.call('bookadmin', Object.assign({ 
              start: params.start.unix(), 
              end: this.endTimeParam.unix(), 
              timezone: this.timezone,
              service: this.service.id,
              location: this.location.id,
              duration: this.duration,
              staff_id: this.activeStaff.id !== undefined?this.activeStaff.id:null,
              }, this.bookingForm))
        },
    }
}
</script>
<style>
.selected-service{
    border-radius: .5rem;
    margin: 1rem 0;
    padding: .5rem !important;
    background-color: var(--white);
    color: var(--dark);
    display: inline-block;
    border: 2px dashed var(--primary);
}

.selected-service .text-dark, .selected-service .text-primary {
    background-color: var(--secondary);
    padding: .3em;
    border-radius: .3em;
}

#wrapperAdmin .wbtn.wbtn-secondary.wbtn-cell .service-label{
    text-align: left;
    margin: 0 .8em;
}

</style>