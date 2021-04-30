<template>
  <div class="wpage px-4 pb-2">
        <template v-if="clientDataToSave === null">
            <WPListingHelp @perPage="perPage" v-if="per_page" :per_page="per_page"/>
            <div class="d-flex align-items-center">
              <h1 class="my-3 mr-3" @click="reloadListing">Clients</h1>

              <button v-if="isUserAdministrator" type="button" class="btn btn-outline-primary d-flex align-items-center" @click.prevent.stop="addCustomField">
                <span class="dashicons dashicons-id text-primary mr-2" ></span> Add/Edit Custom Fields
              </button>
            </div>
            <div v-if="Object.keys(clientListing).length > 1" class="d-flex align-items-center">
              <span v-for="(listingComp,key) in clientListing" :class="{'btn btn-link':view!=key}" @click="view=key">{{ listingComp.label }}</span>
            </div>
            
            <component ref="listing" :is="view" @editClient="editClient" @deleteClient="deleteClient" @loaded="loadedResult"/>
        </template>
        <template v-else>
          <button class="btn btn-link btn-xs mb-2" @click="back"> < Back</button>
          <WAPFormGenerator ref="formgenerator" :schema="schema" classWrapper="clientage-form" :data="clientDataToSave" @submit="saveClient" />
        </template>
  </div>
</template>

<script>

import MainClients from './ClientsListing'
import WPListingHelp from '../WP/ScreenListing'
import ClientsService from '../Services/V1/Client'
import Request from '../Modules/RequestMaker'
import RequiresAddon from '../Mixins/RequiresAddon'
import hasPermissions from '../Mixins/hasPermissions'
let client_listing = window.wappointmentExtends.filter('clientListing', {MainClients})

export default {
    components: Object.assign({WPListingHelp}, client_listing),
    mixins: [Request, RequiresAddon, hasPermissions],
    data: () => ({
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
          this.$emit('fullyLoaded')
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

        addCustomField(){
          this.requiresAddon('services', 'Create your own Custom Field')
        },

        editClient(client){
          this.clientDataToSave = null
          setTimeout(this.clientEdited.bind(null,client), 100)
        },

        clientEdited(client){
          this.clientDataToSave = client
        },

        deleteClient(clientId){
          this.$WapModal().confirm({
            title: 'Do you really want to delete this client?',
          }).then((result) => {
            if(result === true){
                this.request(this.deleteClientRequest, {id: clientId}, null, null, this.deletedClient)
            } 
          })
          
        },

        deletedClient(response){
          this.serviceSuccess(response)
          this.$refs.listing.elements = this.$refs.listing.elements.filter(e => e.id != response.data.elementDeleted)
        },

        async deleteClientRequest(params) {
          return await this.mainService.call('delete', params) 
        },

        

    }
}   

</script>
<style>
.clientage-form .fields-wrap{
  max-width: 330px;
}
</style>