<template>
    <div>
        {{ currency }}
    </div>
</template>

<script>


export default {
    props:{
        currency: {
            type: Object,
        },
    },
    data: () => ({
        shortcode: '',

    }),
    
}
</script>
