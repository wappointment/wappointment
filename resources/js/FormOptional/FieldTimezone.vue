<template>
    <div :class="{'form-control-tz':classWrapperNeeded}">
        <TimeZones 
        :timezones="definition.timezones_list" 
        @activeOrNot="activeOrNot"
        :staffTimezone="updatedValue" 
        classW="align-self-center pr-2" 
                :labelDefault="label" :defaultTimezone="updatedValue" 
                @updateTimezone="updateTimezone" @initTimezone="updateTimezone"></TimeZones>
    </div>
</template>

<script>

import TimeZones from '../Components/TimeZones'
import AbstractField from '../Form/AbstractField'
export default {
    extends: AbstractField,
    name:'opt-timezone',
    components: {TimeZones},
    props: {
        labelDefault: {
            type: String,
            default: 'Select timezone'
        },
    },
    data() {
        return {
            classWrapperNeeded: true,
        }
    },
    mounted(){
        this.$emit('activated')
    },
    methods: {
        activeOrNot(s, tz){
            this.classWrapperNeeded = !s
        },
        updateTimezone(value) {
            this.updatedValue = value
        },

    }
}
</script>

<style >
.form-control-tz{
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: .9rem;
}
</style>
