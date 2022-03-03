<template>
    <div class="my-4 mx-2">
        <p class="h6 text-muted">{{get_i18n('cals_connect_external_export', 'settings') }}</p>
        <div class="text-danger text-left small">The <strong><a :href="addonLink" target="_blank">Calendar Sync</a></strong> addon is required to use that feature</div>

    </div>
</template>

<script>
import RequestMaker from '../Modules/RequestMaker'
export default {
    props: ['calendar'],
    mixins: [ RequestMaker],
    computed:{
        addonLink(){
            return apiWappointment.apiSite+'/addons'
        },
    }
}
</script>
