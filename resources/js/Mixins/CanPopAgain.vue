<script>
export default {
    data: () => ({
        show: true
    }),
    created(){
        this.$router.afterEach(this.popAgain)
    },
    methods:{
        popAgain(){
            this.show = true
        },
        hidePopup(){
            this.show = false
        },
    },
}
</script>