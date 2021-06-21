
<template>
    <div>
        <div class="p-2">
            <div class="wdescription">{{ options.swift_payment.onsite_desc }}</div>
        </div>
        <button class="wbtn wbtn-primary wbtn-block" @click="confirm">{{ options.swift_payment.onsite_confirm }}</button>
    </div>
</template>

<script>

export default {
    props:['options'],
    methods:{
        confirm(){
            this.$emit('confirm')
        },
    }
}
</script>

