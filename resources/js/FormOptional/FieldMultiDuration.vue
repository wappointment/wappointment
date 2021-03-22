<template>
    <div>
        <div v-if="label" class="mb-2">
            {{ label}} 
        </div>
        <template v-for="(durationObj, idx) in updatedValue" >
            <div class="d-flex align-items-center duration-row" :class="getRowClass(durationObj)" v-if="showActiveRowOrReview(durationObj)">
               <FormFieldDuration :value="durationObj.duration" :model="idx" @change="changedDuration" :definition="definitionDuration" ></FormFieldDuration>
                <template v-if="definitionWooPrice !== null">
                    <div v-if="hasErrors">
                        <div>
                            <FormFieldInput v-if="isVisibleWooPrice" :errors="getErrorsWooPrice(idx)" :value="durationObj.woo_price" 
                        :label="definitionWooPrice.label" 
                        :conditions="definitionWooPrice.conditions" :model="idx" 
                        @change="changedWooPrice" :definition="definitionWooPrice" ></FormFieldInput>
                        </div>
                    </div>
                    <div v-else>
                        <FormFieldInput v-if="isVisibleWooPrice" :errors="getErrorsWooPrice(idx)" :value="durationObj.woo_price" 
                        :label="definitionWooPrice.label" 
                        :conditions="definitionWooPrice.conditions" :model="idx" 
                        @change="changedWooPrice" :definition="definitionWooPrice" ></FormFieldInput>
                    </div>
                </template>
                
                <transition name="fade">
                    <button type="button" v-if="showControls && durationObj.delete === undefined && updatedValue.length > 1" class="btn btn-white text-muted btn-xs" 
                    data-tt="Remove" @submit.stop @click.prevent="removeDuration(idx, durationObj)"><span class="wicon trash" ></span></button>
                </transition>
                <button  type="button" @submit.stop v-if="durationObj.delete !== undefined" class="btn btn-white text-muted btn-xs" data-tt="Undo" @click.prevent="removeRevert(idx, durationObj)"><span class="wicon undo"></span></button> 
            </div>
        </template>

        <p class="text-danger" v-if="hasPendingDelete && !review">
            <small>{{ hasPendingDelete }} durations will be deleted once you save. Revert?</small>
            <button class="btn btn-link btn-xs" @click.prevent="review=true">Review items</button>
        </p>
        <transition name="fade">
            <button type="button" @submit.stop v-if="!minimal && (showControls || hasNoDuration)" class="btn btn-white btn-sm p-0" @click.prevent="addDuration" data-tt="Add duration">
                <span class="wicon plus-alt text-primary" ></span> Add Duration
            </button>
        </transition>
    </div>
</template>

<script>

import AbstractField from '../Form/AbstractField'
import RequestMaker from '../Modules/RequestMaker'
import FormFieldInput from '../Form/FormFieldInput'
import FormFieldDuration from '../Form/FormFieldDuration'
export default {
    mixins: [AbstractField, RequestMaker],
    components:{FormFieldInput, FormFieldDuration},
    props:['woo_sellable'],
    name:'opt-multidurations',
    data: () => ({
        definitionDuration: null,
        definitionWooPrice: null,
        tempUpdated : null,
        review:false,
        showControls: true
    }),
    computed: {
        hasPendingDelete(){
            let test  = []
            if(Array.isArray(this.updatedValue)){
                test  = this.updatedValue.filter((e) => e.delete!==undefined)
            }
            
            return test.length
        },
        isVisibleWooPrice(){
            return this.woo_sellable
        },
        hasNoDuration(){
            return Array.isArray(this.updatedValue) && this.updatedValue.length == 0
        }
    },
    created(){
        this.definitionDuration = Object.assign({},this.definition)
        if(this.definition.woo_price_field !== undefined){
            this.definitionWooPrice = this.definition.woo_price_field
            this.definitionWooPrice.classWrapper = {'woo_price':true}
        }
        if(!Array.isArray(this.updatedValue)){
            this.updatedValue = []
        }
    },
    methods: {
        showActiveRowOrReview(durationObj){
            return durationObj.delete===undefined || (durationObj.delete !== undefined && this.review)
        },
        getRowClass(durationObj){
            return {'deleting-row':durationObj.delete !== undefined}
        },
        getErrorsWooPrice(idx){
            let keyError = 'options.durations.'+idx+'.woo_price'
            if(this.parentErrors[keyError] !== undefined){
                 return this.parentErrors[keyError]
            }
             return false
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
        addDuration(){
            this.requiresAddon('services')
        },
        changedDuration(element,a){
            this.updatedValue[a]['duration'] = element
        },
        changedWooPrice(element,a){
             this.updatedValue[a]['woo_price'] = element
        },
    }
}
</script>
<style>
.deleting-row{
    background-color: #f0cccc;
}

.duration-row {
    padding: .5rem;
}
.duration-row .label-wrapper{
    margin-bottom: 0!important;
}
.woo_price {
    margin: 0 .5rem;
}
.woo_price input{
    width: 100px;
}
</style>
