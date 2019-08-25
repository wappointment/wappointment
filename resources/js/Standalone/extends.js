import clone from 'lodash/clone';
//import externalComponent from '../Standalone/external'

class Extends {

    constructor() {
        this.callbacks = {}
        this.baseUrl = window.apiWappointment.baseUrl
    }

    add(extendName, callBack) {
        if(this.callbacks[extendName] === undefined) this.callbacks[extendName] = []
        this.callbacks[extendName].push(callBack)
    }

    filter(extendName, extendParams, extraParams){
        let paramsNew = clone(extendParams)

        if(this.callbacks[extendName] !== undefined) {
            for (let i = 0; i < this.callbacks[extendName].length; i++) {
                paramsNew = this.callbacks[extendName][i](paramsNew, extraParams)
            }
        }
        return paramsNew
    }

   /* loadComponent(name,componentFile){
        return externalComponent(name, this.baseUrl+componentFile)
    }*/
}

export default new Extends