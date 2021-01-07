<template>
    <div>
        <div v-if="deliveryListing">
            <div class="table-responsive table-hover">
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
                                    <div class="actions ml-4 text-muted">
                                        <span data-tt="Sort"><span class="dashicons dashicons-move"></span></span>
                                        <span data-tt="Edit"><span class="dashicons dashicons-edit" @click.prevent.stop="editElement(locationObj)"></span></span>
                                        <span data-tt="Delete"><span class="dashicons dashicons-trash" @click.prevent.stop="deleteService(locationObj.id)"></span></span>
                                        <span>(id: {{ locationObj.id }})</span>
                                    </div>
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
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <ServicesDeliveryAddEdit :element="elementPassed" @saved="hasBeenSavedDeleted"/>
        </div>
    </div>
</template>

<script>

import WappoServiceLocation from '../Services/V1/Location'
import ServicesDeliveryAddEdit from './ServicesDeliveryAddEdit'
export default {
    extends: window.wappoGet('AbstractListing'),
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

        loadElements() { // overriding
            if(this.currentView == 'listing') {
                this.request(this.requestElements,{},undefined,false,this.loadedElements,this.failedLoadingElements)
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
            }

        },
    }
}   
</script>
