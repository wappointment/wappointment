<template>
    <div class="container-fluid">
      <ServiceModulable :dataPassed="model" 
      :servicesService="servicesService" @saved="$emit('saved')"/>
    </div>
</template>

<script>
import WappoServiceService from '../Services/V1/Services'
import abstractView from '../Views/Abstract'
import ServiceModulable from '../Views/Subpages/ServiceNew'
export default {
  extends: abstractView,
  components:{ServiceModulable},
    props: {
        subview: {
            type: String,
            default:''
        },
        element: {
            type: Object,
            default:null
        },
    },
    data: () => ({
        servicesService: null,
    }),
    created(){
        this.servicesService = this.$vueService(new WappoServiceService)
        if(this.element !== null) {
            this.model = Object.assign({}, this.element)
            this.model.locations_id = this.model.locations.map(el => el.id)
            delete this.model.locations
        }
        
        if(this.model.options.fields === undefined){
            this.model.options.fields = []
        }

        if(this.model.options.fields.length == 0){
            this.model.options.fields.push('email')
            this.model.options.fields.push('name')
        }
        
    },

    methods: {
        initMethod(){
        },
        loaded(viewData){
        },
    }  
}
</script>
<style >
.wappointment-wrap textarea.form-control {
    min-width:420px;
}
.wappointment-wrap .input-360 input {
    min-width:360px;
}


</style>