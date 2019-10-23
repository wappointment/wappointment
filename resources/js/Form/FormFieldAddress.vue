<template>
    <div class="d-flex flex-wrap flex-sm-nowrap">
      <div class="mr-2">
          <LabelMaterial>
                <textarea class="form-control" :class="{'is-invalid':hasErrors}" v-model="updatedValue"
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
      <div>
          <wapIframe :height="200" :src="getIframeMap"></wapIframe>
      </div>
    </div>

</template>

<script>
import AbstractField from './AbstractField'
import LabelMaterial from '../Fields/LabelMaterial'
import wapIframe from '../Components/Iframe'
export default {
    mixins: [AbstractField],
    components: {LabelMaterial, wapIframe},
    computed: {
      getIframeMap(){
          return 'https://maps.google.com/maps?width=100%&height=200&hl=en&q='+this.getEncodedAdress+'&ie=UTF8&t=&z=14&iwloc=B&output=embed'
      },
      getEncodedAdress(){
          return encodeURIComponent(this.updatedValue);
      },
    }
}
</script>
