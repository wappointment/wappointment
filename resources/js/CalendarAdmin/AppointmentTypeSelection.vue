<template>
    <div class="d-flex mb-4" v-if="serviceHasTypes">
        <div v-if="allowedType('physical')" @click="selectType('physical')" class="btn btn-secondary btn-cell" role="button" :class="{selected: physicalSelected}">
            <FaIcon icon="map-marked-alt" size="md"/>
            <div>At a location</div>
        </div>
        <div v-if="allowedType('zoom')" @click="selectType('zoom')" class="btn btn-secondary btn-cell" role="button" :class="{selected: zoomSelected}">
            <FaIcon icon="video" size="md"/>
            <div>Zoom meeting</div>
        </div>
        <div v-if="allowedType('phone')" @click="selectType('phone')" class="btn btn-secondary btn-cell" role="button" :class="{selected: phoneSelected}">
            <FaIcon icon="phone" size="md"/>
            <div>By phone</div>
        </div>
        <div v-if="allowedType('skype')" @click="selectType('skype')" class="btn btn-secondary btn-cell" role="button" :class="{selected: skypeSelected}">
            <FaIcon :icon="['fab', 'skype']" size="md"/>
            <div>By skype</div>
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
