
<template>
    <div class="d-flex align-items-center">
        <span class="welementname" :class="{wenmax:(duration || getPrice)}">{{ service.name }}</span> 
        <span v-if="duration" class="wduration wsep">{{ getDuration }}</span>
        <span v-if="getPrice" class="wprice wsep">{{ getPrice }}</span> 
        <span v-if="cancellable" class="wclose" role="button" @click="$emit('discardElement', service)"></span>
    </div>
</template>

<script>
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    mixins:[CanFormatPrice],
    props: {
        service: {
            type: Object, 
        },
        duration:{
            type: [Number,Boolean],
        },
        cancellable:{
            type: Boolean,
            default: true
        },
        options:{
            type:Object
        }
    },
    computed:{
        getDuration(){
            return this.duration +' '+ this.options.general.min
        },

        getPrice(){
            if(this.service.options.durations === undefined){
                return false
            }
            for (const dur of this.service.options.durations) {
                if(dur.duration == this.duration && ['',undefined].indexOf(dur.woo_price) === -1 ){
                    return this.formatPrice(dur.woo_price)
                }
            }
        }
    },
}
</script>
<style>

.wap-front .wduration{
    font-weight: bold;
    margin: 0 .2em;
}
.wap-front .welementname{
    line-height: 1.2em;
}

.wselected .welementname,
.wselected .wduration,
.wselected .wap-img svg,
.wbtn-primary-light .wap-img svg,
.wap-front .wselected .wprice{
    color: var(--wappo-pri-tx);
}

.success .wap-img svg{
    color: var(--wappo-success-tx);
}

.wap-front .wsep::before{
    content: ' ';
    margin: 0 .4em;
}

.wclose::before, .wclose::after{
    content: ' ';
    position: absolute;
    background-color: #b5b1b1;
}
.wclose::before{
    transform: translateX(15px) rotate(45deg);
}
.wclose::after{
    transform: translateX(15px) rotate(-45deg);
}
.wclose:hover::before, 
.wclose:hover::after{
    background-color: #7a7575;
    width: 1px;
}
.wclose{
    cursor: pointer;
    height: 1em;
    width: 1em;
    display: inline-block;
    border-radius: 2em;
    position:relative;
}

.wclose::before, 
.wclose::after{
    height: .8em;
    width: 1px;
    top: .1em;
    right: 1.5em;
}
</style>

