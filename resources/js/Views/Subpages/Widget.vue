<template>
    <div v-if="widgetData!==null">
      <div class="d-flex my-4">
        <div v-if="isTesting">
            <span class="btn btn-lg btn-secondary selected"><span class="dashicons dashicons-edit"></span> 1 - Test it and Customize it</span>  
            <button class="btn btn-lg btn-secondary" @click="toggleTesting"><span class="dashicons dashicons-layout"></span> 2 - Integrate it in your site</button>
        </div>
        <div v-else>
            <button class="btn btn-lg btn-secondary" @click="toggleTesting"><span class="dashicons dashicons-edit"></span> 1 - Test it and Customize it</button>  
            <span class="btn btn-lg btn-secondary selected"><span class="dashicons dashicons-layout"></span> 2 - Integrate it in your site</span>
        </div>
            
      </div>  
      <div v-if="isTesting">
          <transition name="slide-fade-top">
            <BookingWidgetEditor  :bgcolor="getBgColor" :widgetFields="widgetFields" 
            :config="viewData.config" :preoptions="widgetData" :defaultSettings="widgetDefault" :frontAvailability="frontAvailability" />
          </transition>
      </div>
      <div v-else>
          <transition name="slide-fade-top">
            <WidgetInsert  :title="viewData.widget.button.title" />
          </transition>
      </div>
      
    </div>

</template>

<script>
import wizardLayout from '../abstractWizardLayout'
import BookingWidgetEditor from '../../Components/BookingWidgetEditor'
import WidgetInsert from '../../Components/WidgetInsert'
export default {
  extends: wizardLayout,
  data() {
      return {
          colors: '#ffffff',
          viewName: 'widget',
          isTesting: true
      } 
  },

  components: { BookingWidgetEditor, WidgetInsert },
  methods: {
      toggleTesting(){
          this.isTesting = ! this.isTesting
      }
  },
  computed: {

      widgetData(){
          return (this.viewData !== null && this.viewData.widget !== undefined)?  this.viewData.widget:null
      },
      getBgColor(){
          return (this.viewData !== null && this.viewData.bgcolor !== undefined)?  this.viewData.bgcolor:'#fff'
      },
      widgetFields(){
          return (this.viewData !== null && this.viewData.widgetFields !== undefined)?  this.viewData.widgetFields:null
      },
      widgetDefault(){
          return (this.viewData !== null && this.viewData.widgetDefault !== undefined)?  this.viewData.widgetDefault:null
      },
      frontAvailability(){
          return (this.viewData !== null && this.viewData.front_availability !== undefined)?  this.viewData.front_availability:null
      }
  },
}
</script>
