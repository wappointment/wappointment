
<template>
    <div v-if="duration" role="button" :class="{'duration':show,'wbtn wbtn-cell wbtn-secondary':!show}" 
    @click="$emit('clicked',duration)">
        <div class="duration-value">{{ duration }}min</div>
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        duration:{type:[Boolean,Number]},
        show:{type: Boolean, default:false},
    }
}
</script>
<style>
.duration {
    display: inline-block;
    padding: .2em;
    font-size: .8em;
    background-color: #eee;
    border-radius: .2em;
    margin: .3em .3em 0 0;
    border: 1px solid #ccc
}
</style>

