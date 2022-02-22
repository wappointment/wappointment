<template>
    <StaffModalWrapper nosave :user="user">
        <div id="buttons-block">
            <ul class="nav nav-tabs row px-4" id="myTabICS" role="tablist" >
                <li v-for="(tab, key) in tabs" class="nav-item">
                    <span class="nav-link" :class="{'active' : isActive(key)}" @click="changeTab(key)">{{ tab.label }}</span>
                </li>
            </ul>
            <component v-if="activeComp" :is="activeComp" :calendar="user" />
        </div>
    </StaffModalWrapper>
</template>

<script>
import StaffModalWrapper from './StaffModalWrapper'
import StaffCalendarsExternalImport from './StaffCalendarsExternalImport'
import StaffCalendarsExternalExport from './StaffCalendarsExternalExport'
export default {
    components: window.wappointmentExtends.filter('icsExternalComponents', { StaffModalWrapper, StaffCalendarsExternalImport, StaffCalendarsExternalExport}),
    props: ['user'],
    data() {
        return {
            activeTab:'import',
            tabs: null,
        } 
    },
    created(){
        this.tabs = {
            import:{
                label: this.get_i18n('cals_ext_import', 'settings'),
                component: 'StaffCalendarsExternalImport'
            },
            export:{
                label: this.get_i18n('cals_ext_export', 'settings'),
                component: 'StaffCalendarsExternalExport'
            },
        }
    },


    computed: {
        activeComp(){
            return this.tabs[this.activeTab].component
        },
    },
    methods: {

        isActive(key){
            return key == this.activeTab
        },
        
        changeTab(key){
            this.activeTab = key
        },  
 
    }  
}
</script>