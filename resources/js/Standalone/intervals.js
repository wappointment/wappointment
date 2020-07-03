export default class Intervals {

    constructor(intervals, intervalsPassed = false) {
        if(intervalsPassed === false){
            this.intervals = []
        
            for (let index = 0; index < intervals.length; index++) {
                const element = intervals[index]
                this.intervals.push( { start: element[0], end: element[1] } )
            }
        }else{
            this.intervals = intervals 
        }
    }  

    get(from, until) {
        let newCollection = []
        
        for (let index = 0; index < this.intervals.length; index++) {
             const element = this.intervals[index]


             //if there is an intersection we create a new interval
             /* if(today === true) {
                if(momenttz.unix(element.start).isBefore(from) && momenttz.unix(element.end).isSameOrBefore(until)) {
                    element.start = from.unix() // add time before booking is allowed
                    element.duration = element.end - element.start
                    newCollection.push(element)
                 }
             }
             // if is contained in the segment
             if(momenttz.unix(element.start).isSameOrAfter(from) && momenttz.unix(element.end).isSameOrBefore(until)) {
                element.duration = element.end - element.start
                newCollection.push(element)
             } */

             //if there is an intersection before or after in between two days
             let DummyInterval = {start: from.unix(), end: until.unix()}
             //console.log('get intervals DummyInterval',DummyInterval)
             if(this.intersecting(DummyInterval, element)) {
                //console.log('INTERSECTING', DummyInterval, element)
                if(this.aContainsB(DummyInterval,element)){
                    //console.log('a contains b', DummyInterval, element, {start:element.start, end:element.end, duration:(element.end - element.start),llave:'a'})

                    newCollection.push({start:element.start, end:element.end, duration:(element.end - element.start),llave:'a'})
                }else{
                    //console.log('aContainsB', false)
                    let newt = {}
                    if(DummyInterval.start > element.start){
                        if(DummyInterval.end < element.end){
                            newt = {start:DummyInterval.start, end:DummyInterval.end, duration:(DummyInterval.end - DummyInterval.start),llave:'b1'}
                        }else{
                            newt = {start:DummyInterval.start, end:element.end, duration:(element.end - DummyInterval.start),llave:'b2'}
                        }
                        
                    }else{
                        
                        
                        if(DummyInterval.end <= element.end){

                            newt = {start:element.start, end:DummyInterval.end, duration:(DummyInterval.end - element.start),llave:'c'}
                        }else{
                            newt = {start:element.start, end:element.end, duration:(element.end - element.start),llave:'c'}
                        }
                    }
                    

                    newCollection.push(newt)
                }
             }else{
                //console.log('intersecting', false)
             }

        }

        return new Intervals(newCollection, true)
    }

    substract(removeIntervals){
        
        for (let i = 0; i < this.intervals.length; i++) {
             const interval = this.intervals[i]
             if(Array.isArray(interval)){
                //console.log('interval is array', interval)
             }else{

                for (let j = 0; j < removeIntervals.intervals.length; j++) {
                    
                    const removeMe = removeIntervals.intervals[j]
                    //console.log('try to remove', removeMe,'from interval', interval)
                    if(this.intersecting(interval,removeMe)){
                        //console.log('a and b are intersecting', interval,removeMe)
                       if(this.aContainsB(interval,removeMe)){
                           //we get 2 intervals out
                           //console.log('a contains b', interval,removeMe)
                           this.intervals[i] = this.aMinusBWhenContaining(interval,removeMe)
                       }else{
                         
                           //we get just one interval so we can simply replace it for the next part
                           this.intervals[i] = this.aMinusBWhenIntersect(interval,removeMe)
                           //console.log('a shares a common part with b',this.intervals[i], interval,removeMe)
                       }
                    }else{
                        //console.log('a and b are NOT intersecting', interval,removeMe)
                    }
                }
             }
        }

        let newCollection = []
        for (let i = 0; i < this.intervals.length; i++) {
            const interval = this.intervals[i]
            if(Array.isArray(interval)){
                for (let j = 0; j < interval.length; j++) {
                    newCollection.push(interval[j])
                }
            }else{
                if(interval){
                    newCollection.push(interval)
                }
                
            }
       }

       return new Intervals(newCollection, true)
    }
    /**
     * Only when two intervals are intersecting, get the part left out
     * @param {Interval} a 
     * @param {Interval} b 
     */
    aMinusBWhenContaining(a,b){
        let collection = []
        collection.push({ start: a.start, end: b.start, duration: b.start - a.start })
        collection.push({ start: b.end, end: a.end, duration: b.end - a.end })
        return collection
    }
    /**
     * Only when two intervals are intersecting, get the part left out
     * @param {Interval} a 
     * @param {Interval} b 
     */
    aMinusBWhenIntersect(a,b){

        //in that case we remove the leading part of a
        if(a.start >= b.start ){
            if(a.end > b.end ){
                return { start: b.end, end: a.end , duration: a.end - b.end }
            }
            
        }else{ //in that case we remove the ending part of a
            if(a.end < b.end ){
                return { start: a.start, end: b.start, duration: b.start - a.start  }
            }
        }

        return false
    }
    //it intersects when it not intersects
    intersecting(a,b){
        return !(a.start >= b.end || a.end <= b.start)
    }
    aContainsB(a,b){
        return a.start < b.start && a.end > b.end
    }
    

    arrayToInterval(arrayPassed){
        return new Intervals(arrayPassed)
    }

    splits(slotsDuration) {
        let newCollection = []
        /* for (let index = this.intervals.length -1; index > -1; index--) {
            const element = this.intervals[index]
            element.slots = Math.floor(element.duration / slotsDuration)
            newCollection.push(element)
            
        }
        newCollection.reverse() */
        for (let index = 0; index < this.intervals.length; index++) {
             const element = this.intervals[index]
             element.slots = Math.floor(element.duration / slotsDuration)
             newCollection.push(element)

        }
        return new Intervals(newCollection, true)
    }

    totalSlots(){
        let total = 0;
        for (let index = 0; index < this.intervals.length; index++) {
            if(this.intervals[index].slots !== undefined) {
                total += this.intervals[index].slots
            }
       }
       return total;
    }

}
