<template>
    <div >
        <ul class="my-4 nav nav-tabs" v-if="addon.instructions.length > 1">
            <li class="nav-item"  v-for="(instruction,idx) in addon.instructions">
                <a class="nav-link" :class="{active:currentStep == instruction.step}" href="javascript:;" 
                @click="showStep(instruction.step)">
                    {{ instruction.button }}
                </a>
            </li>
        </ul>

        <div>
            <div v-if="currentInstruction.description !== undefined">{{ currentInstruction.description }}</div>

            <div v-if="stepAddonsSettings">
                <component :is="addon.settingKey" :crumb="false"></component>
            </div>
            <div v-else>
                <component :is="currentStep" :crumb="false" @changeStep="showStep"></component>
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
const FontAwesomeIcon = () => import(/* webpackChunkName: "appFawesome" */ '../appFawesome')
import AbstractListing from '../Views/AbstractListing'

export default {
  components: window.wappointmentExtends.filter('AddonsSettingsComponents', 
  { Service, Widget, Regav, 
  FontAwesomeIcon, AbstractListing }, 
  {extends: abstractView, mixins: [RequestMaker]} ),
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
