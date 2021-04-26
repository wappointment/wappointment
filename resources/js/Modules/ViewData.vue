<script>

import ViewsDataService from '../Services/V1/ViewsData'
export default {
    data: () => ({
        parentLoad:true,
        serviceViewData: null,
        viewName: null,
        viewData: null
    }),

    created(){
        this.serviceViewData = this.$vueService(new ViewsDataService)
        this.initMethod()
    },

    methods: {

        initMethod(){
            if(this.parentLoad === true) {
                this.refreshInitValue()
            }
        },

        refreshInitValue(){
            if(this.viewName !== null){
                this.viewData = null
                this.request(this.initValueRequest, false, undefined, false, this.loaded)
            } 
        },

        loaded(viewData){
            this.viewData = viewData.data
            this.$emit('fullyLoaded')
        },

        async initValueRequest() {
            if(this.viewName !== null) {
                return await this.serviceViewData.call('get', {append: '/'+this.viewName})
            }
        },
    }
}
</script>