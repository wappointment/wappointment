
<template>
    <div role="button" class="wcell" @click="$emit('clicked', element)">
        <slot/>
    </div>
</template>

<script>
export default {
    props: ['element']
}
</script>
<style>
.wcell {
    display: inline-block;
    padding: .2em;
    font-size: .8em;
    background-color: #eee;
    border-radius: .2em;
    margin: .3em .3em 0 0;
    border: 1px solid #ccc
}
</style>

