<template>
  <div class="wpage px-4 pb-2">
        <WPListingHelp @perPage="perPage" v-if="per_page" :per_page="per_page"/>
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

export default {
    components: {WPListingHelp, ordersListing},
    mixins: [Request],
    data: () => ({
        clientDataToSave: null,
        per_page: false,
    }),
   
    created(){
        this.mainService = this.$vueService(new OrderService)
    },

    methods: {
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