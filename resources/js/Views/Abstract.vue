<script>

import SettingsSave from '../Modules/SettingsSave'
import Helpers from '../Modules/Helpers'
import ViewData from '../Modules/ViewData'
import AppService from '../Services/V1/App'
import RequiresAddon from '../Mixins/RequiresAddon'

export default {
  mixins: [ViewData, SettingsSave, Helpers, RequiresAddon],
  data: () => ({
    service: null,
    errorMessages:[],
  }),
  created(){
    this.service = this.$vueService(new AppService)
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

      beforeRequest(){
        this.errorMessages = []
      },
  
    }
}
</script>