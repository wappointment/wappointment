<script>
export default {
    methods:{
        hasPermission(permission){
            return window.apiWappointment.wp_user.permissions.indexOf(permission) !== -1
        },
    },
    computed:{
        isUserAdministrator(){
            return this.hasPermission('administrator')
        },
        canDeleteClient(){
            return this.isUserAdministrator || this.hasPermission('wappo_clients_del')
        },
        canEditClient(){
            return this.isUserAdministrator || this.hasPermission('wappo_clients_edit')
        },
    },
};
</script>
