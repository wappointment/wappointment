<template>
    <div class="wpage px-4 pb-2">
        <div v-if="label" class="mb-2">
            {{ label}} 
        </div>
        <div class="d-flex flex-wrap align-items-stretch check-locations check-cfields" :class="{'showControls':showControls}">

            <template v-for="(item, idx) in items">
                <div :key="idx" @click="onChangedWrapped(item)"  
                class="btn btn-secondary btn-cell align-self-start" 
                :class="{'is-invalid':hasErrors,'core-field':item.core !== undefined,'custom-field':item.core === undefined, noclick: item.always !== undefined}">
                    <div> 
                        <span v-if="item.required || item.core !== undefined" class="text-danger" data-tt="Required">*</span> 
                        <input @click.prevent type="checkbox" :disabled="item.always !== undefined" :checked="(isItemChecked(item) || item.always !== undefined)" 
                        :class="{'is-invalid':hasErrors, selected: (isItemChecked(item) || item.always !== undefined), noclick: item.always !== undefined}"> 
                        {{ item.name }}
                    </div>
                    <transition name="fade">
                        <div v-if="showControls && item.core === undefined" class="edit-button">
                            <span data-tt="Edit">
                                <span class="wicon edit" @click.stop.capture="editCF(item, idx)"></span>
                            </span>
                            <span data-tt="Remove">
                                <span class="wicon trash" @click.stop.capture="removeCF(item, idx)"></span>
                            </span>

                        </div>
                    </transition>
                </div>
            </template>

            <transition name="fade">
                <div v-if="items.length == 0 || showControls" @click="addCF" class="btn btn-secondary btn-cell add-CF" >
                    <span class="wicon plus-alt text-primary" ></span>
                    <div>{{ get_i18n('add_cf', 'settings') }}</div>
                </div>
            </transition>
        </div>
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>
        <WapModal v-if="showAddCF" :show="showAddCF" @hide="hideAddCF" large>
            <h4 slot="title" class="modal-title" v-if="editedItem">Edit Custom Field</h4>
            <h4 slot="title" class="modal-title" v-else>{{ get_i18n('add_cf', 'settings') }}</h4>
            <WAPFormGenerator ref="fg-addCF" :schema="schemaCF" :data="modelHolder" 
        @submit="saveCF" @back="back" :errors="errorsPassed" :key="'formKey'" :labelButton="get_i18n( 'save', 'common')" :backbutton="true" backbuttonLabel="Cancel" />
        </WapModal>
    </div>
</template>

<script>
import AbstractField from '../Form/AbstractField'
import RequestMaker from '../Modules/RequestMaker'
import WappoServiceCF from '../Services/V1/CustomFields'
import FormFieldCheckImages from '../Form/FormFieldCheckImages'
const initModel = () => ({
        name: '',
        type: '',
        required: false,
        values:[],
        
    })
