<template>
    <div v-if="staffLoaded" class="form-group p-0 col-sm-4 col-lg-3 m-0">
        <span v-if="this.staffs.length == 1">{{ displayElementFunc(staffs[0]) }}</span>
        <SearchStaff v-else v-model="staffId" :ph="labelDefault" :elements="staffs" 
            idKey="ID" labelSearchKey="display_name" :displayElement="displayElementFunc"></SearchStaff>
    </div>
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
            default: 'Select or search staff'
        },
    },
    data() {
        return {
            staffId: false,
        }
    },
    created(){
        if(this.activeStaffId!==false){
            this.staffId = this.activeStaffId
        }
    },
    methods:{
        displayElementFunc(element){
            return element !== undefined ? element.display_name + ' - ' + element.user_email: 'Unknown'
        },
    },
    computed: {
        staffLoaded(){
            return (this.staffs !== undefined) ? true:false
        },
    },
    watch: {
        staffId: function (newId, oldId) {
            if(oldId !== false) this.$emit('updateStaff', newId)
        }
    },
}
</script>
