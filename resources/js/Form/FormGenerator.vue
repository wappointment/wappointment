<template>
    <form @submit.prevent="submit" class="form-wrap" :class="classWrapper">
        <div v-if="!formIsReady" class="loading-overlay d-flex align-items-center">
            <WLoader></WLoader>
        </div>
        <div class="fields-wrap" >
            <template v-for="(element, keydi) in schema">
                <div v-if="element.type == 'row'"  class="d-flex justify-content-between flex-wrap flex-sm-wrap">
                    <div class="form-group"  v-for="(subelement, skeydi) in element.fields" :style="getStyle(subelement)">
                        <component :is="getFormComponent(subelement)" :value="getModelValue(subelement)"
                        @loaded="loadedField(keydi, skeydi)"
                        v-bind="allowBind(subelement)" @change="changedValue" />
                    </div>
                </div>
                <div v-else class="form-group"  :style="getStyle(element)">
                    <component :is="getFormComponent(element)" :value="getModelValue(element)"
                    @loaded="loadedField(keydi)"
                    v-bind="allowBind(element)" @change="changedValue" />
                </div>
            </template>
            <slot></slot>
            <div>
                <button class="btn btn-primary" :class="{'btn-disabled':!isValid}" :disabled="!isValid" type="button" @click.prevent="submit">{{ buttonLabel }}</button>
            </div>
        </div>
    </form>
</template>

<script>
import FormFieldInput from './FormFieldInput'
import FormFieldFile from './FormFieldFile'
import FormFieldEditor from './FormFieldEditor'
import FormFieldPrices from './FormFieldPrices'
import FormFieldStatus from './FormFieldStatus'
import FormFieldSelect from './FormFieldSelect'
import DotKey from '../Modules/DotKey'
export default {
    mixins: [DotKey],
    props: {
        schema: {
            type: Array,
            default: []
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
        },
        ready: {
            type: Boolean,
            default: false
        },
    },
    components: {FormFieldInput, FormFieldEditor, FormFieldPrices, FormFieldStatus, FormFieldFile, FormFieldSelect},
    data: () => ({
        modelHolder: {},
        isValid: false,
        fieldsStatus:{},
        formIsReady: false
    }),
    created(){
        this.modelHolder = this.creating === false ? Object.assign({},this.data):{}
        this.verifyModel()
    },

    computed: {
        buttonLabel(){
            return this.labelButton !== undefined ? this.labelButton :(this.creating === false ? this.modify:this.create)
        },
    },
    methods: {
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
                            if(this.schema[key].fields[i].loaded === false) return
                        }
                    }else{
                        if(this.schema[key].loaded === false) return
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
             return styleString;
         },
         runValidation(){
             for (const key in this.modelHolder) {
                 if (this.modelHolder.hasOwnProperty(key)) {
                     if(Array.isArray(this.modelHolder[key]) && this.modelHolder[key].length == 0) return this.isValid = false
                     if(typeof this.modelHolder[key] && this.modelHolder[key] == '') return this.isValid = false
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
            this.runValidation()
        },
        submit(){
            if(!this.isValid) return false
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
            switch (element.type) {
                case 'input':
                    return 'FormFieldInput'
                    break;
                case 'file':
                    return 'FormFieldFile'
                    break;
                case 'editor':
                    return 'FormFieldEditor'
                    break;
                case 'prices':
                    return 'FormFieldPrices'
                    break;
                case 'status':
                    return 'FormFieldStatus'
                    break;
                case 'select':
                    return 'FormFieldSelect'
                    break;
                default:
                    return 'FormFieldInput'
            }
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
</style>