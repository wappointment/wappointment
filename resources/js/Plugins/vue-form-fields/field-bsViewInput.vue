<template>
	<div class="wrapper">
		<input :id="getFieldID(schema)" :name="getFieldID(schema)" 
		required="required" class="form-control" >
	</div> 
</template>

<script>
	import { abstractField } from "vue-form-generator"
   export default {
		 mixins: [ abstractField ],
		 data(){
			 return {
			 }
		 }
   };
</script>
