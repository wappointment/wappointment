<template>
  <div class="px-4 py-2">
    <div class="d-flex align-items-center">
      <h1 class="m-2">Addons</h1>
      <div v-if="dataLoaded">
        <button class="btn btn-secondary" @click="openModal">
          <div class="d-flex align-items-center">
            <div><span class="dashicons dashicons-admin-network"></span></div>
            <div class="ml-2">Enter licence key</div>
          </div>
        </button>
        <a href="javascript:;" v-if="debugIsOn" @click="checkLicence">checkLicence</a>
      </div>
    </div>
    <div class="addons d-flex flex-wrap" v-if="dataLoaded">
      <template v-for="(addon,id) in viewData.addons"> 
        <AddonPreview 
        :apiSite="apiSite" :viewData="viewData" :addon="addon"
        @openWizardModal="openWizardModal" 
        @runInstallation="runInstallation" 
        @install="install"
        @activate="activate" 
        @deactivate="deactivate" />
      </template>
      
    </div>
    <div v-else>
      <div v-if="cantShowAddons" class="text-muted text-center bg-light p-4">
        <p>The addons list cannot be loaded unless you authorize the connection to <a href="https://wappointment.com/">Wappointment.com</a></p>
        <button class="btn btn-secondary" @click="authorizeAddons"><span class="dashicons dashicons-admin-plugins"></span> Show Addons</button>
      </div>
    </div>
    
    <WapModal v-if="showModal" :show="showModal" @hide="hideModal" large noscroll>

        
        <h4 slot="title" class="modal-title">{{ addonWizardOn ? 'Wizard Setup: ' + addonWizard.options.name:'Enter licence key'}}</h4>
        <AddonsWizard :addon="addonWizard" @closeWizard="hideModal" v-if="addonWizardOn"/>
        <div v-else>
          <div class="input-group">
            <input type="text" placeholder="Enter licence key" v-model="productKey" class="form-control form-control-lg">
            <button class="btn btn-primary" :class="{disabled: this.productKey==''}" @click="register">Register</button>
          </div>
          <p class="m-0 text-muted">Your licence key is given to you when you complete your order on <a href="https://wappointment.com/addons">Wappointment.com</a></p>
        </div>
    </WapModal>
  </div>
</template>

<script>
import AddonsService from '../Services/V1/Addons'

