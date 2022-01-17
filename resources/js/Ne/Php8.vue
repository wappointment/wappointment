<template>
    <div>
        <div class="wappo-db-update">
            <WPNotice>
                <div>Wappointment is not yet compatible with PHP 8 yet.</div>
                <div>You can install our PHP 8 beta version following this guide:</div>
                <a href="https://wappointment.com/docs/installing-php8-version/" target="_blank" class="btn btn-primary">Install Wappointment for PHP 8</a>
            </WPNotice>
        </div>
    </div>
</template>
<script>
import WPNotice from '../WP/Notice'
export default {
    components: {WPNotice},
}
</script>
<style>
.wappo-db-update .sub{
    font-size: .9rem;
}
</style>
