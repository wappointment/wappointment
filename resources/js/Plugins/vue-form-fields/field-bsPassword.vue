<template>
	<div class="wrapper">
		<input :id="getFieldID(schema)" :name="getFieldID(schema)"  
		required="required" class="form-control" :type="getTypePasswordField" v-model="value">
		<span class="dashicons" :class="{'dashicons-hidden': !inputPassword, 'dashicons-visibility': inputPassword}" @click="inputPassword=!inputPassword"></span>
	</div> 
</template>

<script>
	import { abstractField } from "vue-form-generator"
   export default {
		 mixins: [ abstractField ],
		 computed: {
			 getTypePasswordField() {
				 return this.inputPassword ? 'password': 'text'
			 }
		 },
		 data(){
			 return {
				 inputPassword: true
			 }
		 }

   };
</script>
<style>
.wrapper {
	position: relative;
}
.wrapper .dashicons {
	position: absolute;
	right: 8px;
	top: 8px;
	font-size: 22px;
	color: #5f5f5f;
	opacity:0;
  	transition: opacity .3s ease-in-out;
	cursor: pointer;
}
.wrapper:hover .dashicons {
	opacity:1;
}

.wrapper .dashicons:hover {
	color: #333333;
}


</style>
