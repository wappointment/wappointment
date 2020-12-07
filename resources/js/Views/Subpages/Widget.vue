<template>
    <div v-if="widgetData!==null">
      <div class="d-flex mt-4 bwe-tabs" v-if="!wizard">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" :class="{active:isTesting && !editing}" href="javascript:;" @click="showPreview">
                    <span class="dashicons dashicons-visibility"></span> 1 - Preview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{active:isTesting && editing}" href="javascript:;" @click="showTesting">
                    <span class="dashicons dashicons-edit"></span> 2 - Customize
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{active:!isTesting}" href="javascript:;" @click="showIntegrate">
                    <span class="dashicons dashicons-layout"></span> 3 - Insert
                </a>
            </li>
        </ul>
            
      </div>  
      <div v-if="isTesting">
          <transition name="slide-fade-top">
            <BookingWidgetEditor  :bgcolor="getBgColor" :editingMode="editing" :widgetFields="widgetFields" :shortcodeParams="params"
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
  props:{
      wizard: {
          type: Boolean,
          default: false
      },
      params:{
          type:Object,
          default: undefined
      }
  },
  data() {
      return {
          colors: '#ffffff',
          viewName: 'widget',
          isTesting: true,
          editing:false,
      } 
  },

  components: { BookingWidgetEditor, WidgetInsert },
  methods: {
      showPreview(){
          this.showTesting()
          this.editing = false
      },
      showTesting(){
          this.editing = true
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