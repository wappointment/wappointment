<template>
  <div>
    <div v-if="!isActive" @click="makeActive(true)" class="clickable" :class="typeClass">
        <span class="dashicons dashicons-admin-site-alt3"></span> 
        <span v-if="value">{{ displaySelectedElement }}</span> 
        <span class="dashicons dashicons-arrow-down-alt2" ></span>
    </div>
    <div v-click-outside="makeInactive" class="elementsContainer" v-else>
        <div class="d-flex align-items-center input-group-sm">
            <LabelMaterial >
                <input ref="searchfield" type="text" v-model="search" class="form-control" :placeholder="placeHolderLabel"> 
            </LabelMaterial>
            <span class="dashicons dashicons-arrow-up-alt2 clickable" @click="makeInactive"></span>
        </div>
        <div v-if="hasGroup" class="dropElements">
            <div v-if="search!=''">
                <div v-for="element in filteredGroupElements" @click="selectElement(element)" 
                class="btn btn-sm m-0 btn-block btn-light clickable" :class="isSelected(element)">
                    <small>{{ displayElement(element) }}</small>
                </div>
            </div>
            <div v-else v-for="(sub_elements, parent) in elements" >
                <div @click="toggleGroup(parent)" class="btn btn-sm m-0 btn-block clickable text-left" :class="[ groupShow == parent ? 'btn-secondary':'btn-light']">
                    {{ displayGroupLabel(parent) }} - <small class="text-muted">{{ groupShow == parent ?  'hide' : countElements(sub_elements) + ' timezones' }}</small>
                </div>
                <div v-if="groupShow == parent" v-for="(element, index) in sub_elements" @click="selectElement(element)" 
                class="btn btn-sm m-0 btn-block btn-light clickable" :class="isSelected(element)" >
                    <small>{{ displayGroupElement(element, index) }} </small>
                </div>
            </div>
        </div>
        <div v-else class="dropElements">
            <div v-for="element in filteredElements" @click="selectElement(element)" 
            class="btn btn-sm m-0 btn-block btn-light clickable" :class="isSelected(element)">
                <small>{{ displayElement(element) }}</small>
            </div>
        </div>
        
    </div>
  </div>
</template>

<script>
import SearchDropdown from './SearchDropdown'
export default {
    extends: SearchDropdown,
    props: {
        displayElement: {
            type: Function,
            default: (element)=>{
                return element.name +  ' [' + element.hours + ':' + element.minutes + ']'
            }
        },
        hasGroup: {
            type: Boolean,
            default: false
        },
        hideGroupElements: {
            type: Boolean,
            default: true
        },
        displayGroupElement: {
            type: Function,
            default: (element, index) => {
                return index + ' [' + element.hours + ':' + element.minutes + ']'
            },
        },
        displayGroupLabel: {
            type: Function,
            default: (indexGroup) => {
                return indexGroup
            },
        },
        typeClass: {
            type: String,
            default: 'small text-muted'
        }
  },
  data () {
    return {
      groupShow: undefined,
      groupElements: [],
    }
  },
  created(){
    if(this.hasGroup){
        for (var continent in this.elements) {
            // skip loop if the property is from prototype
            if(!this.elements.hasOwnProperty(continent)) continue
            let sub_elements = this.elements[continent]
            for(var city in sub_elements){
                if(!sub_elements.hasOwnProperty(city)) continue
                let element = sub_elements[city]
                this.groupElements.push(element)
                if(this.isSelectedValue(continent, city)) {
                    this.selectedElement = element
                }
            }
        }
    }
  },


  computed: {
    filteredGroupElements(){
        return this.groupElements.filter(this.elementMatchSearch)
    },
    displaySelectedElement(){
        return this.selectedElement!== undefined ? this.selectedElement.name + ' [' + this.selectedElement.hours + ':' + this.selectedElement.minutes + ']':''
    }
  },
  methods: {
    isSelectedValue(continent, city){
        let compareTo = (this.value == 'UTC') ? continent:continent+'/'+city

        return compareTo == this.value
    },
    toggleGroup(parent) {
        this.groupShow = (this.groupShow == parent) ? undefined:parent;
    },

  }
}
</script>

