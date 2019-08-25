<template >
    <div class="d-flex justify-content-between">
      <div>
          <textarea v-model="value"
      :id="getFieldID(schema)"
      :class="schema.fieldClasses"
      :disabled="disabled"
      :maxlength="schema.max"
      :minlength="schema.min"
      :readonly="schema.readonly"
      :rows="schema.rows || 2"
      :name="schema.inputName" class="form-control"></textarea>
      <div class="hint">{{schema.placeholder}}</div>
      </div>
      <div>
          <wapIframe :height="200" :src="getIframeMap"></wapIframe>
      </div>
    </div>
</template>

<script>
	import { abstractField } from "vue-form-generator";
    import Iframe from "../../Components/Iframe";
	export default {
    mixins: [ abstractField ],
    components: {'wapIframe': Iframe},
    computed: {
      getIframeMap(){
          return 'https://maps.google.com/maps?width=100%&height=200&hl=en&q='+this.getEncodedAdress+'&ie=UTF8&t=&z=14&iwloc=B&output=embed'
      },
      getEncodedAdress(){
          return encodeURIComponent(this.value);
      },
    }
    };
    
</script>