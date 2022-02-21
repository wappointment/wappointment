<script>

export default {
    methods: {
        wIsEmpty(test){
            return [null,undefined,''].indexOf(test) !== -1
        },
        ucFirst(string){
            return string[0].toUpperCase() + string.substring(1)
        }
    }
}   
</script>

