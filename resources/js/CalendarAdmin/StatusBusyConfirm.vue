<template>
    <div>
        <h5>Confirm that you are busy?</h5>
        <button type="button" class="btn btn-secondary btn-lg" @click="$emit('cancelled')">Cancel</button>
        <button type="button" class="btn btn-primary btn-lg" @click="confirmRequest">Confirm</button>
    </div>
</template>
<script>
import StatusFreeConfirm from './StatusFreeConfirm'
export default {
    extends: StatusFreeConfirm,

    methods:{
        async setRequest(params) {
          return await this.serviceStatus.call('save', 
          {
              start: this.startTime.unix(), 
              end: this.endTime.unix(), 
              timezone: this.timezone, 
              type: 'busy',
              staff_id: this.activeStaff.id !== undefined?this.activeStaff.id:null
            })
        },
    }
}
</script>