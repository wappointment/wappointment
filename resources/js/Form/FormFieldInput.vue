<template>
    <div>
        <LabelMaterial>
            <input type="text" class="form-control" :class="{'is-invalid':hasErrors}" :id="id" 
            aria-describedby="emailHelp" :placeholder="label" 
            v-model="updatedValue">
        </LabelMaterial>
        <small id="emailHelp" v-if="tip" class="form-text text-muted">{{ tip }}</small>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import LabelMaterial from '../Fields/LabelMaterial'
export default {
    components: {LabelMaterial},
    mixins: [AbstractField],
}
</script>
