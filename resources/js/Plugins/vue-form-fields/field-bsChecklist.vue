<template >
	<div class="d-flex" :class="alignment">
	  
	  <div v-if="buttonMode" class="btn-group-toggle tt-below" :class="getItemSubClass(item)" data-toggle="buttons" v-for="(item, idx) in items" :key="idx" :data-tt="getItemSub(item)" >
		 
		 <label class="btn btn-secondary btn-sm mr-2" :class="{'is-checked active': isItemChecked(item)}"> 
        <span v-if="item.class" :class="item.class"></span>
		<input
		type="checkbox" :checked="isItemChecked(item)" 
		:disabled="disabled" @change="onChanged($event, item)" :name="name" :id="name+idx" >
        {{ getItemName(item) }}</label>
	  </div>
	  <div v-else class="form-check form-check-inline" v-for="(item, idx) in items" :key="idx" :class="{'is-checked': isItemChecked(item)}">
		  
        <input
		type="checkbox" :checked="isItemChecked(item)" 
		:disabled="disabled" @change="onChanged($event, item)" :name="name" :id="name+idx" >
        <label  :for="name+idx">{{ getItemName(item) }}</label>
      
	  </div>
      
    </div>

</template>

<script>
	import {isObject, isNil, clone} from "lodash";
	import {abstractField} from "vue-form-generator";
	
	export default {
		mixins: [ abstractField ],

		data() {
			return {
				comboExpanded: false,
			};
		},

		computed: {
			alignment(){
				
				if(this.schema.alignment === undefined) return ''
				return this.schema.alignment
			},
			radioMode(){
				if(this.schema.radioMode === undefined) return false
				return this.schema.radioMode
			},
			buttonMode(){
				if(this.schema.buttonMode === undefined) return false
				return this.schema.buttonMode
			},
			items() {
				let values = this.schema.values;
				if (typeof(values) == "function") {
					return values.apply(this, [this.model, this.schema]);
				} else
					return values;
			},

			selectedCount() {
				if (this.value)
					return this.value.length;

				return 0;
			},
			name(){
				return this.schema.model;
			}
		},

		methods: {

			getItemValue(item) {
				if (isObject(item)){
					if (this.schema["checklistOptions"] !== undefined && this.schema["checklistOptions"]["value"] !== undefined) {
						return item[this.schema.checklistOptions.value];
					} else {
						if (typeof item["value"] !== "undefined") {
							return item.value;
						} else {
							throw "`value` is not defined. If you want to use another key name, add a `value` property under `checklistOptions` in the schema. https://icebob.gitbooks.io/vueformgenerator/content/fields/checklist.html#checklist-field-with-object-values";
						}
					}
				} else {
					return item;
				}
			},
			getItemName(item) {
				if (isObject(item)){
					if (this.schema["checklistOptions"] !== undefined &&  this.schema["checklistOptions"]["name"] !== undefined) {
						return item[this.schema.checklistOptions.name];
					} else {
						if (item["name"] !== undefined) {
							return item.name;
						} else {
							throw "`name` is not defined. If you want to use another key name, add a `name` property under `checklistOptions` in the schema. https://icebob.gitbooks.io/vueformgenerator/content/fields/checklist.html#checklist-field-with-object-values";
						}
					}
				} else {
					return item;
				}
			},
			getItemSubClass(item){
				if (isObject(item)){
					if (this.schema["checklistOptions"] !== undefined && this.schema["checklistOptions"]["subclass"] !== undefined) {
						return item[this.schema.checklistOptions.subclass];
					} else {
						if (item["subclass"] !== undefined) {
							return item.subclass;
						} 
					}
				}
				return ''
			},
			getItemSub(item){
				if (isObject(item)){
					if (this.schema["checklistOptions"] !== undefined && this.schema["checklistOptions"]["sub"] !== undefined) {
						return item[this.schema.checklistOptions.sub];
					} else {
						if (item["sub"] !== undefined) {
							return item.sub;
						} 
					}
				}
				return false
			},

			isItemChecked(item) {
				if(this.radioMode){
					return (this.value && this.value == this.getItemValue(item));
				}else{
					return (this.value && this.value.indexOf(this.getItemValue(item)) != -1);
				}
				
			},

			onChanged(event, item) {
				if(this.radioMode){
					this.value = this.getItemValue(item)
				}else{
					if (isNil(this.value) || !Array.isArray(this.value)){
						this.value = [];
					}

					if (event.target.checked) {
						// Note: If you modify this.value array, it won't trigger the `set` in computed field
						const arr = clone(this.value);
						arr.push(this.getItemValue(item));
						this.value = arr;
					} else {
						// Note: If you modify this.value array, it won't trigger the `set` in computed field
						const arr = clone(this.value);
						arr.splice(this.value.indexOf(this.getItemValue(item)), 1);
						this.value = arr;
					}
				}
				
			},

			onExpandCombo() {
				this.comboExpanded = !this.comboExpanded;				
			}
		}
	};
</script>
<style>
button.is-checked {
	box-shadow: 0 0 0 .2rem rgba(108,117,125,.5);
}
</style>
