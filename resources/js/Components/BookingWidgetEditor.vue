<template>
    <div>
        <div class="d-flex booking-widget-editor" >
            
            <div class="widget-wraper p-3 mr-3"  :style="getStyleThemeBg" >
                <div class="clear pt-2" >
                    <div v-if="!editingMode">
                        <Front :options="preoptions"  classEl="wappointment_widget" ></Front>
                    </div>
                    <div v-if="editingMode && frontAvailability!==undefined" class="d-flex flex-wrap preview">
                        <div  v-for="(stepObj,idx) in editionsSteps" class="bordered" :class="orderedClass(stepObj,idx)" :data-tt="stepObj.key==step?labelActiveStep:false">
                            <div  class="overflowhidden" :class="'step-'+stepObj.key">
                                <FrontDemo :options="options"  classEl="wappointment_widget" :step="stepObj.key" ></FrontDemo>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="editor-bar" class="align-self-start">
                <div class="mb-2">
                    <button class="btn btn-secondary mr-2"  @click="toggleEditingMode">
                        <span v-if="!editingMode"><span class="dashicons dashicons-edit"></span> Edit</span>
                        <span v-else><span class="dashicons dashicons-visibility"></span> Preview</span>
                    </button>
                    <button class="btn btn-primary" :class="{disabled: !canSave}" @click="saveChanges"><FontAwesomeIcon :icon="['fas', 'save']" size="lg"/> Save changes</button>
                </div>
                
                
                
                <div class="widget-fields-wrapper" v-if="editingMode">
                    <div class="d-flex">
                        <div class="d-flex align-items-center mb-2">
                            <button class="btn btn-secondary btn-xs mr-2 btn-switch-edit"  @click="toggleColor">
                                <span v-if="colorEdit"><FontAwesomeIcon :icon="['fas', 'edit']" size="lg"/> Edit Steps</span>
                                <span v-else><FontAwesomeIcon :icon="['fas', 'palette']" size="lg"/> Edit Colors</span>
                            </button>
                            <div class="d-flex flex-wrap" v-if="!colorEdit"> 
                                <button v-for="(stepObj,idx) in editionsSteps" class="btn btn-secondary btn-xs m-1 tt-below" 
                                :class="{'selected': (step == stepObj.key)}" @click="setStep(stepObj.key, getLabelForStep(stepObj.key))" :data-tt="stepObj.label"> Step {{ idx + 1 }}</button>
                            </div>
                        </div>
                        
                    </div>
                    <div>
                        <transition name="slide-fade-top">
                            <div class="colors-fields" v-if="colorEdit" >
                                <fieldset v-for="(groupData, group_key) in options.colors">
                                    <legend>{{ getLabel('colors', group_key) }}</legend>
                                    <template v-for="(inputvalue, input_key) in groupData">
                                        <component  v-if="isComponentTypeActive(inputvalue,'colors',group_key, input_key, true)" :key="input_key"  
                                            :is="getComponentType(inputvalue,input_key)" 
                                            v-model="options.colors[group_key][input_key]" 
                                            @change="changedInput"
                                            eventChange="input"
                                            :label="getLabel('colors', group_key, input_key)" 
                                            :ph="defaultSettings.colors[group_key][input_key]"
                                            :options="getOptions('colors', group_key, input_key)" 
                                            allowReset></component>
                                    </template>
                                </fieldset>
                            </div>
                        </transition>
                    </div>
                    <div v-if="!colorEdit" v-for="(stepObj,idx) in reverseEditionsSteps" :key="stepObj.key">
                        <transition name="slide-fade-top"  >
                            <div class="widget-fields" v-if="isCurrentStep(stepObj.key)" :data-step="'Step '+(4-idx)+': '+stepObj.label" 
                            :class="{'active-fields': (step == stepObj.key)}">
                                <template v-for="(inputvalue, group_key) in options[stepObj.key]">
                                    <component  v-if="isComponentTypeActive(inputvalue,stepObj.key,group_key, group_key)" :key="group_key"  
                                    :is="getComponentType(inputvalue,group_key)" 
                                    v-model="options[stepObj.key][group_key]" 
                                    @change="changedInput"
                                    eventChange="input"
                                    :label="getLabel(stepObj.key, group_key)" 
                                    :ph="defaultSettings[stepObj.key][group_key]"
                                    :options="getOptions(stepObj.key, group_key)" 
                                    allowReset></component>
                                </template>
                            </div>
                        </transition>

                    </div>
                    
                </div>
                <ColorPicker small v-model="tbgcolor" label="Test Different Background Color"></ColorPicker>
            </div>
        </div>
    </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core'
