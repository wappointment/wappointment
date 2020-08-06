<template>
    <div :class="getClassWrapper">
        <LabelMaterial>
            <input @keydown.prevent.stop.enter="catchEnterEvent"  
             :id="id" :readonly="isReadOnly"
             :type="getInputType" class="form-control" :class="{'is-invalid':hasErrors}"
            @focusout="$emit('activated')" :placeholder="label" :autocomplete="autocomplete"
            v-model="updatedValue">
            <span v-if="definition.type == 'password'"  
            class="dashicons" :class="[passwordShow?'dashicons-hidden':'dashicons-visibility']" 
            @click="passwordShow=!passwordShow"></span>
        </LabelMaterial>
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>
        <small id="emailHelp" v-if="tip" class="form-text text-muted">{{ tip }}</small>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import LabelMaterial from '../Fields/LabelMaterial'
export default {
    name:'core-input',
    components: {LabelMaterial},
    mixins: [AbstractField],
    data(){
        return {
            passwordShow: false
        }
    },
    methods:{
        catchEnterEvent(e){
            this.$emit('submitted',e)
        },
    },
    computed: {
        isReadOnly(){
            return [undefined,false,null,''].indexOf(this.definition.readonly) === -1
        },
        getInputType(){
            if(this.passwordShow === true) return 'text'
            return this.definition.type != 'input' ? this.definition.type : 'text'
        },
        getClassWrapper(){
            let classes = {}
            classes['input-'+this.definition.type] = true
            if(this.definition.classWrapper !== undefined){
                classes[this.definition.classWrapper] = true
            }
            return classes
        },
    }
}
</script>
<style>
.input-password .dashicons{
    position: absolute;
    top: 16px;
    font-size: 18px;
    color: #484747;
    display: block;
    right: 10px;
    width: 25px;
    background-color: #fff;
    text-align: center;
    box-shadow: 0 .4rem 1rem 0 rgba(0,0,0,.05);
    border-radius: 3.2rem;
    cursor: pointer;
}
</style>
