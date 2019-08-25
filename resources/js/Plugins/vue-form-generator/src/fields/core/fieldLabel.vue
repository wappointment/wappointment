<template lang="pug">
	span(:id="getFieldID(schema)", :class="schema.fieldClasses") {{ value }}
</template>

<script>
	import abstractField from "../abstractField";

	export default {
		mixins: [ abstractField ]
	};
</script>

<style lang="sass">
	.vue-form-generator .field-label label span {
		display: block;
		width: 100%;
		margin-left: 0px;
		padding: .6rem;
		background-color: #f3f4f4;
		border-radius: .4rem;
		font-size: .9rem;
	}
</style>
