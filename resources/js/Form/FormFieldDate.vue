<template>
    <div :class="getClassWrapper">
        <LabelMaterial extraClass="active" :valuePassed="updatedValue">
            <input  :id="id" :placeholder="label" type="date" class="form-control" :class="{'is-invalid':hasErrors}"
                @focusout="$emit('activated')"
            v-model="updatedValue"
            :min="definition.min" :max="definition.max">
        </LabelMaterial>
        
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import LabelMaterial from '../Fields/LabelMaterial'
export default {
    name:'core-date',
    components: {LabelMaterial},
    mixins: [AbstractField],
}
</script>
