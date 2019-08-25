var VueService = function () {};
import BaseService from './BaseService'
VueService.install = function (Vue, options) {
  Vue.prototype.$vueService = (endpoints) => {
        const instance = new BaseService(endpoints, options)
        return instance
    }

}
export default VueService

