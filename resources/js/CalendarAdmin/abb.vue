<template>
    <div id="wrapperAdmin">
        <StyleGenerator :options="viewData.widget" wrapper="wrapperAdmin"></StyleGenerator>
        <div class="mb-2">
            <h3>Book an appointment for your client</h3>
            <div v-if="service" class="selected-service">
                <span class="text-primary" data-tt="Change service" @click.stop.prevent="changeService">{{ service.name }}</span> 
                <span :class="[hasMoreThanOneDuration ? 'text-primary':'text-dark']" v-if="duration" :data-tt="hasMoreThanOneDuration ? 'Change duration':false" @click.stop.prevent="changeDuration">{{ duration }}min</span> 
                <span :class="[hasMoreThanOneLocation ? 'text-primary':'text-dark']" v-if="location" :data-tt="hasMoreThanOneLocation ? 'Change location':false" @click.stop.prevent="changeLocation">{{ location.name }}</span>
            </div>
            <div v-if="!service && hasMoreThanOneService">
                <ServiceSelection @serviceSelected="serviceSelected" :options="viewData.widget" :services="viewData.services" :admin="true"/>
            </div>
            <div v-if="service && !duration" class="p-2">
                <DurationSelection  @durationSelected="durationSelected" :service="service"/>
                <div>
                    Your calendar selection : 
                    <span class="text-primary" v-if="duration !== durationSelectedFC" data-tt="Change duration" @click.stop.prevent="backToOriginalDuration">{{ durationSelectedFC }}min</span> 
                </div>
            </div>
            
            <div v-if="service && !location" class="p-2">
                <LocationSelection v-if="hasMoreThanOneLocation"  @locationSelected="locationSelected"  :service="service"/>
            </div>
            
            <div v-if="allSelected">
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
                        :options="viewData.widget" :disabledButtons="disabledButtons" />

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
import ServiceSelection from '../BookingForm/ServiceSelection.vue'
import DurationSelection from '../BookingForm/DurationSelection.vue'
import LocationSelection from '../BookingForm/LocationSelection.vue'
import FieldsGenerated from '../BookingForm/FieldsGenerated.vue'
import WappoServiceBooking from '../Services/V1/Booking'
import StyleGenerator from '../Components/StyleGenerator'
export default {
    components: {ServiceSelection, DurationSelection, LocationSelection, FieldsGenerated, StyleGenerator},
    props:['viewData', 'startTime', 'endTime', 'realEndTime'],
    data: () => ({
        service: false,
        duration:false,
        durationSelectedFC:false,
        location:false,
        services: [],
        serviceBooking: null,
        endTimeParam: false,
    }),
    created(){
        this.bookingForm = {email: ''}
        
        this.serviceBooking = this.$vueService(new WappoServiceBooking)

        this.services = this.viewData.services
        if(!this.hasMoreThanOneService) {
            this.serviceSelected('ignore',{service: this.services[0]})
        }
        this.durationSelectedFC = (this.realEndTime.unix() - this.startTime.unix())/60
    },
    computed: {
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
            this.location = this.hasMoreThanOneLocation ? false:this.service.locations[0]
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
        async bookingRequest(params) {

          return await this.serviceBooking.call('bookadmin', Object.assign({ 
              start: params.start.unix(), 
              end: this.endTimeParam.unix(), 
              timezone: this.timezone,
              service: this.service.id,
              location: this.location.id,
              duration: this.duration,
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

.hide-cf{
    display:none;
}
</style>