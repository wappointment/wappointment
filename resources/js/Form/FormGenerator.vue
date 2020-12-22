<template>
    <form @submit.prevent.stop="submitTrigger" class="form-wrapppo" :class="classWrapper" :autocomplete="autocomplete?'on':'off'">
        <div v-if="!formIsReady" class="loading-overlay d-flex align-items-center">
            <WLoader></WLoader>
        </div>
        <div v-if="reload" class="fields-wrap" >
            <template v-for="(element, keydi) in schema">
                <div v-if="element.type == 'row'"  :class="getRowClass(element)">
                    <div class="form-group"  v-for="(subelement, skeydi) in element.fields" 
                    :class="getRowEachClass(element,subelement)" :style="getStyle(subelement)">
                        <div :class="{'d-none': inVisibles(subelement)}">
                            <component :is="getFormComponent(subelement)" :value="getModelValue(subelement)" 
                            :parentErrors="errorsData" :parentModel="modelHolder" :formGen="true"
                            @loaded="loadedField(keydi, skeydi)"
                            v-bind="allowBind(subelement)" @change="changedValue" @activated="wasActive(subelement)" 
                            :definition="subelement"  :errors="getErrors(subelement)" />
                        </div>
                    </div>
                </div>
                <div v-else class="form-group" :class="{'d-none': inVisibles(element)}"  :style="getStyle(element)" >
                    <div :class="{'d-none': inVisibles(element)}">
                        <component :is="getFormComponent(element)" :value="getModelValue(element)" 
                        :parentErrors="errorsData" :parentModel="modelHolder" :formGen="true"
                    @loaded="loadedField(keydi)" :errors="getErrors(element)"
                    v-bind="allowBind(element)" @change="changedValue" @activated="wasActive(element)" :definition="element"/>
                    </div>
                    
                </div>
            </template>
            <slot></slot>
            <div v-if="buttons">
                <button v-if="backbutton" class="btn btn-secondary" type="button" @click.prevent="$emit('back')">{{ backbuttonLabel }}</button>
                <button class="btn btn-primary" :class="{'btn-disabled':!isValid}" :disabled="!isValid" type="button" @click.prevent.stop="submitTrigger">{{ buttonLabel }}</button>
            </div>
        </div>
    </form>
</template>

