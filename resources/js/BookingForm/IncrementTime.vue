<script>


export default {

    methods: {
         incrementTime(){
            return this.incrementType * 60
        },
        /**
         * By default we increment by service duration
         * but if more_st is on then it's another value 
         */
        incrementType(){
            return this.viewData.more_st ? this.viewData.starting_each:this.duration
        },
    }

}
</script>


