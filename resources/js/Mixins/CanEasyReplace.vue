<script>
export default {
    methods:{
        toAlphaNum(string){
            return string.replace(/[\W_]+/g,"")
        },
    },
}
</script>