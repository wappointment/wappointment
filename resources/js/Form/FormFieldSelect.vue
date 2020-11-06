<template>
    <div>
        <SearchDropdown v-model="updatedValue" :ph="label" :elements="elements" :extraClass="getExtraClass"
             :idKey="idKey" :labelSearchKey="labelSearchKey" :flexWrap="flexWrap" :hasMulti="multi" :horizontal="horizontal"></SearchDropdown>
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import RequestMaker from '../Modules/RequestMaker'
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    name: 'core-select',
    mixins: [AbstractField, RequestMaker],
    components: {SearchDropdown},
    props: {
        labelSearchKey: {
            type: String,
        },
        idKey: {
            type: String,
        },
        elements: {
            type: Array,
        },
        flexWrap: {
            type:Boolean,
            default:false
        },
        multi: {
            type: Boolean,
            default: false
        },
        horizontal: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        getExtraClass(){
            return this.hasErrors ? 'is-invalid':''
        }
    }
}
</script>

