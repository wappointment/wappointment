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
        for (const insertor of inserts) {
            editionsSteps = this.insertViewBefore(editionsSteps, insertor.insertAt, insertor.insertStep)
        }
        for (const remover of removes) {
            editionsSteps = this.removeView(editionsSteps, remover)
        }
        return editionsSteps
    }
};