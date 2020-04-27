<template>
    <div >
        <ul class="my-4 nav nav-tabs" v-if="addon.instructions.length > 1">
            <li class="nav-item"  v-for="(instruction,idx) in addon.instructions">
                <a class="nav-link" :class="{active:currentStep == instruction.step}" href="javascript:;" @click="showStep(instruction.step)">
                    {{ idx + 1 }} - {{ instruction.button }}
                </a>
            </li>
        </ul>

        <div>
            <div v-if="currentInstruction.description !== undefined">{{ currentInstruction.description }}</div>
            <Service :crumb="false" v-if="stepService" />
            <Widget v-if="stepWidgetEditor" />
            <Regav v-if="stepRegav" :minimal="true"/>
            <div v-if="stepAddonsSettings">
                <component :is="addon.settingKey"></component>
            </div>
        </div>
    </div>
</template>

<script>
import Service from '../Views/Subpages/Service'
import Widget from '../Views/Subpages/Widget'
import Regav from '../Views/Subpages/Regav'
import abstractView from '../Views/Abstract'
import RequestMaker from '../Modules/RequestMaker'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkedAlt, faPhone, faCalendarCheck } from '@fortawesome/free-solid-svg-icons'
import { faSkype} from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import AbstractListing from '../Views/AbstractListing'

library.add(faMapMarkedAlt, faPhone, faSkype, faCalendarCheck)
export default {
  components: window.wappointmentExtends.filter('AddonsSettingsComponents', { Service, Widget, Regav, FontAwesomeIcon, AbstractListing }, {extends: abstractView, mixins: [RequestMaker]} ),
    props: ['addon'],
    data() {
        return {
            currentStep: false,
            addons: window.wappointmentExtends.filter('AddonsActive', window.wappointmentAdmin.addons )
        }
    },
    created(){
        this.currentStep = this.addon.instructions[0].step
    },
    computed: {
        stepService(){
           return this.currentStep == 'service'
        },
        stepWidgetEditor(){
            return this.currentStep == 'widget'
        },
        stepRegav(){
            return this.currentStep == 'regav'
        },
        stepAddonsSettings(){
            return this.currentStep == 'addons_settings'
        },
        currentInstruction(){
            for (let i = 0; i < this.addon.instructions.length; i++) {
                if(this.addon.instructions[i].step == this.currentStep){
                    return this.addon.instructions[i]
                }
            }
        }
    },
    methods: {

        showStep(step){
            this.currentStep = step
        }
    }
} 
</script>
