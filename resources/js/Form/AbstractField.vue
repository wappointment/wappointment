<script>
export default {
    props: ['value', 'label', 'tip', 'model', 'eventChange', 'definition', 'errors'],
    watch: {
        updatedValue(newVal, oldVal){
            if(this.definition!==undefined && this.definition.liveParse !== undefined) {
                const parsedVal = this.definition.liveParse(newVal)
                if(newVal != parsedVal){
                    setTimeout(this.updateValueDelay.bind(null,parsedVal), 100);
                }
                
            }
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
    methods: {
        updateValueDelay(newVal){
            this.updatedValue = newVal
        }
    },
    computed:{
        hasErrors(){
            return this.errors !== undefined && Object.keys(this.errors).length > 0
        }
    },

}
</script>
