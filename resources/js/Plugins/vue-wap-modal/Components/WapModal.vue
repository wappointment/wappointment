<template>
    <div @click.self="hideModal" class="wapmodal" :class="{'wapmodal-show': show }">
        <div class="loader-wrap d-flex align-items-center" v-if="loader">
            <WLoader></WLoader>
        </div>
        <div v-else class="wapmodal-content" :class="[screenshot ? 'screenshot':'standard', noscroll ? ' noscroll':'', large ? ' large':'']">
            <div class="wapmodal-header d-flex justify-content-between align-items-center">
                <slot name="title"></slot>
                <span @click.prevent="hideModal" class="close"></span>
            </div>
            <div class="wapmodal-body" :class="classExtra">
                <div class="wapmodal-body-wrapper">
                    <slot></slot>
                    <div v-if="prompt">
                        <div class="d-flex justify-content-center" >
                            <div class="w-100 mr-4">
                                <button class="btn btn-secondary btn-block btn-lg" @click="canceled">{{ labelCancel }}</button>
                            </div>
                            <div class="w-100">
                                <button class="btn btn-primary btn-block btn-lg m-0" @click="confirmed">{{ labelConfirm }}</button>
                                <div v-if="rememberIsOn" class="form-check form-check-inline small">
                                    <input class="form-check-input" type="checkbox" id="remembersetting" v-model="remember">
                                    <label class="form-check-label" for="remembersetting">Remember setting</label>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
  props: {
    show: {
        type: Boolean,
        default: false
    },
    loader: {
        type: Boolean,
        default: false
    },
    screenshot: {
        type: Boolean,
        default: false
    },
    prompt: {
        type: Boolean,
        default: false
    },
    noscroll: {
        type: Boolean,
        default: false
    },
    large: {
        type: Boolean,
        default: false
    },
    options: {
        type: Object,
        default: null
    },
    classExtra: {
        type:String,
        default: ''
    }
  },
  data: () => ({
    remember: true,
  }),
  computed:{
      labelCancel(){
          return this.options.cancel !== undefined ? this.options.cancel:'Cancel'
      },
      labelConfirm(){
          return this.options.confirm !== undefined ? this.options.confirm:'Confirm'
      },
      rememberIsOn(){
          return this.options.remember !== undefined
      }
  },
  methods: {
    confirmed(){
        this.$emit('confirmed', this.remember)
        this.$emit('hide')
    },
    canceled(){
        this.$emit('canceled')
        this.$emit('hide')
    },
    hideModal(){
        this.$emit('canceled')
        this.$emit('hide')
    },
  }  
}
</script>
<style>
.loader-wrap{
    height:100%;
}

.wapmodal-content .close {
  width: 32px;
  height: 32px;
  opacity: .3;
  cursor:pointer;
}
.wapmodal-content .close:hover {
  opacity: 1;
}
.wapmodal-content .close:before, .wapmodal-content .close:after {
  content: ' ';
  height: 33px;
  width: 4px;
  position: absolute;
  background-color: #fff;
}
.wapmodal-content .close:before {
  transform: translateX(15px) rotate(45deg);
}
.wapmodal-content .close:after {
  transform: translateX(15px) rotate(-45deg);
}

</style>

