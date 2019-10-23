<template>
    <form @submit.prevent="submitTrigger" class="form-wrap" :class="classWrapper">
        <div v-if="!formIsReady" class="loading-overlay d-flex align-items-center">
            <WLoader></WLoader>
        </div>
        <div class="fields-wrap" >
            <template v-for="(element, keydi) in schema">
                <div v-if="element.type == 'row'"  :class="getRowClass(element)">
                    <div class="form-group"  v-for="(subelement, skeydi) in element.fields" :class="getRowEachClass(element,subelement)" :style="getStyle(subelement)">
                        <component v-if="isVisible(subelement)" :is="getFormComponent(subelement)" :value="getModelValue(subelement)"
                        @loaded="loadedField(keydi, skeydi)"
                        v-bind="allowBind(subelement)" @change="changedValue" :definition="subelement" :errors="getErrors(subelement)" />
                    </div>
                </div>
                <div v-else class="form-group"  :style="getStyle(element)" :class="getRowEachClass(element)">
                    <component v-if="isVisible(element)" :is="getFormComponent(element)" :value="getModelValue(element)"
                    @loaded="loadedField(keydi)" :errors="getErrors(element)"
                    v-bind="allowBind(element)" @change="changedValue" :definition="element"/>
                </div>
            </template>
            <slot></slot>
            <div>
                <button class="btn btn-primary" :class="{'btn-disabled':!isValid}" :disabled="!isValid" type="button" @click.prevent="submitTrigger">{{ buttonLabel }}</button>
            </div>
        </div>
    </form>
</template>

<script>
import AbstractField from './AbstractField'
import LabelMaterial from '../Fields/LabelMaterial'
import FormFieldInput from './FormFieldInput'
import FormFieldDuration from './FormFieldDuration'
import FormFieldAddress from './FormFieldAddress'
import FormFieldFile from './FormFieldFile'
import FormFieldEditor from './FormFieldEditor'
import FormFieldPrices from './FormFieldPrices'
import FormFieldStatus from './FormFieldStatus'
import FormFieldCountrySelector from './FormFieldCountrySelector'
import FormFieldSelect from './FormFieldSelect'
import FormFieldCheckImages from './FormFieldCheckImages'

