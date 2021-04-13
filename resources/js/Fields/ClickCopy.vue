<template>
    <div>
        <transition name="fade" mode="out-in">
            <div v-if="show" class="text-success">Copied to clipboard</div>
        </transition>
        <div class="input-group-sm d-flex">
            <input ref="inputctcp" type="text" class="form-control disabled" :value="value" @click="copyToClipboard(this)" readonly>
            <div class="input-group-append">
                <button class="btn btn-secondary" type="button" @click="copyToClipboard">Copy</button>
            </div>
        </div>
    </div>
    
</template>
<script>
import copy from 'copy-to-clipboard'
export default {
    props:['value'],
    data: () => ({
        show: false,
    }),
    methods: {
      copyToClipboard(input = false){
          this.$refs.inputctcp.select()
          if(copy(this.value)) {
              this.show = true
              setTimeout(this.hide,200)
          }
      } ,
      hide(){
          this.show = false
      }
    }
}
</script>

