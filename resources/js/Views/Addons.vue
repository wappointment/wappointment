<template>
  <div class="px-4 pb-2">
    <WPScreenOptions>
      <div class="d-flex mb-2">
                <label class="form-check-label w-100" for="allow-wappointment">
                  <div class="d-flex align-items-center" v-if="dataLoaded">
                    <input type="checkbox" v-model="viewData.wappointment_allowed" id="allow-wappointment" @change="changedWappointmentAllowed()">
                    <div>Allow connection to wappointment.com</div>
                  </div>
                </label>
            </div>
    </WPScreenOptions>

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
      
    <div  v-if="dataLoaded">
      
      <div class="addons d-flex flex-wrap">
          <template v-for="(bundle,id) in getBundles"> 
            <BundlePreview 
            :apiSite="apiSite" :viewData="viewData" :idx="id" :addon="bundle" />
          </template>
      </div>
      <div class="addons d-flex flex-wrap pb-4">
        <template v-for="(addon,id) in getAddons"> 
          <AddonPreview 
          :apiSite="apiSite" :viewData="viewData" :idx="id" :addon="addon"
          @openWizardModal="openWizardModal" 
          @runInstallation="runInstallation" 
          @install="install"
          @activate="activate" 
          @deactivate="deactivate" />
        </template>
      </div>

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
import BundlePreview from '../Components/Bundle'
let services_install = window.wappointmentExtends.filter('AddonsServiceInstall', {})
import WPScreenOptions from '../WP/ScreenOptions'

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
        showSettings: false
    }),
    components: {AddonPreview, BundlePreview, AddonsWizard, WPScreenOptions},
    created(){
      this.serviceAddons = this.$vueService(new AddonsService)
      this.services_install = services_install

      if(window.apiWappointment.allowed === true) {
        return this.request(this.loadAddons, false,undefined,false,  this.loadedAddons)
      }
      
      this.authorizeAddons()
    },
    computed:{
      apiSite(){
        return window.apiWappointment.apiSite
      },
      addonWizardOn(){
        return this.addonWizard !== null
      },
      getAddons(){
        return this.viewData.addons.filter((el) => el.solutions.length == 1)
      },
      getBundles(){
        return this.viewData.addons.filter((el) => el.solutions.length > 1)
      }
    },
    methods: {
      changedWappointmentAllowed(){
        this.changed(this.viewData.wappointment_allowed, 'wappointment_allowed')
        window.apiWappointment.allowed = this.viewData.wappointment_allowed
      },

      changed(value, key) {
        this.settingSave(key, value)
      },

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
            this.request(this.loadAddons, response.remember,undefined,false,  this.loadedAddons)
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
          this.request(this.loadAddons, false,undefined,false,  this.loadedAddons)
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
          this.request(this.loadAddons, false,undefined,false,  this.loadedAddons)
        },

        successActivate(response){

          if(response.data.message !== undefined){
            this.$WapModal().notifySuccess(response.data.message)
            this.$WapModal()
              .request(this.sleep(4000))
            window.location = window.apiWappointment.base_admin + '?page=wappointment_addons'
          }else{
            this.$WapModal().notifyError(response.data)
          }
          
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
.wappointment-wrap .addons p {
    margin-bottom: .2rem;
}
</style>


