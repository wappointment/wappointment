<template>
  <div class="wpage px-4 pb-2">
        <WPListingHelp @perPage="perPage" v-if="per_page" :per_page="per_page">
          <div>
            <div class="d-flex mb-2 align-items-center">
                <label for="per_page" class="col-sm-3">Tax</label>
                  <div class="col-sm-4">
                      <input type="text" v-model="tax" size="2" @change="saveTax"> %
                  </div>
            </div>
             
          </div>
        </WPListingHelp>
        <div class="d-flex align-items-center">
          <h1 class="my-3 mr-3" @click="reloadListing">Orders</h1>
        </div>
        
        <component ref="listing" is="ordersListing" @loaded="loadedResult"/>
  </div>
</template>

<script>

import ordersListing from './OrdersListing'
import WPListingHelp from '../WP/ScreenListing'
import OrderService from '../Services/V1/Order'
import Request from '../Modules/RequestMaker'
import SettingsSave from '../Modules/SettingsSave'

export default {
    components: {WPListingHelp, ordersListing},
    mixins: [Request, SettingsSave],
    data: () => ({
        clientDataToSave: null,
        per_page: false,
        tax: 0,
    }),
   
    created(){
        this.mainService = this.$vueService(new OrderService)
    },

    methods: {
        saveTax(){
          this.settingSave('tax', this.tax)
        },  
        perPage(per_page){
          this.$refs.listing.perPage(per_page)
        },

        reloadListing(){
          this.$refs.listing.loadElements()
        },

        back(){
          this.clientDataToSave = null
        },

        loadedResult(response){
          this.per_page = parseInt(response.data.viewData.per_page)
          this.tax = parseFloat(response.data.viewData.tax)
          this.$emit('fullyLoaded')
        },

    }
}   

</script>
<style>
.clientage-form .fields-wrap{
  max-width: 330px;
}
</style>