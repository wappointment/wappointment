<template>
    <div >
        <div class="d-flex bg-secondary p-2 rounded packlisting" v-if="!isIsolated" @mouseenter="showSettingsNow" @mouseleave="delayRemove">
            <button @click="manageServices" class="btn btn-light mr-2"  :class="{active: !packShown}">{{ get_i18n('services_manage', 'settings') }}</button>
            <button @click="managePackages" class="btn btn-light mr-2" :class="{active: packShown}">{{ get_i18n('sell_pack', 'settings') }}</button>
            <transition name="fade" mode="out-in">
                <div class="d-flex">
                    <div  v-if="showSettings">
                        <div class="d-flex align-items-center">
                            <div class="text-muted small">
                                {{ get_i18n('currency', 'settings') }} <span v-if="wooAddonActive" data-tt="Configure it in WooCommerce" class="text-dark tt-danger">{{ currencyText }}</span>
                                <a v-else href="javascript:;" @click="setCurrency">{{ currencyText }}</a>
                            </div>
                            <div v-if="!wooAddonActive" class="text-muted small ml-2">
                                {{ get_i18n('tax', 'settings') }} <a v-if="!canEditTax" href="javascript:;" @click="editTax">{{ tax }}%</a>
                                <span v-else>
                                    <input type="text" v-model="tax" size="2" @keyup.enter.prevent="saveTax" >% 
                                    <button class="btn btn-outline-primary btn-sm" @click="saveTax">{{ get_i18n('save', 'common') }}</button>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="text-muted small">{{ get_i18n('services_payments', 'settings') }}</div> 
                            <PaymentAllowed @clicked="clicked" @opened="opened" @closed="closed" />
                        </div>
                    </div>
                </div>
            </transition>
        </div>
        <component :is="getComponent" @isolate="isolate" @dataUp="dataUp" class="p-2 border border-secondary" />
        <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" noscroll>
            <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
            <CurrencyEditor v-if="showCurrency"  @close="updateCurrency" />
        </WapModal>
    </div>
</template>

<script>
import CurrencyEditor from './CurrencyEditor'
import PaymentAllowed from './PaymentAllowed'
import RequiresAddon from '../Mixins/RequiresAddon'
import ServicesManage from './ServicesManage'
import HasPopup from '../Mixins/HasPopup'
import CanFormatPrice from '../Mixins/CanFormatPrice'
import SettingsSave from '../Modules/SettingsSave'
let componentsLoaded = window.wappointmentExtends.filter('ServicesAndPackagesViews', { ServicesManage, CurrencyEditor, PaymentAllowed })
export default {
    mixins:[RequiresAddon, HasPopup, CanFormatPrice, SettingsSave],
    components: componentsLoaded,
    data: () => ({
        currentView: 'services',
        showSettings: false,
        settingstimeout: false,
        isIsolated: false,
        popupOn:false,
        tax: 0,
        canEditTax: false,
        hasPackageView: componentsLoaded.PackagesManage !== undefined,
    }),

    computed:{
        getComponent(){
            return this.currentView == 'services' ? 'ServicesManage':'PackagesManage'
        },
        packShown(){
            return this.currentView == 'packages'
        }
    },
    methods: {
        dataUp(data){
            this.tax = data.tax
        },
        saveTax(){
            this.settingSave('tax', this.tax)
        },
        editTax(){
            this.canEditTax = true
        },
        updateCurrency(){
            this.$WapModal().reload()
        },
        isolate(value){
            this.isIsolated = value
        },
        clicked(payment){
            if(payment.key == 'stripe'){
                this.requiresAddon('stripe')
            }
            if(payment.key == 'paypal'){
                this.requiresAddon('paypal')
            }
            if(payment.key == 'woocommerce'){
                this.requiresAddon('woocommerce')
            }
        },
        closed(){
            this.removeSettings()
            this.popupOn = false
            
        },
        opened(){
            this.popupOn = true
            clearTimeout(this.settingstimeout)
        },

        showSettingsNow(){
            if(this.settingstimeout !== false){
                clearTimeout(this.settingstimeout)
                this.showSettings = false
            }
            this.showSettings=true
        },
        delayRemove(){
            if(this.popupOn === false){
                this.settingstimeout = setTimeout(this.removeSettings, 2000)
            }
            
        },
        removeSettings(){
            this.showSettings = false
            this.canEditTax = false
        },
        manageServices(){
            this.currentView = 'services'
        },
        managePackages(){
            if(!this.hasPackageView){
                return this.requiresAddon('packages')
            }
            this.currentView = 'packages'
        },
        setCurrency(){
            this.openPopup('Set currency')
            this.showCurrency = true
        },
    }
}   
</script>
<style>
.text-dark.tt-danger{
    cursor:default;
}
.packlisting .btn-light:not(:disabled):not(.disabled).active,
.packlisting .btn-light:not(:disabled):not(.disabled):active
 {
  color: #fff;
  background-color: #b5b4e6;
  border-color: #d3d9df;
}
</style>