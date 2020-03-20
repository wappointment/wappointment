<template>
    <div class="label-formgen" :class="getClassWrapper" v-html="label"></div>
</template>

<script>
import AbstractField from './AbstractField'
export default {
    mixins: [AbstractField],
}
</script>
