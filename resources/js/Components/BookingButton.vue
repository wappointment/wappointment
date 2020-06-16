<template>
    <button type="button"
    :class="className"
    @click="$emit('click')"
    ><slot></slot></button>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: ''
        },
        options: {
            type: Object,
        },
        className: {
            type: String,
            default: 'wbtn wbtn-primary'
        },
    },
}   
</script>