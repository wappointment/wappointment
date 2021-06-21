export default {
    findKey: function(editionsSteps, keyFind){
        return editionsSteps.findIndex((element) => element.key == keyFind)
    },
    insertViewBefore: function(editionsSteps, keyFind, dataInsert){
        editionsSteps.splice(this.findKey(editionsSteps, keyFind), 0, dataInsert)
        return editionsSteps
    },
    removeView: function(editionsSteps, view){
        editionsSteps.splice(this.findKey(editionsSteps, view), 1)
        return editionsSteps
    },
    updateEditionsSteps: function(editionsSteps, inserts, removes){
        for (let o = 0; o < inserts.length; o++) {
            editionsSteps = this.insertViewBefore(editionsSteps, inserts[o].insertAt, inserts[o].insertStep)
        }
        for (let j = 0; j < removes.length; j++) {
            editionsSteps = this.removeView(editionsSteps, removes[j])
        }
        return editionsSteps
    }
};