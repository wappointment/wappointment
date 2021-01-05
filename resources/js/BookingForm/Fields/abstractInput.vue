<script>
export default {
    props:['error','name','options', 'value'],
    watch: {
        updateValue(newVal, oldVal){
            return this.$emit('input', newVal, this.model)
        }
    },
    created(){
        this.updateValue = this.value
    },
    data: () => ({
        updateValue: undefined,
    }),
    computed: {
        getClasses(){
            let classes = {}
            if(this.isRequired) classes['field-required'] = true
            classes[this.hasError ? 'isInvalid':'isValid'] = true
            return classes
        },
        hasError(){
            return ['',undefined,false].indexOf(this.error) === -1
        },
        isRequired(){
            return this.options.core!== undefined || (this.options.required !== undefined && this.options.required)
        },
        getLabel(){
            return this.options.name
        }
    }
}   
</script>