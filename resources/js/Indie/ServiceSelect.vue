<template>
    <SearchDropdown v-if="staffLoaded" v-model="staffId" :ph="labelDefault" :elements="staffs" 
            idKey="ID" labelSearchKey="display_name" :displayElement="displayElementFunc" />
</template>

<script>
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    components:{SearchDropdown},
    props: {
        services: null,
        service_id: false,
        labelDefault: {
            type: String,
            default: 'Select Service'
        },
        autoselect: true
    },
    data() {
        return {
            element_id: false,
        }
    },
    created(){
        if([false,undefined,''].indexOf(this.service_id) === -1){
            this.element_id = this.service_id
        }else{
            this.element_id = this.autoselect ? this.services[0].ID:false
        }
    },
    methods:{
        displayElementFunc(element){
            return element !== undefined ? element.user_email: 'Unknown'
        },
    },
    computed: {
        staffLoaded(){
            return (this.services !== undefined) ? true:false
        },
    },
    watch: {
        element_id: function (newId, oldId) {
            this.$emit('update', newId)
        }
    },
}
</script>
<style>
.clickable.label-wrapper,
.elementsContainer .label-wrapper{
    margin-bottom: 0!important;
}
</style>
