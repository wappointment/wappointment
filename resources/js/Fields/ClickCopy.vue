<template>
    <div class="wrapper-click">
        <transition name="raise" >
            <div v-if="show" class="border rounded border-success text-success p-1">Copied to clipboard</div>
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
              setTimeout(this.hide,1000)
          }
          this.$emit('clicked')
      } ,
      hide(){
          this.show = false
      }
    }
}
</script>

<style>
.wrapper-click{
    position:relative;
}
.wrapper-click .text-success{
    position:absolute;
    right:0;
    top: -42px;
}


.raise-enter-active{
  transition: all .3s ease;
}

.raise-leave-active{
  transition: all .1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.raise-enter, .raise-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>

