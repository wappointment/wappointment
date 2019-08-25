import Vue from 'vue'
import axios from 'axios'
import wappoExtend from './Standalone/extends.js'
if(apiWappointment.nonce !== undefined) axios.defaults.headers.common['X-WP-Nonce'] = apiWappointment.nonce
window.wappointmentExtends = wappoExtend
export default Vue