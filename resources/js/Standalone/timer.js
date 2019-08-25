export default class Timer {

    constructor(callback, delay = 0) {
        this.timerId = null
        this.start = 0
        this.callback = callback
        this.remaining = delay
        this.resume()
    }
   
    pause(){
        clearTimeout(this.timerId);
        this.remaining -= new Date() - this.start;
    }

    resume(){
        this.start = new Date();
        if(this.timerId !== null){
            clearTimeout(this.timerId);
        } 
        this.timerId = setTimeout(this.callback, this.remaining);
    }

}