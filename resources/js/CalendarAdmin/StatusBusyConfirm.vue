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
          {start: this.startTime.format(), end: this.endTime.format(), timezone: this.timezone, type: 'busy'})
        },
    }
}
</script>