<template>
    <div>
        <WAPFormGenerator ref="fgaddservice" :buttons="buttons" :schema="schemaParsed()" :data="modelHolder" 
        @submit="save" @back="$emit('back')" @ready="isReady" :errors="errorsPassed" :key="formKey" 
        labelButton="Save" v-bind="extraOptions" :minimal="minimal" />
    </div>
</template>

<script>
import ServiceService from '../../Services/V1/Service'
import abstractView from '../Abstract'
import CanFormatPrice from '../../Mixins/CanFormatPrice'
export default {
  extends: abstractView,
  mixins:[CanFormatPrice],
  props:['dataPassed', 'servicesService', 'extraOptions', 'buttons', 'minimal', 'params'],
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
              class: 'd-flex flex-wrap flex-sm-nowrap align-items-top fieldthumb',
              classEach: 'mr-2',
              fields: [
                {
                  type: 'opt-imageselect',
                  model: 'options.icon',
                  cast: String,
                },
                {
                    type: 'input',
                    label: 'Service',
                    model: 'name',
                    cast: String,
                    class: 'input-360'
                },
              ]
            },
            {
                type: 'checkbox',
                label: "Sell service",
                model: "options.woo_sellable",
                cast: Boolean,
                default: false,
            },
            {
              type: 'address',
              label: 'Short Description',
              model: 'options.description',
              address: false,
              cast: String,
            },
            {
                type: 'opt-multidurations',
                label: 'Service duration(s)',
                model: 'options.durations',
                cast: String,
                class: 'w-100',
                default: [{ duration:60}],
                min: 5,
                max: 240,
                step: 5,
                int: true,
                unit: 'min',
                required_options_props:{
                  'woo_sellable':'woo_sellable'
                },
               
            },
            {
                type: 'opt-modality',
                label: 'Delivery modality',
                model: 'locations_id',
                cast: Array,
                checklistOptions: { value:'id'}
            },
            {
              type: 'opt-customfields',
              label: 'When client select this service, display the following fields',
              model: 'options.fields',
              bus: true,
              listenBus: true,
              cast: Array,
              checklistOptions: { value:'namekey'}
            },
            {
              type: 'countryselector',
              label: 'Phone field accepted countries',
              model: 'options.countries',
              cast: Array,
              conditions: [
                { model:'options.fields', values: ['phone'] },
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

    getParamsPassed(){
      let params = this.params
      if(this.dataPassed.options.slots !== undefined){
        params.isGroup = true
      }
      return params;
    },

    errorsPassed(){
      return this.errors
    }
  },
  methods: {
    schemaParsed(){
      return window.wappointmentExtends.filter('ServiceFormSchema', this.addPriceField(this.schema), this.getParamsPassed)
    },
    generatePriceField(){
      return {
          type: 'input',
          label: "Price" +' (' +this.currencyText+') ',
          model: "options.woo_price",
          cast: String,
          conditions: [
            { model:'options.woo_sellable', values: [true] }
          ],
          liveParse: function(val) {
              
              val = val.replace(',','.')
              val = val.replace(/[^0-9.]/g,'')
              let floatPos = val.indexOf('.')
              if( floatPos !== -1 && floatPos < val.length -2){
                val = Number.parseFloat(val).toFixed(2)
              }
            
            return val
          }
      }
    },
    addPriceField(schema){
      for (var i = 0; i < schema.length; i++) {
          if(schema[i]['type'] == 'opt-multidurations'){
            schema[i]['woo_price_field'] = this.generatePriceField()
          }
      }
      return schema
    },
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
      this.$refs.fgaddservice.reRender()
      this.serviceError(e)
    },
    async saveServiceRequest() {
        return await this.serviceService.call('save', this.modelHolder)
    },
    saved(e){
        this.$emit('saved', e)
    }
  }  
}
</script>
