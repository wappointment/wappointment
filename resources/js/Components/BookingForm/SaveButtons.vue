
<template>
    <div class="saveButtons">
        <span class="btn-secondary btn googlecal" @click="goToUrl(saveToGoogle)">
            <FontAwesomeIcon :icon="['fab', 'google']" size="lg"/> Google
        </span>
        <span class="btn-secondary btn outlook" @click="goToUrl(saveToIcal)">
            <FontAwesomeIcon :icon="['fab', 'windows']" size="lg"/> Outlook
        </span>
        <span class="btn-secondary btn" @click="goToUrl(saveToIcal)">
            <FontAwesomeIcon :icon="['fab', 'apple']" size="lg"/> iCal
        </span>
        <span class="btn-secondary btn" @click="goToUrl(saveToYahoo)">
            <FontAwesomeIcon :icon="['fab', 'yahoo']" size="lg"/> Yahoo
        </span>
    </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core'
import {  faApple, faWindows, faYahoo, faGoogle } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faApple, faWindows, faYahoo, faGoogle)
import saveToCalendar from '../../Modules/saveToCalendar'
export default {
    mixins: [saveToCalendar],
    components:{FontAwesomeIcon},
    methods: {
        goToUrl(url){
            window.open(url)
        },
    }
}
</script>


