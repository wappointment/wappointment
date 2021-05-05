<template>
    <div class="container-fluid">
        <h3 class="d-flex align-items-center justify-content-center">
            <img :src="user.avatar || user.gravatar" class="img-fluid wrounded" width="40" :alt="user.name" />
            <div class="ml-2">{{ user.name }}</div>
        </h3>
        <slot />
        <button v-if="nosave === false" class="btn btn-primary" @click="$emit('save')">Save</button>
    </div>
</template>

<script>
export default {
    props:  {
        user: {
            type: Object
        },
        nosave: {
            type: Boolean,
            default: false
        }
    }
}
</script>