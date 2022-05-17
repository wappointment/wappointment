<template>
    <StaffModalWrapper @save="save" nosave :user="staff">
        <div class="mb-2">
            <div>Describe your staff better with fields to be used in emails and SMS</div>
            <div class="text-muted small">For instance, you can create fields for staff's phone number or Skype account and ask the clients to call on those within the confirmation emails</div>
        </div>
        <div class="p-2 rounded bg-secondary">
            <div v-if="adding">
                <InputPh v-model="field_name" ph="Field name" />
                <button class="btn btn-primary" :class="{'disabled':!isValidField}" @click="saveField">{{get_i18n('save', 'common') }}</button>
            </div>
            <div v-else>
                <button class="btn btn-secondary btn-sm mb-2" @click="newField" v-if="isUserAdministrator"><span class="dashicons dashicons-insert"></span> Add Custom Field</button>
                <div v-if="filteredCF.length > 0 ">
                    <div class="d-flex align-items-center" v-for="field in filteredCF">
                        <div>
                            <InputPh v-model="field_values[field.key]" :ph="field.name" />
                        </div>
                        <a href="javascript:;" @click="deleteField(field.key)"><span class="dashicons dashicons-trash"></span></a>
                        <a href="javascript:;" @click="editField(field)" data-tt="Edit Field Label"><span class="dashicons dashicons-edit"></span></a>
                    </div>
                    <button class="btn btn-primary" @click="saveCustomFields">Save staff values</button>
                </div>
            </div>
        </div>
    </StaffModalWrapper>
</template>

<script>

import StaffModalWrapper from './StaffModalWrapper'
import RequestMaker from '../Modules/RequestMaker'
export default {
    props: ['staff', 'mainService', 'isUserAdministrator'],
    mixins:[RequestMaker],
    components:{StaffModalWrapper},
    data() {
        return {
            custom_fields: [],
            field_values:{},
            adding: false,
            field_name: '',
            deleted_fields:[],
            editingField: false
        } 
    },
    created(){
        if(this.staff.custom_fields !== undefined){
            this.field_values = Object.assign({},this.staff.custom_fields)
        }
        this.loadCustomFields()
    },

    computed:{
        filteredCF(){
            let deleted = this.deleted_fields
            return this.custom_fields.filter((e)=> deleted.indexOf(e.key) === -1 )
        },
        isValidField(){
            return this.field_name.trim()!=''
        }
    },
    methods:{ 

        closeRefresh(){
            this.$emit('close')
        },
        loadedFields(e){
            this.custom_fields = e.data.custom_fields
        },
        loadCustomFields(){
            this.request(this.loadCustomFieldsRequest, {}, undefined, false, this.loadedFields)
        },
         async loadCustomFieldsRequest(){
           return await this.mainService.call('loadCustomFields')
        },
        saveCustomFields(customFields, fieldsValues, deletedFields){
            this.request(this.saveCustomFieldsRequest, {
                id: this.staff.id, 
                custom_fields:this.custom_fields, 
                values:this.field_values, 
                deleted:this.deleted_fields}, undefined, false, this.closeRefresh)
        },
         async saveCustomFieldsRequest(params){
           return await this.mainService.call('saveCustomFields',params)
        },
        updateFieldName(){
            this.field_name = this.field_name.trim()
        },
        deleteField(fieldKey){
            this.$WapModal().confirm({
                title: 'Do you really want to delete this field?',
                content: 'It Will be removed for every staff'
            }).then((result) => {
                if(result === true){
                    this.deleted_fields.push(fieldKey)
                } 
            })
        },

        newField(){
            this.adding = true
        },

        editField(field){
            this.editingField = field
            this.newField()
            this.field_name = field.name
        },

        saveField(){
            if(!this.isValidField){
                return false
            }
            if(this.editingField){
                this.updateLabel()
            }else{
                this.insertField()
            }
            
            this.field_name = ''
            this.adding = false
            this.editingField = false
        },

        updateLabel(){
            let keyLook = this.editingField.key
            let foundIndex = this.custom_fields.findIndex(e => e.key == keyLook )
            this.custom_fields[foundIndex].name = this.field_name
        },

        insertField(){
            this.custom_fields.push({
                name: this.field_name,
                key: this.generateNamekey(this.field_name),
            })
        },

        generateNamekey(string){
            return string.toLowerCase().replace(/[^a-z0-9]/gi,'_')
        },
        
        loaded(viewData){
            this.custom_fields = [null,undefined,false].indexOf(viewData.data.custom_fields) === -1?viewData.data.custom_fields:[]
        },
        
    }

}
</script>