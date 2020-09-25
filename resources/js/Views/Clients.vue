<template>
  <div class="wpage px-4 pb-2">
        <template v-if="clientDataToSave === null">
            <WPListingHelp @perPage="perPage" v-if="per_page" :per_page="per_page"/>
            <div class="d-flex align-items-center">
              <h1 class="my-3 mr-3" @click="reloadListing">Clients</h1>
              <button type="button" class="btn btn-outline-primary d-flex align-items-center" @click.prevent.stop="addClient">
                  <span class="dashicons dashicons-plus-alt text-primary mr-2" ></span> Add Client
              </button>
            </div>
            <div v-if="Object.keys(clientListing).length > 1" class="d-flex align-items-center">
              <span v-for="(listingComp,key) in clientListing" :class="{'btn btn-link':view!=key}" @click="view=key">{{ listingComp.name }}</span>
            </div>
            
            <component ref="listing" :is="view" @editClient="editClient" @loaded="loadedResult"/>

        </template>
        <template v-else>
            <button class="btn btn-link btn-xs mb-2" @click="back"> < Back</button>
            <WAPFormGenerator  ref="formgenerator" :schema="schema" classWrapper="clientage-form" :data="clientDataToSave" 
            @submit="saveClient"  >
            </WAPFormGenerator>
        </template>
  </div>
</template>

<script>

import MainClients from './ClientsListing'
import WPListingHelp from '../WP/ScreenListing'
import ClientsService from '../Services/V1/Client'
import Request from '../Modules/RequestMaker'

let client_listing = window.wappointmentExtends.filter('clientListing', {MainClients})

export default {
    components: Object.assign({WPListingHelp}, client_listing),
    mixins: [Request],
    data: () => ({
        mainService: null,
        clientDataToSave: null,
        per_page: false,
        view: 'MainClients',
        schema: [
            {
                type: 'input',
                label: 'Name',
                model: 'name',
                cast: String,
                validation: ['required']
            },
            {
                  type: 'input',
                  label: 'Email',
                  model: 'email',
                  cast: String,
                  validation: ['required'],
                  readonly: false
            },
            {
                  type: 'opt-timezone',
                  label: 'Timezone',
                  model: 'options.tz',
                  timezones_list: [],
                  cast: String,
                  validation: ['required']
            },
            {
                  type: 'opt-phone',
                  label: 'Phone number',
                  model: 'options.phone',
                  cast: String,
            }, 
            {
                  type: 'input',
                  label: 'Skype username',
                  model: 'options.skype',
                  cast: String,
            },

        ]
    }),
   
    created(){
        this.mainService = this.$vueService(new ClientsService)
    },
    computed:{
      clientListing(){
        return client_listing
      },
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
          this.schema[2].timezones_list = response.data.viewData.timezones_list
          this.schema[1].readonly = this.clientDataToSave !== null && this.clientDataToSave.id !==undefined
        },

        saveClient(clientData){
          this.request(this.saveClientRequest, clientData, null, null, this.savedClient)
        },
        savedClient(response){
          this.serviceSuccess(response)
          this.back()
          //this.$refs.listing.loadElements()
        },

        async saveClientRequest(params) {
          return await this.mainService.call('save', params) 
        },
        getPhone(client){
          return client.options.phone !== undefined ? client.options.phone:'---'
        },

        addClient(){
          this.clientDataToSave = {
            name:'',
            email:'',
            options:{
                phone:'',
            },
          }
        },
        editClient(client){
          this.clientDataToSave = null
          setTimeout(this.clientEdited.bind(null,client), 100)
        },
        clientEdited(client){
          this.clientDataToSave = client
        },

    }
}   

</script>
<style>
.clientage-form .fields-wrap{
  max-width: 330px;
}
</style>