<template>
    <div :class="getClassWrapper" @mouseover="showControls=true" @mouseout="showControls=false">
        <div class="d-flex align-items-center inputs-row"  v-for="(valueObj,idx) in updatedValue" 
        :class="getRowClass(valueObj)" v-if="showActiveRowOrReview(valueObj)">
            <FormFieldInput :label="definition.definitionChild.label + ' ' +(idx+1)" :value="valueObj.label" 
            :parentErrors="parentErrors" :id_ovr="getModelValue(valueObj,idx)" :parentModel="parentModel"
                     :errors="errors" :model="getModelValue(valueObj,idx)"
                     @change="changedValue" @submitted="addValueToggleNext" :definition="definition.definitionChild"
                    ></FormFieldInput>
            <transition name="fade">
                <button v-if="showControls && valueObj.delete === undefined" class="btn btn-white text-muted btn-xs" 
                data-tt="Remove" @click.prevent="removeDuration(idx, valueObj)"><span class="dashicons dashicons-trash" ></span></button>
            </transition>
            <button  v-if="valueObj.delete !== undefined" class="btn btn-white text-muted btn-xs" 
            data-tt="Undo" @click.prevent="removeRevert(idx, valueObj)"><span class="dashicons dashicons-undo"></span></button> 
        </div>
        <p class="text-danger" v-if="hasPendingDelete && !review">
            <small>{{ hasPendingDelete }} values will be deleted once you save. Revert?</small>
            <button class="btn btn-link btn-xs" @click.prevent="review=true">Review items</button>
        </p>
        <transition name="fade">
            <button v-if="showControls" class="btn btn-white btn-sm p-0" @click.prevent="addValue" data-tt="Add value">
                <span class="dashicons dashicons-plus-alt text-primary" ></span> Add Value
            </button>
        </transition>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import FormFieldInput from './FormFieldInput'
import makeid from '../Standalone/makeid'
export default {
    components: {FormFieldInput},
    mixins: [AbstractField],
    data: () => ({
        showControls: false,
        review:false,
        tempUpdated : null,
    }),
    computed: {
        hasPendingDelete(){
            let test  = []
            if(Array.isArray(this.updatedValue)){
                test  = this.updatedValue.filter((e) => e.delete!==undefined)
            }
            
            return test.length
        },

    },
    mounted(){
        if(!Array.isArray(this.updatedValue)) {
            this.updatedValue = []
        }
        if(this.updatedValue.length < 1 ){
            this.addValue()
        }
    },
    methods: {
        addValueToggleNext(e){
            let input = this.addValue()
            setTimeout(this.addValueDelay.bind(null,input) , 100);
        },
        addValueDelay(input){
            document.getElementById(input.value).focus()
        },
        getModelValue(valueObj,idx){
            return this.updatedValue[idx]['value']
        },
        changedValue(value,id){
            for (let i = 0; i < this.updatedValue.length; i++) {
                if(this.updatedValue[i]['value'] == id){
                    this.updatedValue[i]['label'] = value
                }
            }
        },
        showActiveRowOrReview(valueObj){
            return valueObj.delete===undefined || (valueObj.delete !== undefined && this.review)
        },
        getRowClass(valueObj){
            return {'deleting-row':valueObj.delete !== undefined}
        },
        removeDuration(a){
            this.updatedValue[a]['delete'] = true
            this.tempUpdated = this.updatedValue
            this.updatedValue= []
            this.review = false
            setTimeout(this.removeDelay, 100)
        },
        removeRevert(a){
            this.updatedValue[a]['delete'] = undefined
            this.updatedValue[a] = this.cleanObject(this.updatedValue[a])
            this.tempUpdated = this.updatedValue
            this.updatedValue= []
            setTimeout(this.removeDelay, 100)
        },

        cleanObject(ObjectToClean) {
            return Object.entries(ObjectToClean).reduce((a,[k,v]) => (v === undefined ? a : {...a, [k]:v}), {})
        },

        removeDelay(){
            this.updatedValue = this.tempUpdated
            this.tempUpdated = null
        },

        addValue(){
            let valueObj = {
                'label': '',
                'value': 'inp-' + makeid().toLowerCase()
            }

            this.updatedValue.push(valueObj)
            return valueObj
        },

        changedDuration(element,a){
            this.updatedValue[a]['duration'] = element
        },

    }
    
}
</script>
<style>
.deleting-row{
    background-color: #f0cccc;
}

.inputs-row {
    padding: .5rem;
}
.inputs-row .label-wrapper{
    margin-bottom: 0!important;
}

</style>
