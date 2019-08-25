<template>
    <div class="container-fluid">
      <div class="reduced">
          <div class="d-flex">
                <h1>Create reminder</h1>
            </div>

          <ErrorList :errors="errorMessages"></ErrorList>

          <form novalidate >
            <vue-form-generator ref="vfgen" :schema="schema" :model="model" :options="formOptions" @validated="onValidated" ></vue-form-generator>
          </form>
          <button v-if="noback" class="btn btn-primary" @click="save">Save</button>
      </div>
      
    </div>

</template>

<script>
import Vue from '../../appVue'
const FieldBsRadios = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsRadios')
const FieldBsCheckList = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsChecklist')
const fieldBSSlider = () => import(/* webpackChunkName: "group-fields" */ '../../Plugins/vue-form-fields/field-bsSlider')

window.noUiSlider = require('nouislider');
import "nouislider/distribute/nouislider.css";
Vue.component("field-bsradios", FieldBsRadios);
Vue.component("field-bschecklist", FieldBsCheckList);
Vue.component("field-bsslider", fieldBSSlider);

import ServiceService from '../../Services/Service' 
import abstractView from '../Abstract'

export default {
  extends: abstractView,
  props:['noback'],
  data() {
      return {
          serviceService: null,
          viewName: 'service',
          model: {             
            name: '',
            duration: 60,
            type: '',
            address: '',
            phone: '',
            skype: ''
          },

          originalSchema: {
            fields: [{
              type: "input",
              inputType: "text",
              label: "Subject",
              model: "subject",
              placeholder: "e.g.: Subject",
              required: true,
              validator: ['string'],
              styleClasses: 'col-md-12'
            },
              
              {
                type: "bschecklist",
                label: "Type",
                model: "type",
                buttonMode: true,
                radioMode: true,
                required: true,
                validator: ['array'],
                values: [
                  { value:'email', name:'Email', class: 'dashicons dashicons-email-alt'},
                  { value:'sms', name:'SMS', class: 'dashicons dashicons-testimonial'},
                ],
                styleClasses: 'col-md-12'
              },
              {
                type: "textArea",
                label: "Body",
                model: "body",
                placeholder: "",
                required: true,
                max: 500,
                rows: 16,
                validator: ['string'],
                styleClasses: 'col-md-12'
              }
              
            ]
            
          },

          formOptions: {
            validateAfterLoad: false,
            validateAfterChanged: false,
          },
      } 
  },
  created(){
    this.extraFields = {fields:[]}

    this.serviceService = this.$vueService(new ServiceService)
    //console.log('refreshInitvalue start 3 ')
    this.request(this.initValueRequest,  undefined, this.loaded)

  },
  computed: {
    schema(){
      return {fields: _.unionWith(this.originalSchema.fields, this.extraFields.fields)};
    }
  },

  methods: {
    loaded(viewData){
        this.model = viewData.data.service
        this.model.duration = parseInt(this.model.duration)
    },

    save() {
        this.request(this.saveReminderRequest,  undefined, this.saved)
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
