
<template>
    <img class="img-fluid" :title="image.title" :src="resourcesUrl+image.icon" :alt="image.alt" />
</template>

<script>
export default {
    props: {
        image: {
            type:Object
        }
    },
    computed:{
        resourcesUrl(){
            return window.apiWappointment.resourcesUrl+'images/'
        },
    }
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

