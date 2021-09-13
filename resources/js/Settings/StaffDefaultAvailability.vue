<template>
    <div>
        <p class="h4">Set the default availability that will be used for new staff</p>
        <RegularAvailability :initValue="getRegav" :minimal="true" :viewData="defaultSettings"
             @updatedDays="updatedRA" />
        <div class="save-floating-button button-absolute" v-if="regavUpdated">
            <button class="btn btn-primary" @click="saveDefaultRegav">Save</button>
        </div>
    </div>
</template>

<script>
import SettingsSave from '../Modules/SettingsSave'
import RegularAvailability from '../RegularAvailability/RegularAvailability'
export default {
    mixins: [ SettingsSave],
    components: {RegularAvailability},
    props: ['defaultSettings'],

    computed:{
        getRegav(){
            return this.defaultSettings.regav
        }
    },
    data() {
        return {
            regavUpdated: false,
        } 
    },

    methods: {
        updatedRA(regavUpdated){
            this.regavUpdated = regavUpdated
        },
        saveDefaultRegav(){
            this.settingSave('regavDefault', this.regavUpdated)
        }
    }  
}
</script>
<style>
.save-floating-button.button-absolute{
    position: absolute;
}
</style>