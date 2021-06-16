
<template>
    <div>
        <div class="p-2">
            <div class="wdescription">You will pay on the day of your appointment</div>
        </div>
        <button class="wbtn wbtn-primary wbtn-block" @click="confirm">Confirm</button>
    </div>
</template>

<script>

export default {

    methods:{
        confirm(){
            this.$emit('confirm')
        },
    }
}
</script>

