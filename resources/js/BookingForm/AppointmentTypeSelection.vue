
<template>
    <div class="text-center">
        <div v-if="allowedType('physical')" @click="$emit('selectType','physical')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: physicalSelected}">
            <WapImage faIcon="map-marked-alt" size="md" />
            <div>{{options.form.inperson}}</div>
        </div>
        <div v-if="allowedType('phone')" @click="$emit('selectType','phone')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: phoneSelected}">
            <WapImage faIcon="phone" size="md" />
            <div>{{options.form.byphone}}</div>
        </div>
        <div v-if="allowedType('skype')" @click="$emit('selectType','skype')" role="button" class="wbtn wbtn-secondary wbtn-cell" :class="{selected: skypeSelected}">
            <WapImage :faIcon="['fab', 'skype']" size="md" />
            <div>{{options.form.byskype}}</div>
        </div>
    </div>
</template>

<script>

export default {
    props:['options', 'typeSelected', 'typesAllowed'],
    computed:{
        phoneSelected(){
            return this.typeSelected == 'phone'
        },
        physicalSelected(){
            return this.typeSelected == 'physical'
        },
        skypeSelected(){
            return this.typeSelected == 'skype'
        },
    },
    methods:{
        allowedType(type){
            return this.typesAllowed.indexOf(type) !== -1
        },
    }
}
</script>