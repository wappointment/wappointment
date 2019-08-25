<template>
    <div class="d-flex justify-content-between">     
				<PhoneInput 
					:phone="value"
					:autoCountry="true"
					@onInput="onInput" 
				></PhoneInput>
    </div>
</template>

<script>
	import PhoneInput from '../../Components/BookingForm/PhoneInput'
	import { abstractField } from "vue-form-generator"
   export default {
				 mixins: [ abstractField ],
				 components: {
							PhoneInput,
					}, 
         methods: {
			onInput( result ) {

				if(result.isValid===false) {

					/* this.$parent.errors.push(
						{
							error: 'This phone is not valid',
							field: {
								type: "bsphone",
								label: "Phone number",
								model: "phone",
								placeholder: "Phone number",
								required: true,
								styleClasses: 'col-md-6',
							}
						}
					) */
					this.$emit('notValid')
				}
			},
		}
   };
</script>

<style>
 .phone-field .dropdown ul{
	 z-index:10;
 }
</style>
