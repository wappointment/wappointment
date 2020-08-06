<template>
    <div>
        <label >{{ label }}</label>
        <SearchDropdown v-model="updatedValue" :ph="labelDefault" :elements="statuses" 
             idKey="id" labelSearchKey="label"></SearchDropdown>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import RequestMaker from '../Modules/RequestMaker'
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    name: 'core-status',
    mixins: [AbstractField, RequestMaker],
    components: {SearchDropdown},
    props: {
        labelDefault: {
            type: String,
            default: 'Select a status'
        },
    },
    data(){
        return {
            statuses: [
                { id: 1, label: 'Publish'},
                { id: -1, label: 'Unpublish'},
                { id: 0, label: 'Coming-soon'}
            ],

        }
    },

}
</script>

