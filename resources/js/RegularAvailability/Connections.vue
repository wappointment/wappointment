<template>
    <div :class="[vertical ?'wservices-list wintegrations text-muted':'d-flex wintegrations']">
        <div v-for="connectionKey in orderedConnections" class="d-flex mr-2 slot tt-lg align-items-center" :class="{disabled: !isConnected(connectionKey)}"  
            :data-tt="connectionDescription(connectionKey)"  >
            <img :src="connectionImage(connectionKey)" :alt="connectionLabel(connectionKey)"/>
            <span v-if="showLabel" class="ml-2">{{ connectionLabel(connectionKey) }}</span>
        </div>
    </div>
</template>

<script>
import Abstract from '../Views/Abstract'
import ConnectionsMixins from './ConnectionsMixins'
export default {
    extends: Abstract,
    mixins: [ConnectionsMixins],
    props: {
        connections:{
            type: Array,
            default: []
        },
        showLabel:{
            type: Boolean,
            value: false
        },
        vertical:{
            type: Boolean,
            value: false
        },
    },
    data: () => ({
        availableConnections: ['zoom','google', 'googlemeet'],
    }),
    computed: {
        orderedConnections(){
            return this.availableConnections.sort(this.sortConnections)
        },
    },
    methods:{
        boolToInt(bool){
            return bool ? 1:0
        },
        sortConnections(a,b){
            return this.boolToInt(this.isConnected(b)) - this.boolToInt(this.isConnected(a))
        },
        isConnected(connectionKey){
            return this.connections.find(e => connectionKey.indexOf(e) !== -1)
        },
        
    }
}
</script>
<style >
.slot {
    border-radius: .3rem;
    font-size: 13px;
}
.slot img{
  max-height: 20px;
}
.slot.disabled img {
    filter:grayscale(1);
}
.slot.disabled:hover img {
    filter:grayscale(0);
}
.wservices-list {
    background-color: #f0f0f0;
    border-radius: .6em;
    padding: 1rem;
    width:300px;
}
.wservices-list .label-title{
    width:116px;
    font-size: .9rem;
}
.wservices-list .slot {
    margin-bottom: .9em;
    color: #535353;
}
.wservices-list .slot.disabled {
    color: #b9b2b2;
}

.wintegrations .slot.tt-lg[data-tt]::before {
    background-color: rgba(83, 168, 65, 0.8);
    content: '\2714 ' attr(data-tt);
}
.wintegrations .slot.disabled.tt-lg[data-tt]::before {
    background-color: rgba(144, 144, 159, 0.8);
    content: '\2716 ' attr(data-tt);
}
</style>