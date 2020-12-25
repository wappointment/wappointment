<template>
    <div>
        <label v-if="label" class="mb-2">
            {{ label}}
        </label>
        <div class="d-flex">
            <div v-for="(item, idx) in images" :key="idx" @click="onChanged(item)"  role="button"
            class="btn btn-secondary btn-cell d-flex align-items-center" :class="getClassesImage(item)" :data-tt="item.sub">
                <span class="dashicons" :class="[isItemChecked(item) ? 'dashicons-yes-alt text-primary':'dashicons-marker']"></span>
                <div>
                    <div v-if="item.icon !== undefined">
                        <WapImage v-if="item.icontype===undefined" :faIcon="item.icon" size="md" />
                        <span v-if="item.icontype=='wicon'" :class="'wicon ' + item.icon"></span>
                        <span v-if="item.icontype=='wp'" :class="'dashicons ' + getWPicon(item)"></span>
                        <img v-if="item.icontype=='img'" :class="[item.realsize ? '':'img-height']" :src="resourcesUrl+item.icon" :alt="item.alt" />
                    </div>
                    
                    <div>{{ item.name }}</div>
                    <div class="small" v-if="item.subname !== undefined">{{ item.subname }}</div>
                </div>
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

export default {
    name:'core-checkimages',
    mixins: [AbstractField],
    props:{
        images:{
            type:Array,
        },
    },
    computed: {
        resourcesUrl(){
            return window.apiWappointment.resourcesUrl+'images/'
        },
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
.btn-secondary.btn-cell img{
    filter: grayscale(1);
}

.btn-secondary.btn-cell.selected img,
.btn-secondary.btn-cell:hover img{
    filter: grayscale(0);
}
.wicon{
    font-family: dashicons;
    display: inline-block;
    line-height: 1;
    font-weight: 400;
    font-style: normal;
    speak: never;
    text-decoration: inherit;
    text-transform: none;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    width: 20px;
    height: 20px;
    font-size: 20px;
    vertical-align: top;
    text-align: center;
}
.wicon.wordpress-alt::before {
    content: "\f324";
}
.wicon.admin-settings::before {
    content:"\f108";
}

.wicon.edit::before {
    content: "\f464";
}
.wicon.trash::before {
    content:"\f182";
}
.wicon.undo::before {
    content:"\f171";
}

.wicon.plus-alt::before {
    content:"\f502";
}
.img-height{
    height:40px;
}
</style>