import DotKey from '../Modules/DotKey'
export default {
    mixins: [DotKey],
    props: {
        schema: {
            type: Array,
            default: []
        }, 
        errors: {
            type: Object,
            default: {}
        },
        data: {
            type: Object,
            default: undefined
        }, 
        create: {
            type: String,
            default: 'Create'
        }, 
        modify: {
            type: String,
            default: 'Update'
        },
        creating: {
            type: Boolean,
            default: false
        },
        labelButton: {
            type: String,
        },
        classWrapper: {
            type: String,
            default: 'basic-form-wrapper'
        },
        ready: {
            type: Boolean,
            default: false
        },
    },
    components: {FormFieldInput, FormFieldEditor,FormFieldPrices,
        FormFieldStatus,FormFieldFile, FormFieldSelect,FormFieldCheckImages,
        FormFieldAddress, FormFieldDuration,FormFieldCountrySelector,},
    data: () => ({
        modelHolder: {},
        isValid: false,
        fieldsStatus:{},
        formIsReady: false,
        pendingValidation: false,
        submitted: false,
        errorsData: {},
    }),
    created(){
        if(this.submittedErrors){
            this.errorsData = Object.assign({}, this.errors)
            this.submitted = true
        }
        this.modelHolder = this.creating === false ? Object.assign({},this.data):{}
        this.verifyModel()
    },

    computed: {
        submittedErrors(){
            return Object.keys(this.errors).length > 0
        },
        buttonLabel(){
            return this.labelButton !== undefined ? this.labelButton :(this.creating === false ? this.modify:this.create)
        },
    },
    methods: {
        getErrors(element){
            return this.submitted && this.errorsData[element.model] !== undefined ? this.errorsData[element.model]:false
        },
        getRowClass(row){
            return row.class!== undefined ? row.class: 'd-flex justify-content-between flex-wrap flex-sm-wrap'
        },
        getRowEachClass(row, sube){
            let classStr = row.classEach!== undefined ? row.classEach: ''
            classStr += sube!== undefined && sube.class!== undefined ? ' '+sube.class: ''
            return classStr
        },
        getElementDefinition(model){
            for (let i = 0; i < this.schema.length; i++) {
                const row = this.schema[i]
                if(row.type !== undefined && row.type == 'row'){
                    for (let j = 0; j < row.fields.length; j++) {
                        if(row.fields[j].model == model){
                            return row.fields[j]
                        } 
                    }
                }else{
                    if(row.model == model){
                        return row
                    }  
                }
            }
            
        },
        isVisible(element){
            
            if(element.conditions !== undefined){
                let conditions_failed = false
                for (let i = 0; i < element.conditions.length; i++) {
                    const condition = element.conditions[i]
                    //console.log('typeof',typeof this.modelHolder[condition.model], this.modelHolder[condition.model])
                    if(['array','object'].indexOf(typeof this.modelHolder[condition.model]) !== -1){
                        
                        conditions_failed = !this.atLeastOne(this.modelHolder[condition.model], condition)
                    }else{
                        if(condition.values.indexOf(this.modelHolder[condition.model]) === -1){
                            conditions_failed = true
                        }
                    }
                    
                    
                }
                //console.log('conditions_failed', conditions_failed)
                if(conditions_failed) return false
            }
            return true
        },
        atLeastOne(values, condition){
            let at_least_one = false
            for (let j = 0; j < values.length; j++) {
                const value = values[j]
                
                //console.log('value match',condition.values, value, condition.values.indexOf(value))
                if(condition.values.indexOf(value) !== -1){
                    at_least_one = true
                }
            }
            return at_least_one
        },
        loadedField(keydi, skeydi = false){
            if(skeydi !== false) {
                this.schema[keydi].fields[skeydi].loaded = true
            }else{
                this.schema[keydi].loaded = true
            }

            this.testFormReady()
        },
        testFormReady(){
            for (const key in this.schema) {
                if (this.schema.hasOwnProperty(key)) {
                    if(this.schema[key].type == 'row'){
                        for(var i = 0, length1 = this.schema[key].fields.length; i < length1; i++){
                            if(this.isVisible(this.schema[key].fields[i]) && this.schema[key].fields[i].loaded === false) return
                        }
                    }else{
                        if(this.isVisible(this.schema[key]) && this.schema[key].loaded === false) return
                    }
                    
                }
            }
            this.formIsReady = true
        },
         getStyle(element) {
             return element.styles !== undefined ? this.convertStyleParams(element.styles) : ''
         },
         convertStyleParams(styleObject){
             let styleString = ''
             for (const key in styleObject) {
                 if (styleObject.hasOwnProperty(key)) {
                     const element = styleObject[key];
                     styleString += key+':'+styleObject[key]+';'
                 }
             }
             return styleString
         },
         setError(keyEl, type){
             if(this.errorsData[keyEl] === undefined )this.errorsData[keyEl] = {}
             if(this.errorsData[keyEl][type] === undefined) this.errorsData[keyEl][type] = this.getErrorMsg(type)
         },
         clearError(keyEl, type){
             
            delete this.errorsData[keyEl]
            this.errorsData = this.cleanObject(this.errorsData)
/*             if(Object.keys(this.errorsData[keyEl]).length < 1){
                
            }
              if(this.errorsData[keyEl] !== undefined && this.errorsData[keyEl][type] !== undefined){
                 delete this.errorsData[keyEl][type]
                 this.errorsData[keyEl] = this.cleanObject(this.errorsData[keyEl])
                 
             }  */
            /* if(this.errorsData[keyEl] !== undefined && this.errorsData[keyEl][type] !== undefined){
                 delete this.errorsData[keyEl][type]
                 this.errorsData[keyEl] = this.cleanObject(this.errorsData[keyEl])
                 console.log('new obj 1', this.errorsData[keyEl])
                 if(Object.keys(this.errorsData[keyEl]).length == 0) {
                     delete this.errorsData[keyEl]
                     this.errorsData = this.cleanObject(this.errorsData)
                     console.log('new obj 2', this.errorsData)
                 }
                 
             }  */
         },

        cleanObject(obj) {
            let newObj = {}
            Object.keys(obj).forEach(key => {
                if( obj[key] != undefined) newObj[key] = obj[key]
            })  
            return newObj
        },

         getErrorMsg(type){
             switch (type) {
                 case 'required':
                     return 'Element is required'
             }
         },

         isEmptyValue(value){
             switch (typeof value) {
                 case 'array':
                     return value.length == 0
                 case 'string':
                     return value == ''
                 case 'boolean':
                     return value == ''
                 case 'object':
                     return Object.keys(value).length == 0
             }
         },

         validateElement(key, subkey = false){
             let keyEl = subkey ? key+'.'+subkey:key
             let value = subkey ? this.modelHolder[key][subkey]:this.modelHolder[key]
             if(this.getElementDefinition(keyEl)!== undefined && this.isVisible(this.getElementDefinition(keyEl))) {
                if(this.isEmptyValue(value)){
                     console.log('set error for', keyEl)
                     this.setError(keyEl,'required')
                     return false
                }else{
                    console.log('clear error for', keyEl,  typeof value, value)
                    this.clearError(keyEl,'required')
                }
                
            }
            return true
         },
         runValidation(){

             //this.errorsData = {}
             for (const key in this.modelHolder) {
                 if (this.modelHolder.hasOwnProperty(key) ) {
                     
                     if(key == 'options'){
                         for (const subkey in this.modelHolder[key]) {
                             if (this.modelHolder[key].hasOwnProperty(subkey) ) {
                                 if(!this.validateElement(key, subkey)){
                                     return this.isValid = false
                                 }
                             }
                         }
                     }else{
                         if(!this.validateElement(key)){
                            return this.isValid = false
                        }
                     }
                     
                 }
             }
             this.isValid = true
         },
         verifyModel(){
            for (const key in this.schema) {
                if (this.schema.hasOwnProperty(key)) {
                     if(this.schema[key].type== 'row'){
                        for(var i = 0, length1 = this.schema[key].fields.length; i < length1; i++){
                            
                            this.getModelValue(this.schema[key].fields[i])
                            this.schema[key].fields[i].loaded = false
                        }
                    }else{
                        this.getModelValue(this.schema[key])
                        this.schema[key].loaded = false
                    }
                    
                }
            }
        }, 
        allowBind(element){
            let ignoreKeys = ['cast', 'type']

            return Object.keys(element)
            .filter((e) => ignoreKeys.indexOf(e) === -1)
            .reduce((obj, key) => {
                obj[key] = element[key];
                return obj;
            }, {})
        },
        changedValue(newVal, model){
            this.setModelValue(newVal, model)
            if(this.pendingValidation === false){
                this.pendingValidation = setTimeout(this.runningValidation, 200)
            }
        },
        runningValidation(){

            this.runValidation()
            this.testFormReady()
            
            
            this.pendingValidation = false
        },
        submitTrigger(forceRequest = false){
            this.submitted = true
            if(forceRequest=== false && !this.isValid) return false
            this.$emit('submit', Object.assign({}, this.modelHolder), this.creating)
        },

        setModelValue(newVal, model){
             if(model.indexOf('.')!== -1){
                let myarr = model.split('.')
                this.modelHolder[myarr[0]][myarr[1]] = newVal
            }else{
                this.modelHolder[model] = newVal
            }
        },
        getModelValue(element){
            if(element.model.indexOf('.')!== -1){
                let myarr = element.model.split('.')
                if(this.modelHolder[myarr[0]]==undefined || this.modelHolder[myarr[0]][myarr[1]] === undefined){
                    this.defineSubModelEntry(element, myarr)
                } 
                return this.modelHolder[myarr[0]][myarr[1]]
            }else{
                if(this.modelHolder[element.model] === undefined){
                    this.defineModelEntry(element)
                } 
                return this.modelHolder[element.model]
            }
             
            
        },
        defineModelEntry(element){
            this.modelHolder[element.model] = new element.cast 
        },
        defineSubModelEntry(element, myarr){
            if(Array.isArray(this.modelHolder[myarr[0]])) this.modelHolder[myarr[0]] = undefined
            if(this.modelHolder[myarr[0]] === undefined ) this.modelHolder[myarr[0]] = new Object
            this.modelHolder[myarr[0]][myarr[1]] = new element.cast 
        },
        getFormComponent(element){
            let fieldsTypes = {
                'input' : 'FormFieldInput',
                'file' : 'FormFieldFile',
                'editor' : 'FormFieldEditor',
                'prices' : 'FormFieldPrices',
                'status' : 'FormFieldStatus',
                'select' : 'FormFieldSelect',
                'checkimages' : 'FormFieldCheckImages',
                'address' : 'FormFieldAddress',
                'duration' : 'FormFieldDuration',
                'countryselector' : 'FormFieldCountrySelector',
            }

            fieldsTypes = window.wappointmentExtends.filter( 'FormGeneratorFieldsTypes', fieldsTypes, {mixins: AbstractField, components:{LabelMaterial} } )

            return fieldsTypes[element.type]!== undefined ? fieldsTypes[element.type]:'FormFieldInput'
        }
    }
}
</script>
<style>
.form-wrap{
    border-radius: .8rem;
    position:relative;
}
.loading-overlay{
    position: absolute;
    background-color: rgba(255,255,255,.7);
    width: 100%;
    height: 100%;
    z-index: 2;
    border-radius: .8rem;
    top: 0rem;
    left: 0rem;
}
.fields-wrap {
      border-radius: .8rem;
}
.basic-form-wrapper {
    max-width: 700px;
}
</style>