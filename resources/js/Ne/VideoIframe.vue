<template>
    <div>
        <div class="loader-video">
            <WLoader/>
        </div>
        
        <div class="loader-iframe">
            <iframe width="560" height="315" :src="src" frameborder="0" 
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        
    </div>
</template>

<script>
export default {
    props:['src']
}
</script>
<style>
.loader-video {
    float: left;
    z-index: 1;
    margin-left: 12rem !important;
    margin-top: 3rem;
    height: 320px;
}
.loader-iframe {
    position: absolute;
    z-index: 2;
}
</style>