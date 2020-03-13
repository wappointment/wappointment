<template>
    <div v-if="widgetData!==null">
      <div class="d-flex mt-4">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" :class="{active:isTesting}" href="javascript:;" @click="showTesting">
                    <span class="dashicons dashicons-edit"></span> 1 - Customize it
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{active:!isTesting}" href="javascript:;" @click="showIntegrate">
                    <span class="dashicons dashicons-layout"></span> 2 - Integrate it
                </a>
            </li>
        </ul>
            
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
      showTesting(){
          this.isTesting = true
      },
      showIntegrate(){
          this.isTesting = false
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