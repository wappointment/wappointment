<template>
    <div>
      <div v-if="hasActiveNotifications" class="notify-wrapper">
        <template v-for="notification in notifications">
          <WapNotify 
          v-if="isActive(notification)" 
          :notification="notification" 
          @expired="notifyHasExpired"><template v-if="notification.slots.length > 0">
            <div v-for="line in notification.slots">{{ line }}</div>
            </template></WapNotify>
        </template>
      </div>
      <div v-if="isReloading" class="wapmodal wapmodal-show d-flex align-items-center justify-content-center">
          <div>
            <WLoader />
            <div class="text-white text-center">Page is reloading</div>
          </div>
      </div>
      <WapModal v-if="show" :show="show" 
      :loader="loader" :screenshot="screenshot" :options="options" :prompt="prompt" 
      @hide="hideModal"  @canceled="canceled" @confirmed="confirmed">
            <h4 class="modal-title" slot="title">{{ title }}</h4>
            <div v-if="content" v-html="cleanHtml"></div>
      </WapModal>
    </div>
</template>

<script>
import makeId from '../../../Standalone/makeid.js'
export default {

 data() {
    return {
      show: false,
      title: '',
      content: '',
      screenshot: false,
      classes: {},
      options: {},
      prompt: false,
      pResolve: null,
      pReject: null,
      loader: false,
      finalCallback: null,
      notifications: [],
      cleared: [],
      list_body_elements:[],
      isReloading:false
    };
  },
  watch: {

   cleared(val){
     if(this.notifications.length > 0 && this.notifications.length == val.length){
       this.notifications = []
      this.cleared = []
     }
   },

   show(val){
     for (let i = 0; i < this.list_body_elements.length; i++) {
       this.list_body_elements[i].style.filter = (val === true) ? 'blur(1px)':''
       //wp hack
       let is_wpbackend = document.getElementsByClassName('wp-toolbar')

       if(is_wpbackend.length > 0){
         this.list_body_elements[i].style.paddingTop = (val === true) ?  '32px':''
         this.list_body_elements[i].style.marginTop =  (val === true) ?  '-32px':''
       }
     }
   }

  },
  created(){
    for (const element of document.body.childNodes) {
      if(element.innerHTML !== undefined && ['style','script'].indexOf(element.nodeName.toLowerCase()) === -1){
        this.list_body_elements.push(element)
      }
    }
  },

  computed: {
    cleanHtml(){
      return this.$sanitize(this.content)
    },
    hasActiveNotifications(){
      return this.notifications.length > 0
    }
  },

  methods: {
    reload(){
      this.isReloading = true
      window.location.reload()
    },
    isActive(notif){
      return this.cleared.indexOf(notif.id) == -1 
    },

    notifyHasExpired(id){
      this.cleared.push(id)
    },

    notifySuccess(title,duration){
      this.queueNotification(title, 'success',duration)
    },

    notifyError(title, slots=[]){
      this.queueNotification(title, 'error', 5, slots)
    },

    queueNotification(title, type = 'success', duration = 5, slots=[]){
      this.notifications.push({
        title: title,
        type: type,
        id: makeId(),
        duration: duration,
        slots: slots
      })
    },

    showModal(title, content, screenshot = false, options = false){
        this.show = true
        this.prompt = false
        this.loader = false
        this.options = options
        for (const property in options) {
          if(['prompt'].indexOf(property) !== -1){
            this[property] = options[property]
          }
        }

        this.title = title
        this.content = content
        this.screenshot = screenshot
        //blur bg
        document.getElementById('wpwrap').style.filter = 'blur(2px)'
    },

    showPremium(title, content, screenshot = false){
        
        this.showModal(title, content, screenshot)
        this.options = {classes:['premium']}
        this.options.premiumGetDiscount = this.get_i18n( 'get_addon', 'common')

        return new Promise(this.promiseCallback)
    },

    request(promise, optionalCallback = null){
      if(this.$passedStore !== undefined) {
        this.$passedStore.commit('clearErrors')
      }
      this.show = true
      this.loader = true  
      this.finalCallback = optionalCallback
      promise.then(
        this.successRequest
      ).catch(
        this.failRequest
      )
      
      return new Promise(this.promiseCallback)
    },

    successRequest(s){
      this.show = false
      this.pResolve(s)
      if(this.finalCallback!== null){
        this.finalCallback(s)
      }
    },

    failRequest(e){
      this.show = false
      this.pReject(e)
      if(this.finalCallback!== null){
        this.finalCallback(e)
      }
    },

    confirm(options){
      this.options = options
      this.title = options.title
      if(options.content!==undefined){
        this.content = options.content
      }
      this.loader = false
      this.show = true
      this.prompt = true
      
      return new Promise(this.promiseCallback)
    },

    promiseCallback(resolve, reject) {
      this.pResolve = resolve
      this.pReject = reject
    },

    canceled(){
      if(this.pResolve !== null) {
        this.pResolve(false)
      }
    },

    confirmed(remember){
      let response = this.options.remember === undefined ? true: {result: true, remember: remember} 
      this.pResolve(response)
    },

    hideModal(){
        this.show = false
        this.loader = false
        this.title = ''
        this.content = ''
        this.options = {}
    },
  }  
}
</script>
<style>
.notify-wrapper {
  position: fixed;
  top: 3rem;
  right: 0;
  z-index: 99999 !important;
}
.wappo-popup #adminmenuwrap{
  z-index: 1;
}
@media (max-width: 470px) { 
    .notify-wrapper {
        width: 100%;
    }
}
</style>
