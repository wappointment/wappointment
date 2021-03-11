<template>
    <div>
        <div v-if="label" class="mb-2">
            {{ label}}
        </div>
        <div class="d-flex flex-wrap align-items-stretch check-locations" v-if="items.length > 0">
            <template  v-for="(item, idx) in items" >
                <div :key="idx" @click="onChanged(item)"  :class="getClasses(item)">
                    <span class="dashicons" :class="[isItemChecked(item) ? 'dashicons-yes-alt text-primary':'dashicons-marker']"></span>
                    <WapImage :element="item" :desc="item.name" size="md"/>
                    <div>{{ item.name }}</div>
                </div>
            </template>
            <div v-if="!minimal" @click="addLocation" class="btn btn-secondary btn-cell add-location d-flex align-items-center" >
                <div>
                    <span class="wicon plus-alt text-primary" ></span>
                    <div>Add Delivery Modality</div>
                </div>
            </div>
        </div>
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>

import AbstractField from '../Form/AbstractField'
import RequestMaker from '../Modules/RequestMaker'
import FormFieldCheckImages from '../Form/FormFieldCheckImages'
import WappoServiceLocation from '../Services/V1/Location'

export default {
    mixins: [AbstractField, RequestMaker],
    extends: FormFieldCheckImages,
    created(){
        this.servicesLocations = this.$vueService(new WappoServiceLocation) 
        this.request(this.loadLocationsRequest, null, undefined,false, this.loadedLocations)
    },
    name:'opt-modality',
    data: () => ({
        servicesLocations: null,
        itemsLoaded: [],
        showAddLocation: false,
        showButton: true,
        errorsAddLocation: {},
        editedItem: false,
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
        
    }),
    computed: {
        items() {
            return this.itemsLoaded
        },
        
        errorsPassed(){
          return this.errorsAddLocation
        },
        
    },

    methods: {
        getClasses(item){
            let classses = 'btn btn-secondary btn-cell'

            if(this.isItemChecked(item)){
                classses+= ' selected'
            }
            if(this.hasErrors){
                 classses+= ' is-invalid'
            }
            return classses
        },

        back(){
            this.$emit('back')
        },

        editLocation(item,idx){
            this.modelHolder = item
            this.editedItem =idx
            this.addLocation()
        },
  
        addLocation(){
            this.requiresAddon('services')
        },
        
        saveLocation(model){
            
            this.request(this.saveLocationsRequest, model, undefined,false, this.savedSuccess, this.savedError)
        },

        savedSuccess(result){
            this.itemsLoaded = result.data.locations
        },

        savedError(result){
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
            return await this.servicesLocations.call('delete',id) 
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

        async saveLocationsRequest(params) {
            return await this.servicesLocations.call('save',params) 
        },

        loadedLocations(result){
            this.itemsLoaded = result.data
        },

        async loadLocationsRequest() {
            return await this.servicesLocations.call('index') 
        },
    }
}
</script>
<style>

.check-locations .btn.btn-secondary.btn-cell.add-location {
    background-color: #fff;
    border: 2px dashed var(--secondary);
    color: var(--gray);
    font-size: .7rem;
}
.check-locations .btn.btn-secondary.btn-cell.add-location:hover {
    border-color: var(--primary);
}

.check-locations .btn.btn-secondary.btn-cell {
    position: relative;
    margin: 0 .5rem 2.5rem .5rem;
}
.edit-button{
    cursor: pointer;
    color: var(--muted);
    text-align: center;
    font-size: .7rem;
    position: absolute;
    bottom: -28px;
    border: 1px solid #eee;
    border-radius: .2rem;
    background-color: #fff;
    left: 10px;
    box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.08);
    padding: .2rem;
}

</style>