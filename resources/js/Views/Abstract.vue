<script>

import SettingsSave from '../Modules/SettingsSave'
import Helpers from '../Modules/Helpers'
import AppService from '../Services/V1/App'
import ViewsDataService from '../Services/V1/ViewsData'

export default {
  mixins: [SettingsSave, Helpers],
  data: () => ({
    service: null,
    serviceViewData: null,
    parentLoad:true,
    errorMessages:[],
    viewName: null,
    viewData: null
  }),


  created(){
    this.service = this.$vueService(new AppService)
    this.serviceViewData = this.$vueService(new ViewsDataService)
    this.initMethod()
  },
  computed: {
    dataLoaded(){
        return (this.viewData !== null)
    },
  },
  methods: {
      isSetupLabel(is_already_setup){
        return is_already_setup ? 'Edit' : 'Setup'
      },
      initMethod(){
        if(this.parentLoad === true) this.refreshInitValue()
      },
      refreshInitValue(){
        if(this.viewName !== null){
          this.request(this.initValueRequest, false,undefined,false,  this.loaded)
        } 
      },
      beforeRequest(){
        this.errorMessages = []
      },

      loaded(viewData){
          this.viewData = viewData.data
      },

      async initValueRequest() {
          if(this.viewName !== null) return await this.serviceViewData.call('get', {append: '/'+this.viewName})
      },
  
    }
}
</script>