import { faPalette, faEdit, faSave } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faPalette,faEdit, faSave)
import Front from '../Front'
import FrontDemo from '../FrontDemo'
import ColorPicker from './ColorPicker'
import FormFieldCheckbox from '../Form/FormFieldCheckbox'
import FormFieldSlider from '../Form/FormFieldSlider'
import BookingFormDemo from './BookingFormDemo'
import Colors from '../Modules/Colors'
import SettingsSave from '../Modules/SettingsSave'
import CountrySelector from './CountrySelector'
import InputPh from '../Fields/InputLabelMaterial'
import eventsBus from '../eventsBus'
export default {
    components: {
        Front,
        FrontDemo,
        ColorPicker,
        CountrySelector,
        InputPh,
        FormFieldCheckbox,
        FormFieldSlider,
        FontAwesomeIcon,
    },
    mixins: [Colors, SettingsSave],
    props: ['preoptions','bgcolor', 'config', 'widgetFields', 'defaultSettings', 'frontAvailability'],
    data: () => ({
        step: 'button',
        stepPassed: 'button',
        
        options: null,
        tbgcolor: '#fff',
        colorEdit: false,
        textEdit: true,
        editingMode: false,
        canSave: false,
        showAllText: false,
        labelActiveStep: '',
        editionsSteps: [
            {
                key: 'button',
                label: 'Booking button'
            },
            {
                key: 'selection',
                label: 'Slot selection'
            },
            {
                key: 'form',
                label: 'Form'
            },
            {
                key: 'confirmation',
                label: 'Confirmation'
            }
        ],
        reverseEditionsSteps: [],

    }),

    created(){
        this.editionsSteps = window.wappointmentExtends.filter('WidgetEditorEditionsSteps', this.editionsSteps,  this.config )
        this.reverseEditionsSteps = this.editionsSteps.slice(0).reverse()
        this.options = Object.assign ({}, this.preoptions)
        this.options.editionsSteps = this.editionsSteps
        this.options.frontAvailability = this.frontAvailability
        this.options.eventsBus = eventsBus
        this.options.demoData = {
            form: {
                ctz:"Europe/Paris",
                name: 'John Mcgregor',
                email: 'j.mcgregor@gmail',
                phone: '6552',
                skype: 'jmcg',
                type: this.config.service.type[0],
                time: this.preselection(),
            }
        }
        this.tbgcolor = this.bgcolor
        eventsBus.listens('stepChanged', this.stepChanged)
        eventsBus.listens('dataDemoChanged', this.dataChanged)

    },

    computed: {
        pendingByDefault(){
            return this.config.approval_mode > 1
        },
        hasMultipleTypes(){
            return this.allowedTypeMultiple
        },
        getStyleThemeBg(){
            return 'background-color:' + this.hx_rgb(this.tbgcolor)
        },
        allowedTypeMultiple(){
            return this.config.service.type.length > 1
        },
        stepButton(){
            return this.step == 'button'
        },
        stepCalendar(){
            return this.step == 'selection'
        },
        stepForm(){
            return this.step == 'form'
        },
        stepConfirmation(){
            return this.step == 'confirmation'
        },
        currentIndexStep(){
            return this.editionsSteps.findIndex((e) => e.key == this.step )
        }
    },
    watch: {
        options:{
            handler: function (val,oldval) { 
               if(oldval!== null) this.canSave = true
             },
            deep: true
        },  
    },


    methods: {
        dataChanged(newValue){
            this.options.demoData.form = newValue
        },
        getOrderClass(stepObj,stepIdx){
            let orderItem = 0;
            if((stepIdx >= this.currentIndexStep)){
                orderItem = stepIdx - this.currentIndexStep
                
            }else{
                orderItem = (this.editionsSteps.length - (this.currentIndexStep-stepIdx))
            }
            return 'order-' + orderItem
        },
        orderedClass(stepObj, stepIdx){
            let classes = {hover: stepObj.key==this.step}
            if(this.colorEdit === false){
                classes[this.getOrderClass(stepObj, stepIdx)] = true
            }
            
            return classes
        },
        screenScrollWatch(e,a){
            if(e[0].intersectionRatio == 1) {
                this.$refs.editorbar.classList.remove('stick-to-top')
            }else{
                this.$refs.editorbar.classList.add('stick-to-top')
            }
        },
        toggleEditingMode(){
            this.editingMode = !this.editingMode
            this.stepChanged('button')
        },
        stepChanged(step){
            
            this.setStep(step, this.getLabelForStep(step))
        },
        getLabelForStep(step){
            for (let i = 0; i < this.editionsSteps.length; i++) {
                if(step == this.editionsSteps[i].key) return 'Step '+(i+1)+': '+this.editionsSteps[i].label
            }
        },
        changedInput(a,b){
            //console.log('changedInput',a,b)
        },
        getFieldAdminInfos(section, key){
            return (this.widgetFields !== null && this.widgetFields[section]!== undefined && this.widgetFields[section][key] !== undefined) ? this.widgetFields[section][key]:false
        },
        getLabel(section, key, subkey=false){
            let fieldInfos = this.getFieldAdminInfos(section,key)
            if(subkey!== false && this.hasFieldKey(fieldInfos, subkey)){
                return  fieldInfos.fields[subkey].label !== undefined ? fieldInfos.fields[subkey].label:subkey
            }else{
                return  fieldInfos !== false ? fieldInfos.label:key
            }
            
        },
        getOptions(section, key){
            let fieldInfos = this.getFieldAdminInfos(section,key)
            return  fieldInfos !== false ? fieldInfos.options:null
        },
        hasFieldKey(fieldInfos, subkey){
            return fieldInfos !== false && fieldInfos.fields[subkey] !== undefined
        },
        getVisibility(section, key, subkey=false){
            let fieldInfos = this.getFieldAdminInfos(section, key)
            if(subkey!== false && this.hasFieldKey(fieldInfos, subkey)){
                return fieldInfos.fields[subkey].hidden !== undefined  ? false:true
            }else{
                return  fieldInfos !== false && fieldInfos.hidden !== undefined ? false:true
            }
            return  fieldInfos !== false && fieldInfos.hidden !== undefined ? false:true
        },
        isCurrentStep(testingStep){
            return this.step == testingStep
        },
        isThisOrPreviousStep(testingStep){
            let thistep = this.step
            let found = this.editionsSteps.findIndex(function(element, idx){
                return element.key == thistep
            })

            let steps = [];
            //found++;
            for (let i = 0; i < this.editionsSteps.length; i++) {
                if(i > found) continue
                const element = this.editionsSteps[i]
                steps.push(element.key)
            }
            //let steps = this.editionsSteps.slice(0,found)
            //console.log('this and prev','testing '+testingStep, 'found '+found, steps, 'found current step '+this.step+' at position '+steps.indexOf(thistep))
            return steps.indexOf(testingStep) !== -1
        },
        isExtraSet(key) {
            return ['button', 'selection', 'form', 'confirmation'].indexOf(key) === -1
        },
        isComponentTypeActive(value, section, group_key, key, onlycolors=false){
            if(value[0] == '#'){
  
                return onlycolors===true && this.colorEdit && this.getVisibility(section, group_key, key)
            }else{
                if(onlycolors===true) return false
                if(this.showAllText === true){
                    return true
                }else{
                    return this.getVisibility(section, key)
                }
            }
        },

        getComponentType(value, type){

            if(['check_','slide_'].indexOf(type.substr(0,6)) !== -1){
                return type.substr(0,6) == 'check_' ? 'FormFieldCheckbox':'FormFieldSlider'
            }else{
                return value[0] == '#' ? 'ColorPicker':'InputPh'
            }
            
        },
        allowedType(type){
            return this.config.service.type.indexOf(type) !== -1
        },
        saveChanges(){
            if(this.canSave){
                this.$WapModal()
                .request(this.settingSaveRequest({
                    key:'widget',
                    val: this.options
                }))
                .then(this.savedWidgetSuccess)
                .catch(this.serviceError)
            }
        },
        savedWidgetSuccess(s){
            this.canSave = false
            this.serviceSuccess(s)
        },  
        savedWidgetFailed(e){
        },
        setStep(step, labelActiveStep){
            this.step = step
            this.labelActiveStep = labelActiveStep
        },

        preselection(){
            return (new Date()).getTime() / 1000
        },
        toggleColor(){
            this.colorEdit=!this.colorEdit
        },
        toggleText(){
            this.textEdit=!this.textEdit
        }
    }
}
</script>
<style>
.booking-widget-editor{
    min-height: 300px;
}
.booking-widget-editor .wapmodal-content.large {
    width: 60%;
}
.biggerPop .booking-widget-editor{
    position: absolute;
}

