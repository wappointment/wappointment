<template>
    <div>
        <div v-if="deliveryListing">
            <div><button class="btn btn-link btn-xs mb-2" @click="backToServices"> < Back to services</button></div>
            <button @click="addElement" class="btn btn-outline-primary btn my-2">Add modality</button>
            <div class="table-hover">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                        </tr>
                    </thead>
                    <draggable @change="orderChanged" v-model="elements" draggable=".row-click" handle=".dashicons-move" tag="tbody" v-if="elements.length > 0">

                        <tr  class="row-click" v-for="(locationObj, idx) in elements">
                            <td>
                                <div @mouseover="">{{ idx + 1 }} </div> 
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <WapImage :element="locationObj" :config="{mauto:false}" :desc="locationObj.name" size="md" /> 
                                    <div class="ml-2">{{ locationObj.name }}</div>
                                </div>
                                <div class="wlist-actions text-muted">
                                    <!-- <span data-tt="Sort"><span class="dashicons dashicons-move"></span></span> -->
                                    <span data-tt="Edit"><span class="dashicons dashicons-edit" @click.prevent.stop="editElement(locationObj)"></span></span>
                                    <span data-tt="Delete" v-if="locationObj.id > 4">
                                        <span class="dashicons dashicons-trash" @click.prevent.stop="deleteModality(locationObj.id)"></span>
                                    </span>
                                    <!--<span>(id: {{ locationObj.id }})</span> -->
                                </div>
                            </td>
    
                        </tr>

                    </draggable>
                     <tbody v-else>
                         <tr>
                            You don't have any delivery modalities yet
                        </tr>
                     </tbody>
                </table>
            </div>

        </div>
        <div v-if="deliveryAdd">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back to modalities</button>
            <ServicesDeliveryAddEdit :element="elementPassed" @saved="hasBeenSavedDeleted"/>
        </div>
    </div>
</template>

<script>

import WappoServiceLocation from '../Services/V1/Location'
import ServicesDeliveryAddEdit from './ServicesDeliveryAddEdit'
import AbstractListing from '../Views/AbstractListing'
export default {
    extends: AbstractListing,
    components:{
        ServicesDeliveryAddEdit
    },
    data: () => ({
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        servicesOrder: []
    }),
    created(){
        this.mainService = this.$vueService(new WappoServiceLocation)
    },
   
    computed: {
        deliveryListing(){
            return this.currentView == 'listing'
        },
        deliveryAdd(){
            return ['add','edit'].indexOf(this.currentView) !== -1
        }
    },
    methods: {
        backToServices(){
            this.$router.push({name:'services'})
        },
        selectElement(modality_id){
            this.editElement(this.elements.find(e => e.id == modality_id))
        },
        addElement(){
            this.requiresAddon('services', 'Add more Modality')
        },
        loadElements() { // overriding
            this.request(this.requestElements,{},undefined,false,this.loadedElements,this.failedLoadingElements)
        },
        afterLoaded(response){
            if(['modalities_add','modalities_edit'].indexOf(this.$route.name) !== -1){
                this.currentView = this.$route.name.replace('modalities_','')
                if(this.currentView == 'edit'){
                    this.selectElement(this.$route.params.id)
                }
            }
        },
        orderChanged(val){
            this.request(this.reorderRequest,{id:val.moved.element.id, 'new_sorting':val.moved.newIndex}, undefined, false, this.hasBeenSavedNoReload)
        },
        async reorderRequest(params){
           return await this.mainService.call('reorder',params)
        },
        hasBeenSavedNoReload(result){
            return this.hasBeenSavedDeleted(result, false)
        },
        
        deleteModality(modality_id){
            this.$WapModal().confirm({
                title: 'Do you really want to delete this modality?',
            }).then((response) => {
                if(response !== false){
                    this.request(this.deleteRequest,{id:modality_id},undefined,false,this.hasBeenSavedDeleted)
                }
            })
        },

        async deleteRequest(params){
           return await this.mainService.call('delete',params)
        },
        hasBeenSavedDeleted(result, reload = true){
            if(reload) {
                this.showListing()
                this.loadElements()
            }
            
            if(result.data.message!==undefined){
                this.$WapModal().notifySuccess(result.data.message)
            }
        },

        editElement(element){
            if(this.crumb){
                this.$emit('updateCrumb',[
                    { target: 'goToMain', label: 'General'},
                    { target: 'goToService', label: 'Services', subview: 'listing' },
                    { target: 'goToServiceAdd', label: 'Edit' , disabled:true},
                ], 'edit', {element:element})
            }else{
                this.currentView = 'edit'
                this.elementPassed = element
                this.$router.push({name:'modalities_edit', params:{id:element.id}})
            }
        },

        showListing(){
            if(this.crumb){
                this.$emit('updateCrumb',[
                    { target: 'goToMain', label: 'General'},
                    { target: 'goToService', label: 'Services' , disabled:true},
                ],'listing')
              }else{
                this.currentView = 'listing'
                this.elementPassed = null
                this.$router.push({name:'modalities'})
            }

        },
    }
}   
</script>
