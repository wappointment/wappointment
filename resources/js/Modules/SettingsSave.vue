<script>

import SettingService from '../Services/Setting'
import SettingStaffService from '../Services/SettingStaff'
import RequestMaker from './RequestMaker'

export default {
  mixins: [RequestMaker],
  data: () => ({
    serviceSetting: null,
    serviceSettingStaff: null
  }),

  created(){
    this.serviceSetting = this.$vueService(new SettingService)
    this.serviceSettingStaff = this.$vueService(new SettingStaffService)
  },
  methods: {

      settingSaveMany(settings){
        this.requestRunner({settings: settings})
      },
      settingSave(setting, value){
        this.requestRunner({key: setting, val: value})
      }, 
      settingStaffSave(setting, value){
        this.requestRunner({key: setting, val: value}, true)
      }, 
      requestRunner(params, staff = false){
        this.request(this.settingSaveRequest, params, undefined, staff)
      },
      async settingSaveRequest(params, staff = false) {
          if(staff) return await this.serviceSettingStaff.call('save', params) 
          return await this.serviceSetting.call('save', params) 
      },

         
    }
}
</script>