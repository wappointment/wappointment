
<template>
    <div class="text-center">
        <div v-if="allowedType('physical')" @click="selectType('physical')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: physicalSelected}">
            <WapImage faIcon="map-marked-alt" size="md" />
            <div>{{options.form.inperson}}</div>
        </div>
        <div v-if="allowedType('zoom')" @click="selectType('zoom')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: zoomSelected}">
            <WapImage :faIcon="['fas', 'video']" size="md" />
            <div>{{options.form.byzoom}}</div>
        </div>
        <div v-if="allowedType('phone')" @click="selectType('phone')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: phoneSelected}">
            <WapImage faIcon="phone" size="md" />
            <div>{{options.form.byphone}}</div>
        </div>
        <div v-if="allowedType('skype')" @click="selectType('skype')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: skypeSelected}">
            <WapImage :faIcon="['fab', 'skype']" size="md" />
            <div>{{options.form.byskype}}</div>
        </div>
    </div>
</template>

<script>
import MixinTypeSelected from './MixinTypeSelected'
export default {
    mixins: [MixinTypeSelected],
    props:['options', 'typeSelected', 'typesAllowed'],
    mounted(){
        this.selection = this.typeSelected
    },

    methods:{
        selectType(type){
            this.selection = type
            this.$emit('selectType',type)
        },
        allowedType(type){
            return this.typesAllowed.indexOf(type) !== -1
        },
    }
}
</script>