<template >
	<div class="d-flex">
		<div v-for="(item, idx) in items" :key="idx" @click="onChanged(item)" class="btn btn-secondary btn-cell" :class="{selected: isItemChecked(item)}">
			<font-awesome-icon :icon="item.icon" size="lg"/>
			<div>{{ item.name }}</div>
		</div>
	</div>
</template>

<script>
	import {isObject, isNil, clone} from "lodash";
	import {abstractField} from "vue-form-generator";
	import { library } from '@fortawesome/fontawesome-svg-core'
	import { faMapMarkedAlt, faPhone, faCalendarCheck } from '@fortawesome/free-solid-svg-icons'
	import { faSkype} from '@fortawesome/free-brands-svg-icons'
	import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

	library.add(faMapMarkedAlt, faPhone, faSkype, faCalendarCheck)
	
	export default {
		mixins: [ abstractField ],
		components: {
			'font-awesome-icon': FontAwesomeIcon,
		}, 

		computed: {

			radioMode(){
				if(this.schema.radioMode === undefined) return false
				return this.schema.radioMode
			},

			items() {
				let values = this.schema.values;
				if (typeof(values) == "function") {
					return values.apply(this, [this.model, this.schema]);
				} else
					return values;
			},

			name(){
				return this.schema.model;
			}
		},

		methods: {

			getItemValue(item) {
				if (isObject(item)){
					if (typeof this.schema["checklistOptions"] !== "undefined" && typeof this.schema["checklistOptions"]["value"] !== "undefined") {
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
			isItemChecked(item) {
				if(this.radioMode){
					return (this.value && this.value == this.getItemValue(item));
				}else{
					return (this.value && this.value.indexOf(this.getItemValue(item)) != -1);
				}
				
			},

			onChanged(item) {
				if(this.radioMode){
					this.value = this.getItemValue(item)
				}else{
					if (isNil(this.value) || !Array.isArray(this.value)){
						this.value = [];
					}
	
					if (this.value.indexOf(this.getItemValue(item))!==-1) {
						const arr = clone(this.value);
						arr.splice(this.value.indexOf(this.getItemValue(item)), 1);
						this.value = arr;
						
					} else {
						const arr = clone(this.value);
						arr.push(this.getItemValue(item));
						this.value = arr;
					}
				}
				
			},

		}
	};
</script>
<style>
.btn-cell{
    text-align: center;
    padding: .4rem;
}
</style>
