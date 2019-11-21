<template>
    <div class="container-fluid">
      <div v-if="dataLoaded">
          <h1>Edit Cancel/Reschedule page</h1>
          
          <div class="booking-widget-editor d-flex">
            <div class="widget-wraper p-3 mr-3">
              <div class="clear pt-2">
                <h1>{{ isCancelPage ? viewData.widget.cancel.page_title: viewData.widget.reschedule.page_title}}</h1>
                <Front v-if="displayed" :options="widgetData" classEl="wappointment_page" :step="stepPassed"></Front>
              </div>
            </div>
          
            <div>
              <div class="row no-gutters">
                  <transition name="fade">
                      <div class="col-12 p-0">
                          <div class="d-flex step-skip "> 
                              <button v-for="(stepObj,idx) in editionsSteps" class="btn btn-secondary btn-xs mr-2" 
                              :class="{'selected': (step == stepObj.key)}" @click="setStep(stepObj.key)" >{{ stepObj.label }}</button>
                          </div>
                      </div>
                  </transition>
              </div>
              <transition name="slide-fade-top">
                <div v-if="isCancelPage">
                    <InputPh v-model="viewData.widget.cancel.page_title" :ph="widgetDefault.cancel.page_title" allowReset/>
                    <InputPh v-model="viewData.widget.cancel.title" :ph="widgetDefault.cancel.title" allowReset/>
                    <InputPh v-model="viewData.widget.cancel.button" :ph="widgetDefault.cancel.button" allowReset/>
                    
                    <hr>
                    <InputPh v-model="viewData.widget.cancel.confirmation" :ph="widgetDefault.cancel.confirmation" allowReset/>
                    <InputPh v-model="viewData.widget.cancel.confirm" :ph="widgetDefault.cancel.confirm" allowReset/>

                    <InputPh v-model="viewData.widget.cancel.confirmed" :ph="widgetDefault.cancel.confirmed" allowReset/>
                    <InputPh v-model="viewData.widget.cancel.toolate" :ph="widgetDefault.cancel.toolate" allowReset/>
                </div>
              </transition>
              <transition name="slide-fade-top">
                <div v-if="!isCancelPage">
                    <InputPh v-model="viewData.widget.reschedule.page_title" :ph="widgetDefault.reschedule.page_title" allowReset/>
                    <InputPh v-model="viewData.widget.reschedule.title" :ph="widgetDefault.reschedule.title" allowReset/>
                    <InputPh v-model="viewData.widget.reschedule.button" :ph="widgetDefault.reschedule.button" allowReset/>
                    <hr>
                    <InputPh v-model="viewData.widget.reschedule.toolate" :ph="widgetDefault.reschedule.toolate" allowReset/>
                </div>
              </transition>
            
              <button class="btn btn-primary" @click="saveChanges">Save</button>
            </div>
          </div>
      </div>
      
    </div>

</template>

<script>
import SettingsSave from '../../Modules/SettingsSave'
import abstractView from '../Abstract';
import InputPh from '../../Fields/InputLabelMaterial'
import Front from '../../Front'
export default {
  extends: abstractView,
  mixins: [SettingsSave],
  components: {
        InputPh, Front
    },
  data() {
      return {
          viewName: 'widgetcancel',
          step: 'cancel-event',
          stepPassed: 'cancel-event',
          displayed: true,
          options: null,
          editionsSteps: [
            {
                key: 'cancel-event',
                label: 'Cancel Page'
            },
            {
                key: 'reschedule-event',
                label: 'Reschedule Page'
            },
        ],
      } 
  },
  computed: {
    isCancelPage(){
      return 'cancel-event' == this.step
    },
    widgetData(){
      if(this.viewData !== null && this.viewData.widget !== undefined){
          this.viewData.widget.demoData = Object.assign({},this.options.demoData)
          this.viewData.widget.demoData.appointmentData.staff = this.viewData.staff
          this.viewData.widget.demoData.view = this.step
          return this.viewData.widget
      }
        return null
    },
    getBgColor(){
        return (this.viewData !== null && this.viewData.bgcolor !== undefined)?  this.viewData.bgcolor:'#fff'
    },
    widgetFields(){
        return (this.viewData !== null && this.viewData.widgetFields !== undefined)?  this.viewData.widgetFields:null
    },
    widgetDefault(){
        return (this.viewData !== null && this.viewData.widgetDefault !== undefined)?  this.viewData.widgetDefault:null
    }
  },
  created(){
    this.options = {}
    this.options.demoData = {
          appointmentData: {
            "appointment":
          {
            "start_at":this.preselection(),
            "end_at":this.preselection()+(3600),
            "type":"phone",
            "canRescheduleUntil":this.preselection()-(3600*3),
            "canCancelUntil":this.preselection()-(3600*3)
          },
          "client":{
            "name":"John Patrick",
            "email":"jp@email"+this.preselection()+".com",
            "options":{
              "phone":"+33 6 51 02 48 81",
              "skype":"j.patrick",
              "tz":"Europe\/Paris"
              }
            },
            "service":{
              "name":"Consultation",
              "duration":60,
              "type":["skype"],
              "address":"",
              "options":{
                "countries":["FR"]
                }},
                "staff":{
                  "id":1,
                  "a":"http:\/\/0.gravatar.com\/avatar\/3982abebd0a6ec871c022e502cc016f1?s=40&d=mm&r=g",
                  "n":"ben caub",
                  "t":"Europe\/Madrid"
                  },
                  "date_format":"F j, Y","time_format":"g:i a","date_time_union":" - "
                  }
      }
  },
  methods: {
    /*------------------------*/
    preselection(){
            return (new Date()).getTime()
        },
    setStep(step){
        this.displayed = false
        this.step = step
        this.stepPassed = step

        setTimeout(this.displayTimeout, 100);
        
    },
    displayTimeout(step){
        this.displayed = true
    },
    /*------------------------*/
    saveChanges(){
        this.$WapModal()
        .request(this.settingSaveRequest({
            key:'widget',
            val: this.viewData.widget
        }))
        .then(this.savedWidgetSuccess)
        .catch(this.savedWidgetFailed)
    },

    successRequest(result) {
      //console.log('successRequest',result.data)
    },

    // this method is called after response wrong from request make by execute method
    failedRequest(error) {
      //console.log('failedRequest',error)
    },

    savedWidgetSuccess(s){
        this.canSave = false
        this.serviceSuccess(s)
    },  

    savedWidgetFailed(e){
    },

  }  
}
</script>

