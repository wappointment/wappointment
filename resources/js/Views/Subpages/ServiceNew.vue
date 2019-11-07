<template>
    <div>
        <FormGenerator ref="formgenerator" :schema="schemaParsed" :data="modelHolder" 
        @submit="save" @back="$emit('back')" :errors="errorsPassed" :key="formKey" labelButton="Save" v-bind="extraOptions">
        </FormGenerator>
    </div>

</template>

<script>
import FormGenerator from '../../Form/FormGenerator'
import ServiceService from '../../Services/V1/Service'
import abstractView from '../Abstract'
export default {
  extends: abstractView,
  components:{FormGenerator},
  props:['dataPassed', 'servicesService', 'extraOptions'],
  data() {
      return {
          
          serviceService: null,
          modelHolder: {             
            name: '',
            duration: 60,
            type: '',
            address: '',
            options: {
              countries: []
            }
          },
          errors:{},
          formKey: 'form',
          schema: [
            {
              type: 'row',
              class: 'd-flex flex-wrap flex-sm-nowrap align-items-center',
              classEach: 'mr-2',
              fields: [
                {
                    type: 'input',
                    label: 'Service',
                    model: 'name',
                    cast: String,
                    styles: {'max-width':'200px'},
                    validation: ['required']
                },
                {
                    type: 'duration',
                    label: 'Duration',
                    model: 'duration',
                    cast: String,
                    class: 'w-100',
                    default: 60,
                    min: 5,
                    max: 240,
                    step: 5,
                    int: true,
                    unit: 'min',
                    validation: ['required']
                },
              ]
            },
            {
                type: 'checkimages',
                label: 'How is it provided?',
                model: 'type',
                cast: Array,
                images: [
                  { value:'physical', name:'At a location', icon: 'map-marked-alt'},
                  { value:'phone', name:'By Phone', icon: 'phone'},
                  { value:'skype', name:'By Skype', icon: ['fab', 'skype']}
                ],
                validation: ['required']
            },
            {
                type: 'address',
                label: 'Address',
                model: 'address',
                cast: String,
                conditions: [
                  { model:'type', values: ['physical'] }
                ],
                validation: ['required']
            },
            {
                type: 'countryselector',
                label: 'Countries you will provide phone service for',
                model: 'options.countries',
                cast: Array,
                conditions: [
                  { model:'type', values: ['phone'] }
                ],
                validation: ['required']
            },

        ]

      } 
  },
  created(){
    if(this.dataPassed!== undefined){
       this.modelHolder = Object.assign({}, this.dataPassed)
    }
    
  },
  computed: {

    schemaParsed(){
      return  window.wappointmentExtends.filter('serviceFormSchema', this.schema, this.modelHolder )
    },

    errorsPassed(){
      return this.errors
    }
  },
  methods: {
    initMethod(){
      if(this.servicesService!==undefined){
        this.serviceService = this.servicesService
      }else{
        this.serviceService = this.$vueService(new ServiceService)
      }
      

    },

    saveExternal(){
      this.$refs.formgenerator.submitTrigger(true)
    },
    save(data) {
        this.modelHolder = data
        this.request(this.saveServiceRequest,  undefined, this.saved, false, false, this.failedValidation)
    },
    failedValidation(e){
      if(e.response!== undefined && e.response.data!== undefined 
      && e.response.data.data!== undefined && 
      e.response.data.data.errors!== undefined &&
      e.response.data.data.errors.validations !== undefined){
        this.errors = e.response.data.data.errors.validations
        this.formKey = 'form' + ((new Date()).getTime())
      }
    },
    async saveServiceRequest() {
        return await this.serviceService.call('save', this.modelHolder)
    },
    saved(){
        this.$emit('saved')
    }
  }  
}
</script>
