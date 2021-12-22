<template>
    <StickyBar>
        <div class="d-block d-md-none">
            <div class="d-flex flex-wrap">
                <div class="progress align-self-center">
                    <div class="progress-bar progress-bar-striped bg-info" role="progressbar" :style="'width:'+((pstep/total)*100)+'%'">
                        {{ get_i18n( 'wizard_wizard', 'wizard') }} {{ step }} / {{ total }}
                    </div>
                </div>
                <div class="buttons align-self-center">
                    <button class="btn btn-secondary btn-xs" @click="emitBack">{{ get_i18n( 'wizard_back', 'wizard') }}</button>
                    <button class="btn btn-primary btn-xs" :class="{'disabled':!nextEn}" :disabled="!nextEn" @click="emitNext">{{ get_i18n( 'wizard_next', 'wizard') }}</button>
                </div>
            </div>
        </div>
        <div class="d-none d-md-block">
            <div class=" d-flex flex-wrap">
                <div class="progress align-self-center">
                    <div class="progress-bar progress-bar-striped bg-info" role="progressbar" :style="'width:'+((pstep/total)*100)+'%'">
                        {{ get_i18n( 'wizard_wizard', 'wizard') }} {{ step }} / {{ total }}
                    </div>
                </div>
                <div class="buttons align-self-center">
                    <button class="btn btn-secondary btn-xl" @click="emitBack">{{ get_i18n( 'wizard_back', 'wizard') }}</button>
                    <button  class="btn btn-primary btn-xl" :class="{'disabled':!nextEn}" :disabled="!nextEn" @click="emitNext">{{ get_i18n( 'wizard_next', 'wizard') }}</button>
                </div>
                <ContactButton subject="Installation Wizard" buttonClass="btn btn-secondary btn-sm" buttonLabel="Help" />
            </div>
        </div>
        
    </StickyBar>
</template>

<script>
import ContactButton from '../Wappointment/ContactButton'
export default {
  components:{ContactButton},
  props: {
      step:0,
      total:0,
      nextEnabled:  {
        type: Boolean,
        default: false
    },
  },
  data() {
      return {
          pstep: 0,
          nextEn: false
      } 
  },

  mounted(){
      this.pstep = window.prevStep
      this.nextEn = this.nextEnabled === true
      setTimeout(this.delayMounted , 50);
  },
  

  methods: {
      toggleNext(enable){
          this.nextEn = enable
      },
      delayMounted(){
        this.pstep = this.step
      },
      emitBack(){
          this.$emit('back')
      },

      emitNext(){
          if(this.nextEn === false) return 
          this.$emit('next')
      },
     
  },

}
</script>
<style>
.progress {
    width:40%;
    height: 2.2rem;
    margin-right: 2rem;
    margin-left: 1rem;
}

.bg-info {
    background-color: #d1d1d1 !important;
    color: #4d4949;
    font-size: .9rem;
    font-weight: bold;
    transition: all 0.3s ease-in;
}

@media (max-width: 769px) { 
    .progress {
        height: 1.6rem;
    }
}

</style>