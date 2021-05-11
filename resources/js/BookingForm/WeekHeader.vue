
<template>
    <div class="d-flex ddays" >
        <div v-for="(dayH, idy) in weekHeader">
            {{ initial(dayH) }}
        </div>
    </div>
</template>

<script>


export default {
    props: ['weekHeader',],
    methods: {
        initial(string){
            return string.toUpperCase().substring(0, 1)
        },
    },
}
</script>