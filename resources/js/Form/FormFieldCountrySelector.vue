<template>
    <div>
        <div v-if="label" class="mb-2">
            {{ label}}
        </div>
        <div>
            <CountrySelector required :selected="value" @selected="selectedCountries" :hasErrors="hasErrors"></CountrySelector>
        </div>
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
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
