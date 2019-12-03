<template>
    <div>
        <label v-if="label" >{{ label }}</label>
        <SearchDropdown v-model="updatedValue" :ph="labelDefault" :elements="elements" 
             :idKey="idKey" :labelSearchKey="labelSearchKey" :flexWrap="flexWrap" :hasMulti="multi"></SearchDropdown>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import RequestMaker from '../Modules/RequestMaker'
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    mixins: [AbstractField, RequestMaker],
    components: {SearchDropdown},
    props: {
        labelDefault: {
            type: String,
            default: 'Make a selection'
        },
        labelSearchKey: {
            type: String,
        },
        idKey: {
            type: String,
        },
        elements: {
            type: Array,
        },
        flexWrap: {
            type:Boolean,
            default:false
        },
        multi: {
            type: Boolean,
            default: false
        }
    },
}
</script>

