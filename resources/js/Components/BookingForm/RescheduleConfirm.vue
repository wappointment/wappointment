<template>
    <div>
        <div class="mb-2">
            <h4>{{options.form.header}}</h4>
            <h5>{{ getMoment(selectedSlot, currentTz).format(fullDateFormat) }}</h5>
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
    props: ['options','selectedSlot','currentTz','fullDateFormat'],
    methods: {
        back(){
            this.$emit('back')
        },
        confirm(){
            this.$emit('confirm')
        },

    }

}
</script>
