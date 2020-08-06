<template>
    <div class="d-flex flex-wrap flex-sm-nowrap">
      <div class="mr-2">
          <LabelMaterial>
                <textarea class="form-control" @focusout="$emit('activated')" :class="{'is-invalid':hasErrors}" v-model="updatedValue"
            :id="id"
            :maxlength="definition.max"
            :minlength="definition.min"
            :rows="definition.rows || 2"
            :placeholder="definition.label"></textarea>
        </LabelMaterial>
        <small id="emailHelp" v-if="tip" class="form-text text-muted">{{ tip }}</small>
        <div class="small text-danger" v-if="hasErrors">
            <div v-for="error in errors">
                {{ error }}
            </div>
        </div>
      </div>
    </div>

</template>

<script>
import AbstractField from './AbstractField'
import LabelMaterial from '../Fields/LabelMaterial'
export default {
    name: 'core-textarea',
    mixins: [AbstractField],
    components: {LabelMaterial},
}
</script>
