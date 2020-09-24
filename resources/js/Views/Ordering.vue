<script>

export default {
    data: () => ({
        orderedBy: 'id',
        orderedType: 'desc',
        startPage: 1,
        takeX: 10,
    }),

    methods: {
        orderBy(key){
            //if(key == 'tests') return
            if(this.orderedBy != key) {
                this.orderedBy = key
                this.orderedType = 'desc'
            }else{
                this.orderedType = this.orderedType == 'desc' ? 'asc':'desc'
            }
            this.headersListing.orderBy = this.orderedBy
            this.headersListing.orderedType = this.orderedType

            this.loadElements()
        },

        orderedColumn(key){
            let className = ' '
            //let className = (key!= 'tests') ? 'canClick ':' '
            if(key == this.orderedBy){
                className += (this.orderedType == 'desc') ? 'orderedDesc' : 'orderedAsc'
            }
            return className
        },

        
    }
}
</script>