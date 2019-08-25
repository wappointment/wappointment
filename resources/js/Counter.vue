<template>
    <div>
        <i class="count" v-text="count"></i>
        <button @click="count++">Increment</button>
    </div>
</template>

<script>
export default {
    data () {
        return {
            count: 0
        };
    }
}
</script>
