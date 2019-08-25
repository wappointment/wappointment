<script>
import abstractView from './Abstract'
import topPane from '../Components/WizardTopPane'

export default {
  extends: abstractView,
  components: {
      topPane
  },

  methods: {
        setPrevStep (){
            window.prevStep = this.currentStep;
        },
        nextStep() {
            this.setPrevStep()
            this.request(this.nextStepRequest, undefined, this.redirectNext)
        },

        async nextStepRequest() {
            return await this.service.call('wizard', {step: this.currentStep+1})
        },
        
        redirectNext(){
          this.$router.push({name: 'wizard'+(this.currentStep+2)})
        },

        prevStep() {
            this.setPrevStep()
            this.request(this.prevStepRequest, undefined, this.redirectPrev)
        },

        async prevStepRequest() {
            return await this.service.call('wizard', {step: (this.currentStep-1)})
        },
        
        redirectPrev(){
          this.$router.push({name: 'wizard'+(this.currentStep)})
        }
  }  
}
</script>

