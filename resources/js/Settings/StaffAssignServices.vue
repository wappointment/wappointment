<template>
    <StaffModalWrapper :user="user" nosave>
        <SearchDropdown v-model="value" hasMulti ph="Pick services provided by staff" :elements="services" 
                idKey="id" labelSearchKey="name" />
        <button class="btn btn-primary mt-2" @click="$emit('save', value)">{{get_i18n('save', 'common') }}</button>
    </StaffModalWrapper>
</template>

<script>
import SearchDropdown from '../Fields/SearchDropdown'
import StaffModalWrapper from './StaffModalWrapper'
export default {
    props: ['current', 'services', 'user'],
    components:{ StaffModalWrapper, SearchDropdown},
    data() {
        return {
            value: [],
        } 
    },
    created(){
        this.value = [].concat(this.current)
    },

}
</script>