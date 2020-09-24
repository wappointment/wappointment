<template>
    <div class="wpage">
        <WPListingHelp @perPage="perPage" :viewData="per_page"/>
        <h1>Elements</h1>
        <div class="table-responsive">
            <table class="table">
                <caption>List of elements</caption>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(element, idx) in elements">
                            <td>{{ idx + 1 }}</td>
                            <td>{{ element }}</td>
                        </tr>
                    </tbody>
            </table>
        </div>
        <Pagination v-if="isPaginated"  :pagination="pagination" @changePage="changePage"/>
    </div>  
</template>

<script>

import RequestMaker from '../Modules/RequestMaker'
import draggable from 'vuedraggable'
import Pagination from '../Views/Pagination'
import WPListingHelp from '../WP/ScreenListing'
import Ordering from './Ordering'

export default {
    mixins: [RequestMaker, Ordering],
    data: () => ({
        mainService: null,
        elements: [],
        pagination: null,
        loadedData: false,
        viewData: null,
        page: 1
    }),
    components: {
        draggable, Pagination, WPListingHelp
    },
    mounted(){
        this.loadElements()
    },
    computed: {
        isPaginated(){
            return this.pagination !== null && this.pagination.last_page > 1
        }
    },
    methods: {
        loadedElements(response){
            if(response.data.paginated !== undefined){ //paginated
                this.pagination = {}
                for (const key in response.data.paginated) {
                    if (response.data.paginated.hasOwnProperty(key)) {
                        const element = response.data.paginated[key]
                        if(key == 'data'){
                            this.elements = element
                        }else{
                            this.pagination[key] = element
                        }
                    }
                }

                this.viewData = response.data.viewData

                this.elements = response.data.paginated.data
            }else{
                this.elements = response.data // not paginated
            }
            this.loadedData = true
            if(this.afterLoaded !== undefined && typeof this.afterLoaded == 'function'){
                this.afterLoaded(response)
            }
            
        },
        failedLoadingElements(fail){
            this.$WapModal().notifyError('Error Loading elements')
        },
        loadElements(params) {
            if(params === undefined) params = {}
            params.page = this.page
            this.request(this.requestElements, params, undefined, false, this.loadedElements, this.failedLoadingElements)
        },
        changePage(page){
            this.page = page
            this.loadElements()
        },
        perPage(per_page){
            this.loadElements({per_page:per_page})
        },
        async requestElements(params){
           return await this.mainService.call('index', params)
        }
    }
}
</script>
<style >
.wpage h1 {
    cursor: pointer;
}
.table-responsive.table-hover {
    color: #626060;
}
.table-responsive .actions {
    visibility: hidden;
}
.table-responsive .actions .dashicons-move{
    cursor: pointer;
}
.table-responsive tr:hover .actions{
    visibility: visible;
}
</style>