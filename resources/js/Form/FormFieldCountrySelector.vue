<template>
    <div>
        <label v-if="label" class="mb-2">
            {{ label}}
        </label>
        <div>
            <CountrySelector required :selected="value" @selected="selectedCountries" :hasErrors="hasErrors"
            @activated="$emit('activated')"></CountrySelector>
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
    name:'core-countryselector',
    mixins: [AbstractField],
    components: {CountrySelector},
    methods: {
        selectedCountries(countries){
            this.updatedValue = countries
        }
    }
}
</script>