import abstractView from './Abstract'
import AddonsWizard from '../Addons/Wizard'
import AddonPreview from '../Components/Addon'
let services_install = window.wappointmentExtends.filter('AddonsServiceInstall', {})
export default {
    extends: abstractView,
    data: () => ({
        serviceAddons: null,
        paramsBase: {},
        productKey: '',
        showModal: false,
        cantShowAddons: false,
        addonWizard: null,
        services_install: {},
        currentServiceAddon:null,
    }),
    components: {AddonPreview, AddonsWizard},
    created(){
      this.serviceAddons = this.$vueService(new AddonsService)
      this.services_install = services_install

      if(window.apiWappointment.allowed === true) {
        return this.request(this.loadAddons, false, this.loadedAddons)
      }
      
      this.authorizeAddons()
    },
    computed:{
      apiSite(){
        return window.apiWappointment.apiSite
      },
      addonWizardOn(){
        return this.addonWizard !== null
      }
    },
    methods: {
      runInstallation(addon){
        let solution_key = addon.solutions[0].namekey.replace('-','_')
        if(this.services_install[solution_key] !== undefined){
          this.currentServiceAddon = this.$vueService(this.services_install[solution_key]) 
          this.request(this.installAddon, false, undefined,false, this.initialSetupSuccess)
        }
      },
      initialSetupSuccess(e){
        this.successActivate(e)
      },
      async installAddon(){
          return await this.currentServiceAddon.call('install')
        },
      openWizardModal(addon){
        this.addonWizard = addon
        this.openModal()
      },
      openModal(){
        this.showModal = true
      },
      
      authorizeAddons(){
         //ask for connection confirmation
        this.$WapModal().confirm({
          title: 'Confirm connection to wappointment.com?',
          content: `<div class="small font-italic">We need to connect to 
          <a href="https://wappointment.com" target="_blank" title="Wappointment\'s website ">wappointment.com</a></div>`,
          remember: true
        }).then((response) => {

            if(response === false){
                this.cantShowAddons = true
                return
            }
            if(response.remember) {
              window.apiWappointment.allowed = true
            }
            this.request(this.loadAddons, response.remember, this.loadedAddons)
        })  
      },
       
        updatedStatuses(statuses){
          this.viewData.statuses = statuses
        },
        
        checkLicence(){
          this.$WapModal().request(this.checkLicenceRequest()).then(this.successInstalled).catch(this.failedCheckRequest)
            //this.request(this.checkLicenceRequest, false, this.loadedAddons)
        },
        failedCheckRequest(error){
          this.failedRequest(error)
          this.request(this.loadAddons, false, this.loadedAddons)
        },
 
        async checkLicenceRequest() {
            return await this.serviceAddons.call('check')
        }, 

        hideModal(){
            this.showModal = false
            this.addonWizard = null
        },

        
        async loadAddons(remember = false){
          return await this.serviceAddons.call('get', {remember: remember})
        },

        loadedAddons(response){
          this.viewData = response.data
        },
        install(addon){
          this.$WapModal().request(this.installAddonRequest(addon)).then(this.successInstalled).catch(this.failedRequest)
        },
        async installAddonRequest(addon) {
            return await this.serviceAddons.call('install', { addon: addon })
        }, 
        successInstalled(response){

          this.$WapModal().notifySuccess(response.data.message)
          this.request(this.loadAddons, false, this.loadedAddons)
        },
        successActivate(response){
          this.$WapModal().notifySuccess(response.data.message)
          this.$WapModal()
            .request(this.sleep(4000))
          window.location = window.apiWappointment.base_admin + '?page=wappointment_addons'
        },
        activate(addon){
          this.$WapModal().request(this.activateAddonRequest(addon)).then(this.successActivate).catch(this.failedRequest)
        },
        async activateAddonRequest(addon) {
            return await this.serviceAddons.call('activate', { addon: addon })
        }, 

        deactivate(addon){
          this.$WapModal().request(this.deactivateAddonRequest(addon)).then(this.successInstalled).catch(this.failedRequest)
        },
        async deactivateAddonRequest(addon) {
            return await this.serviceAddons.call('deactivate', { addon: addon })
        }, 

        register(){
          if(this.productKey == '') return
          this.$WapModal().request(this.registerWappointmentRequest()).then(this.successRequestAlt).catch(this.failedRequest)
        },

        successRequestAlt(response) {
          if(response.data.message!==undefined) {
            this.hideModal()
            this.viewData.addons = response.data.addons
            return this.$WapModal().notifySuccess(response.data.message)
          }
        },

        async registerWappointmentRequest() {
            return await this.serviceAddons.call('register', { pkey: this.productKey })
        }, 

    }  
}
</script>
<style>

.addons .addon {
    border-radius: 1rem;
    background-color: #fcfcfc;
    border: 1px solid #f2f2f2;
    margin: 1rem;
    
    max-width: 320px;
}

.addons .addon.addon-active{
    cursor: pointer;
    transition: all .3s ease-in-out;
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(0,0,0,.1);
}
.addons .addon.addon-active:hover{
    transform: scale(1.02);
    box-shadow: 0 .4rem 1rem 0 rgba(0,0,0,.1);
}
.addon hr{
  width: 100%;
  border-top: 2px solid rgba(210, 210, 210, 0.4);
}
.addons .addon::before {
    height: 1rem;
    width: 1rem;
    content: "";
    border-radius: 2rem;
    position: absolute;
    right: 12px;
    top: 12px;
}

.addons .addon.registered{
  border: 1px solid #6664cb;
}

.addons .addon.addon-active.coming-soon {
    border: 2px dashed #cacaca;
    background-color: #fbfbfb;
}


.addon.coming-soon .addon-header {
    background-color:#777699;
}
.addons .addon.registered.installed-addon.activated{
  border: 1px solid #64cb86;
}

.addons .addon.registered.installed-addon::before {
    border: 2px solid #6ed52d;
}
.addons .addon.registered.installed-addon.activated::before {
    background-color: #72ea9a;
}

.wappointment-wrap .addons p {
    margin-bottom: .2rem;
}
.addon ul li{
    padding-left: 0;
    list-style: none;
}
        
.addon ul li{
    list-style-position: outside;
    margin-left: 1.6rem;
    position: relative;
    font-size: .9rem;
}
        
.addon ul li::before {
    font-family: dashicons;
    color: #6664cb;
    content: "\f147";
    font-weight: 900;
    margin-right:.5rem !important;
    position: absolute;
    left: -1.4rem;
}
.addon h2{
    color: #fff;
    font-size: 1.8rem;
}
.addon .addon-header{
    padding: 1rem 2rem;
    background-color: #8684d9;
    color: #fff;
    border-radius: 1rem 1rem 0 0;
    height: 100px;
}
.addon .addon-desc{
  padding: 2rem;
}
</style>


