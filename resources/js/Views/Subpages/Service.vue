<template>
    <div class="container-fluid">
      <div class="reduced">
          <div v-if="noback" class="col-12">
                <div class="d-flex">
                    <h1>Service Setup</h1>
                </div>
                <p class="h6 text-muted">The service visitors will book you for</p>
          </div>
          <ErrorList :errors="errorMessages"></ErrorList>
          <form novalidate >
            <vue-form-generator ref="vfgen" :schema="schema" :model="model" :options="formOptions" @validated="onValidated" ></vue-form-generator>
          </form>
          <div class="col-12" >
              <button  class="btn btn-primary" @click="save">Save</button>
          </div>
      </div>
      
    </div>

</template>

<script>
import Vue from '../../appVue'
const FieldBsRadios = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsRadios')
const FieldBsCheckListNew = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsChecklistNew')
const fieldBSSlider = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsSlider')
const fieldBSAdressField = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsAddressField')
const fieldBSCountryField = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsCountryField')

window.noUiSlider = require('nouislider');
import "nouislider/distribute/nouislider.css";
Vue.component("field-bsslider", fieldBSSlider);
Vue.component("field-bsradios", FieldBsRadios);
Vue.component("field-bschecklistnew", FieldBsCheckListNew);
Vue.component("field-bsaddressfield", fieldBSAdressField);
Vue.component("field-bscountryfield", fieldBSCountryField);


import ServiceService from '../../Services/V1/Service' 
import Validation from '../../Modules/Validation'
import abstractView from '../Abstract'

export default {
  extends: abstractView,
  mixins: [Validation],
  props:['noback'],
  data() {
      return {
          serviceService: null,
          viewName: 'service',
          parentLoad: false,
          model: {             
            name: '',
            duration: 60,
            type: '',
            address: '',
            options: {
              countries: []
            }
          },

          originalSchema: {
            fields: [{
              type: "input",
              inputType: "text",
              label: "Service",
              model: "name",
              placeholder: "e.g.: Consultation",
              required: true,
              validator: ['string'],
              styleClasses: 'col-md-6'
            },
            {
              type: "bsslider",
              label: "Duration",
              model: "duration",
              placeholder: "Duration",
              required: true,
              default: 60,
              min: 5,
              max: 240,
              step: 5,
              int: true,
              unit: 'min',
              styleClasses: 'col-md-6'
            },
            { 
              type: "bschecklistnew",
              label: "How is it provided?",
              model: "type",
              buttonMode: true,
              required: true,
              validator: ['array'],
              values: [
                { value:'physical', name:'At a location', icon: 'map-marked-alt'},
                { value:'phone', name:'By Phone', icon: 'phone'},
                { value:'skype', name:'By Skype', icon: ['fab', 'skype']}
              ],
              styleClasses: 'col-md-12'
            }]
          },
          extraFields: {fields:[]},
          formOptions: {
            validateAfterLoad: false,
            validateAfterChanged: false,
          },
      } 
  },
  computed: {
    schema(){
      var ModifiedSchema = window.wappointmentExtends.filter('serviceSchema', this.originalSchema.fields, this.model )
      return  { fields: _.unionWith(ModifiedSchema,this.extraFields.fields) }
    }
  },

  watch: {
    model: {
      handler(newval){
        this.extraFields = {fields:[]}

        if(newval!== undefined && newval.type.indexOf('physical') != -1){
            this.extraFields.fields.push(
              {
                type: "bsaddressfield",
                label: "Address",
                model: "address",
                placeholder: "Address where the client will come for the service",
                required: true,
                max: 500,
                rows: 4,
                validator: ['string'],
                styleClasses: 'col-md-12'
              }
              
            )
        }
        if(newval!== undefined && newval.type.indexOf('phone') != -1){
            this.extraFields.fields.push(
              {
                type: "bscountryfield",
                label: "Countries you will provide phone service for",
                model: "options.countries",
                placeholder: "Countries allowed",
                required: true,
                validator: ['array'],
                styleClasses: 'col-md-12'
              }
              
            )
        }
        newval = window.wappointmentExtends.filter('serviceWatchModel', newval )

      },
      deep: true
    }
  },

  methods: {
    initMethod(){
      this.extraFields = {fields:[]}

      this.serviceService = this.$vueService(new ServiceService)
      //console.log('initMethod Service ')
      this.request(this.initValueRequest,  undefined, this.loaded)

    },
    loaded(viewData){
        this.model = viewData.data.service
        this.model.duration = parseInt(this.model.duration)
        if( ['', null, undefined].indexOf(this.model.options) !==-1 ){
          this.model.options = {
            countries: []
          }
        }
    },
    

    save() {
        this.request(this.saveServiceRequest,  undefined, this.saved)
    },
    async saveServiceRequest() {
        return await this.serviceService.call('save', this.model)
    },
    saved(){
        this.$emit('saved')
    }
  }  
}
</script>
