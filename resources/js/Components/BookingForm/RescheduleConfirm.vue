<template>
    <div>
        <div class="mb-2">
            <h4>{{options.form.header}}</h4>
            <h5>{{ getMoment(selectedSlot, timeprops.currentTz).format(timeprops.fullDateFormat) }}</h5>
        </div>
        <div class="d-flex btn-confirm">
            <div class="mr-2"><span class="btn-secondary btn" @click="back">{{options.form.back}}</span></div>
            <span class="btn-primary btn w100 mr-0" @click="confirm">{{options.form.confirm}}</span>
        </div>
    </div>
</template>

<script>
import Dates from "../../Modules/Dates"
import AbstractFront from "../../Views/abstractFront"
export default {
    extends: AbstractFront,
    mixins: [Dates],
    props: ['options','selectedSlot','timeprops','relations','rescheduleData'],
    methods: {
        back(){
            this.$emit('back', this.relations.prev, {selectedSlot:false})
        },

        confirm(){
            let data = {
                appointmentkey: this.timeprops.appointmentkey,
                time: this.selectedSlot,
                ctz: this.timeprops.ctz,
            }
            this.$emit('loading', {loading:true, dataSent: data})
            this.saveRescheduleRequest(data)
            .then(this.appointmentRescheduled)
            .catch(this.appointmentReschedulingError)
        },
        async saveRescheduleRequest(data) {
            return await this.serviceBooking.call('reschedule', data)
        }, 

        appointmentRescheduled(result){
            //console.log('result',result.data)
            let data = result.data
            data.appointmentkey = this.timeprops.appointmentkey
            data.time = this.selectedSlot
            data.ctz = this.timeprops.ctz
            data.client = this.rescheduleData.client
            this.$emit('confirmed', this.relations.next, {
                appointmentSavedData: result.data,
                isApprovalManual:(result.data.status == 0), 
                appointmentSaved: true, 
                loading: false,
                dataSent: data
            })
        },

        appointmentReschedulingError(error){
            this.serviceError(error)
        },

        serviceError(error){
            this.$emit('serviceError',error)
        },

    }

}
</script>
