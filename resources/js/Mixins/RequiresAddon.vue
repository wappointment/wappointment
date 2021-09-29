<script>

export default {

    data() {
        return {
            addonsDetails: {
                services:{
                    label: 'Services Suites',
                },
                woocommerce:{
                    label: 'Appointments for WooCommerce',
                },
                twilio:{
                    label: 'Twilio SMS',
                },
                staff:{
                    label: 'Calendars & Staff',
                },
                packages:{
                    label: 'Packages',
                },
                stripe:{
                    label: 'Stripe payments',
                },
                paypal:{
                    label: 'Paypal payments',
                },
            },
        };
    },
    methods: {
        requiresAddon(addon_key, message = ''){
            this.$WapModal().showPremium(
                message !== '' ? message:'Unlock this feature',
                '<h3>Unlock premium feature with our addon "'+this.addonsDetails[addon_key].label+'"</h3>',
            ).then((result) => {
                if(result === true){
                    window.open( apiWappointment.apiSite+'/addons', "_blank");
                } 
            })
        },
        runDbUpdate(){
            this.$WapModal().notifyError('Run database updates first')
        }
    }
};
</script>
<style >
.wapmodal .premium .wapmodal-header {
    padding: 2px 32px;
    background-color: #e68557;
    color: #4d4d4d;
}
</style>