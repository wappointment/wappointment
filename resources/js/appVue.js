import Vue from 'vue'
import axios from 'axios'
import wappoExtend from './Standalone/extends.js'
import __get from 'lodash/get'

if(apiWappointment.nonce !== undefined) axios.defaults.headers.common['X-WP-Nonce'] = apiWappointment.nonce

window.wappointmentExtends = wappoExtend
window.wappoGet = function(name, from = 'commons'){
    let components_list = window.wappointmentExtends.get(from)
    if(typeof components_list !== 'object' || Object.keys(components_list).length ==0 ){
        console.log('components_list',components_list)
        throw "wappoGet: Collection is empty "+ name+ " from " + from
    }
    if([undefined,null,{}].indexOf(components_list[name]) !== -1){
        throw "wappoGet: Can't find component "+ name+ " from " + from
    }
    return components_list[name]
}

Vue.mixin({
    methods: {
        __get,
        __isEmpty(value){
            return [undefined, false].indexOf(value) !== -1
        }
    }
});

export default Vue