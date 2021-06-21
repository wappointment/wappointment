<template>
    <div >
        <div class="d-flex bg-secondary p-2 rounded" v-if="!isIsolated" @mouseenter="showSettingsNow" @mouseleave="delayRemove">
            <button @click="manageServices" class="btn btn-light mr-2">Manage services</button>
            <button @click="managePackages" class="btn btn-light mr-2 active">Sell packages</button>
            <transition name="fade" mode="out-in">
                <div class="d-flex">
                    <div  v-if="showSettings">
                        <div class="text-muted small">
                            Currency: <span v-if="wooAddonActive" data-tt="Configure it in WooCommerce" class="text-dark tt-danger">{{ currencyText }}</span>
                            <a v-else href="javascript:;" @click="setCurrency">{{ currencyText }}</a>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="text-muted small">Payments accepted:</div> 
                            <PaymentAllowed @clicked="clicked" @opened="opened" @closed="closed" />
                        </div>
                    </div>
                </div>
            </transition>
        </div>
        <component :is="getComponent" @isolate="isolate" class="p-2 border border-secondary" />
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
export default {
    mixins:[RequiresAddon, HasPopup, CanFormatPrice],
    components:{
        ServicesManage,
        CurrencyEditor,
        PaymentAllowed
    },
    data: () => ({
        currentView: 'services',
        showSettings: false,
        settingstimeout: false,
        isIsolated: false,
        popupOn:false
    }),
    computed:{
        getComponent(){
            return this.currentView == 'services' ? 'ServicesManage':'ServicesManage'
        },
    },
    methods: {
        updateCurrency(){
            window.location.reload()
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
        },
        manageServices(){
            this.currentView = 'services'
        },
        managePackages(){
            this.requiresAddon('packages')
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
</style>