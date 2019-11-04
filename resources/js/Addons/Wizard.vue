<template>
    <div >
        <div class="d-flex my-4" >
            <button v-for="(instruction,idx) in addon.instructions" class="btn btn-secondary" :class="{selected: currentStep == instruction.step }" @click="showStep(instruction.step)">
               {{ idx + 1 }} - {{ instruction.button }}
            </button>
        </div>
        <div>
            <Service v-if="stepService" />
            <Widget v-if="stepWidgetEditor" />
            <div v-if="stepAddonsSettings">
                <component :is="addon.settingKey"></component>
            </div>
        </div>
    </div>
</template>

<script>
import Service from '../Views/Subpages/Service'
import Widget from '../Views/Subpages/Widget'
import abstractView from '../Views/Abstract'
import RequestMaker from '../Modules/RequestMaker'
export default {
  components: window.wappointmentExtends.filter('AddonsSettingsComponents', {Service, Widget}, {extends: abstractView, mixins: [RequestMaker]} ),
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
        stepAddonsSettings(){
            return this.currentStep == 'addons_settings'
        }
    },
    methods: {

        showStep(step){
            this.currentStep = step
        }
    }
} 
</script>
