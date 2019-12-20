<template>
    <div class="addon addon-active d-flex flex-column align-self-start" 
      :class="{registered: isRegistered, 'installed-addon': 
      isInstalled, activated: isActivated, 'coming-soon': !isPublished}">
          <h3 class="mb-3 text-center">{{ addon.options.name }}</h3>
            <div class="text-muted mb-4" v-html="addon.options.description"></div>
            <div v-if="isPublished">
                <div v-if="!isRegistered" class="mt-auto d-flex align-items-center">
                <div class="font-italic">
                    <div>Price: <strong>{{ getRealPrice(getPrice) }}€</strong></div>
                </div>
                <div class="ml-auto pl-2"> 
                    <a :href="buyAddonUrl" target="_blank" class="btn btn-outline-primary btn-block">Get it</a>
                </div>
                </div>
                <div v-else class="mt-auto">
                    <div>Licence <strong class="text-success">active</strong> until : <u class="small">{{ addon.expires_at }}</u></div>
                    <div v-if="isPlugin" class="my-2">
                    <div v-if="!isInstalled">
                        <button class="btn btn-primary" @click="install">Install</button>
                    </div>
                    <div v-else>
                        
                        <button v-if="!isActivated" class="btn btn-primary" @click="activate">Activate</button>
                        <button v-else class="btn btn-secondary btn-sm" @click="deactivate">Deactivate</button>
                        
                        <button v-if="requireSetup" class="btn btn-sm" :class="['btn-primary']" @click="runInstallation">
                            <span class="dashicons dashicons-admin-generic"></span> Run Installation
                        </button>
                        <button v-if="!requireSetup && hasWizard" class="btn btn-sm" 
                        :class="[wizardHasBeenRanAlready?'btn-secondary':'btn-primary']" @click="openWizardModal">
                            <span class="dashicons dashicons-admin-generic"></span> Run Wizard
                        </button>
                    </div>
                    </div>
                    
                </div>
            </div>
            <div v-else>
                <hr class="mt-0 mb-4">
                <SubscribeNewsletter v-if="!isActivated" :list="addon.key" :defaultEmail="viewData.admin_email" :statuses="viewData.statuses"
                 @updatedStatuses="updatedStatuses">
                    <p class="h6 font-italic">Want to know when it's out?</p>
                </SubscribeNewsletter>
            </div>
    </div>
    
</template>

<script>
import HelpersPackages from '../Helpers/Packages'

import SubscribeNewsletter from '../Wappointment/SubscribeNewsletter'
export default {
    components: {SubscribeNewsletter},
    mixins: [HelpersPackages],
    props: ['addon', 'viewData', 'apiSite'],
    computed: {
        requireSetup(){
            return this.isActivated && this.addon.initial_install!==undefined && this.addon.initial_install
        },
        hasWizard(){
            return this.isActivated && this.addon.instructions!==undefined && this.addon.instructions.length > 0
        },
        wizardHasBeenRanAlready(){
            return this.addon.initial_wizard || this.addon.settingKey === undefined //doesnt have settings
        },
        buyAddonUrl(){
         return this.apiSite + '/addons?addon=' + this.addon.key +'&origin=' + encodeURIComponent(window.location.href)
       },
       isActivated(){
          return this.isRegistered && (this.addon.solutions.length > 1 || this.addon.activated)
        },
        isInstalled(){
          return this.isRegistered && (this.addon.solutions.length > 1 || this.addon.installed)
        },
        isPublished(){
            return this.addon.status > 0
        },
        isRegistered(){
          return this.addon.expires_at !== undefined
        },
        isPlugin(){
          return this.addon.plugin
        },
        getPrice(){
            return this.addon.options.prices.find(element => element.number == 1).price
        }
    },
    methods: {
        runInstallation(){
            this.$emit('runInstallation', this.addon)
        },
        openWizardModal(){
            this.$emit('openWizardModal', this.addon)
        },
        updatedStatuses(statuses){
            this.$emit('updatedStatuses', statuses)
        },
        install(){
            this.$emit('install', this.addon)
        },
        activate(){
            this.$emit('activate', this.addon)
        },
        deactivate(){
            this.$emit('deactivate', this.addon)
        }
    }
}
</script>