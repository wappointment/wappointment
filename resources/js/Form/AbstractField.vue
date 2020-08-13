<script>
export default {
    props: ['value', 'label', 'tip', 'model', 'eventChange', 'definition', 'errors', 'parentModel', 'parentErrors', 'id_ovr'],
    watch: {
        updatedValue(newVal, oldVal){
            if(this.formGen !== false && this.model === undefined ) return
            if(this.definition!==undefined && this.definition.liveParse !== undefined) {
                const parsedVal = this.definition.liveParse(newVal)
                if(newVal != parsedVal){
                    setTimeout(this.updateValueDelay.bind(null,parsedVal), 100)
                }
                
            }
            let type = this.definition !== undefined && this.definition.type !== undefined ? this.definition.type:''
            if(oldVal !== undefined) {
                return this.$emit(this.eventEmit, newVal, this.model, type)
            }
        }
    },
    data: () => ({
        updatedValue: undefined,
        waitForScript: false,
        id: null,
        eventEmit: 'change',
        formGen: false,
    }),
    created(){
        this.updatedValue = this.value
        if(['',undefined,null].indexOf(this.eventChange) === -1) this.eventEmit = this.eventChange
    },
    mounted(){
        if(this.waitForScript === false) this.$emit('loaded')
        this.id = this.id_ovr !== undefined ? this.id_ovr:this._uid
    },
    methods: {
        updateValueDelay(newVal){
            this.updatedValue = newVal
        }
    },
    computed:{
        getClassWrapper(){
            return this.definition.classWrapper !== undefined ? this.definition.classWrapper:{}
        },
        hasErrors(){
            return this.errors !== undefined && Object.keys(this.errors).length > 0
        },
        autocomplete(){
            return this.definition.autocomplete === undefined ? 'off':this.definition.autocomplete
        }
    },

}
</script>
