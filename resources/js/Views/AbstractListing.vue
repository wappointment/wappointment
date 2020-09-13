<template>
    <div>
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
    </div>  
</template>

<script>

import RequestMaker from '../Modules/RequestMaker'
import draggable from 'vuedraggable'
export default {
    mixins: [RequestMaker],
    data: () => ({
        mainService: null,
        elements: [],
    }),
    components: {
        draggable,
    },
    mounted(){
        this.loadElements()
    },
    methods: {
        loadedElements(response){
            this.elements = response.data
        },
        failedLoadingElements(fail){
            this.$WapModal().notifyError('Error Loading elements')
        },
        loadElements() {
            this.request(this.requestElements,{},undefined,false,this.loadedElements,this.failedLoadingElements)
        },
        async requestElements(){
           return await this.mainService.call('index')
        }
    }
}
</script>
<style >
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