<script>
import AbstractField from './AbstractField'
import RequestMaker from '../Modules/RequestMaker'
import eventsBus from '../eventsBus'
import CoreFieldss from './CoreFields'
import DotKey from '../Modules/DotKey'
let CoreFields = CoreFieldss
export default {
    mixins: [DotKey],
    props: {
        schema: {
            type: Array,
            default: []
        }, 
        errors: {
            type: Object,
            default: () =>{
                return {}
            }
        },
        data: {
            type: Object,
            default: undefined
        }, 
        create: {
            type: String,
            default: 'Save'
        }, 
        modify: {
            type: String,
            default: 'Save'
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
        buttons: {
            type: Boolean,
            default: true
        },
        backbutton: {
            type: Boolean,
            default: false
        },
        backbuttonLabel: {
            type: String,
        },
        autocomplete: {
            type: Boolean,
            default:true
        }
    },
    components: CoreFields.components,
    data: () => ({
        modelHolder: null,
        isValid: false,
        fieldsStatus:{},
        formIsReady: false,
        pendingValidation: false,
        submitted: false,
        errorsData: {},
        visibles: [],
        childrensHidden: [],
        activated_fields: [],
        reload: true,
        replaceRefresh: false,
    }),
    created(){
        this.refresh()
        if(this.modelHolder.id !== undefined){
            this.isValid = true
        }
    },

    computed: {
        submittedErrors(){
            return Object.keys(this.errors).length > 0
        },
        hasErrors(){
            return Object.keys(this.errorsData).length > 0
        },
        buttonLabel(){
            return this.labelButton !== undefined ? this.labelButton :(this.creating === false ? this.modify:this.create)
        },
    },
    methods: {
        refresh(){
            if(this.submittedErrors){
                this.errorsData = Object.assign({}, this.errors)
                this.submitted = true
            }
            
            if(this.modelHolder === null || this.replaceRefresh === true){
                this.modelHolder = this.creating === false ? Object.assign({},this.data):{}
            }
            this.verifyModel()
        },
        reRender(replaceRefresh = false){
            this.formIsReady = false
            this.reload = false
            this.replaceRefresh = replaceRefresh
            setTimeout(this.reRenderDelay, 100)
        },
        reRenderDelay(){
            this.formIsReady = true
            this.reload = true
            this.refresh()
        },
        back(){
            return this.$emit('back')
        },
        getFormComponent(element){
            CoreFields = window.wappointmentExtends.filter('FormAddonsFields', CoreFields)

            let fieldsType = CoreFields.inputTypes
            let elementType = element.type.indexOf('-') === -1 ? 'core-'+element.type:element.type

            
            return fieldsType[elementType]!== undefined ? fieldsType[elementType]:'FormFieldInput'
        },
        inVisibles(element){
            return element.model !== undefined ? this.visibles.indexOf(element.model) === -1: true
        },
        getErrors(element){
            if(element.model !== undefined && this.hasErrors && (this.submitted || this.activated_fields.indexOf(element.model) !== -1)){
                if(this.errorsData[element.model] !== undefined ) {
                    return this.errorsData[element.model]
                }else{
                    let errorsStack = {}
                    for (const key in this.errorsData) {
                        if (this.errorsData.hasOwnProperty(key)) {
                            if(key.indexOf(element.model)!==-1){
                                errorsStack[key]=this.errorsData[key]
                            }
                        }
                    }
                    if(Object.keys(errorsStack).length > 0) return errorsStack
                }
            }
            return false
        },
        getRowClass(row){
            if(row.conditions !== undefined && !this.isVisible(row)) {
                this.hideChildrenFields(row)
                return 'd-none'
            }
            return row.class!== undefined ? row.class: 'd-flex justify-content-between flex-wrap flex-sm-wrap'
        },
        hideChildrenFields(row){
            for (let i = 0; i < row.fields.length; i++) {
                const eldef = row.fields[i]
                if(eldef !== undefined){
                    this.removeFromVisible(eldef.model)
                }
                if( this.childrensHidden.indexOf(eldef.model)=== -1) {
                    this.childrensHidden.push(eldef.model)
                }
                
            } 
        },
        removeFromVisible(model){
            const idx = this.visibles.indexOf(model)
            if(idx !== -1){
                this.visibles.splice(idx, 1)
            }
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
                            let field = row.fields[j]
                            if(field.conditions === undefined && row.conditions !== undefined){
                                field.conditions = Object.assign({},row.conditions)
                            }
                            return field
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
            if(this.childrensHidden.indexOf(element.model) !== -1){
                this.removeFromVisible(element.model)
                return false
            } 
            if(element.conditions !== undefined){
                let conditions_failed = false
                for (let i = 0; i < element.conditions.length; i++) {
                    const condition = element.conditions[i]
                    if(condition.type !== undefined && condition.type == 'or'){
                        conditions_failed = !(this.passedCondition(condition.conda) || this.passedCondition(condition.condb))
                    }else{
                        conditions_failed = !this.passedCondition(condition)
                    }

                    if(conditions_failed) return false
                    
                }
                
            }
            if(element.model!==undefined && this.visibles.indexOf(element.model) === -1) this.visibles.push(element.model)
            return true
        },

        passedCondition(condition){
            const elDefinition = this.getElementDefinition(condition.model)
            let modelValue = undefined
            let failed = false
            if(elDefinition !== undefined){
                modelValue = this.getModelValue(elDefinition)
            }else{
                if(this.modelHolder[condition.model] !== undefined){
                    modelValue = this.modelHolder[condition.model]
                }else{
                    failed = true
                }
            }
            if(condition.isempty !== undefined){
                failed = !this.isEmptyValue(modelValue)
            }else{
                if(condition.notempty !== undefined){
                    failed = this.isEmptyValue(modelValue)
                }else{
                    if(['array','object'].indexOf(typeof modelValue) !== -1){
                        if(condition.notin !== undefined){
                            failed = this.atLeastOne(modelValue, condition)
                        }else{
                            failed = !this.atLeastOne(modelValue, condition)
                        }
                    }else{
                        if(condition.notin !== undefined){
                            if(condition.values.indexOf(modelValue) !== -1){
                                failed = true
                            }
                        }else{
                            if(condition.values.indexOf(modelValue) === -1){
                                failed = true
                            }
                        }
                    }
                }
            }

            return !failed
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
                            if(this.isVisible(this.schema[key].fields[i]) && this.schema[key].fields[i].loaded === false){
                                 return
                            }
                        }
                    }else{
                        if(this.isVisible(this.schema[key]) && this.schema[key].loaded === false){
                             return
                        }
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
                 case 'undefined':
                     return true
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
             let elDefinition = this.getElementDefinition(keyEl)
             let hasError = false
             
             if(elDefinition!== undefined && this.isVisible(elDefinition)) {
                if(elDefinition.validation !== undefined){
                    for (let i = 0; i < elDefinition.validation.length; i++) {
                        const validationType = elDefinition.validation[i]
                        if(this.validateType(validationType, value, elDefinition)){
                            this.setError(keyEl,validationType)
                            hasError = true
                            //return false
                        }else{
                            this.clearError(keyEl,validationType)
                        }
                    }
                }
                
            }
            return !hasError
         },
         validateType(type, value, elDefinition){
             switch (type) {
                 case 'required':
                     return this.isEmptyValue(value)
                 case 'required2':
                     return this.isEmptyValue(value)
                 case 'number':
                     return this.isEmptyValue(value)
                     return parseInt(value) >= elDefinition.min && parseInt(value) <= elDefinition.max
             }
         },
         runValidation(){

             //this.errorsData = {}
             //console.log('runvaldiation mholder', this.modelHolder)
             const modelKeys = Object.keys(this.modelHolder)
             this.isValid = true
             for (let i = 0; i < modelKeys.length; i++) {
                 const key = modelKeys[i]
                 
                 if(key == 'options'){
                     for (let j = 0; j < Object.keys(this.modelHolder[key]).length; j++) {
                         const subkey = Object.keys(this.modelHolder[key])[j]
                         if(!this.validateElement(key, subkey)){
                            this.isValid = false
                        }
                     }

                }else{
                    if(!this.validateElement(key)){
                        this.isValid = false
                    }
                }
             }
             this.$emit('ready',this.isValid)
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
            
            let ignoreKeys = ['cast', 'type', 'required_options_props']

            let objectProp = Object.keys(element)
            .filter((e) => ignoreKeys.indexOf(e) === -1)
            .reduce((obj, key) => {
                obj[key] = element[key]
                return obj
            }, {})

            if(element.required_options_props !== undefined) {
                for (const key in element.required_options_props) {
                    if (element.required_options_props.hasOwnProperty(key) && this.modelHolder.options!== undefined && this.modelHolder.options[element.required_options_props[key]] !== undefined) {
                        objectProp[key] = this.modelHolder.options[element.required_options_props[key]]
                    }
                }
            }
            if(element.bus !== undefined) objectProp.eventsBus = eventsBus
            
            return objectProp
        },
        wasActive(element){
            if(element.model === undefined) return true
            if(this.activated_fields.indexOf(element.model) === -1) this.activated_fields.push(element.model)
        },
        changedValue(newVal, model, type){
            this.setModelValue(newVal, model, type)
            if(this.pendingValidation === false){
                this.pendingValidation = setTimeout(this.runningValidation, 200)
            }
        },
        runningValidation(){
            this.visibles = []
            this.childrensHidden = []
            this.checkRowsVisibility()
            this.runValidation()
            this.testFormReady()
            this.pendingValidation = false
        },

        checkRowsVisibility(){
            for (const key in this.schema) {
                if (this.schema.hasOwnProperty(key)) {
                    const row = this.schema[key]
                    if(row.type=='row'){
                        this.getRowClass(row)
                    }
                }
            }
            
        },
        submitTrigger(forceRequest = false){
            this.submitted = true
            this.errorsData = {}
            if(forceRequest=== false && !this.isValid) return false
            this.$emit('submit', Object.assign({}, this.modelHolder), this.creating)
        },

        setModelValue(newVal, model, type){
             if(model.indexOf('.')!== -1){
                let myarr = model.split('.')
                this.modelHolder[myarr[0]][myarr[1]] = newVal
            }else{
                this.modelHolder[model] = newVal
            }
            this.$emit('changedValue',this.modelHolder,  model, newVal, type)
        },
        getModelValue(element){
            if(element.model === undefined){
                return ''
            }

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
        getDefaultCasted(element){
            let defaultVal = element.default !== undefined ? element.default:''
            
            return defaultVal 
        },
        defineModelEntry(element){
            let castType = element.cast
            let defaultVal = element.default !== undefined ? element.default:''
            this.modelHolder[element.model] = this.getDefaultCasted(element)
        },
        defineSubModelEntry(element, myarr){
            if(Array.isArray(this.modelHolder[myarr[0]])) this.modelHolder[myarr[0]] = undefined
            if(this.modelHolder[myarr[0]] === undefined ) this.modelHolder[myarr[0]] = new Object
            this.modelHolder[myarr[0]][myarr[1]] = this.getDefaultCasted(element)
        },
        
    }
}
</script>
<style>
.form-wrapppo{
    border-radius: .8rem;
    position:relative;
}
.form-wrapppo p{
    font-size: 1rem;
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