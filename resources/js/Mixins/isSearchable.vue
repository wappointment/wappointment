<script>
export default {
     data: () => ({
        searchterm: '',
        searchkey: 'name'
    }),
    computed:{
        filteredSearchable(){
            let searchterm = this.searchterm.toLowerCase()
            let searchkey = this.searchkey
            return this.searchable.filter(e => e[searchkey].toLowerCase().indexOf(searchterm)!==-1)
        },
    },
};
</script>
