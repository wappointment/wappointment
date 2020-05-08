<template>                                                                       
    <div class="addon addon-active d-flex flex-column align-self-start" 
      :class="{registered: isRegistered, 'installed-addon': 
      isInstalled, activated: isActivated, 'coming-soon': !isPublished, 'odd':!odd}">
            <div class="d-flex addon-header align-items-center">
                <img :src="'//cdn.wappointment.com/images/addon-'+addon.key+'.svg'" class="img-fluid m-auto"/>
            </div>
            <div class="content-addon">
                <h2 class="pb-4 m-auto">{{ addon.options.name }}</h2>
                <div class="addon-desc text-muted mb-0" v-html="addon.options.description"></div>
            </div>
            <div v-if="isPublished" class="footer p-4">
                <div v-if="hasGallery && !isRegistered" class="d-flex my-2">
                    <img v-for="media in addon.media" :src="media" @click="showFullScreen(media)" class="img-gallery img-fluid"/>
                </div>
                <div v-if="!isRegistered" class="d-flex justify-content-between align-items-center">
                    <div>
                        <a class="btn btn-outline-primary" :href="learnAddonUrl">Learn more</a>
                    </div>
                </div>

                <div v-else class="mt-auto">
                    <div class="d-flex align-items-center">
                        <span class="indicator"></span>
                        <span>
                            Licence <strong class="text-success">active</strong> until: <u class="small">{{ addon.expires_at }}</u>
                        </span>
                        </div>
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
                        <div v-if="hasWarning" class="text-danger">
                            {{ addon.warning }}
                        </div>
                    </div>
                    </div>
                    
                </div>
            </div>
            <div v-else class="footer p-4">
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
import AbstractListing from '../Views/AbstractListing'
import SubscribeNewsletter from '../Wappointment/SubscribeNewsletter'

export default {
    components: {SubscribeNewsletter},
    mixins: [HelpersPackages],
    props: ['addon', 'viewData', 'apiSite', 'idx'],
    computed: {
        siteUrl(){
            return window.apiWappointment.resourcesUrl  
        },
        odd(){
            return this.idx%2 == 0
        },
        requireSetup(){
            return this.isActivated && this.addon.initial_install!==undefined && this.addon.initial_install
        },
        hasWizard(){
            return this.isActivated && this.addon.instructions!==undefined && this.addon.instructions.length > 0
        },
        hasWarning(){
            return this.addon.warning !== undefined
        },
        wizardHasBeenRanAlready(){
            return this.addon.initial_wizard || this.addon.settingKey === undefined //doesnt have settings
        },
        learnAddonUrl(){
         return this.addon.options.product_page !== undefined ? this.addon.options.product_page : this.buyAddonUrl
       },
        
        buyAddonUrl(){
         return this.apiSite + '/addons'
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
        hasGallery(){
             return this.addon.media !== undefined && this.addon.media.length > 0
        },
        isPlugin(){
          return this.addon.plugin
        },
        getPriceSingle(){
            return this.getPrice(this.addon, 1)
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
        },
        showFullScreen(image){
            this.$WapModalOn({
                title: this.addon.options.name,
                content: `<img src="${image}" class="img-fluid" />`,
                screenshot:true
            })
        }
    }
}
</script>
<style>
@import '../../css/addon.css';
.addons .addon {
    border-radius: 1rem;
    background-color: #fcfcfc;
    border: 1px solid #f2f2f2;
    margin: 1rem;
    max-width: 420px;
}

.addons .addon.addon-active{
    cursor: pointer;
    transition: all .3s ease-in-out;
    box-shadow: 0 0 0 0 rgba(0,0,0,.1);
}
.addons .addon.addon-active:hover{
    box-shadow: 0 .4rem 1rem 0 rgba(0,0,0,.1);
}

.addons .addon .content-addon{
  position:relative;
}
.addons .addon .footer .indicator {
    height: .8rem;
    width: .8rem;
    display: inline-block;
    margin-right: .5rem;
    border-radius: 2rem;
}

.addons .addon.registered.installed-addon .footer .indicator {
    background-color: #66c677;
}


.addons .addon.addon-active.coming-soon {
    border: 2px dashed #cacaca;
    background-color: #fbfbfb;
}

.addons .addon{
  overflow:hidden;
}

.addons .addon .footer{
  background: #f7f7f7;
}


.addon.coming-soon .addon-header {
    background-color:#777699;
}

.addon .img-gallery {
    border: 1px solid #ccc;
    border-radius: .4em;
    cursor: pointer;
    margin-right: .2em;
}
.addon .img-gallery:hover {

    border-color: #6664cb;

}
.addon ul{
    margin-top: 1rem;
}
.addon hr{
  width: 100%;
  border-top: 2px solid rgba(210, 210, 210, 0.4);
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
    

.addon h2{
    font-size: 1.8rem;
    text-align: center;
}
.addon .btn-outline-primary {
    color: #9393c8 !important;
}
.addon .addon-header{
    padding: 1rem 2rem;
    background: linear-gradient(130deg,#a7a6cc,#bc8f9e);
    color: #fff;
    border-radius: 1rem 1rem 0 0;
    min-height: 100px;
    max-height: 220px;
}

.addon .addon-header img{
    max-width:300px;
}
.addon.odd .addon-header {
    background: linear-gradient(130deg,#bc8f9e,#feecb1);
}
.addon .content-addon{
  padding:2rem;
}

</style>