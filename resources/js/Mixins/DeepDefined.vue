<script>
export default {
    methods: {
        isDDF(objectToTest, testParamsString){
            let objectTemp = Object.assign({},objectToTest)
            for (const attribute of testParamsString.split('.')) {
                if(objectTemp[attribute] === undefined){
                    return false
                }
                objectTemp = objectTemp[attribute] //reassign to the next value to test
            }
            return true
        },
    }
};
</script>