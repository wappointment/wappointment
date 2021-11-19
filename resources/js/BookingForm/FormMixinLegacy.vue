<script>


export default {
    

    methods: {

        confirm(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'confirmation')
              return
            } 
            let data = this.bookingFormExtended
            data.time = this.selectedSlot.start
            data.ctz = this.timeprops.ctz
            data.service = this.service.id
            data.location = this.location.id
            data.duration = this.duration
            //turns loading mode on in parent
            this.$emit('loading', {loading:true, dataSent: data})
            //create request
            this.saveBookingRequest(data)
            .then(this.appointmentBooked)
            .catch(this.appointmentBookingError)
        },
        confirmLegacy(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'confirmation')
              return
            } 
            let data = this.bookingFormExtended
            data.time = this.selectedSlot.start
            data.type = this.selection
            data.ctz = this.timeprops.ctz
            //turns loading mode on in parent
            this.$emit('loading', {loading:true, dataSent: data})
            //create request
            this.saveBookingRequestLegacy(data)
            .then(this.appointmentBookedLegacy)
            .catch(this.appointmentBookingError)
        },
        

        async saveBookingRequestLegacy(data) {
            return await this.serviceBooking.call('save', data)
        }, 

        appointmentBookedLegacy(result){
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
        
        selectDefaultType(){
            this.selection = this.service.type[0]
        },
        
        selectType(type){
            this.selection = type
            let bookingForm =  Object.assign ({}, this.bookingForm)
            this.bookingForm = {}
            this.bookingForm = bookingForm
            this.bookingForm.type = type
            this.$emit('selectedLocation', type)
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
