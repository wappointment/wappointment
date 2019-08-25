import momenttz from '../appMoment'
export default class Intervals {

    constructor(intervals, noparsing = false) {
        if(noparsing === false){
            this.intervals = []
        
            for (let index = 0; index < intervals.length; index++) {
                const element = intervals[index]
                this.intervals.push( { start: element[0], end: element[1] } )
            }
        }else{
            this.intervals = intervals 
        }
    }  

    get(from, until, today=false) {
        let newCollection = []
        for (let index = 0; index < this.intervals.length; index++) {
             const element = this.intervals[index]

             
             //if there is an intersection we create a new interval
             if(today === true) {
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
             }

             //if there is an intersection before or
             /* if(momenttz.unix(element.start).isBefore(from) && momenttz.unix(element.end).isSameOrBefore(until)) {
                element.start = from.unix() // add time before booking is allowed
                element.duration = element.end - element.start
                newCollection.push(element)
             } */
             

        }
        return new Intervals(newCollection, true)
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
