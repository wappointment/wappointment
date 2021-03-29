<template>
    <div >
        <div class="d-flex">
            <button @click="manageServices" class="btn btn-outline-primary btn my-2">Manage services</button>
            <button @click="managePackages" class="btn btn-outline-primary btn my-2">Sell packages</button>
        </div>
        <component :is="getComponent" />
    </div>
</template>

<script>
import RequiresAddon from '../Mixins/RequiresAddon'
import ServicesManage from './ServicesManage'

export default {
    mixins:[RequiresAddon],
    components:{
        ServicesManage,
    },
    data: () => ({
        currentView: 'services',
    }),
    computed:{
        getComponent(){
            return this.currentView == 'services' ? 'ServicesManage':'ServicesManage'
        }
    },
    methods: {
        manageServices(){
            this.currentView = 'services'
        },
        managePackages(){
            this.requiresAddon('packages')
        },
    }
}   
</script>