.preview > .bordered {
    margin: .5rem;
    box-shadow: inset 0px 0px 10px 0 rgba(0,0,0,.2);
    border-radius: .2rem;
}

.preview > .hover{
    box-shadow: inset 0px 0px 10px 0 rgba(127, 126, 208, 0.6);
}

.preview > .hover[data-tt]::before, .preview > .hover[data-tt]::after{
    visibility: visible;
    opacity: 1;
    bottom:100%;
}

.widget-wraper{
    box-shadow: inset 0 0 5px #959090;
    width: 360px;
    
}

@media (min-width: 1410px) { 
    .widget-wraper{
        width: 750px !important;
    }
}

@media (max-width: 876px) { 
    .widget-wraper{
        padding: 0 !important;
        padding-top: 1rem !important;
    }
}

.right-color{
    float:right;
}
.widget-fields-wrapper {
    overflow: hidden;
}
.widget-fields{
    opacity:.6;
    border: 1px solid #ccc;
    border-radius: 0.2rem;
    padding: .6rem;
    margin-bottom: 0.6rem;
    height: 56px;
    overflow: hidden;
    box-shadow: inset 0px 0px 10px -5px rgba(0,0,0,.5);
}
.widget-fields.active-fields, .widget-fields:hover{
    opacity:1;
    height: auto;
    box-shadow: none;
}

.widget-fields::before {
    content: attr(data-step);
    position: absolute;
    background-color: rgba(236, 236, 236, 0.9);
    border-radius: 0.4rem;
    padding: 0.3rem;
    display: block;
    width: 85%;
    border: 3px dashed #999;
    text-align: center;
    z-index: 9;
}

.widget-fields.active-fields::before, .widget-fields:hover::before{
    display:none;
}


.preview .wap-front{
    min-width: 300px;
    margin: 1rem;
}

.colors-fields legend {
    display: block;
    width: auto;
    max-width: 100%;
    padding: 0;
    margin-bottom: 0;
    font-size: 1rem;
    line-height: inherit;
    color: inherit;
    white-space: normal;
}
.colors-fields fieldset {
    min-width: 0;
    padding: .4rem;
    margin: 0;
    border: 1px solid #ccc;
    display: flex;
}
.colors-fields fieldset .wap-color-picker {
    margin-right:.5rem;
}
.btn-switch-edit{
    width: 90px;
}
#editor-bar{
    position: sticky;
    top: 6rem;
}

</style>
