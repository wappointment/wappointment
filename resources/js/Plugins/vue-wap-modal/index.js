import './styles.css';
import WapModal from './Components/WapModal';
import WapNotify from './Components/WapNotify';
import WapModalContainer from './Components/WapModalContainer';

var VueWapModal = function () {};
VueWapModal.install = function (Vue, options) {
  
  Vue.component('WapModal', WapModal)
  Vue.component('WapNotify', WapNotify)
  Vue.component('WapModalContainer', WapModalContainer)

  Vue.prototype.$WapModalContainerInstance = null
  //modalOn
  Vue.prototype.$WapModalBoot = function (methodOptions) {

    var ComponentClass = Vue.extend(WapModalContainer)
    Vue.prototype.$WapModalContainerInstance = new ComponentClass()

    Vue.prototype.$WapModalContainerInstance.$mount()

    let idC = 'WapModalContainer'
    let modalContainer = document.getElementById(idC)

    if(modalContainer === null){
      modalContainer = document.createElement('div')
      modalContainer.setAttribute('id', idC)
      modalContainer.setAttribute('class', 'wappointment-wrap')
      modalContainer = document.body.appendChild(modalContainer)
    }
    modalContainer.appendChild(Vue.prototype.$WapModalContainerInstance.$el) 
    
  }

  Vue.prototype.$WapModalOn = function (methodOptions) {

    if(Vue.prototype.$WapModalContainerInstance === null) {
      Vue.prototype.$WapModalBoot()
    }
    Vue.prototype.$WapModalContainerInstance.showModal(methodOptions.title, methodOptions.content)

  }

  Vue.prototype.$WapModal = function (methodOptions) {

    if(Vue.prototype.$WapModalContainerInstance === null) {
      Vue.prototype.$WapModalBoot()
    }
    return Vue.prototype.$WapModalContainerInstance

  }

  /* Vue.prototype.$WapModalOff = function () {
    Vue.prototype.$WapModalContainerInstance.hideModal()
  } */
}
export default VueWapModal
