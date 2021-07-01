<script>
export default {
    data: () => ({
        serviceLocked: false,
        staffLocked:false
    }),
    created(){
        this.checkServiceLocked()
        this.checkStaffLocked()
    },
    methods:{
        checkServiceLocked(){
            return false
        },
        checkStaffLocked(){
            return false
        },
    }
}
</script>
