<template>
    <div class="d-flex mb-4" v-if="serviceHasTypes">
        <div v-if="allowedType('physical')" @click="selectType('physical')" class="btn btn-secondary btn-cell" role="button" :class="{selected: physicalSelected}">
            <font-awesome-icon icon="map-marked-alt" size="lg"/>
            <div>At a location</div>
        </div>
        <div v-if="allowedType('phone')" @click="selectType('phone')" class="btn btn-secondary btn-cell" role="button" :class="{selected: phoneSelected}">
            <font-awesome-icon icon="phone" size="lg"/>
            <div>By phone</div>
        </div>
        <div v-if="allowedType('skype')" @click="selectType('skype')" class="btn btn-secondary btn-cell" role="button" :class="{selected: skypeSelected}">
            <font-awesome-icon :icon="['fab', 'skype']" size="lg"/>
            <div>By skype</div>
        </div>
    </div>
</template>

<script>

import { library } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkedAlt, faPhone } from '@fortawesome/free-solid-svg-icons'
import { faSkype } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faMapMarkedAlt, faPhone, faSkype)

export default {
    props: ['service', 'preselect'],
    data: () => ({
        selection: false,
    }),
    components: {
        'font-awesome-icon': FontAwesomeIcon,
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
        phoneSelected(){
            return this.selection == 'phone'
        },
        physicalSelected(){
            return this.selection == 'physical'
        },
        skypeSelected(){
            return this.selection == 'skype'
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
