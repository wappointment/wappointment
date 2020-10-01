<template>                                                                       
    <div class="addon addon-active bundle" :class="{registered: isRegistered}">
        <div class="d-flex addon-header align-items-center">
            <img :src="cdnUrl+'/images/addon-'+addon.key+'.svg'" class="img-fluid m-auto"/>
        </div>
        <div class="p-4">
            <h2 class="pb-4 m-auto">{{ addon.options.name }}</h2>
            <div v-if="!isRegistered" class="addon-desc text-muted mb-0" v-html="addon.options.description"></div>
            <div v-if="!isRegistered" class="d-flex justify-content-between align-items-center">
                <div>
                    <a class="btn btn-outline-primary" target="_blank" :href="learnAddonUrl">Learn more</a>
                </div>
            </div>

            <div v-else class="mt-auto">
                <div class="d-flex align-items-center">
                    <span class="indicator"></span>
                    <span>
                        Licence <strong class="text-success">active</strong> until: <u class="small">{{ addon.expires_at }}</u>
                    </span>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import Addon from './Addon'
export default {
    extends: Addon,
}
</script>
<style>
.addons .addon.bundle .addon-header {
    background: linear-gradient(130deg,#d09d04,#e7cf27);
}
.addons .addon.bundle.registered .addon-header {
    padding: 0;
}
.addon.bundle.registered .addon-header img {
    max-width: 90px;
}

.addon.bundle.registered .content-addon {
    padding: .4rem;
    text-align: left;
}
.addon.bundle.registered h2 {
    font-size: 1.4rem;
    text-align: left;
    padding: 0 !important;
}

@media (min-width:768px) {
    .addons .bundle .addon-header{
        border-radius: 1rem 0 0 1rem;
        max-height: 320px;
    }
    .addons .addon.bundle {
        max-width: 100%;
        display: flex !important;
    }
    
}

</style>