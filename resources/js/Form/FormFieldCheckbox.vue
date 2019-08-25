<template>
    <div>
        <input type="checkbox" :id="id" v-model="updatedValue">
        <label :for="id">{{ label }}</label>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
export default {
    mixins: [AbstractField],
}
</script>