export default {
    extends:FormFieldCheckImages,
    mixins: [AbstractField, RequestMaker],
    name:'opt-customfields',
    props:['eventsBus'],
    created(){
        this.servicesCF = this.$vueService(new WappoServiceCF) 
        this.request(this.loadCFRequest, null, undefined,false, this.loadedCF)
        if(this.definition.listenBus !== undefined){
            this.eventsBus.listens('customfieldsUpdate', this.updateCustomFields)
        }

    },

    data: () => ({
        servicesCF: null,
        itemsLoaded: [],
        showAddCF: false,
        errorsAddCF: {},
        showControls: true,
        editedItem: false,
        modelHolder: initModel(),
        schemaCF: [
            {
              type: 'row',
              class: 'd-flex flex-wrap flex-sm-nowrap align-items-top',
              classEach: 'mr-2',
              fields: [
                {
                    type: 'input',
                    label: 'Name',
                    model: 'name',
                    cast: String,
                    class: 'input-360'
                },
                {
                    type: 'select',
                    model: 'type',
                    flexWrap: true,
                    cast: String,
                    label: 'Type of field',
                    labelSearchKey: 'name',
                    idKey: 'value',
                    elements: [
                        {name: 'Text Input', value:'input'},
                        {name: 'Checkbox', value:'checkbox'},
                        {name: 'Dropdown', value:'select'},
                        {name: 'Radios selection', value:'radios'},
                        {name: 'Checkboxes', value:'checkboxes'},
                        {name: 'Textarea', value:'textarea'},
                        {name: 'Date field', value:'date'},
                    ]
                },
              ]
            },
            {
                type: 'checkbox',
                label: 'Mandatory field',
                model: 'required',
                cast: Boolean,
            },
            {
                type: 'checkbox',
                label: 'Is an address field?',
                model: 'is_address',
                cast: Boolean,
                conditions: [
                  { model:'type', values: ['textarea'] }
                ],
            },
            {
                type: 'checkbox',
                label: 'Only accept date in the future?',
                model: 'is_future',
                cast: Boolean,
                conditions: [
                  { model:'type', values: ['date'] }
                ],
            },
            {
                type: 'inputs',
                label: 'Values',
                model: 'values',
                cast: Array,
                conditions: [
                  { model:'type', values: ['select', 'radios', 'checkboxes'] }
                ],
                definitionChild: {
                    type: 'input',
                    label: 'Value',
                    model: 'valueObject',
                    cast: String,
                    class: 'input-360'
                },
            },

          ]
    }),
    computed: {
        typeLocation(){
            if(this.definition.watchParent !== undefined && this.parentModel[this.definition.watchParent] !== undefined){
                return this.parentModel[this.definition.watchParent]
            }
            return false
        },
        items() {
            if(this.typeLocation){
                for (let i = 0; i < this.itemsLoaded.length; i++) {
                    if(this.itemsLoaded[i].core !== undefined){
                        if(this.itemsLoaded[i].namekey == 'phone'){
                            this.itemsLoaded[i].always = (this.typeLocation == 2) ? true:undefined
                        }
                        
                    }
                    
                }
            }
            
            return this.itemsLoaded
        },
        errorsPassed(){
          return this.errorsAddCF
        }
    },

    methods: {
        updateCustomFields(updatedCustomFields){
            this.itemsLoaded = updatedCustomFields
        },
        onChangedWrapped(item){
            return item.always !== undefined? false:this.onChanged(item)
        },
        back(){
            this.hideAddCF()
            this.$emit('back')
        },
        editCF(item,idx){
            this.modelHolder = item
            this.editedItem =idx
            this.showAddCF = true
        },
        removeCF(item,idx){

            this.$WapModal().confirm({
                title: 'Do you really want to delete this custom field?',
            }).then((response) => {
                if(response !== false){
                    this.request(this.removeCFRequest, {namekey:item.namekey}, undefined,false, this.deletedSuccess)
                }
                
            }) 
        },
        async removeCFRequest(namekey){
            return await this.servicesCF.call('delete',namekey) 
        },
        deletedSuccess(result){
            this.emitFieldsChanges(result)
            this.itemsLoaded = Object.assign({},result.data.fields)
            this.serviceSuccess(result)
        },
        hideAddCF(){
            this.showAddCF = false
            this.modelHolder = initModel()
        },
        addCF(){
            this.requiresAddon('services')
        },

        saveCF(model){
            this.request(this.saveCFRequest, model, undefined,false, this.savedSuccess, this.savedError)
        },
        
        savedSuccess(result){
            this.emitFieldsChanges(result)
            this.itemsLoaded = Object.assign({},result.data.fields)
            this.hideAddCF()
            //close popup 
            //refresh providers
        },
        emitFieldsChanges(result){
            let fieldsEmit = Object.assign({},result.data.fields)
            this.eventsBus.emits('customfieldsUpdate', fieldsEmit)
        },
        savedError(result){
        },
        
        async saveCFRequest(params) {
            return await this.servicesCF.call('save',params) 
        },
        loadedCF(result){
            this.itemsLoaded = result.data
        },

        async loadCFRequest() {
            return await this.servicesCF.call('index') 
        },
    }
}
</script>
