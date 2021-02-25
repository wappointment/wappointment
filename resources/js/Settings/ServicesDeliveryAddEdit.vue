<template>
    <div class="container-fluid">
      <WAPFormGenerator ref="fgaddlocation" :schema="schemaLocation" :data="modelHolder" 
        @submit="saveLocation" :errors="errorsPassed" :key="'formKey'" labelButton="Save" :backbutton="true" backbuttonLabel="Cancel" />
    </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import WappoServiceLocation from '../Services/V1/Location'
export default {
  extends: abstractView,
    props: {
        subview: {
            type: String,
            default:''
        },
        element: {
            type: Object,
            default:null
        },
    },
    data: () => ({
        servicesService: null,
        modelHolder: {             
            name: '',
            type: 5,
            options: {
              icon: '',
              address: '',
              countries: [],
              fields:[]
            }
          },
        schemaLocation: [
            {
              type: 'row',
              class: 'd-flex flex-wrap flex-sm-nowrap align-items-top fieldthumb',
              classEach: 'mr-2',
              fields: [
                  {
                    type: 'opt-imageselect',
                    model: 'options.icon',
                    cast: String,
                },
                {
                    type: 'input',
                    label: 'Name',
                    model: 'name',
                    cast: String,
                    class: 'input-360'
                },
                
              ]
            },
            {
                type: 'checkimages',
                label: 'Select Type',
                model: 'type',
                images: [
                  { value:5, name:'Video meeting', icon: ['fas', 'video'], sub:'(Zoom, Google meet, ...)'},
                  { value:1, name:'At an address', icon: 'map-marked-alt', sub:'For physical address'},
                  { value:2, name:'By Phone', icon: 'phone', sub:'Includes a phone number field'},
                  { value:3, name:'By Skype', icon: ['fab', 'skype'], sub:'Includes a skype username field'},
                  { value:4, name:'Custom' , sub:'Set your own modality'},
                ],
                labelSearchKey: 'name',
                cast: String,
                radioMode: true
            },
            {
                type: 'checkimages',
                label: 'Video meeting provider',
                radioMode: true,
                model: 'options.video',
                cast: Array,
                images: [
                  { value:'zoom', name:'Zoom', icon: 'zoom.png', icontype: 'img' , realsize: true},
                  { value:'googlemeet', name:'Google Meet', icon: 'google-meet.png', icontype: 'img' , realsize: true},
                ],
                conditions: [
                  { model:'type', values: [5] }
                ],
                validation: ['required']
            },
            {
                type: 'address',
                label: 'Address',
                model: 'options.address',
                conditions: [
                  { model:'type', values: [1] }
                ],
                cast: String,
            },
            {
                type: 'countryselector',
                label: 'Phone field accepted countries',
                model: 'options.countries',
                cast: String,
                conditions: [
                  { model:'type', values: [2] }
                ],
            },
            {
              type: 'opt-customfields',
              label: 'When client select this modality, display following fields',
              model: 'options.fields',
              bus: true,
              listenBus: true,
              cast: Array,
              checklistOptions: { value:'namekey'}
            },
            
          ]
    }),
    created(){
        if(this.element !== null) {
            this.modelHolder = Object.assign({}, this.element)
        }
        this.mainService = this.$vueService(new WappoServiceLocation)
    },
    computed:{
        errorsPassed(){
          return this.errorMessages.validations === undefined ? {}:this.errorMessages.validations
        },
    },
    methods: {
        saveLocation(model){
            this.request(this.saveLocationsRequest, model, undefined,false, this.savedSuccess, this.serviceErrorWrap)
        },
         async saveLocationsRequest(params) {
            return await this.mainService.call('save',params) 
        },
        savedSuccess(result){
            this.itemsLoaded = result.data.locations

            this.$emit('saved', result)
        },
        serviceErrorWrap(e){
            this.$refs.fgaddlocation.reRender()
            return this.serviceError(e)
        },

        removeLocation(item,idx){
            this.$WapModal().confirm({
                title: 'Do you really want to delete this location?',
            }).then((response) => {
                if(response !== false){
                    this.request(this.removeLocationRequest, {id:item.id}, undefined,false, this.deletedSuccess)
                }
            }) 
        },

        async removeLocationRequest(id){
            return await this.mainService.call('delete',id) 
        },
        
        deletedSuccess(result){
            if(result.data.result){
                let itemsLoaded = []
                for (let i = 0; i < this.itemsLoaded.length; i++) {
                    if(this.itemsLoaded[i].id != result.data.deleted){
                        itemsLoaded.push(this.itemsLoaded[i])
                    }
                    
                }
                this.itemsLoaded = itemsLoaded
            }
            this.serviceSuccess(result)
        },
       
        loadedLocations(result){
            this.itemsLoaded = result.data
        },

        async loadLocationsRequest() {
            return await this.mainService.call('index') 
        },
    }  
}
</script>