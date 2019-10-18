<script>
export default {
    props: ['value', 'label', 'tip', 'model', 'eventChange', 'definition', 'errors'],
    watch: {
        updatedValue(newVal, oldVal){
            return this.$emit(this.eventEmit, newVal, this.model)
        }
    },
    data: () => ({
        updatedValue: undefined,
        waitForScript: false,
        id: null,
        eventEmit: 'change'
    }),
    created(){
        this.updatedValue = this.value
        if(['',undefined,null].indexOf(this.eventChange) === -1) this.eventEmit = this.eventChange
    },
    mounted(){
        if(this.waitForScript === false) this.$emit('loaded')
        this.id = this._uid
    },
    computed:{
        hasErrors(){
            return this.errors !== undefined && this.errors.length > 0
        }
    }
}
</script>
