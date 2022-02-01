<template>
    <div>
        <SearchStaff v-if="staffLoaded" v-model="staffId" :ph="defaulLabel" :elements="staffsPassed" 
            idKey="ID" labelSearchKey="display_name" groupKey="role" @searching="searching" :displayElement="displayElementFunc" />
        <div v-if="isEmailWithNoValue && isUserAdministrator" class="bg-secondary p-2 rounded mt-2 border border-primary">
            <h4 v-if="accountCreated === false">Create a new account? </h4>
            <h4 v-else class="text-success"><span class="dashicons dashicons-yes-alt"></span> Account Created </h4>
            <div class="d-flex justify-content-between">
                <div>
                    <div>Email: <strong>{{ this.search_term }}</strong></div>
                    <div>Password: <strong>{{ this.generatedPassword }}</strong> <a v-if="accountCreated === false" @click="generatePass" href="javascript:;" data-tt="Regenerate"><span class="dashicons dashicons-image-rotate"></span></a></div>
                </div>
                <button v-if="accountCreated === false" class="btn btn-primary" @click="createAccount">Create</button>
            </div>
            
        </div>
    </div>
</template>

<script>
import isEmail from 'validator/es/lib/isEmail'
import SearchStaff from '../Fields/SearchDropdown'
import WPUsersService from '../Services/WP/Users' 
import RequestMaker from '../Modules/RequestMaker' 
import hasPermissions from '../Mixins/hasPermissions' 
import CanEasyReplace from '../Mixins/CanEasyReplace' 
export default {
    mixins:[RequestMaker, hasPermissions, CanEasyReplace],
    components:{SearchStaff},
    props: {
        staffs: null,
        activeStaffId: false,
        labelDefault: '',
        autoselect: true,
    },
    data() {
        return {
            staffId: false,
            generatedPassword:'',
            search_term:'',
            accountCreated: false,
            staffsPassed: [],
            serviceWPUser: null,
            defaultLabel: ''
        }
    },
    created(){
        if([false,undefined,''].indexOf(this.activeStaffId) === -1){
            this.staffId = this.activeStaffId
        }else{
            this.staffId = this.autoselect ? this.staffs[0].ID:false
        }
        this.defaulLabel = this.labelDefault != '' ? this.get_i18n('regav_step1_email','common'):this.labelDefault
        this.serviceWPUser = this.$vueService(new WPUsersService)
        this.staffsPassed = [].concat(this.staffs)
    },
    methods:{
        createAccount() {
            this.request(this.createAccountRequest,  undefined,undefined,false,  this.successCreatedAccount)
        },

        
        async createAccountRequest() {
            let params = {username: this.toAlphaNum(this.search_term), email:this.search_term, password:this.generatedPassword, roles:['wappointment_staff']}
            return await this.serviceWPUser.call('create', params )
        },

        successCreatedAccount(r){
            if(r.data!==undefined && r.data.id !== undefined && r.data.id > 0){
                this.accountCreated = r.data
                this.staffsPassed.push({
                    ID:r.data.id,
                    display_name: '',
                    user_email: r.data.email,
                    gravatar: r.data.avatar_urls[48]
                })
                this.staffId = r.data.id
            }   
        },

        searching(search_term, matches){
            if(parseInt(matches) > 0){
                this.search_term = ''
                return;
            }
            this.search_term = search_term
            this.generatePass()
        },
        generatePass(){
            this.generatedPassword = Math.random().toString(36).slice(-12)
        },
        displayElementFunc(element){
            return element !== undefined ? element.user_email: 'Unknown'
        },

        findUserById(staffId){
            return this.staffsPassed.find(e => e.ID == staffId)
        },

    },
    computed: {
        
        isEmailWithNoValue(){
            return this.search_term !== '' && isEmail(this.search_term)
        },
        staffLoaded(){
            return this.staffs !== undefined
        },
    },
    watch: {
        staffId: function (newId, oldId) {
            this.$emit('updateStaff', this.findUserById(newId))
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
