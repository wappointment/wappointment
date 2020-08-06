<template>
    <div>
        <div v-if="label" class="mb-2">
            {{ label}}
        </div>
        <div class="d-flex">
            <div v-for="(item, idx) in images" :key="idx" @click="onChanged(item)"  
            class="btn btn-secondary btn-cell" :class="getClassesImage(item)" :data-tt="item.sub">
                <div v-if="item.icon !== undefined">
                    <FontAwesomeIcon v-if="item.icontype===undefined" :icon="item.icon" size="lg"/>
                    <span v-if="item.icontype=='wp'" :class="'dashicons ' + getWPicon(item)"></span>
                </div>
                
                <div>{{ item.name }}</div>
            </div>
        </div>
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import {isObject, isNil, clone} from "lodash"
import { library } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkedAlt, faPhone, faCalendarCheck } from '@fortawesome/free-solid-svg-icons'
import { faSkype} from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faMapMarkedAlt, faPhone, faSkype, faCalendarCheck)

export default {
    name:'core-checkimages',
    mixins: [AbstractField],
    components: {FontAwesomeIcon},
    props:{
        images:{
            type:Array,
        },
    },
    computed: {

        radioMode(){
            if(this.definition.radioMode === undefined) return false
            return this.definition.radioMode
        },

        items() {
            let images = this.definition.images
            if (typeof(images) == "function") {
                return images.apply(this, [this.model, this.definition])
            } else
                return images
        },

        name(){
            return this.definition.model
        }
    },

    methods: {
        getWPicon(item){
            return item.icon.indexOf('dashicons-') === -1?'dashicons-'+item.icon:item.icon
        },
        getClassesImage(item){
            let classes = {}
            if(this.hasErrors) classes['is-invalid'] = true
            if(this.isItemChecked(item)) classes['selected'] = true
            if(item.subclass !== undefined) classes[item.subclass] = true
            return classes
        },
        getItemValue(item) {
            if (isObject(item)){
                if (typeof this.definition["checklistOptions"] !== "undefined" && typeof this.definition["checklistOptions"]["value"] !== "undefined") {
                    return item[this.definition.checklistOptions.value]
                } else {
                    if (typeof item["value"] !== "undefined") {
                        return item.value
                    } else {
                        throw "`value` is not defined. If you want to use another key name, add a `value` property under `checklistOptions` in the schema. https://icebob.gitbooks.io/vueformgenerator/content/fields/checklist.html#checklist-field-with-object-values";
                    }
                }
            } else {
                return item
            }
        },
        isItemChecked(item) {
            if(this.radioMode){
                return (this.updatedValue && this.updatedValue == this.getItemValue(item))
            }else{
                return (this.updatedValue && this.updatedValue.indexOf(this.getItemValue(item)) != -1)
            }
            
        },

        onChanged(item) {
            this.$emit('activated')
            if(this.radioMode){
                this.updatedValue = this.getItemValue(item)
            }else{
                if (isNil(this.updatedValue) || !Array.isArray(this.updatedValue)){
                    this.updatedValue = []
                }

                if (this.updatedValue.indexOf(this.getItemValue(item))!==-1) {
                    const arr = clone(this.updatedValue)
                    arr.splice(this.updatedValue.indexOf(this.getItemValue(item)), 1)
                    this.updatedValue = arr
                    
                } else {
                    const arr = clone(this.updatedValue)
                    arr.push(this.getItemValue(item))
                    this.updatedValue = arr
                }
            }
            
        },

    }
}
</script>
<style>
.btn-secondary.is-invalid {
    border-color: #ed7575 !important;
}
</style>