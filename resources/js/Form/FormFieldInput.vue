<template>
    <div :class="getClassWrapper">
        <LabelMaterial>
            <input :type="getInputType" class="form-control" @keydown.prevent.stop.enter="catchEnterEvent"  :class="{'is-invalid':hasErrors}" :id="id" 
            @focusout="$emit('activated')" :placeholder="label" 
            v-model="updatedValue">
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
    components: {LabelMaterial},
    mixins: [AbstractField],
    methods:{
        catchEnterEvent(e){
            this.$emit('submitted',e)
        },

    },
    computed: {
        getInputType(){
            return this.definition.type != 'input' ? this.definition.type : 'text'
        }
    }
}
</script>
