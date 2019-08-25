<template>
    <div class="input-group-sm d-flex">
        <input ref="inputctcp" type="text" class="form-control disabled" :value="value" @click="copyToClipboard(this)" readonly>
        <div class="input-group-append">
            <button class="btn btn-secondary" type="button" @click="copyToClipboard">Copy</button>
        </div>
    </div>
</template>
<script>
import copy from 'copy-to-clipboard'
export default {
    props:['value'],
    methods: {
      copyToClipboard(input = false){
          this.$refs.inputctcp.select()
          if(copy(this.value)) this.$WapModal().notifySuccess('Copied to clipboard',1)
      } ,
    }
}
</script>

