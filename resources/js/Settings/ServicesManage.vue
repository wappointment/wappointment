<template>
    <div >
        <div v-if="serviceListing">
            <button @click="showService" class="btn btn-outline-primary btn my-2">Add service</button>
            <div class="table-hover" v-if="elements">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Service</th>
                            <th scope="col">Durations</th>
                            <th scope="col">Delivery Modalities <a href="javascript:;" v-if="!requiresDBUpgrade" @click="goToDelivery">Manage</a></th>
                        </tr>
                    </thead>
                    <draggable @change="orderChanged" v-model="elements" draggable=".row-click" handle=".dashicons-move" tag="tbody" v-if="elements.length > 0">

                        <tr  class="row-click" v-for="(service, idx) in elements">
                            <td>
                                <div @mouseover="">{{ idx + 1 }} </div> 
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <WapImage v-if="serviceHasIcon(service)" :element="service" :config="{mauto:false}" :desc="service.name" size="lg" /> 
                                    <div class="ml-2">{{ service.name }}</div>
                                </div>
                                <div class="wlist-actions text-muted">
                                    <span data-tt="Sort" v-if="elements.length > 1" ><span class="dashicons dashicons-move"></span></span>
                                    <span data-tt="Get Shortcode"><span class="dashicons dashicons-shortcode" @click.prevent.stop="getShortCode(service.id)"></span></span>
                                    <span data-tt="Edit"><span class="dashicons dashicons-edit" @click.prevent.stop="editElement(service)"></span></span>
                                    <span data-tt="Delete"  ><span class="dashicons dashicons-trash" @click.prevent.stop="deleteService(service.id)"></span></span>
                                    <span >(id: {{ service.id }})</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <DurationCell v-for="(durationObj,dkey) in getDurations(service)" :key="dkey" :show="true" :duration="durationObj.duration"/>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <div v-for="locationObj in getLocations(service)" class="location d-flex align-items-center" :data-tt="locationObj.name">
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
            <Pagination v-if="isPaginated"  :pagination="pagination" @changePage="changePage"/>
            <WapModal v-if="showShortcode" :show="showShortcode" @hide="hideShortcode" noscroll>
                <h4 slot="title" class="modal-title"> 
                    <span>Get Booking Widget Shortcode</span>
                </h4>
                <ShortcodeDesigner :service_id="showShortcode" :showTip="false" />
            </WapModal>
        </div>
        <div v-if="serviceAdd">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <ServicesEditLegacy v-if="currentView=='editLegacy'" :legacy="true" :element="elementPassed" @saved="hasBeenSavedDeleted"/>
            <ServicesAddEdit v-else :element="elementPassed" :legacy="false" @saved="hasBeenSavedDeleted"/>
        </div>
    </div>
</template>

<script>

import WappoServiceService from '../Services/V1/Services'
import ServicesAddEdit from './ServicesAddEdit'
import ServicesEditLegacy from '../Views/Subpages/Service'
import AbstractListing from '../Views/AbstractListing'
import DurationCell from '../BookingForm/DurationCell'
import ShortcodeDesigner from './ShortcodeDesigner'
export default {
    extends: AbstractListing,
    components:{
        DurationCell,
        ServicesAddEdit,
        ServicesEditLegacy,
        ShortcodeDesigner
    },
    data: () => ({
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        servicesOrder: [],
        showShortcode: false,
        keyDataSource:'services'
    }),
    created(){
        this.mainService = this.$vueService(new WappoServiceService)
        if(this.$route.name === 'modalities'){
            this.goToDelivery()
        }
    },
    computed: {
        serviceListing(){
            return this.currentView == 'listing'
        },
        serviceAdd(){
            return ['add','edit','editLegacy'].indexOf(this.currentView) !== -1
        },
        requiresDBUpgrade(){
            return this.elements.db_required 
        },
        limitReached(){
            return this.elements.limit_reached
        }
    },
    methods: {
        getShortCode(service_id){
            this.showShortcode = service_id
        },
        hideShortcode(){
            this.showShortcode = false
        },
        goToDelivery(){
            this.$router.push({name:'modalities'})
        },
        serviceHasIcon(service){
            return service.options.icon != ''
        },
        isLegacy(service){
            return service.type !== undefined
        },
        getDurations(service){
            return this.isLegacy(service) ? [{duration: service.duration}]:service.options.durations
        },
        getLocations(service){
            if(!this.isLegacy(service)){
                return service.locations
            }
            let newTypes = []
            for (let i = 0; i < service.type.length; i++) {
                newTypes.push({
                    name: service.type[i],
                    type: service.type[i],
                    options:{}
                })
            }
            return newTypes
        },
        // loadElements() { // overriding
        //     if(this.currentView == 'listing') {
        //         this.request(this.requestElements, {}, undefined, false, this.loadedElements, this.failedLoadingElements)
        //     }
        // },
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
            
            if(result.data.message!==undefined) {
                this.$WapModal().notifySuccess(result.data.message)
            }
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
                this.currentView = this.requiresDBUpgrade ? 'editLegacy':'edit'
                this.elementPassed = element
            }
        },
        showService(){
            if(this.requiresDBUpgrade){
                return this.runDbUpdate()
            }
            if(this.elements.limit_reached !== false){
                return this.requiresAddon('services', this.elements.limit_reached)
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