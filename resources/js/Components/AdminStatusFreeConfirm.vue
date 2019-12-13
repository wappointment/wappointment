<template>
    <div>
        <h5>Confirm that you are free?</h5>
        <button type="button" class="btn btn-secondary btn-lg" @click="$emit('cancelled')">Cancel</button>
        <button type="button" class="btn btn-primary btn-lg" @click="confirmRequest">Confirm</button>
    </div>
</template>
<script>
import StatusService from '../Services/V1/Status'
import RequestMaker from '../Modules/RequestMaker'
export default {
    props: ['startTime','endTime','timezone','viewData'],
    mixins:[RequestMaker],
    data: () => ({
        serviceStatus: null,
    }),
    created(){
        this.serviceStatus = this.$vueService(new StatusService)
    },

    
    methods:{
        confirmRequest(){
            this.request(this.setRequest,{}, this.statusSaved)
        },
        statusSaved(){
            this.$emit('confirmed')
        },
        async setRequest(params) {
            return await this.serviceStatus.call('save', 
            {start: this.startTime.format(), end: this.endTime.format(), timezone: this.timezone, type: 'free'})
        },
    }
}
</script>