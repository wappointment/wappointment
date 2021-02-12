<template>
    <div class="container-fluid">
      <WAPFormGenerator ref="fg-addlocation" :schema="schemaLocation" :data="modelHolder" 
        @submit="saveLocation" @back="back" :errors="errorsPassed" :key="'formKey'" labelButton="Save" :backbutton="true" backbuttonLabel="Cancel" />
    </div>
</template>

<script>
import abstractView from '../Views/Abstract'

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
        errorsAddLocation: {},
        modelHolder: {             
            name: '',
            type: 1,
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
                type: 'opt-ss-customfields',
                label: 'When client selects this modality, display following fields',
                model: 'options.fields',
                bus: true,
                cast: Array,
                watchParent: 'type',
                checklistOptions: { value:'namekey'}
            },
          ]
    }),
    created(){
        if(this.element !== null) {
            this.modelHolder = Object.assign({}, this.element)
        }
        
    },
    computed:{
        errorsPassed(){
          return this.errorsAddLocation
        },
    },
    methods: {
        saveLocation(model){
            this.request(this.saveLocationsRequest, model, undefined,false, this.savedSuccess, this.savedError)
        },
        savedSuccess(result){
            this.itemsLoaded = result.data.locations
            //this.itemsLoaded[this.editedItem] = result.data.location
            this.hideAddLocation()
            //close popup 
            //refresh providers
        },
        savedError(result){
        },
    }  
}
</script>