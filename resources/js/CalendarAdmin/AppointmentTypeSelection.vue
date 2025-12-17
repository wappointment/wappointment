<template>
    <div class="d-flex mb-4" v-if="serviceHasTypes">
        <div v-if="allowedType('zoom')" @click="selectType('zoom')" class="btn btn-secondary btn-cell" role="button" :class="{selected: zoomSelected}">
            <span class="dashicons" :class="[zoomSelected ? 'dashicons-yes-alt text-primary':'dashicons-marker']"></span>
            <FaIcon icon="video" size="lg"/>
            <div>Video meeting</div>
            <div class="small">(Zoom, Google meet, ...)</div>
        </div>
        <div v-if="allowedType('physical')" @click="selectType('physical')" class="btn btn-secondary btn-cell" role="button" :class="{selected: physicalSelected}">
            <span class="dashicons" :class="[physicalSelected ? 'dashicons-yes-alt text-primary':'dashicons-marker']"></span>
            <FaIcon icon="map-marked-alt" size="lg"/>
            <div>At an address</div>
        </div>
        <div v-if="allowedType('phone')" @click="selectType('phone')" class="btn btn-secondary btn-cell" role="button" :class="{selected: phoneSelected}">
            <span class="dashicons" :class="[phoneSelected ? 'dashicons-yes-alt text-primary':'dashicons-marker']"></span>
            <FaIcon icon="phone" size="lg"/>
            <div>By phone</div>
        </div>
    </div>
</template>

<script>

//import appFawesome from '../appFawesome'
const appFawesome = () => import(/* webpackChunkName: "appFawesome" */ '../appFawesome')
import MixinTypeSelected from '../BookingForm/MixinTypeSelected'
export default {
    props: ['service', 'preselect'],
    data: () => ({
        selection: false,
    }),
    mixins: [MixinTypeSelected],
    components: {
        'FaIcon': appFawesome,
    }, 
    mounted(){
        if(this.service.type.length == 1){
            this.selectType(this.service.type[0])
        }
        if(this.preselect){
            this.selectType(this.preselect)
        }
    },
    computed: {
        serviceHasTypes(){
            return this.service!== undefined && this.service.type.length > 1 
        },
    },
    methods: {
        allowedType(type){
            return this.service!== undefined && this.service.type.indexOf(type) !== -1
        },
        selectType(type){
            this.selection = type
            this.$emit('selected', type)
        },
    }
}
</script>