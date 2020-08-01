import clone from 'lodash/clone'
//import externalComponent from '../Standalone/external'

class Extends {

    constructor() {
        this.callbacks = {}
        this.baseUrl = window.apiWappointment.baseUrl // TODO probably no need for that line
        this.storage = {}
    }

    add(extendName, callBack) {
        if(this.callbacks[extendName] === undefined) this.callbacks[extendName] = []
        this.callbacks[extendName].push(callBack)
    }
    

    filter(extendName, extendParams, extraParams, cloning = true){
        
        if(this.callbacks[extendName] !== undefined) {
            let paramsNew = cloning? clone(extendParams):extendParams
            for (let i = 0; i < this.callbacks[extendName].length; i++) {
                paramsNew = this.callbacks[extendName][i](paramsNew, extraParams)
            }
            return paramsNew
        }else{
            return extendParams
        }
        
    }
    store(extendName, objects){
        this.storage[extendName] = objects
    }
    get(extendName) {
        return this.storage[extendName]
    }
}

export default new Extends