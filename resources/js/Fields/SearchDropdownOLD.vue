<template>
  <div>
    <div v-if="!isActive" @click="makeActive(true)" class="clickable small text-muted">
        <span class="dashicons" :class="icon"></span> 
        <span v-if="value">{{ displaySelectedElement }}</span> 
        <span class="dashicons dashicons-arrow-down-alt2" ></span>
    </div>
    <div v-else  v-click-outside="makeInactive" class="elementsContainer" >
        <div class="d-flex align-items-center input-group-sm">
            <LabelMaterial >
                <input ref="searchfield" type="text" v-model="search" class="form-control" :placeholder="placeHolderLabel"> 
            </LabelMaterial>
            <span class="dashicons dashicons-arrow-up-alt2 clickable" @click="makeInactive"></span>
        </div>
        <div class="dropElements">
            <div v-for="element in filteredElements" @click="selectElement(element)" 
            class="btn btn-sm m-0 btn-block clickable" :class="isSelected(element)">
                <small>{{ displayElement(element) }}</small>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import LabelMaterial from './LabelMaterial'
import ClickOutside from 'vue-click-outside'
export default {
    directives: { ClickOutside },
    components:{ LabelMaterial },
    props: {
        elements: {
            type: [Object, Array],
        }, 
        value: {
            type: [String, Number],
        }, 
        icon: {
            type: String,
            default: 'dashicons-admin-site-alt3'
        },
        idKey:{
            type: String,
            default: 'id'
        },
        searchKey:{
            type: String,
            default: 'name'
        },
        displayElement: {
            type: Function,
            default: (element)=>{
                return element.name
            }
        },
        ph: {
            type: String,
            default:''
        }
    },
    data () {
        return {
            search: '',
            active: false,
            selectedElement: undefined
        }
    },

    updated(){
        if(this.isActive){
            this.focusSearchField()
        }
    },

  computed: {

    isActive(){
        return this.active
    },
    filteredElements(){
        return this.elements.filter(this.elementMatchSearch)
    },
    displaySelectedElement(){
        return this.selectedElement!== undefined ? this.selectedElement.name : ''
    },
    placeHolderLabel(){
        return this.ph =='' ? this.displaySelectedElement:this.ph
    },
  },
  methods: {
    
    focusSearchField(){
        this.$refs.searchfield.focus()
    },
    countElements(elements){
        if(typeof elements === 'object'){
             return Object.keys(elements).length
        }
        return elements.length
    },
    makeActive(s=true){
        this.active = s
    },
    makeInactive(){
        this.makeActive(false)
        this.groupShow = undefined
        this.search = ''
    },
    selectElement(element){
        this.$emit('input', element[this.idKey], element)
        this.makeInactive()
        this.selectedElement = element
    },
    elementMatchSearch(element){
        return element[this.searchKey].toLowerCase().indexOf(this.search.toLowerCase()) !== -1
    },
    isSelected(element){
        return this.value == element[this.idKey] ? 'btn-outline-primary':'btn-light'
    }
  }
}
</script>

<style>
.clickable {
    cursor: pointer;
}

.elementsContainer{
    position: relative;
}
.elementsContainer .dropElements {
    position: absolute;
    background-color: #fff;
    padding: 10px;
    width: 100%;
    max-height: 150px;
    overflow: auto;
    box-shadow: 0 .01rem .6rem 0 rgba(0,0,0,.1);
    border-radius: 0 .4rem;
    z-index: 5;
}

</style>
