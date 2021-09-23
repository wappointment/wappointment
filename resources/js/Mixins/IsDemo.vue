<script>
export default {

    data() {
        return {
            disabledButtons: false,
        };
    },
    created(){
        if(this.isDemo){
            this.disabledButtons = true
        }
    },
    computed:{
        isDemo(){
            return this.options !== undefined && this.options.demoData !== undefined
        },
    },
    methods: {

        triggersDemoEvent(newStep){
            if(this.disabledButtons  && this.options !== undefined) {
              this.options.eventsBus.emits('stepChanged', newStep)
              return true
            } 
        }

    }
};
</script>
