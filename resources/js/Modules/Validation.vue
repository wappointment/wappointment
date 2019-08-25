<script>
import VueFormGenerator from 'vue-form-generator'
import 'vue-form-generator/dist/vfg.css'
import ErrorList from '../Components/ErrorList' 
export default {
  components:{
      "vue-form-generator": VueFormGenerator.component,
      ErrorList
  },
  data: () => ({
    formCanBeSubmitted: false,
    errorMessages:[],
  }),
  methods: {
      validateForm() {
        this.$refs.vfgen.validate();
      },
      beforeRequest(){
        this.errorMessages = []
      },
      async load() {
        this.isPreLoading = true;
        this.mainElements = await this.service.call('list')
        this.isPreLoading = false;
        this.resetModel();
      },

      selectElement(key) {
        this.formCanBeSubmitted = true;
        this.model = this.mainElements[key];
      },

      
      onValidated(isValid, errors) {
        this.formCanBeSubmitted = isValid;
        //console.log("Validation result: ", isValid, ", Errors:", errors);
      },
  }
}
</script>
