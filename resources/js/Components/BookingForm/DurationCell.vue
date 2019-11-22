
<template>
    <span v-if="duration" :class="{'duration':show,'btn btn-cell btn-secondary':!show}" @click="$emit('clicked',duration)">{{ duration }}min</span>
</template>

<script>
export default {
    props: {
        duration:{type:[Boolean,Number]},
        show:{type: Boolean, default:false}
    }
}
</script>
<style>
.duration {
    padding: .2em;
    font-size: .8em;
    background-color: #eee;
    border-radius: .2em;
    margin: .3em .3em 0 0;
    border: 1px solid #ccc
}
</style>

