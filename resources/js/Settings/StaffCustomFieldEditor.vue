<template>
    <StaffModalWrapper @save="save" nosave :user="staff">
        <div class="p-2 rounded bg-secondary">
            <div v-if="adding">
                <InputPh v-model="field_name" ph="Field name" />
                <button class="btn btn-primary" @click="addField">Add new field</button>
            </div>
            <div v-else>
                <button class="btn btn-secondary" @click="newField">Add Custom Field</button>
                <div v-if="custom_fields.length > 0 ">
                    <div class="d-flex" v-for="field in custom_fields">
                        <div class="ml-2">
                            <InputPh v-model="field_values[field.key]" :ph="field.name" />
                        </div>
                    </div>
                    <button class="btn btn-primary" @click="save">Save values</button>
                </div>
            </div>
        </div>
    </StaffModalWrapper>
</template>

<script>

import StaffModalWrapper from './StaffModalWrapper'
import ViewData from '../Modules/ViewData'
import RequestMaker from '../Modules/RequestMaker'
export default {
    props: ['staff'],
    mixins:[RequestMaker, ViewData],
    components:{StaffModalWrapper},
    data() {
        return {
            custom_fields: [],
            viewName: 'staffCustomField',
            field_values:{},
            adding: false,
            field_name: ''
        } 
    },
    created(){
        if(this.staff.custom_fields !== undefined){
            this.field_values = Object.assign({},this.staff.custom_fields)
        }
    },
    methods:{
        newField(){
            this.adding = true
        },
        addField(){
            this.custom_fields.push({
                name: this.field_name,
                key: this.field_name.toLowerCase().replace(/[^a-z0-9]/gi,'_'),
            })
            this.field_name = ''
            this.adding = false
        },
        loaded(viewData){
            this.custom_fields = [null,undefined,false].indexOf(viewData.data.custom_fields) === -1?viewData.data.custom_fields:[]
        },
        save(){
            this.$emit('save', this.custom_fields, this.field_values)
        },

    }

}
</script>