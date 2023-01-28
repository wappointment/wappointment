
<template>
    <button class="btn btn-primary btn-lg" @mouseover="startCountDown" @mouseout="cancelCountDown"><slot/></button>
</template>

<script>

export default {
    data: () => ({
        hover: false,
        timeoutId: false,
    }),
    methods:{
        startCountDown(){
            this.timeoutId=setTimeout(this.accepted, 1500);
        },
        cancelCountDown(){
            clearTimeout(this.timeoutId)
        },
        accepted(){
            this.$emit('accepted');
        }
    }
}
</script>
