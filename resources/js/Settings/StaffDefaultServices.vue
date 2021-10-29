<template>
    <div>
        <p class="h4">Set the default services that will be assigned to new staff</p>
        <div>
            <label class="form-check-label" for="Assign all services available to new staff">
                <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="assignAll" id="allow-cancel">
                    Assign all services 
                </div>
            </label>
        </div>
        <SearchDropdown v-if="assignAll !== true" v-model="defaultServices" hasMulti ph="Pick default services staff" :elements="services" 
                idKey="id" labelSearchKey="name" />
        <button class="btn btn-primary mt-2" @click="saveDefaultServices">Save</button>
    </div>
</template>

<script>
import SettingsSave from '../Modules/SettingsSave'
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    mixins: [ SettingsSave],
    components: {SearchDropdown},
    props: ['defaultSettings', 'services'],
    data() {
        return {
            assignAll:true,
            defaultServices: [],
        } 
    },
    created(){
        this.assignAll = this.defaultSettings === true 
        this.defaultServices = this.assignAll !== true? [].concat(this.defaultSettings):[].concat(this.services.map( e => e.id))
    },
    methods:{
        saveDefaultServices(){
            this.settingSave('servicesDefault', this.assignAll?true:this.defaultServices)
        }
    }
}
</script>