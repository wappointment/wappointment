
<template>
    <div class="d-flex align-items-center">
        <span>{{ service.name }} -</span> 
        <span class="wduration">{{ getDuration }}</span> 
        <span v-if="cancellable" class="wclose" role="button" @click="$emit('discardElement', service)"></span>
    </div>
</template>

<script>
export default {
    props: {
        service: {
            type: Object, 
        },
        duration:{
            type: Number,
        },
        cancellable:{
            type: Boolean,
            default: true
        }
    },
    computed:{
        getDuration(){
            return this.duration + 'min'
        }
    }
}
</script>
<style>

.wap-front .wduration{
    font-weight: bold;
    margin: 0 .2em;
}

.wclose::after {
    transform: translateX(15px) rotate(-45deg);
}
.wclose::before, .wclose::after {
    content: ' ';
    position: absolute;
    background-color: #b5b1b1;
}
.wclose::before {
    transform: translateX(15px) rotate(45deg);
}
.wclose:hover::before, .wclose:hover::after {
    background-color: #7a7575;
    width: 1px;
}
.wclose{
    cursor: pointer;
    height: 1em;
    width: 1em;
    display: inline-block;
    background: #fff;
    border-radius: 2em;
    position:relative;
}

.wclose::before, .wclose::after  {
    height: .8em;
    width: 1px;
    top: .1em;
    right: 1.5em;
}
</style>

