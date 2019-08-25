<template >
    <CountrySelector required :selected="value" @selected="selectedCountries"></CountrySelector>
</template>

<script>
    import { abstractField } from "vue-form-generator";
    import CountrySelector from '../../Components/CountrySelector'
	export default {
        mixins: [ abstractField ],
        components: {CountrySelector},
        methods: {
            selectedCountries(countries){
                this.value = countries
            }
        }
    };
</script>