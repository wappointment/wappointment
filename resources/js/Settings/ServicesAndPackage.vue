<template>
    <div >
        <div class="d-flex bg-secondary p-2 rounded" v-if="!isIsolated" @mouseenter="showSettingsNow" @mouseleave="delayRemove">
            <button @click="manageServices" class="btn btn-light mr-2">Manage services</button>
            <button @click="managePackages" class="btn btn-light mr-2 active">Sell packages</button>
            <transition name="fade" mode="out-in">
                <div class="d-flex">
                    <div class="text-muted small" v-if="showSettings">
                        <div>
                            Currency: <span v-if="wooAddonActive" data-tt="Configuration in WooCommerce" class="text-primary">{{ wooCurrencyText }}</span>
                            <a v-else href="javascript:;" @click="setCurrency">{{ currencyText }}</a>
                        </div>
                        <div class="d-flex align-items-center"><div>Payments accepted:</div> <PaymentAllowed @clicked="clicked" /></div>
                    </div>
                </div>
            </transition>
        </div>
        <component :is="getComponent" @isolate="isolate" class="p-2 border border-secondary" />
        <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" noscroll>
            <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
            <CurrencyEditor v-if="showCurrency" :currency="currencyInfo"  />
        </WapModal>
    </div>
</template>

<script>
import CurrencyEditor from './CurrencyEditor'
import PaymentAllowed from './PaymentAllowed'
import RequiresAddon from '../Mixins/RequiresAddon'
import ServicesManage from './ServicesManage'
import HasPopup from '../Mixins/HasPopup'
import HasWooVariables from '../Mixins/HasWooVariables'
export default {
    mixins:[RequiresAddon, HasPopup, HasWooVariables],
    components:{
        ServicesManage,
        CurrencyEditor,
        PaymentAllowed
    },
    data: () => ({
        currentView: 'services',
        showSettings: false,
        settingstimeout: false,
        isIsolated: false
    }),
    computed:{
        getComponent(){
            return this.currentView == 'services' ? 'ServicesManage':'ServicesManage'
        },
        currencyInfo(){
            return {
                charge: 'EUR',
                display: 'FR'
            }
        }
    },
    methods: {
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
        showSettingsNow(){
            if(this.settingstimeout !== false){
                clearTimeout(this.settingstimeout)
                this.showSettings = false
            }
            this.showSettings=true
        },
        delayRemove(){
            this.settingstimeout = setTimeout(this.removeSettings, 2000);
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