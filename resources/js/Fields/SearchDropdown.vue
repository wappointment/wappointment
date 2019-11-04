<template>
  <div>
    <div v-if="!isActive" @click.prevent.stop="makeActive(true)" 
    class="clickable small text-muted container-values d-flex justify-content-between align-items-center form-control">
        <div v-if="icon" class="dashicons" :class="icon"></div> 
        <div v-if="emptyValue">{{ placeHolderLabel }}</div>
        <div v-else>
            <span  v-if="hasMulti" class="d-flex flex-wrap">
                <span class="value-card" v-if="value.length > 0"  v-for="val in value">
                    <span class="label">{{ displayElementFunc(getElement(val)) }}</span>
                    <span class="close" @click.prevent.stop="selectElement(getElement(val))"></span>
                </span>
            </span>
            <span class="mr-2" v-else>
                {{ displayElementFunc(getElement(value)) }}
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
            <div class="dropElements">
                <div v-if="filteredElements.length > 0" v-for="elementLoop in filteredElements" @click="selectElement(elementLoop)" 
                class="btn btn-sm m-0 btn-block clickable" :class="[ isSelected(elementLoop)? 'btn-outline-primary':'btn-light']">
                    <small>{{ displayElementFunc(elementLoop) }}</small>
                </div>
                <div v-else>
                    <div v-if="search">No results for that search</div>
                </div>
            </div>
        </div>
        
    </div>
  </div>
</template>

<script>
import LabelMaterial from './LabelMaterial'
import ClickOutside from 'vue-click-outside'
import DotKey from '../Modules/DotKey'
export default {
    directives: { ClickOutside },
    components:{ LabelMaterial },
    mixins: [DotKey],
    props: {
        elements: {
            type: [Object, Array],
        }, 
        value: {
            type: [String, Number, Array],
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
        }
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
        return (this.hasMulti && this.value.length == 0) || (this.hasMulti === false && typeof this.value == 'string' && this.value != '') 
    }
  },
  methods: {
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
    },
    makeInactive(){
        this.makeActive(false)
        this.groupShow = undefined
        this.search = ''
        console.log('makeInactive')
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
            this.selectedElement = element
            this.$emit('input', element[this.idKey], element)
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

.value-card {
    background-color: #ececec;
    padding: .1rem 1rem .1rem .2rem;
    font-size: .8rem;
    border-radius: .2rem;
    position: relative;
    margin: 0 .6rem .3rem 0;
    border: 1px solid #dfdfdf;
}
.value-card:hover {
    background-color: #f4f4f4;
}

.close::after {
    transform: translateX(15px) rotate(-45deg);
}
.close::before, .close::after {
    content: ' ';
    position: absolute;
    background-color: #b5b1b1;
}
.close::before {
    transform: translateX(15px) rotate(45deg);
}
.value-card .close:hover::before, .value-card .close:hover::after {
    background-color: #575656;
    width: 2px;
}


.value-card .close::before, .value-card .close::after  {
    height: 10px;
    width: 1px;
    top: 3px;
    right: 24px;
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

</style>
