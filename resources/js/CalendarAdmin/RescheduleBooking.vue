<template>
    <div>
        <h5>Do you really want to reschedule?</h5>
        <button type="button" class="btn btn-secondary btn-lg" @click="$emit('cancelled')">Back</button>
        <button type="button" class="btn btn-primary btn-lg" @click="confirmRequest">Confirm</button>
    </div>
</template>
<script>
export default {
    props: ['appointment','timezone','viewData','activeStaff'],
    methods:{
        confirmRequest(){
            this.$emit('confirmed', {rescheduled:true})
        },
    }
}
</script>