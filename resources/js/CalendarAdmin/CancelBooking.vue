<template>
    <div>
        <h5>Do you really want to cancel?</h5>
        <button type="button" class="btn btn-secondary btn-lg" @click="$emit('cancelled')">Back</button>
        <button type="button" class="btn btn-primary btn-lg" @click="confirmRequest">Confirm</button>
    </div>
</template>
<script>
import EventService from '../Services/V1/Event'
import RequestMaker from '../Modules/RequestMaker'
export default {
    props: ['appointment','timezone','viewData','activeStaff'],
    mixins:[RequestMaker],
    data: () => ({
        serviceEvent: null,
    }),
    created(){
        this.serviceEvent = this.$vueService(new EventService)
    },

    
    methods:{
        confirmRequest(){
            this.request(this.setRequest,{id:this.appointment.extendedProps.dbid}, undefined,false, this.statusSaved)
        },
        statusSaved(){
            this.$emit('confirmed')
        },
        async setRequest(params) {
            return await this.serviceEvent.call('forceDelete', params)
        },
    }
}
</script>