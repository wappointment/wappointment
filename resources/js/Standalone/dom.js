export default class Wel {

    constructor(element) {
        this.els = typeof element == 'string' ? document.querySelectorAll(element):[element]
    }

    click(func){
        this.attach('click', func)
    }
    mouseenter(func){
        this.attach('mouseenter', func)
    }
    mouseleave(func){
        this.attach('mouseleave', func)
    }
    mousedown(func){
        this.attach('mousedown', func)
    }
    submit(func){
        this.attach('submit', func)
    }
    attr(name,value){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].setAttribute(name, value)
        }
    }
    hide(){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].style.setProperty('display', 'none')
        }
    }
    show(){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].style.setProperty('display', 'default')
        }
    }
    tg(){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].style.setProperty('display', this.els[i].style.getPropertyValue('display') == 'none' ? 'default':'none' )
        }
    }
    tgClass(classa = 'd-block', classb = 'd-none'){
        for (let i = 0; i < this.els.length; i++) {
            if(this.els[i].classList.contains(classa)){
                this.els[i].classList.replace( classa, classb )
            }else{
                this.els[i].classList.replace( classb, classa )
            }
        }
    }

    toggleClass(classa = 'd-block'){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].classList.toggle(classa)
        }
    }

    addClass(classa){
        for (let i = 0; i < this.els.length; i++) {
            if(!this.els[i].classList.contains(classa)) this.els[i].classList.add(classa)
        }
    }
    
    html(html){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].innerHTML = html
        }
    }
    htmlConvert(callback){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].innerHTML = callback(this.els[i])
        }
    }
    val(){
        
        return this.els[0].value
    }

    attach(event = 'click', func){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].addEventListener(event, func)
        }
    }

    dettach(event = 'click', func){
        for (let i = 0; i < this.els.length; i++) {
            this.els[i].removeEventListener(event, func)
        }
    }

    serializeArray(){
        var form  = new FormData(this.els[0])
        var object = {}
        form.forEach((value, key) => {object[key] = value})
        return object
    }
    find(string){
        let elements = []
        console.log('find a',string)
        console.log('element abc',this.els.querySelectorAll(element))
        return this.els.querySelectorAll(element)
        for (let i = 0; i < this.els.length; i++) {
            console.log('find b ',string, this.els[i])
            found = this.els[i].querySelectorAll(element)
            console.log('find c',string)
            if(found.length){
                elements.push(found)
            }
        }
        console.log('elements', elements, string)
        return elements
        
    }
    
}
