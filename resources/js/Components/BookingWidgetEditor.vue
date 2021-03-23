<template>
    <div>
        <div class="d-flex booking-widget-editor" >
            
            <div class="widget-wraper p-3 mr-3"  :style="getStyleThemeBg" >
                <div class="clear pt-2" >
                    <div v-if="!editingMode">
                        <Front :options="preoptions"  classEl="wappointment_widget" :attributesEl="shortcodeParams" ></Front>
                    </div>
                    <div v-if="editingMode && frontAvailability!==undefined" class="d-flex flex-wrap preview-book">
                        <div  v-for="(stepObj,idx) in editionsSteps" class="bordered" :class="orderedClass(stepObj,idx)" :data-tt="stepObj.key==step?labelActiveStep:false">
                            <div  class="overflowhidden" :class="'step-'+stepObj.key">
                                <FrontDemo :options="options"  classEl="wappointment_widget" :step="stepObj.key" ></FrontDemo>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="editor-bar" class="align-self-start" v-if="editingMode">
                <div class="mb-2" >
                    <button class="btn btn-primary" :class="{disabled: !canSave}" @click="saveChanges"><FontAwesomeIcon :icon="['fas', 'save']" size="lg"/> Save changes</button>
                </div>
                
                <div class="widget-fields-wrapper" >
                    <div >
                        <div>
                            <button class="btn btn-secondary btn-cell btn-xs ml-0 mr-2 btn-switch-edit" :class="{'selected' : !colorEdit}" @click="toggleColor">
                                <span><FontAwesomeIcon :icon="['fas', 'edit']" size="lg" /> Edit Text</span>
                            </button>
                            <button class="btn btn-secondary btn-cell btn-xs ml-0 mr-2 btn-switch-edit" :class="{'selected' : colorEdit}" @click="toggleColor">
                                <span><FontAwesomeIcon :icon="['fas', 'palette']" size="lg" /> Edit Color</span>
                            </button>
                        </div>
                        <div class="d-flex align-items-center my-2">
                            <div class="d-flex flex-wrap align-items-center" v-if="!colorEdit"> 
                                <button v-if="widgetFields.general !== undefined" class="btn btn-link btn-xs mr-1 tt-below" 
                                :class="{'selected': (step == 'general')}" @click="setStep('general', 'General')" data-tt="General"> General</button>
                                <span class="small text-muted mr-1">></span>
                                <template v-for="(stepObj,idx) in editionsSteps">
                                     <button class="btn btn-link btn-xs mr-1 tt-below" 
                                    :class="{'selected': (step == stepObj.key)}" @click="setStep(stepObj.key, getLabelForStep(stepObj.key))" :data-tt="stepObj.label"> Step {{ idx + 1 }}</button>
                                    <span v-if="idx !== editionsSteps.length-1" class="small text-muted mr-1">></span>
                                </template>
                               
                            </div>
                        </div>
                    </div>
                    <div class="bwe-settings-bar">
                        <transition name="slide-fade-top">
                            <div class="colors-fields" v-if="colorEdit" >
                                <fieldset v-for="(groupData, group_key) in options.colors" v-if="isMain('colors', group_key) || showAdvancedColors">
                                    <legend>{{ getLabel('colors', group_key) }}</legend>
                                    <div v-for="(inputvalue, input_key) in groupData" v-if="canShowField('colors',group_key, input_key)" :data-tt="getFieldTip('colors',group_key, input_key) ? getFieldTip('colors',group_key, input_key) : false" class="tt-below">
                                        <component v-if="isComponentTypeActive(inputvalue,'colors',group_key, input_key, true)" :key="input_key"  
                                            :is="getComponentType(inputvalue,input_key)" 
                                            v-model="options.colors[group_key][input_key]" 
                                            
                                            eventChange="input"
                                            :label="getLabel('colors', group_key, input_key)" 
                                            :ph="defaultSettings.colors[group_key][input_key]"
                                            :options="getOptions('colors', group_key, input_key)" 
                                            allowReset></component>
                                    </div>
                                </fieldset>
                                <a class="my-2 small" href="javascript:;" v-if="!showAdvancedColors" @click="showAdvancedColors=true">Edit more colors</a>
                            </div>
                        </transition>
                    </div>
                    <div class="bwe-settings-bar" v-if="!colorEdit" >
                        <div v-for="(stepObj,idx) in reverseEditionsSteps" :key="stepObj.key">
                            <transition name="slide-fade-top"  >
                                <div class="widget-fields" v-if="isCurrentStep(stepObj.key)" :data-step="'Step '+(4-idx)+': '+stepObj.label" 
                                :class="{'active-fields': (step == stepObj.key)}">
                                    <div v-if="widgetFields[stepObj.key] !== undefined && widgetFields[stepObj.key].categories !== undefined">
                                        <div :class="{'selected-tab': showCategory ==  cat_object.label}" v-for="(cat_object, catid) in widgetFields[stepObj.key].categories">
                                            <div :class="[showCategory ==  cat_object.label ? 'btn btn-light btn-sm':'btn btn-link btn-sm']"  role="button" @click="showCategory = cat_object.label">
                                                {{cat_object.label}} <span v-if="showCategory !=  cat_object.label">[+]</span>
                                            </div>
                                            <div v-if="showCategory ==  cat_object.label" class="ml-3 mt-3">
                                                <div class="small" v-if="cat_object.sub !== undefined"> {{ cat_object.sub }}</div>
                                                <div v-for="(fieldDescription, field_key) in cat_object.fields" :data-tt="getFieldTip(stepObj.key, field_key, catid) ? getFieldTip(stepObj.key, field_key, catid) : false" 
                                                v-if="canShowField(stepObj.key, field_key, catid)" class="tt-below">
                                                    {{ changedInput(stepObj.key, field_key, options[stepObj.key][field_key]) }}
                                                    <component v-if="isComponentTypeActive(options[stepObj.key][field_key],stepObj.key, field_key, field_key)" :key="field_key"  
                                                    :is="getComponentType(options[stepObj.key][field_key],field_key)" 
                                                    v-model="options[stepObj.key][field_key]" 
                                                    @input="(e) => changedInput(stepObj.key, field_key, e)"
                                                    eventChange="input"
                                                    :label="fieldDescription.label" 
                                                    :ph="defaultSettings[stepObj.key][field_key]"
                                                    :options="fieldDescription.options !== undefined ? fieldDescription.options:{}" 
                                                    allowReset></component>
                                                    <div v-if="[undefined,false].indexOf(tagRemoved[stepObj.key + '_' + field_key]) === -1" class="small text-danger">
                                                    You should leave the tag(s) {{ getTags(defaultSettings[stepObj.key][field_key]).join(', ') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <div v-for="(inputvalue, field_key) in options[stepObj.key]" :data-tt="getFieldTip(stepObj.key, field_key) ? getFieldTip(stepObj.key, field_key) : false" v-if="canShowField(stepObj.key, field_key)" class="tt-below">
                                            {{ changedInput(stepObj.key, field_key, inputvalue) }}
                                            <component  v-if="isComponentTypeActive(inputvalue,stepObj.key, field_key, field_key)" :key="field_key"  
                                            :is="getComponentType(inputvalue,field_key)" 
                                            v-model="options[stepObj.key][field_key]" 
                                            @input="(e) => changedInput(stepObj.key, field_key, e)"
                                            eventChange="input"
                                            :label="getLabel(stepObj.key, field_key)" 
                                            :ph="defaultSettings[stepObj.key][field_key]"
                                            :options="getOptions(stepObj.key, field_key)" 
                                            allowReset></component>
                                            <div v-if="[undefined,false].indexOf(tagRemoved[stepObj.key + '_' + field_key]) === -1" class="small text-danger">
                                            You should leave the tag(s) {{ getTags(defaultSettings[stepObj.key][field_key]).join(', ') }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </transition>
                        </div>
                        <transition name="slide-fade-top"  >
                            <div class="widget-fields" v-if="isCurrentStep('general')" data-step="General" 
                            :class="{'active-fields': (step == 'general')}">
                                <div v-for="(inputvalue, field_key) in options['general']" :data-tt="getFieldTip('general', field_key) ? getFieldTip('general', field_key) : false" v-if="canShowField('general', field_key)" class="tt-below">
                                    {{ changedInput('general', field_key, inputvalue) }}
                                    <component  v-if="isComponentTypeActive(inputvalue,'general',field_key, field_key)" :key="field_key"  
                                    :is="getComponentType(inputvalue,field_key)" 
                                    v-model="options['general'][field_key]" 
                                    @input="(e) => changedInput('general', field_key, e)"
                                    eventChange="input"
                                    :label="getLabel('general', field_key)" 
                                    :ph="defaultSettings['general'][field_key]"
                                    :options="getOptions('general', field_key)" 
                                    allowReset></component>
                                    <div v-if="[undefined,false].indexOf(tagRemoved['general_' + field_key]) === -1" class="small text-danger">
                                        You should leave the tag(s) {{ getTags(defaultSettings['general'][field_key]).join(', ') }}
                                    </div>
                                </div>
                            </div>
                        </transition>

                    </div>
                    
                </div>
                <ColorPicker v-if="colorEdit" small v-model="tbgcolor" label="Test Different Background Color"></ColorPicker>
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
import Colors from '../Modules/Colors'
import SettingsSave from '../Modules/SettingsSave'
import CountrySelector from './CountrySelector'
import eventsBus from '../eventsBus'

export default {
    components: {
        Front,
        FrontDemo,
        ColorPicker,
        CountrySelector,
        InputPh: window.wappoGet('InputPh'),
        FormFieldCheckbox,
        FormFieldSlider,
        FontAwesomeIcon,
    },
    mixins: [Colors, SettingsSave],
    props: ['preoptions','bgcolor', 'config', 'widgetFields', 'defaultSettings', 'frontAvailability', 'editingMode', 'shortcodeParams'],
    data: () => ({
        step: 'general',
        showAdvancedColors: false,
        options: null,
        tbgcolor: '#fff',
        colorEdit: false,
        textEdit: true,
        canSave: false,
        labelActiveStep: '',
        tagRemoved: {},
        showCategory: '',
        editionsStepsLegacy: [
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
        editionsSteps: [
            {
                key: 'button',
                label: 'Booking button'
            },
            {
                key: 'service_selection',
                label: 'Service selection'
            },
            {
                key: 'service_duration',
                label: 'Duration selection'
            },
            {
                key: 'service_location',
                label: 'Location selection'
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
        this.editionsSteps = window.wappointmentExtends.filter('WidgetEditorEditionsSteps', this.isLegacy? this.editionsStepsLegacy:this.editionsSteps,  this.config )
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
                type: this.config.service.type !== undefined ? this.config.service.type[0]:'',
                time: false,
            }
        }
        this.tbgcolor = this.bgcolor
        eventsBus.listens('stepChanged', this.stepChanged)
        eventsBus.listens('dataDemoChanged', this.dataChanged)

        this.stepChanged('general')

    },

    computed: {
        isLegacy(){
            return this.frontAvailability.services.length < 2 && this.frontAvailability.services[0].type !== undefined
        },
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
        
        tagHasBeenRemoved(defaultVal, currentValue){
            if(typeof defaultVal == "string"){
                let tags = this.getTags(defaultVal)
                if(tags === false) {
                    return tags 
                }
                for (let i = 0; i < tags.length; i++) { //check whetehr the tags are presents in the string
                    if(currentValue.indexOf(tags[i]) === -1 ){
                        return true // tag has been removed
                    }
                }
            }
            return false
        },
        getTags(defaultVal){
            const found = defaultVal.match(/\[[^\]]*]/g)
            return Array.isArray(found) ? found:false
        },
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
            let classes = {hover: stepObj.key==this.step, hidestep: ['general',stepObj.key].indexOf(this.step)===-1}
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

        stepChanged(step){
            
            this.setStep(step, this.getLabelForStep(step))
            
        },
        getLabelForStep(step){
            for (let i = 0; i < this.editionsSteps.length; i++) {
                if(step == this.editionsSteps[i].key) return 'Step '+(i+1)+': '+this.editionsSteps[i].label
            }
        },

        changedInput(step, group, value){
            let key = step+'_'+group
            this.tagRemoved[key] = this.tagHasBeenRemoved(this.defaultSettings[step][group], this.options[step][group])
        },

        getFieldAdminInfos(section, key, catid = false){
            if(section == 'colors'){
                let data = (this.widgetFields !== null && this.widgetFields[section]!== undefined && this.widgetFields[section]!== undefined && this.widgetFields[section][key] !== undefined) ? this.widgetFields[section][key]:false
                return data !== false  && data.fields !== undefined && data.fields[catid] !== undefined? data.fields[catid]:data
            }

            if(catid !== false){
                return (this.widgetFields !== null && this.widgetFields[section].categories[catid]!== undefined && this.widgetFields[section].categories[catid].fields!== undefined && this.widgetFields[section].categories[catid].fields[key] !== undefined) ? this.widgetFields[section].categories[catid].fields[key]:false
            }
            return (this.widgetFields !== null && this.widgetFields[section]!== undefined && this.widgetFields[section].fields!== undefined && this.widgetFields[section].fields[key] !== undefined) ? this.widgetFields[section].fields[key]:false
        },

        getFieldTip(section, key, catid = false){
            let fieldInfos = this.getFieldAdminInfos(section, key, catid)
             
            return fieldInfos.tip !== undefined ? fieldInfos.tip : ''
        },

        canShowField(section, key, field_key = false){
            let fieldConditions = this.getConditions(section, key,field_key)
            if(field_key!== false){
                let fieldInfos = this.getFieldAdminInfos(section, key, field_key)
                //fieldInfos === false || 
                if(fieldInfos.fields !== undefined && fieldInfos.fields[field_key] === undefined) {
                    return false
                }
            }
            if(fieldConditions !== false){
                let canShowField = true
                for (let i = 0; i < fieldConditions.length; i++) {
                    let condition = fieldConditions[i]
                    if(this.getValueOnDottedKey(condition.key) !== condition.val){
                        return false //failed conditions
                    }
                }
                return canShowField
            }
            return true
        },
        getValueOnDottedKey(dottedKey){
            let keys = []
            if(dottedKey.indexOf('.') !== -1){
                keys = dottedKey.split('.')
            }else{
                keys = [dottedKey]
            }
            return this.getDeepValue(this.options, keys)
        },
        getDeepValue(deepObject, keys){
            let key1 = keys.shift()
            let newDeepOrValue = deepObject[key1] !== undefined ? deepObject[key1]:false
            return keys.length > 0 ? this.getDeepValue(newDeepOrValue, keys): newDeepOrValue
        },
        getConditions(section, key, field_key= false){
            let fieldInfos = this.getFieldAdminInfos(section, key, field_key)
            return fieldInfos.conditions !== undefined ? fieldInfos.conditions : false
        },
        getLabel(section, key, subkey=false){
            let fieldInfos = this.getFieldAdminInfos(section,key)
            if(subkey!== false && this.hasFieldKey(fieldInfos, subkey)){
                return fieldInfos.fields[subkey].label !== undefined ? fieldInfos.fields[subkey].label:subkey
            }else{
                return fieldInfos !== false ? fieldInfos.label:key
            }
        },
        isMain(section, key){
            let fieldInfos = this.getFieldAdminInfos(section,key)
            return fieldInfos.main === true
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
            }
            return  fieldInfos !== false && fieldInfos.hidden !== undefined ? false:true
        },
        isCurrentStep(testingStep){
            return this.step == testingStep
        },

        isExtraSet(key) {
            return ['button', 'selection', 'form', 'confirmation'].indexOf(key) === -1
        },
        isComponentTypeActive(value, section, group_key, key, onlycolors=false){
            if(value === undefined) {
                return false
            }
            if(value[0] == '#'){
                return onlycolors===true && this.colorEdit && this.getVisibility(section, group_key, key)
            }else{
                return onlycolors===true? false:this.getVisibility(section, key)
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

            if(this.widgetFields[step]!== undefined && this.widgetFields[step].categories !== undefined){
                this.showCategory = this.widgetFields[step].categories[0].label
            }
        },

        toggleColor(){
            this.colorEdit=!this.colorEdit
            this.step = 'general'
        },
        toggleText(){
            this.textEdit=!this.textEdit
        }
    }
}
</script>
<style>
.selected-tab{
    background-color: #ececec;
    padding: 0 .8em .4em 0;
    border-radius: .4em;
    border: 1px solid #b8b8f5;
}
.booking-widget-editor{
    min-height: 300px;
}
.booking-widget-editor .wapmodal-content.large {
    width: 60%;
}
.biggerPop .booking-widget-editor{
    position: absolute;
}

.preview-book > .bordered {
    margin: .5rem;
    box-shadow: inset 0px 0px 10px 0 rgba(0,0,0,.2);
    border-radius: .2rem;
}

.preview-book > .hover{
    box-shadow: inset 0px 0px 10px 0 rgba(127, 126, 208, 0.6);
}
.preview-book > .hidestep{
    display: none;
}

.booking-widget-editor [data-tt]{
    z-index: auto;
}
.booking-widget-editor [data-tt]::before{
    z-index: 1;
}

.booking-widget-editor .bwe-settings-bar{
    max-height: 80vh;
    overflow: scroll;
}

.preview-book > .hover[data-tt]::before, .preview-book > .hover[data-tt]::after{
    visibility: visible;
    opacity: 1;
    bottom:100%;
}

.widget-wraper{
    box-shadow: inset 0 0 5px #959090;
    width: 430px;
    border-radius: 2rem;
}

@media (min-width: 1410px) { 
    .widget-wraper{
        width: 850px !important;
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
    margin-bottom: .4rem;
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
.widget-fields .tt-below{
    margin-bottom: .6em;
}

.preview-book .wap-front{
    min-width: 360px;
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
