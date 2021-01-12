import axios from 'axios'
import eventsBus from '../../eventsBus'

class BaseService {
    constructor(service, options) {
        this.headers = {
            'Content-Type': 'application/json',
        }

        this.endpoints = service.endpoints
        this.suffix = service.suffix()
        this.base = options.base !== undefined ? options.base:'/'
        this.base += this.suffix
    }

    call(endpoint, params = {}, headers = {} ) {
        const endpoints = this.endpoints()
        if(endpoints[endpoint] === undefined){
            throw Error("Endpoint '"+endpoint+"' is undefined")
        }
        const endpointConfig = endpoints[endpoint]
        let append = ''
        if(params.append !== undefined){
            append = params.append
            delete params.append
            params = { ...params }
        }
        let endpointRoute = endpointConfig.route.toLowerCase() + append
        return this.request(endpointRoute, params, headers, endpointConfig.method,  endpointConfig.timeout)
    }

    request(route, data, headers, method, timeout = 10000) {

        let params = {
            method: method,
            baseURL: this.base ,
            timeout: timeout,
            responseType: 'json', 
            url: route,
            headers: Object.assign(this.headers, headers),
        }

        params = this.replaceModernVerbs(params)
        
        //weird distinction from axios config between GET and other requests
        if(method.toUpperCase() == 'GET' || this.headers['Content-Type'] == 'application/x-www-form-urlencoded')  params['params'] = data
        else  params['data'] = data
        eventsBus.emits('beforeRequest')
        return axios(params)
          .then(result => this.success(result))
    }

    replaceModernVerbs(params){
        //console.log('params.method',params.method)
        if(['put','patch','delete'].indexOf(params.method) !== -1){
            params.url += '/'+ params.method
            params.method = 'post'
        }
        return params
    }

    success(result) {

        if(result.data === null){
            throw 'Response is malformed'
        }
        result.data = typeof result.data != 'object' ? {} : result.data
        return result
    } 

}

export default BaseService