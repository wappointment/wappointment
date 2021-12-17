<script>
export default {
    data: () => ({
        resettedValues:{
            'elementPassed': null
        }
    }),
    methods:{
        resettingValues(){
            for (const key in this.resettedValues) {
                if (Object.hasOwnProperty.call(this.resettedValues, key)) {
                    if(key.indexOf('.') !== -1){
                        let keys = key.split('.')
                        this[keys[0]][keys[1]] = this.resettedValues[key]
                    }else{
                        this[key] = this.resettedValues[key]
                    }
                    
                }
            }
        },
    },
}
</script>