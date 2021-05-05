<template>
    <StaffModalWrapper @save="save" :user="user">
        <div class="p-2 rounded bg-secondary">
            <div v-for="(permission, permissionkey) in permissions">
                <div class="d-flex">
                    <label>
                        <input type="checkbox" v-model="new_permissions" :value="permissionkey">
                        {{ permission.name }}
                    </label>
                </div>
                <div v-if="new_permissions.indexOf(permissionkey) !== -1" class="ml-4" >
                    <div class="d-flex" v-for="(sub_permission, subpermissionkey) in permission.sub_caps">
                        <label>
                            <input type="checkbox" v-model="new_permissions" :value="subpermissionkey">
                            {{ sub_permission }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </StaffModalWrapper>
</template>

<script>

import StaffModalWrapper from './StaffModalWrapper'
export default {
    components:{StaffModalWrapper},
    props: ['permissions', 'user'],
    data() {
        return {
            new_permissions: [],
        } 
    },
    created(){
        this.initPermissions()
    },
    computed:{
        user_permissions(){
            return this.user.permissions
        }
    },
    methods:{
        save(){
            this.$emit('save', this.new_permissions)
        },
        initPermissions(){
            for (const key in this.permissions) {
                this.lookForPermission(key, this.permissions)
            }
        },
        lookForPermission(key, permissions){
            if (permissions.hasOwnProperty(key) && this.user_permissions.indexOf(key) !== -1) {
                this.new_permissions.push(key)
                this.lookForSubCaps(permissions[key])
            }
        },
        lookForSubCaps(permissionObj){
            if (permissionObj.sub_caps !== undefined) {
                for (const subkey in permissionObj.sub_caps) {
                    this.lookForPermission(subkey, permissionObj.sub_caps)
                }
            }
        }
    }

}
</script>