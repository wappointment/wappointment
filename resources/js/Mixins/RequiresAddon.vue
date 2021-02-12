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
        requiresAddon(addon_key, message = ''){
            this.$WapModal().showPremium(
                message !== '' ? message:'Unlock this feature',
                '<h3>Unlock premium feature with our addon "'+this.addonsDetails[addon_key].label+'"</h3>' +
                '<p class="text-muted">Register your site on wappointment.com and get <strong> 50% off on all of our <a href="https://wappointment.com/addons" target="_blank">addons</a></strong> (limited time offer)</p>',
            ).then((result) => {
                if(result === true){
                } 
            })
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