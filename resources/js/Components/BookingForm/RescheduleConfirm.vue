<template>
    <div>
        <div class="mb-2">
            <h4>{{options.form.header}}</h4>
            <h5>{{ getMoment(selectedSlot, timeprops.currentTz).format(timeprops.fullDateFormat) }}</h5>
        </div>
        <hr>
        <div class="d-flex btn-confirm">
            <div class="mr-2"><span class="btn-secondary btn" @click="back">{{options.form.back}}</span></div>
            <span class="btn-primary btn w100 mr-0" @click="confirm">{{options.form.confirm}}</span>
        </div>
    </div>
</template>

<script>
import Dates from "../../Modules/Dates";
export default {
    mixins: [Dates],
    props: ['options','selectedSlot','timeprops','relations'],
    methods: {
        back(){
            this.$emit('back', this.relations.prev, {selectedSlot:false, loading:true})
        },

        confirm(){
            let data = {
                appointmentkey: this.timeprops.appointmentkey,
                time: this.selectedSlot,
                ctz: this.timeprops.ctz,
            }
            this.$emit('loading', {loading:true, dataSent: data})
            this.saveRescheduleRequest()
            .then(this.appointmentBooked)
            .catch(this.appointmentBookingError)
        },
        async saveRescheduleRequest() {
            
            return await this.serviceBooking.call('reschedule', data)
        }, 

        appointmentBooked(result){
            this.$emit('confirmed', this.relations.next, {
                isApprovalManual:(result.data.status == 0), 
                appointmentSaved: true, 
                loading: false
            })
        },

        appointmentBookingError(error){
            this.serviceError(error)
        },

        serviceError(error){
            this.$emit('serviceError',error)
        },

    }

}
</script>
