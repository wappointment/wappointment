<template>
  <div :class="labelClass">
    <label :for="id" v-if="floatLabel!=''" >{{ floatLabel }}</label>
    <slot></slot>
    <a v-if="allowBack" class="resetClass" @click="backToDefault" href="javascript:;">reset</a>
  </div>
</template>

<script>
export default {
  props: {
    for:{
      type:String
    }, 
    extraClass:{
      type:String
    },  
    allowReset:{
      type:Boolean,
      default:false
    }, 
    forceFloat:{
      type:Boolean,
      default:false
    }, 
    labelAbove:{
      type:Boolean,
      default:false
    }, 
  },
  data () {
    return {
      input: undefined,
      isFocused: false,
      value:''
    }
  },
  mounted () {
    this.input = this.$el.querySelector('input, textarea, select')
    this.input.addEventListener('input', this.valueChange)
    this.input.addEventListener('blur', this.unfocused)
    this.input.addEventListener('focus', this.focused)
    this.value = this.input.value
  },
  beforeDestroy () {
    this.input.removeEventListener('input', this.valueChange)
    this.input.removeEventListener('blur', this.unfocused)
    this.input.removeEventListener('focus', this.focused)
  },
  methods: {
    valueChange(e){
        this.value = e.target.value
    },
    focused (e) {
        this.isFocused = true
    },
    unfocused (e) {
        setTimeout(this.delayUnfocused, 400);
    },
    delayUnfocused(){
          this.isFocused = false
    },
    backToDefault(){
      this.$emit('reseted', this)
    }
  },
  computed: {
    allowBack(){
      return this.isFocused === true && this.allowReset === true
    },
    id() {
      return this.for
    },
    isActive() {

        return [undefined,''].indexOf(this.value) === -1
    },
    
    labelClass () {
      if(this.labelAbove){
        return ''
      }
      var obj = {
        'label-wrapper': true,
        'focused': this.isFocused,
        'active': this.isActive || this.forceFloat,
      }
      if(this.extraClass != undefined) obj[this.extraClass] = true
      return obj
    },
    inputType () {
      return this.input ? this.input.tagName.toLowerCase() : ''
    },
    floatLabel () {

      switch (this.inputType) {
        case 'input':
        case 'textarea':
          return this.input.placeholder
        case 'select':
          return this.input.querySelector('option[disabled][selected]').innerHTML
        default:
          return ''
      }
    }
  }
}
</script>

<style>
.label-wrapper {
    position: relative;
    width: 100%;
    overflow: hidden;
    margin-bottom: .5em !important;
}

.label-wrapper label {
    position: absolute;
    top: 12px;
    left: 12px;
    overflow: hidden;
    font-size: 16px;
    white-space: nowrap;
    pointer-events: none;
    transition: all 0.2s ease-out;
    color: #ced4da;
}
.resetClass{
    position: absolute;
    bottom: 0px;
    font-size: 12px;
    right: 3px;
}
.label-wrapper.focused label, 
.label-wrapper.active label {
    top: 0px;
    font-size: 12px;
    left: 6px;
}
.label-wrapper.focused label, 
.label-wrapper.active.focused label {
    color: #6664cb;
}
.label-wrapper.active label {
    color: #ced4da;
}
.label-wrapper input, .label-wrapper textarea{
    height: auto;
    padding-top: 1rem;
}
.wappointment-wrap .label-wrapper .form-control::placeholder,
.label-wrapper input::placeholder,
.label-wrapper textarea::placeholder,
.label-wrapper select::placeholder {
  opacity: 0;
}
</style>
