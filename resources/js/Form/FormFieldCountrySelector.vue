<template>
    <div>
        <div v-if="label" class="mb-2">
            {{ label}}
        </div>
        <div>
            <CountrySelector required :selected="value" @selected="selectedCountries" :hasErrors="hasErrors"></CountrySelector>
        </div>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import CountrySelector from '../Components/CountrySelector'
export default {
    mixins: [AbstractField],
    components: {CountrySelector},
    methods: {
        selectedCountries(countries){
            this.updatedValue = countries
        }
    }
}
</script>
