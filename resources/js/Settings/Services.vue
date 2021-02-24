<template>
    <div >
        <BreadCrumbs v-if="crumbs.length>0" :crumbs="crumbs" @click="goTo"/>
        <router-view></router-view>
    </div>
</template>

<script>
import hasBreadcrumbs from '../Mixins/hasBreadcrumbs'

export default {
    //components: {ServicesDelivery, ServicesManage},
    mixins: [hasBreadcrumbs],
    props: {
        crumb:{
            type:Boolean,
            default:false
        },
        element: {
            type: Object,
            default:null
        },
    },
    data: () => ({
        currentView: 'listing',
        elementPassed: null,
    }),
    created(){
        if(this.subview !== ''){
            this.currentView = this.subview
        }
        
        this.elementPassed = this.element
    },
    computed: {
        serviceDeliveries(){
            return this.currentView == 'delivery'
        },
    },
    methods: {
        changeView(newView){
            if(newView == 'delivery'){
                this.showDeliveries()
            }else{
                if(newView != 'listing'){
                    this.showService()
                }else{
                    this.showListing()
                }
            }
        },

        showDeliveries(){
            if(this.$route.name == 'services'){
                this.$router.push({name: 'modalities'})
            }
            this.updateCrumb([
                    { target: 'showListing', label: 'Services', subview: 'listing' },
                    { target: 'showDeliveries', label: 'Delivery Modalities' , disabled: true},
                ], 'delivery')
            this.currentView = 'delivery'
            
        },
        showService(){
            if(this.elements.db_required){
                return this.$WapModal().notifyError('Run database updates first')
            }
            if(this.elements.length > 2){
                return this.requiresAddon('services', '3 services max allowed')
            }

            if(this.crumb){
                this.updateCrumb([
                    { target: 'showListing', label: 'Services', subview: 'listing' },
                    { target: 'showService', label: 'Add' , disabled:true},
                ], 'add')
            }else{
                this.currentView = 'add'
                this.elementPassed = null
            }
            
        },

        showListing(){
            if(this.$route.name == 'modalities'){
                this.$router.push({name: 'services'})
            }
            this.crumbs = []  
            this.currentView = 'listing'
        },

    }
}   
</script>