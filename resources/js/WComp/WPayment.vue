
<template>
    <div class="payment tt-lg" :class="getClass" :data-tt="payment.description" @click="$emit('click', payment)">
        <WCell>
            <WImage :image="getImage"/>
            <span class="ml-1" v-if="payment.hideLabel!==true">{{ payment.name }}</span>
            <template v-if="payment.installed">
                <span  data-tt="Configure"><span class="dashicons dashicons-admin-generic"></span></span>
                <span v-if="payment.installed && payment.configured" data-tt="Activate"><span class="dashicons dashicons-admin-generic"></span></span>
            </template>
        </WCell>
    </div>
</template>

<script>
import WCell from './WCell'
import WImage from './WImage'
export default {
    components:{WCell, WImage},
    props: {
        payment: {
            type:Object
        }
    },

    computed:{
        getImage(){
            return {
                icon: 'methods/'+this.payment.key+'.png',
                alt: this.payment.name,
                title: this.payment.name
            }
        },
        getClass(){
            let classes = {}
            classes['payment-'+this.payment.key] = true
            if(!this.payment.installed || !this.payment.active ){
                classes['tt-disabled'] = true
            }else{
                classes['tt-success'] = true
            }
            return classes
        }
    }
}
</script>

<style>
.payment .wcell .img-fluid{
    height : 18px;
}
.payment.tt-disabled .wcell .img-fluid{
    filter: grayscale(1);
}
.payment.tt-disabled:hover .wcell .img-fluid{
    filter: grayscale(0);
}

.payment .wcell .dashicons{
    display:none;
}
.payment .wcell:hover .dashicons{
    display:inline;
}
.payment .wcell .dashicons{
    font-size: 16px;
}
</style>