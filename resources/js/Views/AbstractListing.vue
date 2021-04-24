<template>
    <div class="wpage">
        <WPListingHelp @perPage="perPage" :viewData="per_page"/>
        <h1>Elements</h1>
        <div class="table-hover">
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
import RequiresAddon from '../Mixins/RequiresAddon'

export default {
    mixins: [RequestMaker, Ordering, RequiresAddon],
    data: () => ({
        elements: [],
        pagination: null,
        loadedData: false,
        viewData: null,
        page: 1,
        keyDataSource: false
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
        getDataSource(response){
            return this.keyDataSource === false ? response.data:response.data[this.keyDataSource]
        },
        loadedElements(response){
            let dataSource = this.getDataSource(response)
            if(dataSource.current_page !== undefined){ //paginated
                this.pagination = {}
                for (const key in dataSource) {
                    if (dataSource.hasOwnProperty(key)) {
                        const element = dataSource[key]
                        if(key == 'data'){
                            this.elements = element
                        }else{
                            this.pagination[key] = element
                        }
                    }
                }

                this.viewData = response.data.viewData
            }else{
                this.elements = response.data // not paginated
            }
            this.loadedData = true
            if(this.afterLoaded !== undefined && typeof this.afterLoaded == 'function'){
                this.afterLoaded(response)
            }
            
        },
        failedLoadingElements(fail){
            let message = fail.response !== undefined && fail.response.data !== undefined && fail.response.data.message !== undefined ? fail.response.data.message:''
            this.$WapModal().notifyError('Error Loading elements('+message+')')
        },
        setLoadingParams(params){
            params = params === undefined ? {}:params
            params.page = this.page
            return params
        },
        loadElements(params) {
            this.request(this.requestElements, this.setLoadingParams(params), undefined, false, this.loadedElements, this.failedLoadingElements)
        },
        // loadElements() { // overriding
        //     if(this.currentView == 'listing') {
        //         this.request(this.requestElements, {}, undefined, false, this.loadedElements, this.failedLoadingElements)
        //     }
        // },
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
.table-hover {
    color: #626060;
}
.table-hover .wlist-actions {
    /* visibility: hidden; */
    display: none;
}
.table-hover .wlist-actions .dashicons-move{
    cursor: pointer;
}
.table-hover tr:hover .wlist-actions{
    /*visibility: visible;*/
    display:inline-block;
}
.wlist-actions{
    background: #f2f2f2;
    display: inline-block;
    box-shadow: 0px 2px 3px 0 rgba(0,0,0,.04);
    padding: .2em .4em;
    border-radius: 0.6em;
    margin-top: .2em;
    position: absolute;
}
</style>