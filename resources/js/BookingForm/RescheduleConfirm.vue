<template>
    <div>
        <div v-if="isCompactHeader" class="mb-2">
            <div class="wselected wclosable wmy-4 d-flex align-items-center d-flex-inline">
                <WapImage :faIcon="['far','clock']" size="auto" />
                <span class="welementname wml-2">{{ getLuxonFormated(selectedSlot.start) }}</span>
            </div>
        </div>
        <div class="d-flex wbtn-confirm my-2">
            <span class="wbtn-secondary wbtn" role="button" @click="back" >{{options.form.back}}</span>
            <span class="wbtn-primary wbtn w100 m-0" role="button" @click="confirm" >{{options.form.confirm}}</span>
        </div>
    </div>
</template>

<script>
import Dates from "../Modules/Dates"
import { DateTime } from "luxon";
import AbstractFront from "./AbstractFront"
export default {
    extends: AbstractFront,
    mixins: [Dates],
    props: ['options','selectedSlot','timeprops','relations','rescheduleData'],
    computed: {
        isCompactHeader(){
            return this.options.general === undefined || [undefined, false].indexOf(this.options.general.check_header_compact_mode) === -1
        },
    },
    methods: {
        back(){
            this.$emit('back', this.relations.prev, {selectedSlot:false})
        },
        getLuxonFormated(start){
            return DateTime.fromSeconds(start).toLocaleString(DateTime.DATETIME_MED)
        },
        confirm(){
            let data = {
                appointmentkey: this.timeprops.appointmentkey,
                time: this.selectedSlot.start,
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
            let data = result.data
            data.appointmentkey = this.timeprops.appointmentkey
            data.time = this.selectedSlot.start
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
