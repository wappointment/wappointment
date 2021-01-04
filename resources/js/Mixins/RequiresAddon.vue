<script>

export default {

    data() {
        return {
            addonsDetails: {
                services:{
                    label: 'Services Suites',
                },
                woo:{
                    label: 'Appointments for WooCommerce',
                },
                twilio:{
                    label: 'Twilio SMS',
                },
            },
        };
    },
    methods: {
        requiresAddon(addon_key){
            this.$WapModal().showPremium(
                `You need the addon ${this.addonsDetails[addon_key].label} in order to access that feature`,
                `Register your site on wappointment.com and get 50% off on all of our products (limited time offer)`,
            )
        },
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