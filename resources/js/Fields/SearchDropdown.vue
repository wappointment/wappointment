<template>
  <div>
    <div v-if="!isActive" @click.prevent.stop="makeActive(true)" 
    class="clickable small text-muted container-values d-flex justify-content-between align-items-center form-control label-wrapper active">
        <label>{{ ph }}</label>
        <div v-if="icon" class="dashicons" :class="icon"></div> 
        <div v-if="emptyValue">{{ placeHolderLabel }}</div>
        <div v-else class="elementsContainer">
            <span v-if="hasMulti" class="d-flex flex-wrap">
                <ValueCard v-if="value.length > 0" v-for="val in value" :key="val"
                        :value="val" @discard="discardElement">{{ displayElementFunc(getElement(val)) }}</ValueCard>
            </span>
            <span v-else>
                <span v-if="[undefined,false].indexOf(value) !== -1"></span>
                <ValueCard v-else :key="value" :canDiscard="false"
                        :value="value">{{ displayElementFunc(getElement(value)) }}</ValueCard>
            </span>
        
        </div>
        <div :class="arrowDownClass" ></div>
    </div>
    <div v-else >
        <div v-click-outside="makeInactive" class="elementsContainer">
            <div class="d-flex align-items-center input-group-sm justify-content-between align-items-center">
                <LabelMaterial >
                    <input ref="searchfield" type="text" v-model="search" class="form-control" :placeholder="placeHolderLabel"> 
                </LabelMaterial>
                <span :class="arrowUpClass" @click="makeInactive"></span>
            </div>
            <div class="dropElements d-flex flex-wrap">
                <ValueCard v-if="filteredElements.length > 0" v-for="elementLoop in filteredElements" :class="{'clickable':true,'unselected':!isSelected(elementLoop)}" :key="value" :canDiscard="false" @click="selectElement(elementLoop)"
                            :value="value">{{ displayElementFunc(elementLoop) }}</ValueCard>
                <div v-else>
                    <div v-if="search">No results for that search</div>
                </div>
            </div>
        </div>
        
    </div>
  </div>
</template>

<script>
import ValueCard from './ValueCard'
import LabelMaterial from './LabelMaterial'
import ClickOutside from 'vue-click-outside'
import DotKey from '../Modules/DotKey'
export default {
    directives: { ClickOutside },
    components:{ LabelMaterial, ValueCard },
    mixins: [DotKey],
    props: {
        elements: {
            type: [Object, Array],
        }, 
        value: {
        }, 
        icon: {
            type: String,
        },
        idKey:{
            type: String,
            default: 'id'
        },
        labelSearchKey:{
            type: String,
            default: 'name'
        },
        labelKey:{
            type: String,
        },
        searchKey:{
            type: String,
        },
        displayElement: {
            type: Function,
        },
        ph: {
            type: String,
            default:''
        },
        hasMulti: {
            type: Boolean,
            default: false
        },
        arrowDownClass: {
            type: String,
            default: 'arrow-down'
        },
        arrowUpClass: {
            type: String,
            default: 'arrow-up mx-2 clickable'
        },
    },
    data () {
        return {
            search: '',
            active: false,
            selectedElement: undefined,
            keySearch: '',
            keyLabel: '',
        }
    },
    created(){
        let convertedLabelSK = this.dotKeyCheck(this.labelSearchKey.toLowerCase())
        if(this.searchKey === undefined) this.keySearch = this.searchKey === undefined ? convertedLabelSK:this.dotKeyCheck(this.searchKey.toLowerCase())
        if(this.labelKey === undefined) this.keyLabel = this.labelKey === undefined ? convertedLabelSK:this.dotKeyCheck(this.labelKey.toLowerCase())
        if(this.displayElement !== undefined) this.displayElementFunc = this.displayElement
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
        let funcCB = this.dotGetKeyOrSubKeyElement
        return this.elements.filter((e) => {
            return funcCB(e, this.keySearch).toLowerCase().indexOf(this.search) !== -1
        })
    },
    placeHolderLabel(){
        return this.ph =='' ? this.getLabelElement(this.selectedElement):this.ph
    },
    emptyValue(){
        return (this.hasMulti && this.value.length == 0) || (this.hasMulti === false && typeof this.value == 'string' && this.value == '') 
    }
  },
  methods: {
    discardElement(val){
        return this.selectElement(this.getElement(val))
    },
    getElement(value){
        let idKey = this.idKey
        return this.elements.find((e) => e[idKey] == value)
    },
    
    displayElementFunc(element){
        return element !== undefined ? this.getLabelElement(element): 'Unknown'
    },
    
    getLabelElement(element){
        return this.dotGetKeyOrSubKeyElement(element, this.keyLabel)
    },
    focusSearchField(){
        this.$refs.searchfield.focus()
    },
    countElements(elements){
        return typeof elements === 'object' ? Object.keys(elements).length : elements.length
    },
    makeActive(s=true){
        this.active = s
        this.$emit('activeOrNot',s)
    },
    makeInactive(){
        this.makeActive(false)
        this.groupShow = undefined
        this.search = ''
        
    },
    selectElement(element){
        if(this.hasMulti){
            let idKey = this.idKey
            let selectedVal = element[idKey]
            let newValues = [].concat(this.value)

            if(this.isSelected(element)){
                newValues = newValues.filter((e) => e != selectedVal)
            }else{
                newValues.push(selectedVal)
            }

            this.$emit('input', newValues)
            //this.makeInactive()
        }else{
            this.selectedElement = this.selectedElement == element[this.idKey]? undefined: element[this.idKey]
            this.$emit('input', this.selectedElement, element)
            this.makeInactive()
        }
    },
    elementMatchSearch(element){
        return element[this.keySearch].toLowerCase().indexOf(this.search.toLowerCase()) !== -1
    },
    isSelected(element){
        let testValuesArray = Array.isArray(this.value) ? this.value:[this.value]
        return testValuesArray.indexOf(element[this.idKey]) !== -1
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

.container-values.form-control{
    height: auto;
}

.arrow-up{
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 5px 5px 5px;
    border-color: transparent transparent #dbdbdb transparent;
}
.arrow-down{
    width: 0;
    height: 0;
    border-style: dashed;
    border-width: 5px 5px 0 5px;
    border-color: #dbdbdb transparent transparent transparent;
}
.clickable.container-values.label-wrapper{
    padding-top: 18px;
}
.elementsContainer .value-card {
    margin: .4rem .2rem;
}
.elementsContainer .value-card.unselected {
    background: #f9f9f9;
    color: #a69b9b;
    border-color: #f4f3f3;
}

</style>
