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
    props: ['startTime','endTime','timezone','viewData','activeStaff'],
    mixins:[RequestMaker],
    data: () => ({
        serviceStatus: null,
        services:[],
        locations:[]
    }),
    created(){
        this.serviceStatus = this.$vueService(new StatusService)
        this.services = this.activeStaff.services
        this.locations = this.getLocations()
    },

    
    methods:{
        getLocations(){
            let locations = []
            for (let i = 0; i < this.services.length; i++) {
                let tempLocations = this.services[i].locations
                for (let j = 0; j < tempLocations.length; j++) {
                    const testLocation = tempLocations[j];
                    if(!locations.find(e => e.id == testLocation.id)){
                        locations.push(testLocation)
                    }
                }
            }
            return locations
        },
        confirmRequest(){
            this.request(this.setRequest,{}, undefined,false, this.statusSaved)
        },
        statusSaved(){
            this.$emit('confirmed')
        },
        async setRequest(params) {

            return await this.serviceStatus.call('save', 
            {
                start: this.startTime.format(), 
                end: this.endTime.format(), 
                timezone: this.timezone, 
                type: 'free',
                staff_id: this.activeStaff.id !== undefined? this.activeStaff.id:null
            })
        },
    }
}
</script>