<script>
export default {
    computed:{
        toWappointment(){
            return apiWappointment.apiSite
        }
    },
    methods:{
        toAddonsPage(campaign){
            return this.toWappointment+'/addons?utm_source=plugin&utm_medium=link&utm_campaign='+campaign
        },
        wappoVersionImage(version, img){
            return window.apiWappointment.apiSite +'/images/v'+version+'/'+img
        },
    },
}
</script>
