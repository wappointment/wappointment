<template>
    <div @click.self="hideModal" class="wapmodal" :class="{'wapmodal-show': show }">
        <div class="loader-wrap d-flex align-items-center" v-if="loader">
            <WLoader />
        </div>
        <div v-else class="wapmodal-content" :class="generateClasses">
            <div class="wapmodal-header d-flex justify-content-between align-items-center">
                <slot name="title"></slot>
                <span @click.prevent="hideModal" class="close"></span>
            </div>
            <div class="wapmodal-body" :class="classExtra">
                <div class="wapmodal-body-wrapper">
                    <slot></slot>
                    <div v-if="isPremium">
                        <div class="d-flex justify-content-center" >
                            <div class="w-100 mr-4">
                                <button class="btn btn-secondary btn-block btn-lg" @click="canceled">{{ labelCancel }}</button>
                            </div>
                            <div class="w-100">
                                <button class="btn btn-primary btn-block btn-lg m-0" @click="confirmed">{{ options.premiumGetDiscount }}</button>
                            </div> 
                        </div>
                    </div>
                    <div v-if="prompt">
                        <div class="d-flex justify-content-center" >
                            <div class="w-100 mr-4">
                                <button class="btn btn-secondary btn-block btn-lg" @click="canceled">{{ labelCancel }}</button>
                            </div>
                            <div class="w-100">
                                <button class="btn btn-primary btn-block btn-lg m-0" @click="confirmed">{{ labelConfirm }}</button>
                                <div v-if="rememberIsOn" class="form-check form-check-inline small">
                                    <input class="form-check-input" type="checkbox" id="remembersetting" v-model="remember">
                                    <label class="form-check-label" for="remembersetting">{{ getRememberLabel }}</label>
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
    right: {
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
    marge: {
        type: Boolean,
        default: false
    },
    options: {
        type: Object,
    },
    classExtra: {
        type:String,
        default: ''
    },
  },
  data: () => ({
    remember: true,
  }),
  created(){
      if(this.show === true){
          document.body.classList.add('wappo-popup')
      }
  },
  destroyed(){
      if(document.getElementsByClassName('wapmodal').length === 0){
          document.body.classList.remove('wappo-popup')
      }
      
  },
  computed:{
      getRememberLabel(){
          return [undefined,false].indexOf(this.options.rememberLabel) === -1 ? this.options.rememberLabel:this.get_i18n( 'remember', 'common')
      },
      isPremium(){
          return this.options!== undefined && this.options.classes !== undefined && this.options.classes.indexOf('premium') !== -1
      },
      labelCancel(){
          return this.options.cancel !== undefined ? this.options.cancel:this.get_i18n( 'back', 'common')
      },
      labelConfirm(){
          return this.options.confirm !== undefined ? this.options.confirm:this.get_i18n( 'confirm', 'common')
      },
      rememberIsOn(){
          return this.options.remember !== undefined
      },
      generateClasses(){
        let obj = {}
        let keys = ['marge', 'right', 'noscroll', 'large']

        for (const namekey of keys) {
            if(this[namekey] === true){
                obj[namekey] = true
            }
        }

        if(this.options !== undefined && this.options.classes !== undefined && this.options.classes.length > 0){
            for (const classname of this.options.classes) {
                obj[classname] = true
            }
        }

        if(this.screenshot){
            obj.screenshot = true
        }else{
            obj.standard = true
        }


        return obj
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

.wapmodal-content .wapmodal-header .close {
  width: 32px;
  height: 32px;
  opacity: .3;
  cursor:pointer;
}
.wapmodal-content .wapmodal-header .close:hover {
  opacity: 1;
}
.wapmodal-content .wapmodal-header .close:before, .wapmodal-content .wapmodal-header .close:after {
  content: ' ';
  height: 33px;
  width: 4px;
  position: absolute;
  background-color: #484848;
}
.wapmodal-content .wapmodal-header .close:before {
  transform: translateX(15px) rotate(45deg);
}
.wapmodal-content .wapmodal-header .close:after {
  transform: translateX(15px) rotate(-45deg);
}

</style>

