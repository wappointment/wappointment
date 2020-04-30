import Vue from 'vue'
import axios from 'axios'
import wappoExtend from './Standalone/extends.js'
if(apiWappointment.nonce !== undefined) axios.defaults.headers.common['X-WP-Nonce'] = apiWappointment.nonce

window.wappointmentExtends = wappoExtend
window.wappoGet = function(name, from = 'commons'){
    let components_list = window.wappointmentExtends.get(from)
    if(typeof components_list !== 'object' || Object.keys(components_list).length ==0 ){
        throw "wappoGet: Collection is empty"
    }
    if([undefined,null,{}].indexOf(components_list[name]) !== -1){
        throw "wappoGet: Can't find component"
    }
    return components_list[name]
}

export default Vue