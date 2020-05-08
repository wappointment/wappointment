<template>
    <div>
        <WAPFormGenerator ref="formgenerator" :schema="schemaParsed" :data="modelHolder" 
        @submit="save" @back="$emit('back')" :errors="errorsPassed" :key="formKey" labelButton="Save" v-bind="extraOptions">
        </WAPFormGenerator>
    </div>

</template>

<script>
import ServiceService from '../../Services/V1/Service'
import abstractView from '../Abstract'
export default {
  extends: abstractView,
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
              countries: [],
              phone_required: false
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
                type: 'checkbox',
                label: 'Ask for phone',
                model: 'options.phone_required',
                cast: Boolean,
                validation: ['required'],
                conditions: [
                  { model:'type', notin:true ,values: ['phone'] }
                ],
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
                label: 'Phone numbers accepted countries',
                model: 'options.countries',
                cast: Array,
                conditions: [
                  {
                    type: 'or',
                    conda: { model:'type', values: ['phone'] },
                    condb: { model:'options.phone_required', values: [true] }
                  }
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
        this.request(this.saveServiceRequest,  undefined, undefined, false, this.saved, this.failedValidation)
    },
    failedValidation(e){
      if(e.response!== undefined && e.response.data!== undefined 
      && e.response.data.data!== undefined && 
      e.response.data.data.errors!== undefined &&
      e.response.data.data.errors.validations !== undefined){
        this.errors = e.response.data.data.errors.validations
        this.formKey = 'form' + ((new Date()).getTime())
      }
      this.serviceError(e)
    },
    async saveServiceRequest() {
        return await this.serviceService.call('save', this.modelHolder)
    },
    saved(e){
        this.$emit('saved')
        this.serviceSuccess(e)
    }
  }  
}
</script>
