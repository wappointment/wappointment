<template>
    <div>
        <label >{{ label }}</label>
        <SearchDropdown v-model="selectedIds" hasMulti :ph="labelDefault" :elements="solutions" 
             idKey="id" labelSearchKey="options.name"></SearchDropdown>
    </div>
</template>

<script>
import AbstractField from '../Form/AbstractField'
import BackendSolutionsService from '../Backend/Solutions/Endpoints'
import RequestMaker from '../Modules/RequestMaker'
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    name:'opt-solutions',
    mixins: [AbstractField, RequestMaker],
    components: {SearchDropdown},
    props: {
        labelDefault: {
            type: String,
            default: 'Select or type to search'
        },
    },
    data(){
        return {
            serviceBackendSolutions: null,
            solutions: [],
            selectedIds: []
        }
    },
    watch: {
        selectedIds(n,o){
            this.updatedValue = n
        }
    },
    created(){
        this.serviceBackendSolutions = this.$vueService(new BackendSolutionsService)
        this.loadSolutions()
        this.selectedIds = this.updatedValue.map((e) => e.id)
    },

    methods: {
        loadSolutions() {
            this.request(this.requestSolutions,{}, null,this.loadedSolutions)
        },
        async requestSolutions(){
           return await this.serviceBackendSolutions.call('index')
        },
        loadedSolutions(response) {
            this.solutions = response.data
        }
    }
}
</script>

