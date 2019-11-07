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

export default {
    mixins: [RequestMaker],
    data: () => ({
        mainService: null,
        elements: [],
    }),

    mounted(){
        this.loadElements()
    },
    methods: {
        loadedElements(response){
            this.elements = response.data
        },
        failedLoadingElements(){
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