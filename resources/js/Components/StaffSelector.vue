<template>
    <SearchStaff v-if="staffLoaded" v-model="staffId" :ph="labelDefault" :elements="staffs" 
            idKey="ID" labelSearchKey="display_name" :displayElement="displayElementFunc" />
</template>

<script>
import SearchStaff from '../Fields/SearchDropdown'
export default {
    components:{SearchStaff},
    props: {
        staffs: null,
        activeStaffId: false,
        labelDefault: {
            type: String,
            default: 'Select WordPress account'
        },
    },
    data() {
        return {
            staffId: false,
        }
    },
    created(){
        if([false,undefined,''].indexOf(this.activeStaffId) === -1){
            this.staffId = this.activeStaffId
        }else{
            this.staffId = this.staffs[0].ID
        }
    },
    methods:{
        displayElementFunc(element){
            return element !== undefined ? element.user_email: 'Unknown'
        },
    },
    computed: {
        staffLoaded(){
            return (this.staffs !== undefined) ? true:false
        },
    },
    watch: {
        staffId: function (newId, oldId) {
            this.$emit('updateStaff', newId)
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
