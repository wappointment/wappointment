<template>
    <div >
        <div v-if="serviceListing">
            <div class="d-flex align-items-center">
                <button @click="showService" class="btn btn-outline-primary btn my-2 btn-sm">Add 1-to-1 service</button>
                <button @click="showGroupService" class="btn btn-outline-primary btn my-2 btn-sm ml-2"><span class="dashicons dashicons-buddicons-buddypress-logo"></span> Add Group service</button>
                <InputPh v-if="elements && elements.length > 10" class="max-200 ml-2 mb-0" type="text" v-model="searchterm" ph="Search name" />
            </div>
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
                        <ServiceRow v-for="(service, idx) in filteredSearchable" :key="'row-service-'+idx"
                        :service="service" :idx="idx" :can_move="searchterm == '' && elements.length > 1" 
                        @edit="editElement" @delete="deleteService" @shortcode="getShortCode" />
                    </draggable>
                     <tbody v-else>
                         <tr>
                            You don't have any services yet
                        </tr>
                     </tbody>
                </table>
            </div>
            <Pagination v-if="isPaginated"  :pagination="pagination" @changePage="changePage"/>
            <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" noscroll>
                <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
                <ShortcodeDesigner v-if="showShortcode" :service_id="showShortcode" :showTip="false" />
            </WapModal>
        </div>
        <div v-if="serviceAdd">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <ServicesAddEdit :element="elementPassed" :params="paramsPassed" :legacy="false" @saved="hasBeenSaved"/>
        </div>
    </div>
</template>

<script>

import WappoServiceService from '../Services/V1/Services'
import ServicesAddEdit from './ServicesAddEdit'
import ServiceRow from './ServiceRow'
import AbstractListing from '../Views/AbstractListing'
import ShortcodeDesigner from './ShortcodeDesigner'
import isSearchable from '../Mixins/isSearchable'
import HasPopup from '../Mixins/HasPopup'
import CanResetValues from '../Mixins/CanResetValues'

export default {
    extends: AbstractListing,
    mixins: window.wappointmentExtends.filter('ServicesManageMixins', [isSearchable, HasPopup, CanResetValues]),
    components:{
        ServicesAddEdit,
        ShortcodeDesigner,
        ServiceRow
    },
    data: () => ({
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        servicesOrder: [],
        showShortcode: false,
        showCurrency: false,
        keyDataSource:'services',
        paramsPassed: {}
    }),
    created(){
        this.mainService = this.$vueService(new WappoServiceService)
        if(this.$route.name === 'modalities'){
            this.goToDelivery()
        }
    },
    watch:{
        currentView(val){
            this.$emit('isolate', val != 'listing')
        }
    },
    computed: {
        searchable(){
            return this.elements
        },
        serviceListing(){
            return this.currentView == 'listing'
        },
        serviceAdd(){
            return ['add','edit','editLegacy'].indexOf(this.currentView) !== -1
        },
        requiresDBUpgrade(){
            return this.dataResponse!== null && this.dataResponse.db_required 
        },
        limitReached(){
            return this.dataResponse!== null && this.dataResponse.limit_reached
        },
    },
    methods: {
        afterLoaded(response){
            this.$emit('dataUp', response.data)
        },
        hideElementsPopup(){
            this.showShortcode = false
            this.showCurrency = false
        },
        
        getShortCode(service_id){
            this.showShortcode = service_id
            this.openPopup('Get Booking Widget Shortcode')
        },
        // hideShortcode(){
        //     this.showShortcode = false
        // },
        goToDelivery(){
            this.$router.push({name:'modalities'})
        },

        orderChanged(val){
            this.request(this.reorderRequest,{id:val.moved.element.id, 'new_sorting':val.moved.newIndex},undefined,false,this.hasBeenSavedNoReload)
        },
        async reorderRequest(params){
           return await this.mainService.call('reorder', params)
        },
        hasBeenSavedNoReload(result){
            return this.hasBeenSavedDeleted(result, false)
        },
        hasBeenSaved(result){
            if(result.data.message!==undefined) {
                this.$WapModal().notifySuccess(result.data.message, 15)
            }
            this.hasBeenSavedDeleted()
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
            this.currentView = this.requiresDBUpgrade ? 'editLegacy':'edit'
            this.elementPassed = Object.assign({},element)
        },
        showService(){
            if(this.requiresDBUpgrade){
                return this.runDbUpdate()
            }
            if(this.dataResponse.limit_reached !== false){
                return this.requiresAddon('services', this.elements.limit_reached)
            }

            this.currentView = 'add'
            this.elementPassed = null
            
        },
        showGroupService(){
            if(this.dataResponse.limit_reached !== false){
                return this.requiresAddon('services', this.elements.limit_reached)
            }
            if(this.showGroupServiceRun !== undefined){
                return this.showGroupServiceRun()
            }
            return this.requiresAddon('group')
        },
        showListing(){
            this.currentView = 'listing'
            this.resettingValues()
        },
    }
}   
</script>
