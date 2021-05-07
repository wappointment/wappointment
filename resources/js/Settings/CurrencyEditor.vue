<template>
    <div>
        <SearchDropdown v-model="selectedCurrency" ph="Select a currency" :elements="modifiedCurrencies" 
                idKey="code" labelSearchKey="name" />
        <button class="btn btn-primary mt-2" @click="save">Save</button>
    </div>
</template>

<script>

import SearchDropdown from '../Fields/SearchDropdown'
import RequestMaker from '../Modules/RequestMaker'
import WappoCurrency from '../Services/V1/Currency'
export default {
    mixins:[RequestMaker],
    components: {SearchDropdown},
    props:{
        currency: {
            type: Object,
        },
    },
    data: () => ({
        serviceCurrency:'',
        currencies:[],
        selectedCurrency: false,
    }),

    created(){
        this.serviceCurrency = this.$vueService(new WappoCurrency)
        this.get()
        this.selectedCurrency = this.currency
    },
    computed:{
        modifiedCurrencies(){
            return this.currencies.map(function(e){
                e.name = e.code+' | '+e.symbol + ' | '+e.name
                return e
            } )
        }
    },
    methods:{
        get(){
            this.request(this.getRequest, {},  undefined, false, this.loaded)
        },
        async getRequest(params) {
          return await this.serviceCurrency.call('index', params)
        },
        loaded(response){
            this.currencies = response.data
        },
        save(){
            this.request(this.saveRequest, {},  undefined, false, this.savedOk)
        },
        async saveRequest(params) {
          return await this.serviceCurrency.call('save', {
              currency: this.selectedCurrency
          })
        },
        savedOk(){
            this.$emit('close', this.selectedCurrency)
        },
    }
}
</script>
