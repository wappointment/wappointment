class Events {

    constructor() {
        this.listeners = {}
    }

    listens(eventName, callBack) {
        if(this.listeners[eventName] === undefined) this.listeners[eventName] = []
        this.listeners[eventName].push(callBack)
    }

    emits(eventName, params = {}){
        if(this.listeners[eventName] !== undefined) {
            for (let i = 0; i < this.listeners[eventName].length; i++) {
                this.listeners[eventName][i](params)
            }
        }
    }
}

export default new Events