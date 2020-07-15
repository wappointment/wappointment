<template>
    <div class="wappo-error">
        <strong>Oops, can't load the booking module</strong>
        <p>Click <a :href="getCacheSafeUrl">here to reload the page</a> and try again</p>
    </div>
</template>

<script>

import makeid from "../Standalone/makeid"

export default {
    computed: {
        getCacheSafeUrl(){
            let url = window.location.href
            url += url.indexOf('?') === -1 ? '?':'&'
            
            return url += 'wappoCacheSafe='+makeid()
        }
    }
}
</script>
<style >
.wappo-error {
    background-color:#c86b5b;
    color:#fff;
    padding: .5em;
    border-radius: .4em;
}
.wappo-error a {
    color: #33c5ff;
    text-decoration: underline;
}
</style>