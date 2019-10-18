<template>
    <div>
        <FormGenerator v-if="dataLoaded" :schema="schema" :data="modelHolder" 
        @submit="save" labelButton="Save">
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
  data() {
      return {
          serviceService: null,
          viewName: 'service',
          parentLoad: false,
          modelHolder: {             
            name: '',
            duration: 60,
            type: '',
            address: '',
            options: {
              countries: []
            }
          },

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
                },
                {
                    type: 'duration',
                    label: 'Duration',
                    model: 'duration',
                    cast: String,
                    default: 60,
                    min: 5,
                    max: 240,
                    step: 5,
                    int: true,
                    unit: 'min',
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
                ]
            },
            {
                type: 'address',
                label: 'Address',
                model: 'address',
                cast: String,
                conditions: [
                  { model:'type', values: ['physical'] }
                ]
            },
            {
                type: 'countryselector',
                label: 'Countries you will provide phone service for',
                model: 'options.countries',
                cast: String,
                conditions: [
                  { model:'type', values: ['phone'] }
                ]
            },

    
        ]

      } 
  },


  methods: {
    initMethod(){
      this.serviceService = this.$vueService(new ServiceService)
      this.request(this.initValueRequest,  undefined, this.loaded)

    },
    loaded(viewData){
        this.modelHolder = viewData.data.service
        this.modelHolder.duration = parseInt(this.modelHolder.duration)
        if( ['', null, undefined].indexOf(this.modelHolder.options) !==-1 ){
          this.modelHolder.options = {
            countries: []
          }
        }
        this.viewData = viewData.data
    },

    save(data) {
        this.modelHolder = data
        this.request(this.saveServiceRequest,  undefined, this.saved)
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
