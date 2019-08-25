<template>
    <div v-if="iframe">
        <wap-iframe :height="200" :src="getIframeMap"></wap-iframe>
    </div>
    <div class="d-flex align-items-center" v-else>
        <div class="icon-address">
            <slot></slot>
        </div>
        <address>
            <a :href="getMapAdress" target="_blank">{{ service.address }}</a>
        </address>
    </div>
</template>

<script>
import Iframe from "../Iframe";
export default {
    components: {
        'wap-iframe': Iframe,
    }, 
    props: {
        service: {
            type: Object
        },
        iframe:{
            type: Boolean,
            default: false
        },
    },
    computed: {
        getIframeMap(){
            return 'https://maps.google.com/maps?width=100%&height=200&hl=en&q='+this.getEncodedAdress+'&ie=UTF8&t=&z=14&iwloc=B&output=embed'
        },
        getMapAdress(){
            return 'https://www.google.com/maps/search/?api=1&query=' + this.getEncodedAdress
        },
        getEncodedAdress(){
            return encodeURIComponent(this.service.address);
        },
    }

}
</script>
<style scoped>
address {
    white-space: pre;
    line-height: 18px;
    font-size: 16px;
    margin:0;
    overflow: hidden;
}
address a {
    white-space: pre-wrap;
    white-space: -moz-pre-wrap;
    white-space: -pre-wrap;
    white-space: -o-pre-wrap;
    word-wrap: break-word;
    
}
</style>

