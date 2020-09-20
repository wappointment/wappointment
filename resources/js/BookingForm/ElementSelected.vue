
<template>
    <div class="d-flex align-items-center">
        <span class="welementname" :class="{wenmax:(duration || getPrice)}">{{ service.name }}</span> 
        <span v-if="duration" class="wduration wsep">{{ getDuration }}</span>
        <span v-if="getPrice" class="wprice wsep">{{ getPrice }}</span> 
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
            return this.duration + this.options.general.min
        },

        getPrice(){
            if(this.service.options.durations !== undefined){
                for (let i = 0; i < this.service.options.durations.length; i++) {
                    const dur = this.service.options.durations[i];
                    if(dur.duration == this.duration && ['',undefined].indexOf(dur.woo_price) === -1 ){
                        return dur.woo_price + wappointment_woocommerce.currency_symbol
                    }
                }
            }else{
                if(['',undefined].indexOf(this.service.options.woo_price) === -1 ){
                    return this.service.options.woo_price + wappointment_woocommerce.currency_symbol
                }
            }
            
            return false
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

.wap-front .wenmax{
    max-width:60%;
}
.wap-front .header-service .wduration{
    font-weight: normal;
    font-size:.9em;
}
.wap-front .wsep{
    display:inline-flex;
}
.wap-front .wsep::before{
    content: ' - ';
    margin-right: .4em;
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

