<template>
    <div >
        <div v-if="serviceListing">
            <button @click="showService" class="btn btn-outline-primary btn my-2">Add new</button>
            <div class="table-responsive table-hover">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Service</th>
                            <th scope="col">Durations</th>
                            <th scope="col">Where</th>
                        </tr>
                    </thead>
                    <draggable @change="orderChanged" v-model="elements" draggable=".row-click" handle=".dashicons-move" tag="tbody" v-if="elements.length > 0">

                            <tr  class="row-click" v-for="(service, idx) in elements">
                                <td>
                                    <div @mouseover="">{{ idx + 1 }} </div> 
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <WapImage :element="service" :config="{mauto:false}" :desc="service.name" size="lg" /> <div class="ml-2">{{ service.name }}</div>
                                        <div class="actions ml-4 text-muted">
                                            
                                            <span data-tt="Sort"><span class="dashicons dashicons-move"></span></span>
                                            <span data-tt="Edit"><span class="dashicons dashicons-edit" @click.prevent.stop="editElement(service)"></span></span>
                                            <span data-tt="Delete"><span class="dashicons dashicons-trash" @click.prevent.stop="deleteService(service.id)"></span></span>
                                            <span>(id: {{ service.id }})</span>
                                        </div>
                                    </div>
                                    
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <DurationCell v-for="(durationObj,dkey) in service.options.durations" :key="dkey" :show="true" :duration="durationObj.duration"/>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <div v-for="locationObj in service.locations" class="location d-flex align-items-center" :data-tt="locationObj.name">
                                            <WapImage :element="locationObj" :desc="locationObj.name" size="md" />
                                        </div>
                                    </div>
                                </td>
                            </tr>

                    </draggable>
                     <tbody v-else>
                         <tr>
                            You don't have any services yet
                        </tr>
                     </tbody>
                </table>
            </div>

        </div>
        <div v-if="serviceAdd">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <ServicesAddEdit :element="elementPassed" @saved="hasBeenSavedDeleted"/>
        </div>
    </div>
</template>

<script>

import WappoServiceService from '../Services/V1/Services'
import ServicesAddEdit from './ServicesAddEdit.vue'
export default {
    extends: window.wappoGet('AbstractListing'),
    components:{
        DurationCell: window.wappoGet('DurationCell'),
        ServicesAddEdit
    },
    props: {
        crumb:{
            type:Boolean,
            default:false
        },
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
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        servicesOrder: []
    }),
    created(){
        this.mainService = this.$vueService(new WappoServiceService)
        if(this.subview !== ''){
            this.currentView = this.subview
        }
        this.elementPassed = this.element
    },
    computed: {
        serviceListing(){
            return this.currentView == 'listing'
        },
        serviceAdd(){
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
            this.request(this.reorderRequest,{id:val.moved.element.id, 'new_sorting':val.moved.newIndex},undefined,false,this.hasBeenSavedNoReload)
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
            
            if(result.data.message!==undefined)this.$WapModal().notifySuccess(result.data.message)
        },
        deleteService(service_id){
            this.$WapModal().confirm({
                title: 'Do you really want to delete this service?',
            }).then((response) => {
                if(response !== false){
                    this.request(this.deleteRequest,{id:service_id},undefined,false,this.hasBeenSavedDeleted)
                }
                
            })
            
        },

        async deleteRequest(params){
           return await this.mainService.call('delete',params)
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
        showService(){
            if(this.elements.length > 2){
                return this.requiresAddon('services', '3 services max allowed')
            }

            if(this.crumb){
                this.$emit('updateCrumb',[
                    { target: 'goToMain', label: 'General'},
                    { target: 'goToService', label: 'Services', subview: 'listing' },
                    { target: 'goToServiceAdd', label: 'Add' , disabled:true},
                ], 'add')
            }else{
                this.currentView = 'add'
                this.elementPassed = null
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
<style>
.location {
    margin: .2rem;
    color: #717171;
}
</style>