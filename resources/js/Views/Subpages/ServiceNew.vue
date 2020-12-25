<template>
    <div>
        <WAPFormGenerator ref="formgenerator" :buttons="buttons" :schema="schemaParsed" :data="modelHolder" 
        @submit="save" @back="$emit('back')" @ready="isReady" :errors="errorsPassed" :key="formKey" 
        labelButton="Save" v-bind="extraOptions">
        </WAPFormGenerator>
    </div>

</template>

<script>
import ServiceService from '../../Services/V1/Service'
import abstractView from '../Abstract'
export default {
  extends: abstractView,
  props:['dataPassed', 'servicesService', 'extraOptions', 'buttons'],
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
              phone_required: false,
              video: ''
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
                    label: 'Service Name',
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
                label: 'Service Delivery',
                model: 'type',
                cast: Array,
                images: [
                  { value:'zoom', name:'Video meeting', subname:'(Zoom, Google meet, ...)', icon: ['fas', 'video']},
                  { value:'physical', name:'At an address', icon: 'map-marked-alt'},
                  { value:'phone', name:'By Phone', icon: 'phone'},
                  { value:'skype', name:'By Skype', icon: ['fab', 'skype']}
                ],
                validation: ['required']
            },

            {
                type: 'checkimages',
                label: 'Video meeting provider',
                radioMode: true,
                model: 'options.video',
                cast: Array,
                images: [
                  { value:'zoom', name:'Zoom', icon: 'zoom.png', icontype: 'img', realsize: true},
                  { value:'googlemeet', name:'Google Meet', icon: 'google-meet.png', icontype: 'img', realsize: true},
                ],
                conditions: [
                  { model:'type', values: ['zoom'] }
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
                type: 'checkbox',
                label: "Clients must provide a phone number",
                model: 'options.phone_required',
                cast: Boolean,
            },
            {
                type: 'countryselector',
                label: 'Accepted countries for phone field',
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
    isReady(ready){
      this.$emit('ready', ready)
    },
    initMethod(){
      this.serviceService = this.servicesService!==undefined ? this.servicesService:this.$vueService(new ServiceService)
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
