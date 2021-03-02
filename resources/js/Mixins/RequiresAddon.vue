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
                staff:{
                    label: 'Calendars & Staff',
                },
            },
        };
    },
    methods: {
        requiresAddon(addon_key, message = ''){
            this.$WapModal().showPremium(
                message !== '' ? message:'Unlock this feature',
                '<h3>Unlock premium feature with our addon "'+this.addonsDetails[addon_key].label+'"</h3>' +
                '<p class="text-muted"> <a href="https://wappointment.com/addons" target="_blank">Get a licence</a> now for our addons</p>',
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