<template>
    <div v-if="widgetData!==null">
      <div class="d-flex mt-4 ml-4 bwe-tabs" v-if="!wizard">
        <ul class="nav nav-tabs">
            <li class="nav-item" v-for="(step ,index) in stepsAllowed">
                <a class="nav-link" :class="{active: shownStep(step) }" href="javascript:;" @click="showStep(step)">
                    <span class="dashicons" :class="getClassTabIcon(step)"></span> {{ index + 1}} - {{step}}
                </a>
            </li>
        </ul>
            
      </div>  
      <div v-if="isWidgetShown">
          <transition name="slide-fade-top">
            <BookingWidgetEditor  :bgcolor="getBgColor" :editingMode="editing" :widgetFields="widgetFields" :shortcodeParams="params"
            :config="viewData.config" :preoptions="widgetData" :defaultSettings="widgetDefault" :frontAvailability="frontAvailability" :steps="viewData.steps" />
          </transition>
      </div>
      <div v-else>
          <transition name="slide-fade-top">
            <WidgetInsert  :title="viewData.widget.button.title" :page_id="viewData.booking_page_id"
            :page_link="viewData.booking_page_url" />
          </transition>
      </div>
      
    </div>

</template>

<script>
import wizardLayout from '../Views/abstractWizardLayout'
import BookingWidgetEditor from '../Components/BookingWidgetEditor'
import WidgetInsert from '../Components/WidgetInsert'
export default {
    name: 'WidgetShow',
  extends: wizardLayout,
  props:{
      wizard: {
          type: Boolean,
          default: false
      },
      params:{
          type:Object,
          default: undefined
      },
      stepsAllowed:{
          type:Array,
          default: ()=> ['Preview','Customize','Insert']
      }
  },
  data() {
      return {
          colors: '#ffffff',
          viewName: 'widget',
          isWidgetShown: true,
          editing:false,
          showingStep: ''
      } 
  },

  components: { BookingWidgetEditor, WidgetInsert },
  created(){
      this.showStep(this.stepsAllowed[0])
  },
  methods: {
      shownStep(step){
          return this.showingStep == step
      },
      showStep(step){
          this.showingStep = step
          this.editing = ['Preview', 'Insert'].indexOf(step) === -1 
          this.isWidgetShown = ['Preview', 'Customize'].indexOf(step) !== -1
      },
      getClassTabIcon(step){
          switch (step) {
              case 'Preview':
                  return 'dashicons-visibility'
              case 'Customize':
                  return 'dashicons-edit'
              case 'Insert':
                  return 'dashicons-layout'
              default:
                  break;
          }
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