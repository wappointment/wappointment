var VueService = function () {};
import BaseService from './BaseService'
VueService.install = function (Vue, options) {
  Vue.prototype.$vueService = (serviceClass) => {
        const instance = new BaseService(serviceClass, options)
        return instance
    }

}
export default VueService

